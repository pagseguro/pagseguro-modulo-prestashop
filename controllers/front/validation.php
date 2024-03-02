<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author
 * 2011-2024 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2024 PagBank - https://pagseguro.uol.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

class PagBankValidationModuleFrontController extends ModuleFrontController
{
	public $redirect_link;
	public $pag_response;
	public $payment_option;
	public $status;
	public $id_order;
	public $current_order = false;
	public $cart_id;
	public $pag_data = array();

	public function postProcess()
	{
		if ($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 || $this->context->cart->id_address_invoice == 0 || !$this->module->active) {
			Tools::redirect('index.php?controller=order&step=1');
		}
		$authorized = false;
		foreach (Module::getPaymentModules() as $module) {
			if ($module['name'] == 'pagbank') {
				$authorized = true;
				break;
			}
		}
		if (!$authorized) {
			die($this->module->l('This payment method is not available.', 'validation'));
		}

		if ($this->current_order !== false) {
			return;
		}

		$connect_response = Tools::getValue('pagbank_response');
		if ($connect_response == false || $connect_response == '') {
			return false;
		}
		$this->pag_response = json_decode($connect_response);
		$this->pag_data['id'] = $this->pag_response->id;
		$this->pag_data['reference_id'] = $this->pag_response->reference_id;
		$this->pag_data['create_date'] = $this->pag_response->created_at;
		$this->pag_data['tax_id'] = $this->pag_response->customer->tax_id;

		$discount_options = [];
		if ((int)Configuration::get('PAGBANK_DISCOUNT_CREDIT') == 1) {
			$discount_options[] = 'credit_card';
		}
		if ((int)Configuration::get('PAGBANK_DISCOUNT_BANKSLIP') == 1) {
			$discount_options[] = 'bankslip';
		}
		if ((int)Configuration::get('PAGBANK_DISCOUNT_PIX') == 1) {
			$discount_options[] = 'pix';
		}

		//Charges
		if (isset($this->pag_response->charges)) {
			$payment = end($this->pag_response->charges);
			$this->status = $payment->status;
			$this->pag_data['payment_value'] = $payment->amount->value;
			$this->pag_data['payment_value_paid'] = $payment->amount->summary->paid;
			$this->pag_data['payment_response'] = $payment->payment_response;
			$payment_method = $payment->payment_method;
			$this->pag_data['payment_method'] = $payment->payment_method;
			$this->pag_data['installments'] = isset($payment->payment_method->installments) ? $payment->payment_method->installments : false;
			$this->pag_data['nsu'] = isset($payment->payment_response->raw_data->nsu) ? $payment->payment_response->raw_data->nsu : false;
			$card = isset($payment->payment_method->card) && is_object($payment->payment_method->card) ? $payment->payment_method->card : false;
			$this->pag_data['card'] = $card;
			$boleto = isset($payment->payment_method->boleto) && is_object($payment->payment_method->boleto) ? $payment->payment_method->boleto : false;
			$this->pag_data['boleto'] = $boleto;

			//Parse Payment Option
			if ($payment_method->type == 'CREDIT_CARD') {
				$this->payment_option = "Cartão de Crédito " . strtoupper($card->brand) . " (Final: " . $card->last_digits . ") - PagBank";
				if (
					in_array('credit_card', $discount_options) &&
					Configuration::get('PAGBANK_DISCOUNT_TYPE') >= 1 &&
					Configuration::get('PAGBANK_DISCOUNT_VALUE') >= 1 &&
					isset($this->pag_data['installments'])
				) {
					if ($this->pag_data['installments'] == 1) {
						$this->module->generateCartRule($this->context->cart);
					}
				}
			} elseif ($payment_method->type == 'BOLETO') {
				$this->payment_option = "Boleto Bancário - PagBank";
				if (
					in_array('bankslip', $discount_options) &&
					Configuration::get('PAGBANK_DISCOUNT_TYPE') >= 1 &&
					Configuration::get('PAGBANK_DISCOUNT_VALUE') >= 1
				) {
					$this->module->generateCartRule($this->context->cart);
				}
			}
			foreach ($payment->links as $pay_link) {
				if ($pay_link->media == 'application/pdf') {
					$this->pag_data['payment_link'] = $pay_link->href;
				}
			}
			//PIX
		} else if (isset($this->pag_response->qr_codes)) {
			$this->payment_option = "PIX - PagBank";
			if (
				in_array('pix', $discount_options) &&
				Configuration::get('PAGBANK_DISCOUNT_TYPE') >= 1 &&
				Configuration::get('PAGBANK_DISCOUNT_VALUE') >= 1
			) {
				$this->module->generateCartRule($this->context->cart);
			}
			foreach ($this->pag_response->qr_codes[0]->links as $link) {
				if ($link->media == 'image/png') {
					$this->pag_data['payment_link'] = $link->href;
				}
			}
		}
		$this->cart_id = (int)$this->context->cart->id;
		$secure_key = $this->context->customer->secure_key;
		$order_total = (float)$this->context->cart->getOrderTotal(true, Cart::BOTH);

		//Create Order
		if (!$this->module->validateOrder($this->cart_id, Configuration::get('_PS_OS_PAGBANK_0'), $order_total, $this->payment_option, NULL, [], $this->context->currency->id, false, $secure_key)) {
			$this->module->saveLog('error', 'Criar Pedido', $this->cart_id, json_encode($this->pag_response), 'Erro ao criar pedido inicial.');
		} else {
			$this->id_order = $this->module->currentOrder;
			$this->current_order = new Order((int)$this->id_order);
		}
		if ($this->current_order !== false) {
			sleep(2);

			if (isset($this->pag_response->qr_codes)) {
				$this->status = 'WAITING';
			}

			$this->module->updateOrderStatus($this->cart_id, $this->status, (int)$this->id_order);

			$this->redirect_link = 'index.php?controller=order-confirmation&id_cart=' . $this->cart_id . '&id_module=' . $this->module->id . '&id_order=' . $this->id_order . '&key=' . $this->context->cart->secure_key;
			$this->redirectFinal();
		}
	}

