<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2025 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2025 PagBank - https://pagbank.com.br
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
	public $payment_type;

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
		if ((int)Configuration::get('PAGBANK_DISCOUNT_WALLET') == 1) {
			$discount_options[] = 'wallet';
		}
		if ((int)Configuration::get('PAGBANK_DISCOUNT_GOOGLE') == 1) {
			$discount_options[] = 'google_pay';
		}

		$this->payment_type = Tools::getValue('pagbank_type');

		if ($this->payment_type == "credit_card") {
			$this->pag_response = $this->processCreditCard();
		} elseif ($this->payment_type == "bankslip") {
			$this->pag_response = $this->processBankSlip();
		} elseif ($this->payment_type == "pix") {
			$this->pag_response = $this->processPix();
		} elseif ($this->payment_type == "wallet") {
			$this->pag_response = $this->processWallet();
		} elseif ($this->payment_type == "google_pay") {
			$this->pag_response = $this->processGooglePay();
		}

		if (isset($this->pag_response->error_messages[0]) && $this->pag_response->error_messages[0]) {
			$pagbank_msg = 'Houve um erro ao processar seu pagamento.<br />Por favor, revise seus dados e tente novamente.';
			if (_PS_VERSION_ >= '1.7.0') {
				$this->redirect_link = 'index.php?controller=order&step=3';
			} else {
				if(Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc'){
					$this->redirect_link = 'index.php?controller=order-opc&isPaymentStep=true';
				} else {
					$this->redirect_link = 'index.php?controller=order&step=3';
				}
			}
			$this->context->cookie->pagbank_msg = $pagbank_msg;
			Tools::redirectLink($this->redirect_link);
		} elseif (isset($this->pag_response->charges) && $this->pag_response->charges[0]->status == 'DECLINED') {
			$pagbank_msg = 'Pagamento não autorizado.<br />Motivo: '.$this->pag_response->charges[0]->payment_response->message.'<br />(Código: '.$this->pag_response->charges[0]->payment_response->code.')';
			if (_PS_VERSION_ >= '1.7.0') {
				$this->redirect_link = 'index.php?controller=order&step=3';
			} else {
				if(Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc'){
					$this->redirect_link = 'index.php?controller=order-opc&isPaymentStep=true';
				} else {
					$this->redirect_link = 'index.php?controller=order&step=3';
				}
			}
			$this->context->cookie->pagbank_msg = $pagbank_msg;
			Tools::redirectLink($this->redirect_link);
		} else {
			$this->pag_data['id'] = $this->pag_response->id;
			$this->pag_data['reference_id'] = $this->pag_response->reference_id;
			$this->pag_data['create_date'] = $this->pag_response->created_at;
			$this->pag_data['tax_id'] = $this->pag_response->customer->tax_id;

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

				if ($payment_method->type == 'CREDIT_CARD' && !isset($payment_method->card->wallet)) {
					$this->payment_option = "Cartão de Crédito " . strtoupper($card->brand) . " (Final: " . $card->last_digits . ") - PagBank";
					$this->payment_type = 'CREDIT_CARD';
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
					$this->payment_type = 'BOLETO';
					if (
						in_array('bankslip', $discount_options) &&
						Configuration::get('PAGBANK_DISCOUNT_TYPE') >= 1 &&
						Configuration::get('PAGBANK_DISCOUNT_VALUE') >= 1
					) {
						$this->module->generateCartRule($this->context->cart);
					}
				} elseif ($payment_method->type == 'CREDIT_CARD' 
						&& isset($payment_method->card->wallet)
						&& $payment_method->card->wallet->type == 'GOOGLE_PAY'
						) {
					$this->payment_option = "Google Pay " . strtoupper($card->brand) . " (Final: " . $card->last_digits . ") - PagBank";
					$this->payment_type = "GOOGLE_PAY";
					if (
						in_array('google_pay', $discount_options) &&
						Configuration::get('PAGBANK_DISCOUNT_TYPE') >= 1 &&
						Configuration::get('PAGBANK_DISCOUNT_VALUE') >= 1 &&
						isset($this->pag_data['installments'])
					) {
						if ($this->pag_data['installments'] == 1) {
							$this->module->generateCartRule($this->context->cart);
						}
					}
				}
				foreach ($payment->links as $pay_link) {
					if ($pay_link->media == 'application/pdf') {
						$this->pag_data['payment_link'] = $pay_link->href;
					}
				}
			} else {
				$this->status = 'WAITING';
				if (isset($this->pag_response->qr_codes) && $this->pag_response->qr_codes[0]->arrangements[0] == 'PIX') {
					$this->payment_option = "Pix - PagBank";
					$this->payment_type = "PIX";
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
				} elseif (isset($this->pag_response->qr_codes) && $this->pag_response->qr_codes[0]->arrangements[0] == 'PAGBANK' ||
				isset($this->pag_response->deep_links) && $this->pag_response->deep_links[0]->url) {
					$this->payment_option = "Pagar com PagBank";
					$this->payment_type = 'WALLET';
					if (
						in_array('wallet', $discount_options) &&
						Configuration::get('PAGBANK_DISCOUNT_TYPE') >= 1 &&
						Configuration::get('PAGBANK_DISCOUNT_VALUE') >= 1
					) {
						$this->module->generateCartRule($this->context->cart);
					}
					if(isset($this->pag_response->qr_codes) && $this->pag_response->qr_codes[0]->arrangements[0] == 'PAGBANK') {
						foreach ($this->pag_response->qr_codes[0]->links as $link) {
							if ($link->media == 'image/png') {
								$this->pag_data['payment_link'] = $link->href;
							}
						}
					} else {
						$this->pag_data['payment_link'] = $this->pag_response->deep_links[0]->url;
					}
				}
			}
			$this->cart_id = (int)$this->context->cart->id;
			$secure_key = $this->context->customer->secure_key;
			$order_total = (float)$this->context->cart->getOrderTotal(true, Cart::BOTH);

			if (!$this->module->validateOrder($this->cart_id, Configuration::get('_PS_OS_PAGBANK_0'), $order_total, $this->payment_option, NULL, [], $this->context->currency->id, false, $secure_key)) {
				$this->module->saveLog('error', 'Criar Pedido', $this->cart_id, json_encode($this->pag_response), 'Erro ao criar pedido inicial.');
			} else {
				$this->id_order = $this->module->currentOrder;
				$this->current_order = new Order((int)$this->id_order);
			}
			if ($this->current_order !== false) {
				$this->module->updateOrderStatus($this->status, (int)$this->id_order);

				$this->redirect_link = 'index.php?controller=order-confirmation&id_cart=' . $this->cart_id . '&id_module=' . $this->module->id . '&id_order=' . $this->id_order . '&key=' . $this->context->cart->secure_key;
				$this->redirectFinal();
			}
		}
	}

	/*
	* Processa Cartão de Crédito
	*/
	public function processCreditCard()
	{
		$form_data = array(
			'payment_type' => 'credit_card',
			'card_installment_value' => Tools::getValue('card_installment_value'),
			'card_installments' => Tools::getValue('card_installments'),
			'get_installments_fees' => Tools::getValue('get_installments_fees'),
			'save_customer_card' => Tools::getValue('save_customer_card'),
			'saved_card' => Tools::getValue('saved_card'),
			'card_name' => Tools::getValue('card_name'),
			'card_bin' => Tools::getValue('card_bin'),
			'card_brand' => Tools::getValue('card_brand'),
			'encrypted_card' => Tools::getValue('encrypted_card'),
			'card_token_id' => Tools::getValue('card_token_id'),
			'card_installment_qty' => Tools::getValue('card_installment_qty'),
			'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
			'telephone' => Tools::getValue('telephone'),
			'invoice_postcode' => Tools::getValue('postcode_invoice'),
			'invoice_address' => Tools::getValue('address_invoice'),
			'invoice_number' => Tools::getValue('number_invoice'),
			'invoice_complement' => Tools::getValue('other_invoice'),
			'invoice_district' => Tools::getValue('address2_invoice'),
			'invoice_city' => Tools::getValue('city_invoice'),
			'invoice_state' => Tools::getValue('state_invoice'),
		);
		$api_response = $this->module->processCardPayment($form_data);

		return $api_response['response'];
	}

	/*
	* Processa Google Pay
	*/
	public function processGooglePay()
	{
		$form_data = array(
			'payment_type' => 'google_pay',
			'google_installment_value' => Tools::getValue('google_installment_value'),
			'google_installments' => Tools::getValue('google_installments'),
			'google_get_installments_fees' => Tools::getValue('google_get_installments_fees'),
			'google_card_brand' => Tools::getValue('google_card_brand'),
			'google_card_bin' => Tools::getValue('google_card_bin'),
			'google_last_digits' => Tools::getValue('google_last_digits'),
			'google_signature' => Tools::getValue('google_signature'),
			'google_name' => Tools::getValue('google_name'),
			'google_card_installment_qty' => Tools::getValue('google_card_installment_qty'),
			'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
			'telephone' => Tools::getValue('telephone'),
			'invoice_postcode' => Tools::getValue('postcode_invoice'),
			'invoice_address' => Tools::getValue('address_invoice'),
			'invoice_number' => Tools::getValue('number_invoice'),
			'invoice_complement' => Tools::getValue('other_invoice'),
			'invoice_district' => Tools::getValue('address2_invoice'),
			'invoice_city' => Tools::getValue('city_invoice'),
			'invoice_state' => Tools::getValue('state_invoice'),
		);
		$api_response = $this->module->processCardPayment($form_data);

		return $api_response['response'];
	}

	/*
	* Processa PIX
	*/
	public function processPix()
	{
		$form_data = array(
			'payment_type' => 'pix',
			'pix_name' => Tools::getValue('pix_name'),
			'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
			'telephone' => Tools::getValue('telephone'),
			'invoice_postcode' => Tools::getValue('postcode_invoice'),
			'invoice_address' => Tools::getValue('address_invoice'),
			'invoice_number' => Tools::getValue('number_invoice'),
			'invoice_complement' => Tools::getValue('other_invoice'),
			'invoice_district' => Tools::getValue('address2_invoice'),
			'invoice_city' => Tools::getValue('city_invoice'),
			'invoice_state' => Tools::getValue('state_invoice'),
		);
		$api_response = $this->module->processPixPayment($form_data);

		return $api_response['response'];
	}

	/*
	* Processa Boleto
	*/
	public function processBankSlip()
	{
		$form_data = array(
			'payment_type' => 'bankslip',
			'bankslip_name' => Tools::getValue('bankslip_name'),
			'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
			'telephone' => Tools::getValue('telephone'),
			'invoice_postcode' => Tools::getValue('postcode_invoice'),
			'invoice_address' => Tools::getValue('address_invoice'),
			'invoice_number' => Tools::getValue('number_invoice'),
			'invoice_complement' => Tools::getValue('other_invoice'),
			'invoice_district' => Tools::getValue('address2_invoice'),
			'invoice_city' => Tools::getValue('city_invoice'),
			'invoice_state' => Tools::getValue('state_invoice'),
		);
		$api_response = $this->module->processBankSlipPayment($form_data);

		return $api_response['response'];
	}

	/*
	* Processa Wallet
	*/
	public function processWallet()
	{
		$form_data = array(
			'payment_type' => 'wallet',
			'wallet_name' => Tools::getValue('wallet_name'),
			'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
			'telephone' => Tools::getValue('telephone'),
			'invoice_postcode' => Tools::getValue('postcode_invoice'),
			'invoice_address' => Tools::getValue('address_invoice'),
			'invoice_number' => Tools::getValue('number_invoice'),
			'invoice_complement' => Tools::getValue('other_invoice'),
			'invoice_district' => Tools::getValue('address2_invoice'),
			'invoice_city' => Tools::getValue('city_invoice'),
			'invoice_state' => Tools::getValue('state_invoice'),
		);
		$api_response = $this->module->processWalletPayment($form_data);

		return $api_response['response'];
	}

	/*
	 * Redirecionamento do usuário 
	 * Confirmação do pedido ou Carrinho
	 */
	public function redirectFinal()
	{
		$insert_data = array(
			"id_customer" => $this->context->cart->id_customer,
			"cpf_cnpj" => $this->pag_data['tax_id'],
			"id_cart" => (int)$this->context->cart->id,
			"id_order" => (int)$this->id_order,
			"reference" => $this->pag_data['reference_id'],
			"transaction_code" => (string)$this->pag_data['id'],
			"buyer_ip" => $this->module->getUserIp(),
			"status" => $this->status,
			"status_description" => $this->module->parseStatus($this->status),
			"payment_type" => $this->payment_type,
			"payment_description" => $this->payment_option,
			"installments" => isset($this->pag_data['installments']) && (int)$this->pag_data['installments'] > 0 ? $this->pag_data['installments'] : 1,
			"nsu" => isset($this->pag_data['nsu']) && $this->pag_data['nsu'] != '' ? (string)$this->pag_data['nsu'] : '',
			"url" => isset($this->pag_data['payment_link']) && $this->pag_data['payment_link'] != '' ? (string)$this->pag_data['payment_link'] : '',
			"date_add" => $this->pag_data['create_date']
		);

		$bd = $this->module->insertPagBankData($insert_data);
		if (!$bd) {
			$this->module->saveLog('error', 'Inserir Dados', $this->context->cart->id, json_encode($this->pag_response), 'Banco de Dados não atualizado.');
		}
		Tools::redirectLink($this->redirect_link);
	}
}
