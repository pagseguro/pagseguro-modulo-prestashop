<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
 * Pagamento com Cartão de Crédito, Google Pay, Pix, Boleto e Pagar com PagBank
 * 
 * @author
 * 2011-2026 PrestaBR - https://prestabr.com.br
 * 
 * @copyright
 * 1996-2026 PagBank - https://pagbank.com.br
 * 
 * @license
 * Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

class PagBankValidationModuleFrontController extends ModuleFrontController
{
	public $redirect_link;
	public $pag_response;
	public $pag_response_two;
	public $payment_option;
	public $id_order;
	public $current_order = false;
	public $cart_id;
	public $pag_data = array();
	public $payment_type;

	public function postProcess()
	{
		if ($this->context->cart->id_customer == 0 || 
			$this->context->cart->id_address_delivery == 0 || 
			$this->context->cart->id_address_invoice == 0 || 
			!$this->module->active ||
			!$this->context->cart->id ||
			$this->context->cart->id === 'NULL'
			) {
			Tools::redirect('index.php?controller=order&step=1');
		}
		$authorized = false;
		foreach (Module::getPaymentModules() as $module) {
			if ($module['name'] === 'pagbank') {
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
		
		$methods = ["credit_card", "bankslip", "pix", "wallet", "google_pay"];
		$this->payment_type = Tools::getValue('payment_type');
		if (!in_array($this->payment_type, $methods)) {
			$pagbank_msg = 'Houve um erro ao processar seu pagamento.<br />Por favor, revise seus dados e tente novamente.';
			$this->context->cookie->pagbank_msg = $pagbank_msg;
			return Tools::redirect($this->checkRedirectLink());
		}

		if ((int)Configuration::get('PAGBANK_RECAPTCHA') == 1 && strlen(Configuration::get('PAGBANK_RECAPTCHA_API_KEY')) >= 32) {
			$recaptcha_token = Tools::getValue('recaptcha_'.$this->payment_type);
			$recaptcha_response = $this->module->processRecaptcha($recaptcha_token);
			if (empty($recaptcha_token) || isset($recaptcha_response['response']->error) || $recaptcha_response === false) {
				$pagbank_msg = 'Antes de processar o seu pagamento, não foi possível verificar a sua identidade com segurança (reCAPTCHA). Se você for um usuário real, por favor, recarregue a página e tente novamente.';
				$this->context->cookie->pagbank_msg = $pagbank_msg;
				return Tools::redirect($this->checkRedirectLink());
			}
		}

		if ($this->payment_type === 'credit_card') {
			$this->pag_response = $this->processCreditCard();
		} elseif ($this->payment_type === 'bankslip') {
			$this->pag_response = $this->processBankSlip();
		} elseif ($this->payment_type === 'pix') {
			$this->pag_response = $this->processPix();
		} elseif ($this->payment_type === 'wallet') {
			$this->pag_response = $this->processWallet();
		} elseif ($this->payment_type === 'google_pay') {
			$this->pag_response = $this->processGooglePay();
		}

		if (empty($this->pag_response) || $this->pag_response === false) {

			$pagbank_msg = 'Houve um erro ao processar seu pagamento.<br />Por favor, revise seus dados e tente novamente.';
			$this->context->cookie->pagbank_msg = $pagbank_msg;
			return Tools::redirect($this->checkRedirectLink());

		} elseif (isset($this->pag_response->charges) && $this->pag_response->charges[0]->status === 'DECLINED') {

			if ($this->pag_response->charges[0]->payment_method->type === 'BOLETO') {
				$api_message = "Não foi possível autorizar a emissão do Boleto Bancário. Por favor, tente novamente utilizando outro meio de pagamento.";
			} else {
				$api_message = $this->pag_response->charges[0]->payment_response->message;
			}
			if ($this->pag_response->charges[0]->payment_method->type === 'CREDIT_CARD') {
				$last_digits = '<br /><br />Cartão final: '.$this->pag_response->charges[0]->payment_method->card->last_digits;
			} else {
				$last_digits = '';
			}
			$pagbank_msg = 'Pagamento não autorizado'.$last_digits.'<br />Motivo: '.$api_message.' (Código: '.$this->pag_response->charges[0]->payment_response->code.')';
			$this->context->cookie->pagbank_msg = $pagbank_msg;
			return Tools::redirect($this->checkRedirectLink());

		} elseif (!isset($this->pag_response->charges) && $this->payment_type === 'credit_card') {

			$this->pag_response_two = $this->module->processTwoCharges($this->pag_response->id, $this->processCreditCard(true));

			if (empty($this->pag_response_two) || $this->pag_response_two === false) {

				$pagbank_msg = 'Houve um erro ao processar seu pagamento.<br />Por favor, revise seus dados e tente novamente.';
				$this->context->cookie->pagbank_msg = $pagbank_msg;
				return Tools::redirect($this->checkRedirectLink());

			} else {

				$resp_two_charges = $this->pag_response_two['response_two']['response']->charges;
				$last_digits_one = $resp_two_charges[0]->payment_method->card->last_digits;
				$last_digits_two = $resp_two_charges[1]->payment_method->card->last_digits;
				$brand_one = $resp_two_charges[0]->payment_method->card->brand;
				$brand_two = $resp_two_charges[1]->payment_method->card->brand;
				$validate_charges = $this->module->validateTwoCharges($resp_two_charges);

				if ($validate_charges['declined']){
					if ($validate_charges['declined'] == 1) {
						$pagbank_msg = 'Pagamento não autorizado - Cartão final: '.$validate_charges['last_digits'].'<br />Motivo: '.$validate_charges['message'].' (Código: '.$validate_charges['code'].')';
					} else {
						$pagbank_msg = 'Pagamento não autorizado<br /><br />
						Cartão 1 final: '.$last_digits_one.'<br />
						Motivo: '.$resp_two_charges[0]->payment_response->message.' (Código: '.$resp_two_charges[0]->payment_response->code.')<br /><br />
						Cartão 2 final: '.$last_digits_two.'<br />
						Motivo: '.$resp_two_charges[1]->payment_response->message.' (Código: '.$resp_two_charges[1]->payment_response->code.')';
					}
					$this->context->cookie->pagbank_msg = $pagbank_msg;
					return Tools::redirect($this->checkRedirectLink());
				}

			}

		}

		$this->pag_data['id'] = $this->pag_response->id;
		$this->pag_data['reference_id'] = $this->pag_response->reference_id;
		$this->pag_data['create_date'] = $this->pag_response->created_at;
		$this->pag_data['tax_id'] = $this->pag_response->customer->tax_id;

		if (isset($resp_two_charges) && isset($validate_charges)) {

			$this->pag_data['status'] = $validate_charges['current_status'];
			$this->pag_data['installments'] = $resp_two_charges[0]->payment_method->installments;
			$this->pag_data['installments_two'] = $resp_two_charges[1]->payment_method->installments;
			$this->pag_data['nsu'] = $resp_two_charges[0]->payment_response->raw_data->nsu;
			$this->pag_data['nsu_two'] = $resp_two_charges[1]->payment_response->raw_data->nsu;
			$this->payment_option = "Cartão de Crédito " . strtoupper($brand_one) . "/" . strtoupper($brand_two) . " (Final: " . $last_digits_one . "/" . $last_digits_two . ") - PagBank";
			$this->payment_type = 'CREDIT_CARD';

		} elseif (isset($this->pag_response->charges)) {
			
			$payment = end($this->pag_response->charges);
			$payment_method = $payment->payment_method;
			$card = isset($payment->payment_method->card) && is_object($payment->payment_method->card) ? $payment->payment_method->card : false;
			$this->pag_data['status'] = $payment->status;
			$this->pag_data['installments'] = isset($payment->payment_method->installments) ? $payment->payment_method->installments : false;
			$this->pag_data['nsu'] = isset($payment->payment_response->raw_data->nsu) ? $payment->payment_response->raw_data->nsu : false;

			if ($payment_method->type === 'CREDIT_CARD' && !isset($payment_method->card->wallet)) {
				$this->payment_option = "Cartão de Crédito " . strtoupper($card->brand) . " (Final: " . $card->last_digits . ") - PagBank";
				$this->payment_type = 'CREDIT_CARD';
			} elseif ($payment_method->type === 'BOLETO') {
				$this->payment_option = "Boleto Bancário - PagBank";
				$this->payment_type = 'BOLETO';
			} elseif ($payment_method->type === 'CREDIT_CARD' && isset($payment_method->card->wallet)
					&& $payment_method->card->wallet->type === 'GOOGLE_PAY') {
				$this->payment_option = "Google Pay " . strtoupper($card->brand) . " (Final: " . $card->last_digits . ") - PagBank";
				$this->payment_type = "GOOGLE_PAY";
			}
			foreach ($payment->links as $pay_link) {
				if ($pay_link->media === 'application/pdf') {
					$this->pag_data['payment_link'] = $pay_link->href;
				}
			}

		} else {

			$this->pag_data['status'] = 'WAITING';
			if (isset($this->pag_response->qr_codes) && $this->pag_response->qr_codes[0]->arrangements[0] === 'PIX') {
				$this->payment_option = "Pix - PagBank";
				$this->payment_type = "PIX";

			} elseif (isset($this->pag_response->qr_codes) && $this->pag_response->qr_codes[0]->arrangements[0] === 'PAGBANK' ||
					isset($this->pag_response->deep_links) && $this->pag_response->deep_links[0]->url) {
				$this->payment_option = "Pagar com PagBank";
				$this->payment_type = 'WALLET';
			}
			if(isset($this->pag_response->deep_links) && $this->pag_response->deep_links[0]->url) {
				$this->pag_data['payment_link'] = $this->pag_response->deep_links[0]->url;
			} else {
				foreach ($this->pag_response->qr_codes[0]->links as $link) {
					if ($link->media === 'image/png') {
						$this->pag_data['payment_link'] = $link->href;
					}
				}
			}

		}

		$this->validateDiscount($this->payment_type);
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
			$this->module->updateOrderStatus($this->pag_data['status'], (int)$this->id_order);
			$this->redirect_link = 'index.php?controller=order-confirmation&id_cart=' . $this->cart_id . '&id_module=' . $this->module->id . '&id_order=' . $this->id_order . '&key=' . $this->context->cart->secure_key;
			$this->redirectFinal();
		}
	}

	/*
	* Valida o desconto na opção de pagamento
	*/
	public function validateDiscount($method_type)
	{
		$discount_options = $this->module->checkDiscounts();
		$discount_type = Configuration::get('PAGBANK_DISCOUNT_TYPE');
		$discount_value = Configuration::get('PAGBANK_DISCOUNT_VALUE');

		if ($discount_type >= 1 && $discount_value >= 1) {
			if (($method_type === 'CREDIT_CARD' && in_array('credit_card', $discount_options)) || 
				($method_type === 'GOOGLE_PAY' && in_array('google_pay', $discount_options))) {
				if (isset($this->pag_data['installments']) && (int)$this->pag_data['installments'] == 1) {
					$this->module->generateCartRule($this->context->cart);
				}
			} elseif (($method_type === 'BOLETO' && in_array('bankslip', $discount_options)) || 
					($method_type === 'PIX' && in_array('pix', $discount_options))){
				$this->module->generateCartRule($this->context->cart);
			}
		}
	}

	/*
	* Redirecionamento do usuário
	* Pagamento com erro ou negado
	*/
	public function checkRedirectLink()
	{
		if (_PS_VERSION_ >= '1.7.0') {
			$link = 'index.php?controller=order&step=3';
		} else {
			if(Configuration::get('PS_ORDER_PROCESS_TYPE') == 1 && Dispatcher::getInstance()->getController() != 'orderopc'){
				$link = 'index.php?controller=order-opc&isPaymentStep=true';
			} else {
				$link = 'index.php?controller=order&step=3';
			}
		}
		return $link;
	}

	/*
	* Processa Cartão de Crédito
	*/
	public function processCreditCard($form = false)
	{
		$form_data = array(
			'payment_type' => 'credit_card',
			'card_value_pagbank' => Tools::getValue('card_value_pagbank'),
			'card_installments' => Tools::getValue('card_installments'),
			'save_customer_card' => Tools::getValue('save_customer_card'),
			'saved_card' => Tools::getValue('saved_card'),
			'card_name' => Tools::getValue('card_name'),
			'card_bin' => Tools::getValue('card_bin'),
			'card_brand' => Tools::getValue('card_brand'),
			'encrypted_card' => Tools::getValue('encrypted_card'),
			'card_token_id' => Tools::getValue('card_token_id'),
			'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
			'telephone' => Tools::getValue('telephone'),
			'invoice_postcode' => Tools::getValue('postcode_invoice'),
			'invoice_address' => Tools::getValue('address_invoice'),
			'invoice_number' => Tools::getValue('number_invoice'),
			'invoice_complement' => Tools::getValue('other_invoice'),
			'invoice_district' => Tools::getValue('address2_invoice'),
			'invoice_city' => Tools::getValue('city_invoice'),
			'invoice_state' => Tools::getValue('state_invoice'),
			'pay_two_card_check' => Tools::getValue('pay_two_card_check'),
			'card_one_input' => Tools::getValue('card_one_input'),
			'card_two_input' => Tools::getValue('card_two_input'),
			'card_installments_two' => Tools::getValue('card_installments_two'),
			'card_name_two' => Tools::getValue('card_name_two'),
			'card_bin_two' => Tools::getValue('card_bin_two'),
			'card_brand_two' => Tools::getValue('card_brand_two'),
			'encrypted_card_two' => Tools::getValue('encrypted_card_two'),
			'cpf_cnpj_two' => Tools::getValue('cpf_cnpj_two'),
		);

		if ($form === true) { 
			return $form_data;
		} else {
			$api_response = $this->module->processCardPayment($form_data);
			return $api_response['response'];
		}
	}

	/*
	* Processa Google Pay
	*/
	public function processGooglePay()
	{
		$form_data = array(
			'payment_type' => 'google_pay',
			'card_value_pagbank' => Tools::getValue('card_value_pagbank'),
			'google_installments' => Tools::getValue('google_installments'),
			'google_card_brand' => Tools::getValue('google_card_brand'),
			'google_card_bin' => Tools::getValue('google_card_bin'),
			'google_signature' => Tools::getValue('google_signature'),
			'google_name' => Tools::getValue('google_name'),
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
	 * Confirmação do pedido
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
			"status" => $this->pag_data['status'],
			"status_description" => $this->module->parseStatus($this->pag_data['status']),
			"payment_type" => $this->payment_type,
			"payment_description" => $this->payment_option,
			"installments" => isset($this->pag_data['installments']) && (int)$this->pag_data['installments'] > 0 ? $this->pag_data['installments'] : 1,
			"installments_two" => isset($this->pag_data['installments_two']) && (int)$this->pag_data['installments_two'] > 0 ? $this->pag_data['installments_two'] : '',
			"nsu" => isset($this->pag_data['nsu']) && $this->pag_data['nsu'] != '' ? (string)$this->pag_data['nsu'] : '',
			"nsu_two" => isset($this->pag_data['nsu_two']) && $this->pag_data['nsu_two'] != '' ? (string)$this->pag_data['nsu_two'] : '',
			"url" => isset($this->pag_data['payment_link']) && $this->pag_data['payment_link'] != '' ? (string)$this->pag_data['payment_link'] : '',
			"date_add" => $this->pag_data['create_date']
		);

		$bd = $this->module->insertPagBankData($insert_data);
		if (!$bd) {
			$this->module->saveLog('error', 'Inserir Dados', $this->context->cart->id, json_encode($this->pag_response), 'Banco de Dados não atualizado.');
		}
		return Tools::redirect($this->redirect_link);
	}
}
