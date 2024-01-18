<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author	  2011-2024 PrestaBR - https://prestabr.com.br
 * @copyright 1996-2024 PagBank - https://pagseguro.uol.com.br
 * @license	  Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

 /* SSL Management */
$ssl = true;
$useSSL = true;

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../init.php');
include_once(dirname(__FILE__) . "/pagbank.php");

// Instancia a classe do módulo
$pagbank = new PagBank();

$context = Context::getContext();
$id_lang = $context->language->id;
$cart = $context->cart;
$customer = $context->customer;

$action = Tools::getValue("action");
$token = Tools::getValue("token");
$token_valid = false;
if (isset($token) && $token == hash('md5', _COOKIE_IV_ . Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $pagbank->name)) {
	$token_valid = true;
}

if (Tools::getIsset('state')) {
	$state = urldecode(Tools::getValue('state'));
	$state_array = explode('/', $state);
	$action = $state_array[2];
}
if (empty($action)) {
	exit("Ação não definida");
}

if ($action == "processCard") {
	$form_data = array(
		'payment_type' => 'credit_card',
		'ps_card_installment_value' => Tools::getValue('ps_card_installment_value'),
		'ps_card_installments' => Tools::getValue('ps_card_installments'),
		'ps_max_installments' => Tools::getValue('ps_max_installments'),
		'ps_installments_min_value' => Tools::getValue('ps_installments_min_value'),
		'ps_installments_min_type' => Tools::getValue('ps_installments_min_type'),
		'ps_save_customer_card' => Tools::getValue('ps_save_customer_card'),
		'saved_card' => Tools::getValue('saved_card'),
		'card_name' => Tools::getValue('card_name'),
		'card_number' => Tools::getValue('card_number'),
		'card_month' => Tools::getValue('card_month'),
		'card_year' => Tools::getValue('card_year'),
		'encryptedCard' => Tools::getValue('encryptedCard'),
		'cardTokenId' => Tools::getValue('ps_card_token_id'),
		'card_installment_qty' => Tools::getValue('card_installment_qty'),
		'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
		'telephone' => Tools::getValue('telephone'),
		'invoice_postcode' => Tools::getValue('ps_postcode_invoice'),
		'invoice_address' => Tools::getValue('ps_address_invoice'),
		'invoice_number' => Tools::getValue('ps_number_invoice'),
		'invoice_complement' => Tools::getValue('ps_other_invoice'),
		'invoice_district' => Tools::getValue('ps_address2_invoice'),
		'invoice_city' => Tools::getValue('ps_city_invoice'),
		'invoice_state' => Tools::getValue('ps_state_invoice'),
	);
	$api_response = $pagbank->processCardPayment($form_data);
} elseif ($action == "processBankSlip") {
	$form_data = array(
		'payment_type' => 'bankslip',
		'bankslip_name' => Tools::getValue('bankslip_name'),
		'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
		'telephone' => Tools::getValue('telephone'),
		'invoice_postcode' => Tools::getValue('ps_postcode_invoice'),
		'invoice_address' => Tools::getValue('ps_address_invoice'),
		'invoice_number' => Tools::getValue('ps_number_invoice'),
		'invoice_complement' => Tools::getValue('ps_other_invoice'),
		'invoice_district' => Tools::getValue('ps_address2_invoice'),
		'invoice_city' => Tools::getValue('ps_city_invoice'),
		'invoice_state' => Tools::getValue('ps_state_invoice'),
	);
	$api_response = $pagbank->processBankSlipPayment($form_data);
} elseif ($action == "processPix") {
	$form_data = array(
		'payment_type' => 'pix',
		'pix_name' => Tools::getValue('pix_name'),
		'cpf_cnpj' => Tools::getValue('cpf_cnpj'),
		'telephone' => Tools::getValue('telephone'),
		'invoice_postcode' => Tools::getValue('ps_postcode_invoice'),
		'invoice_address' => Tools::getValue('ps_address_invoice'),
		'invoice_number' => Tools::getValue('ps_number_invoice'),
		'invoice_complement' => Tools::getValue('ps_other_invoice'),
		'invoice_district' => Tools::getValue('ps_address2_invoice'),
		'invoice_city' => Tools::getValue('ps_city_invoice'),
		'invoice_state' => Tools::getValue('ps_state_invoice'),
	);
	$api_response = $pagbank->processPixPayment($form_data);
} elseif ($action == "installments") {
	$value = Tools::getValue("value");
	$max_installments = (int)Configuration::get('PAGBANK_MAX_INSTALLMENTS');
	$max_installments_no_interest = (int)Configuration::get('PAGBANK_NO_INTEREST');
	if($max_installments_no_interest == 1) {
		$no_interest = 0;
	} else {
		$no_interest = $max_installments_no_interest;
	}
	$credit_card_bin = Tools::getValue("credit_card_bin");
	$params = array(
		'value' => $value,
		'max_installments' => $max_installments,
		'max_installments_no_interest' => $no_interest,
		'credit_card_bin' => $credit_card_bin,
		'payment_methods' => 'credit_card',
	);
	$api_response = $pagbank->curl_send('GET', $pagbank->urls['installments'] . '?' . http_build_query($params, '', '&'), false, 30, $cart->id);
} elseif ($action == "registerUser") {
	// Parâmetros recebidos na notificação    
	$code = Tools::getValue('code');
	$adminDir = $state_array[0];
	$token = $state_array[1];
	$app = (string)$state_array[3];

	if (!$code || $code == '') {
		die('Código não recebido.');
	}

	if ((bool)Configuration::get('PAGBANK_FULL_LOG') !== false) {
		$pagbank->saveLog('success', 'callback', json_encode('APP: ' . (string)$app . '.'), '', (string)$code, 'Cadastro do usuário na aplicação ' . (string)$app . '.');
	}

	$authorization = $pagbank->getAppAuthorization($code, (string)$app);
	if (!$authorization) {
		$pagbank->saveLog('erro', 'callback', json_encode('APP: ' . (string)$app . '.'), '', (string)$code, 'Erro ao pegar o Token de usuário para a aplicação ' . (string)$app . '.');
	} else {
		$appMsg = 'Usuário cadastrado com sucesso na aplicação ' . (string)$app;

		$module_admin = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . $adminDir . '/index.php?controller=AdminModules&configure=pagbank&tab_module=payments_gateways&module_name=pagbank&configure=pagbank&tab_module=payments_gateways&module_name=pagbank&token=' . $token . '&app_msg=' . $appMsg;
		Tools::redirectAdmin($module_admin);
	}
} elseif ($action == "deleteToken") {
	$id_customer_token = Tools::getValue('id_customer_token');
	$api_response = $pagbank->deleteCustomerToken($id_customer_token);
} elseif ($action == "checkOrder") {
	$id_order = Tools::getValue('id_order');
	$order = new Order((int)$id_order);
	$api_response = $order->getHistory($id_lang);
} elseif ($action == "cancelNotPaidPix" && $token_valid === true) {
	$awaiting_payment = Configuration::get('PAGBANK_AWAITING_PAYMENT');
	$payment_deadline = Configuration::get('PAGBANK_PIX_TIME_LIMIT');
	$queryStr = 'SELECT `id_order`, `id_cart` FROM `' . _DB_PREFIX_ . 'orders` WHERE `payment` like "PIX%" AND `current_state` = ' . (int)$awaiting_payment . ' AND `date_add` < DATE_SUB(NOW(),INTERVAL ' . $payment_deadline . ' MINUTE)';
	$ordersToCancel = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryStr);

	if (!empty($ordersToCancel)) {
		$cancelStatus = Configuration::get('PAGBANK_CANCELED');
		$history = new OrderHistory();
		foreach ($ordersToCancel as $order) {
			$history->id_order = (int)$order['id_order'];
			$history->changeIdOrderState((int)$cancelStatus, (int)$order['id_order']);
			if ($history->addWithemail(true)) {
				$api_response = 'Status do pedido ' . $order['id_order'] . ' atualizado na loja';
				$pagbank->saveLog('success', 'Cancela Pedido Pix Não Pago', $order['id_card'], '', 'Status do pedido atualizado na loja.', false, 1);
			} else {
				$api_response = 'Status do pedido ' . $order['id_order'] . ' não atualizado na loja';
				$pagbank->saveLog('error', 'Cancela Pedido Pix Não Pago', $order['id_card'], '', 'Status do pedido não atualizado na loja.', false, 1);
			}
		}
	} else {
		$api_response = 'Sem pedidos para cancelar!';
	}
} elseif ($action == "cancelNotPaidBankslip" && $token_valid === true) {
	$awaiting_payment = Configuration::get('PAGBANK_AWAITING_PAYMENT');
	$payment_deadline = Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT');
	$queryStr = 'SELECT `id_order`, `id_cart` FROM `' . _DB_PREFIX_ . 'orders` WHERE `payment` like "Boleto%" AND `current_state` = ' . (int)$awaiting_payment . ' AND `date_add` < DATE_SUB(NOW(),INTERVAL ' . $payment_deadline . ' DAY) AND DAYOFWEEK(NOW()) NOT IN (1,7)';
	$ordersToCancel = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($queryStr);

	if (!empty($ordersToCancel)) {
		$cancelStatus = Configuration::get('PAGBANK_CANCELED');
		$history = new OrderHistory();
		foreach ($ordersToCancel as $order) {
			$history->id_order = (int)$order['id_order'];
			$history->changeIdOrderState((int)$cancelStatus, (int)$order['id_order']);
			if ($history->addWithemail(true)) {
				$api_response = 'Status do pedido ' . $order['id_order'] . ' atualizado na loja';
				$pagbank->saveLog('success', 'Cancela Pedido Boleto Não Pago', $order['id_card'], '', 'Status do pedido atualizado na loja.', false, 1);
			} else {
				$api_response = 'Status do pedido ' . $order['id_order'] . ' não atualizado na loja';
				$pagbank->saveLog('error', 'Cancela Pedido Boleto Não Pago', $order['id_card'], '', 'Status do pedido não atualizado na loja.', false, 1);
			}
		}
	} else {
		$api_response = 'Sem pedidos para cancelar!';
	}
}

if ($api_response) {
	header("Content-type: application/json; charset=utf-8");
	echo json_encode($api_response, JSON_PRETTY_PRINT);
} else {
	die('Sem Retorno.');
}