	/*
	 * Redirecionamento do usuário 
	 * Confirmação do pedido ou Carrinho
	 */
	public function redirectFinal()
	{
		// Charges
		if (isset($this->pag_response->charges)) {
			$payment = end($this->pag_response->charges);
			$payment_method = $payment->payment_method;
			$payment_type = $payment_method->type;
		} else {
			$payment_type = "PIX";
		}

		$id_cart = (int)$this->context->cart->id;
		if (!$id_cart || (int)$id_cart < 1) {
			$id_cart = explode('.', $this->pag_data['reference_id'])[0];
		}
		$cart = new Cart((int)$id_cart);
		$id_order = (int)$this->id_order;
		if (!$id_order || (int)$id_order < 1) {
			$id_order = Order::getOrderByCartId($id_cart);
		}
		$id_customer = $this->context->cart->id_customer;
		if (!$id_customer || (int)$id_customer < 1) {
			$id_customer = $cart->id_customer;
		}
		$credential_type = Configuration::get('PAGBANK_CREDENTIAL');
		if ($credential_type == 'TAX' || !$credential_type || $credential_type == '') {
			$token_code = Configuration::get('PAGBANK_TOKEN_TAX');
		} elseif ($credential_type == 'D14') {
			$token_code = Configuration::get('PAGBANK_AUTHCODE_D14');
		} elseif ($credential_type == 'D30') {
			$token_code = Configuration::get('PAGBANK_AUTHCODE_D30');
		}
		$installments_qty = (int)Tools::getValue('ps_card_installments');
		$insert_data = array(
			"id_shop" => (int)$this->context->shop->id,
			"id_customer" => (int)$id_customer,
			"cpf_cnpj" => $this->pag_data['tax_id'],
			"id_cart" => (int)$id_cart,
			"id_order" => (int)$id_order,
			"reference" => $this->pag_data['reference_id'],
			"transaction_code" => (string)$this->pag_data['id'],
			"buyer_ip" => $this->module->getUserIp(),
			"status" => $this->status,
			"status_description" => $this->module->parseStatus($this->status),
			"payment_type" => $payment_type,
			"payment_description" => $this->payment_option,
			"installments" => isset($this->pag_data['installments']) && (int)$this->pag_data['installments'] > 0 ? $this->pag_data['installments'] : 1,
			"nsu" => isset($this->pag_data['nsu']) && $this->pag_data['nsu'] != '' ? (string)$this->pag_data['nsu'] : '',
			"url" => isset($this->pag_data['payment_link']) && $this->pag_data['payment_link'] != '' ? (string)$this->pag_data['payment_link'] : '',
			"credential" => $credential_type,
			"token_code" => $token_code,
			"date_add" => $this->pag_data['create_date'],
			"date_upd" => date("Y-m-d H:i:s"),
		);
		$bd = $this->module->insertPagBankData($insert_data);
		if (!$bd) {
			$this->module->saveLog('error', 'Inserir Dados', $this->context->cart->id, json_encode($this->pag_response), 'Banco de Dados não atualizado.');
			echo "<font color=\"red\"><b>Banco de Dados não atualizado!</b></font><br/><br/>";
		}
		if (Configuration::get('PAGBANK_ENVIRONMENT') == 0) {
			echo "<br/><br/><h3 style='color:red'><b>Iniciando debug...</b><br/><br/><b>Sistema executando em modo de TESTES!</b></h3><br/><br/>";
			var_dump($insert_data);
			var_dump($this->current_order);
			if (!$bd) {
				echo "<font color=\"red\"><b>Banco de Dados não atualizado!</b></font><br/><br/>";
			}
			echo "<font color=\"red\"><b>Finalizando debug!</b></font><br/><br/>";
			echo "<a class='btn btn-lg' href='" . Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . $this->redirect_link . "'>Clique aqui e verifique se a página final do pedido será exibida corretamente</a><br/>";
		} else {
			Tools::redirectLink($this->redirect_link);
		}
	}
}
