<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Pix, Boleto e Cartão de Crédito
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

if (!defined('_PS_VERSION_')) {
	exit;
}
if (_PS_VERSION_ >= '1.7.0') {
	include_once _PS_MODULE_DIR_ . 'pagbank/controllers/v8/PagBankV8Class.php';
} else {
	include_once _PS_MODULE_DIR_ . 'pagbank/controllers/v6/PagBankV6Class.php';
}

class PagBank extends PaymentModule
{
	private $_html = '';
	public $array_token;
	public $array_public_key;
	public $environment;
	public $ps_params;
	public $urls;
	public $number_field;
	public $compl_field;
	public $ps_errors;
	public $authorization_code;
	public $credential_type;
	public $pag_controller;
	public $token;
	public $api_info;
	public $client_id;
	public $cryptogram;
	public $public_key;
	public $ready;
	public $device;

	/*
	 * Função inicial da classe
	 * Define os parâmetros básicos e eventuais validações do módulo
	 */
	public function __construct()
	{
		$this->name = 'pagbank';
		$this->tab = 'payments_gateways';
		$this->version = '1.6.1';
		$this->author = 'PrestaBR';
		$this->urls = array(
			'notification' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/notify.php',
			'img' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/img/',
		);
		$this->credential_type = Configuration::get('PAGBANK_CREDENTIAL');
		$this->environment = Configuration::get('PAGBANK_ENVIRONMENT');
		if ($this->environment == 1) {
			$this->urls['api'] = 'https://api.pagseguro.com/';
			$this->urls['installments'] = 'https://api.pagseguro.com/charges/fees/calculate';
			$this->urls['connect'] = 'https://connect.pagseguro.uol.com.br/oauth2/authorize';
			$this->urls['refresh'] = 'https://api.pagseguro.com/oauth2/refresh';
			$this->urls['appauth'] = 'https://api.pagseguro.com/oauth2/token';

			if ($this->credential_type == 'TAX' || !$this->credential_type || $this->credential_type == '') {
				$this->token = Configuration::get('PAGBANK_TOKEN_TAX');
			} elseif ($this->credential_type == 'D14') {
				$this->token = Configuration::get('PAGBANK_TOKEN_D14');
			} elseif ($this->credential_type == 'D30') {
				$this->token = Configuration::get('PAGBANK_TOKEN_D30');
			} else {
				$this->token = '';
			}
			$this->public_key = Configuration::get('PAGBANK_PUBLIC_KEY');
		} else {
			$this->urls['api'] = 'https://sandbox.api.pagseguro.com/';
			$this->urls['installments'] = 'https://sandbox.api.pagseguro.com/charges/fees/calculate';
			$this->urls['connect'] = 'https://connect.sandbox.pagseguro.uol.com.br/oauth2/authorize';
			$this->urls['refresh'] = 'https://sandbox.api.pagseguro.com/oauth2/refresh';
			$this->urls['appauth'] = 'https://sandbox.api.pagseguro.com/oauth2/token';

			if ($this->credential_type == 'TAX' || !$this->credential_type || $this->credential_type == '') {
				$this->token = Configuration::get('PAGBANK_TOKEN_SANDBOX_TAX');
			} elseif ($this->credential_type == 'D14') {
				$this->token = Configuration::get('PAGBANK_TOKEN_SANDBOX_D14');
			} elseif ($this->credential_type == 'D30') {
				$this->token = Configuration::get('PAGBANK_TOKEN_SANDBOX_D30');
			} else {
				$this->token = '';
			}
			$this->public_key = Configuration::get('PAGBANK_PUBLIC_KEY_SANDBOX');
		}
		if ($this->active) {
			$this->api_info = $this->getApiInfo((int)$this->environment, $this->credential_type);

			$this->client_id = $this->api_info['client_id'];
			$this->cryptogram = $this->api_info['cipher_text'];
		}

		if ($this->token != '' && (!$this->public_key || $this->public_key == '')) {
			$this->getPublicKey();
		}

		if (!$this->token || $this->token == '' || !$this->public_key || $this->public_key == '' || !$this->credential_type) {
			$this->ready = false;
		} else {
			$this->ready = true;
		}

		$this->bootstrap = true;

		if (_PS_VERSION_ >= '1.7.0') {
			$this->pag_controller = new PagBankV8();
		} else {
			$this->pag_controller = new PagBankV6();
		}

		$dev_type = new Mobile_Detect();
		if ($dev_type->isTablet()) {
			$this->device = "t";
		} elseif ($dev_type->isMobile()) {
			$this->device = "m";
		} else {
			$this->device = "d";
		}

		parent::__construct();

		$this->displayName = $this->l('PagBank - Checkout Transparente');
		$this->description = $this->l('Módulo Oficial API v.4 - PrestaShop 1.6, 1.7 e 8.0');

		$this->limited_countries = array('BR');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		$this->number_field = 'company';
		if (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . _DB_PREFIX_ . 'address" AND COLUMN_NAME = "number"')) {
			$this->number_field = 'number';
		} elseif (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = "' . _DB_PREFIX_ . 'address" AND COLUMN_NAME = "numend"')) {
			$this->number_field = 'numend';
		}
		$this->compl_field = ($this->number_field == 'numend' ? 'compl' : 'other');
	}

	/*
	 * Cria abas no menu e tabelas no banco
	 * Registra os Hooks da aplicação e define parâmetros de configuração
	 */
	public function install()
	{
		$iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
		if (in_array($iso_code, $this->limited_countries) == false) {
			$this->_errors[] = $this->l('This module is not available in your country.');
		}

		include(dirname(__FILE__) . '/sql/install.php');

		if (!parent::install() || !$this->pag_controller->installation()) {
			return false;
		}else{
			//Credenciais
			Configuration::updateValue('PAGBANK_ENVIRONMENT', '1', false);
			Configuration::updateValue('PAGBANK_CREDENTIAL', '', false);
			Configuration::updateValue('PAGBANK_TOKEN_TAX', '', false);
			Configuration::updateValue('PAGBANK_TOKEN_D14', '', false);
			Configuration::updateValue('PAGBANK_TOKEN_D30', '', false);
			Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_TAX', '', false);
			Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_D14', '', false);
			Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_D30', '', false);
			Configuration::updateValue('PAGBANK_PUBLIC_KEY', '', false);
			Configuration::updateValue('PAGBANK_PUBLIC_KEY_SANDBOX', '', false);

			//Opções de Pagamento
			Configuration::updateValue('PAGBANK_CREDIT_CARD', '1', false);
			Configuration::updateValue('PAGBANK_MAX_INSTALLMENTS', '12', false);
			Configuration::updateValue('PAGBANK_NO_INTEREST', '12', false);
			Configuration::updateValue('PAGBANK_MINIMUM_INSTALLMENTS', '1.00', false);
			Configuration::updateValue('PAGBANK_INSTALLMENTS_TYPE', '1', false);
			Configuration::updateValue('PAGBANK_SAVE_CREDIT_CARD', '1', false);
			Configuration::updateValue('PAGBANK_BANKSLIP', '1', false);
			Configuration::updateValue('PAGBANK_BANKSLIP_DATE_LIMIT', 2, false);
			Configuration::updateValue('PAGBANK_BANKSLIP_TEXT', 'Pedido realizado na loja ' . Configuration::get("PS_SHOP_NAME"), false);
			Configuration::updateValue('PAGBANK_PIX', '1', false);
			Configuration::updateValue('PAGBANK_PIX_TIME_LIMIT', 30, false);

			//Status dos pedido
			Configuration::updateValue('PAGBANK_AUTHORIZED', _PS_OS_PAYMENT_, false);
			Configuration::updateValue('PAGBANK_CANCELED', _PS_OS_CANCELED_, false);
			Configuration::updateValue('PAGBANK_REFUNDED', _PS_OS_REFUND_, false);
			Configuration::updateValue('PAGBANK_IN_ANALYSIS', Configuration::get('_PS_OS_PAGBANK_1'), false);
			Configuration::updateValue('PAGBANK_AWAITING_PAYMENT', Configuration::get('_PS_OS_PAGBANK_2'), false);

			//Configuração de Descontos
			Configuration::updateValue('PAGBANK_DISCOUNT_TYPE', 0, false);
			Configuration::updateValue('PAGBANK_DISCOUNT_VALUE', '0.00', false);
			Configuration::updateValue('PAGBANK_DISCOUNT_CREDIT', 0, false);
			Configuration::updateValue('PAGBANK_DISCOUNT_BANKSLIP', 0, false);
			Configuration::updateValue('PAGBANK_DISCOUNT_PIX', 0, false);

			//Gerenciamento de Banco de Dados, Log e Erros
			Configuration::updateValue('PAGBANK_SHOW_CONSOLE', 0, false);
			Configuration::updateValue('PAGBANK_FULL_LOG', 1, false);
			Configuration::updateValue('PAGBANK_DELETE_DB', 0, false);
		}
		return true;
	}

	/*
	 * Remove abas no menu e tabelas no banco (caso tenha escolhido a opção na configuração do módulo)
	 * Remove os Hooks da aplicação e parâmetros de configuração
	 */
	public function uninstall()
	{
		if (!parent::uninstall() || !$this->pag_controller->uninstallTabs() || !$this->deleteStatus()) {
			return false;
		}

		if (!Db::getInstance()->delete("configuration", "name LIKE 'PAGBANK_%'")) {
			return false;
		}

		if ((bool)Configuration::get('PAGBANK_DELETE_DB') === true) {
			include(dirname(__FILE__) . '/sql/uninstall.php');
		}
		return true;
	}

	/*
	 * Conteúdo da página de configuração do módulo
	 */
	public function getContent()
	{
		$output = '';

		if ((bool)Tools::isSubmit('submitPagBankModule') === true) {
			$update = $this->postProcess();
			if ($update !== false) {
				$output .= $this->displayConfirmation($this->l('Configurações atualizadas!'));
			}
		}
		$app_msg = Tools::getValue('app_msg');
		if (isset($app_msg) && $app_msg != '') {
			$output .= $this->displayConfirmation($app_msg);
		}

		if (Configuration::get('PAGBANK_ENVIRONMENT') == 1) {
			$tax = Configuration::get('PAGBANK_TOKEN_TAX');
			$d14 = Configuration::get('PAGBANK_TOKEN_D14');
			$d30 = Configuration::get('PAGBANK_TOKEN_D30');
			$signin_connect_url = 'https://connect.pagseguro.uol.com.br/oauth2/authorize';
			$app_info_tax = $this->getApiInfo(1, 'TAX');
			$app_info_d14 = $this->getApiInfo(1, 'D14');
			$app_info_d30 = $this->getApiInfo(1, 'D30');
			$client_id_tax = $app_info_tax['client_id'];
			$client_id_d14 = $app_info_d14['client_id'];
			$client_id_d30 = $app_info_d30['client_id'];
		} else {
			$tax = Configuration::get('PAGBANK_TOKEN_SANDBOX_TAX');
			$d14 = Configuration::get('PAGBANK_TOKEN_SANDBOX_D14');
			$d30 = Configuration::get('PAGBANK_TOKEN_SANDBOX_D30');
			$signin_connect_url = 'https://connect.sandbox.pagseguro.uol.com.br/oauth2/authorize';
			$app_info_tax = $this->getApiInfo(0, 'TAX');
			$app_info_d14 = $this->getApiInfo(0, 'D14');
			$app_info_d30 = $this->getApiInfo(0, 'D30');
			$client_id_tax = $app_info_tax['client_id'];
			$client_id_d14 = $app_info_d14['client_id'];
			$client_id_d30 = $app_info_d30['client_id'];
		}

		Configuration::updateValue('PAGBANK_CODE_VERIFIER_TAX', $this->getPkceData('TAX', 'code_verifier'), false);
		Configuration::updateValue('PAGBANK_CODE_CHALLENGE_TAX', $this->getPkceData('TAX', 'code_challenge'), false);
		Configuration::updateValue('PAGBANK_CODE_VERIFIER_D14', $this->getPkceData('D14', 'code_verifier'), false);
		Configuration::updateValue('PAGBANK_CODE_CHALLENGE_D14', $this->getPkceData('D14', 'code_challenge'), false);
		Configuration::updateValue('PAGBANK_CODE_VERIFIER_D30', $this->getPkceData('D30', 'code_verifier'), false);
		Configuration::updateValue('PAGBANK_CODE_CHALLENGE_D30', $this->getPkceData('D30', 'code_challenge'), false);

		if (_PS_VERSION_ >= '1.7.0') {
			$transactions_link = $this->context->link->getAdminLink("AdminPagBank8", false) . '&token=' . Tools::getAdminTokenLite("AdminPagBank8");
			$logs_link = $this->context->link->getAdminLink("AdminPagBank8Logs", false) . '&token=' . Tools::getAdminTokenLite("AdminPagBank8Logs");
		}else{
			$transactions_link = $this->context->link->getAdminLink("AdminPagBank", false) . '&token=' . Tools::getAdminTokenLite("AdminPagBank");
			$logs_link = $this->context->link->getAdminLink("AdminPagBankLogs", false) . '&token=' . Tools::getAdminTokenLite("AdminPagBankLogs");
		}

		$this->context->smarty->assign(array(
			'module_dir' => $this->_path,
			'module_version' => $this->version,
			'transactions_link' => $transactions_link,
			'logs_link' => $logs_link,
			'environment' => $this->environment,
			'new_user_code' => 'prestabr_code_' . $this->environment . '_' . $this->sortRefNumber(),
			'new_user_state_d14' => urlencode(basename(_PS_ADMIN_DIR_) . '/' . Tools::getValue('token') . '/registerUser/D14'),
			'new_user_state_d30' => urlencode(basename(_PS_ADMIN_DIR_) . '/' . Tools::getValue('token') . '/registerUser/D30'),
			'new_user_state_tax' => urlencode(basename(_PS_ADMIN_DIR_) . '/' . Tools::getValue('token') . '/registerUser/TAX'),
			'client_id_tax' => $client_id_tax,
			'client_id_d14' => $client_id_d14,
			'client_id_d30' => $client_id_d30,
			'signin_connect_url' => $signin_connect_url,
			'callback_url' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/update.php',
			'scope' => 'payments.read+payments.create+payments.refund+accounts.read',
			'code_verifier_tax' => Configuration::get('PAGBANK_CODE_VERIFIER_TAX'),
			'code_challenge_tax' => Configuration::get('PAGBANK_CODE_CHALLENGE_TAX'),
			'code_verifier_d14' => Configuration::get('PAGBANK_CODE_VERIFIER_D14'),
			'code_challenge_d14' => Configuration::get('PAGBANK_CODE_CHALLENGE_D14'),
			'code_verifier_d30' => Configuration::get('PAGBANK_CODE_VERIFIER_D30'),
			'code_challenge_d30' => Configuration::get('PAGBANK_CODE_CHALLENGE_D30'),
			'authorization_code' => $this->authorization_code,
			'this_page' => $_SERVER['REQUEST_URI'],
			'ps_version' => substr(_PS_VERSION_, 0, 3),
			'credential' => Configuration::get('PAGBANK_CREDENTIAL'),
			'api_info' => $this->api_info,
			'app_info_tax' => $app_info_tax,
			'app_info_d14' => $app_info_d14,
			'app_info_d30' => $app_info_d30,
			'tokenTax' => (bool)$tax,
			'tokenD14' => (bool)$d14,
			'tokenD30' => (bool)$d30,
			'token_cron' => hash('md5', _COOKIE_IV_ . Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name),
		));

		$this->getConfigFormValues();

		if (!function_exists('curl_init')) {
			$output .= $this->displayError('Este módulo requer CURL ativado no servidor para funcionar corretamente.');
		}
		if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE') == '1') {
			$output .= $this->displayError('Este módulo requer a execução de Módulos não Nativos.');
		}
		if (
			(int)Configuration::get('PAGBANK_CREDIT_CARD') +
			(int)Configuration::get('PAGBANK_BANKSLIP') +
			(int)Configuration::get('PAGBANK_PIX') == 0
		) {
			$output .= $this->displayError('Pelo menos 1 opção de pagamento deve estar ativa para este módulo funcionar.');
		}

		$output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

		return $output . $this->pag_controller->getConfigForm();
	}

	/*
	 * Processa validações diversas sobre configurações básicas do cadastro do cliente na loja, p/ orientar o lojista durante o setup inicial 
	 */
	public function hookDisplayBackOfficeHeader()
	{
		$this->context->controller->addCSS($this->_path . 'css/pagbank_admin.css');
		$this->context->controller->addJS($this->_path . 'js/mascara.js');

		$this->callRefreshToken();

		if (Configuration::get('PAGBANK_SHOW_CONSOLE') == 0) {
			return;
		} else {

			if (_PS_VERSION_ >= '1.7.0') {
				$get_restr_carrier = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_module` FROM `' . _DB_PREFIX_ . 'module_carrier` WHERE `id_module`=' . $this->id . '');
				$set_url_restr_carrier = 'index.php?controller=AdminPaymentPreferences&token=' . Tools::getAdminTokenLite('AdminPaymentPreferences');

				if (empty($get_restr_carrier)) {
					$this->context->controller->warnings[] = '<p><b>PagBank - Restrições de transportadora: </b></p> <p>Verifique se as transportadoras estão vinculadas à forma de pagamento. Isso vai garantir que o módulo seja exibido e esteja disponível para processar pagamentos na tela de checkout.</p> <p>Para ver as configurações de Restrições de transportadora <a href="' . $set_url_restr_carrier . '">Clique aqui</a> (role até o final da página)</p>';
				}
			}
		}
	}

	/*
	* Define alguns elementos no header do front
	*/
	public function hookDisplayHeader()
	{
		if (_PS_VERSION_ >= '1.7.0') {
			if (
				$this->context->controller->php_self == 'order'
				|| $this->context->controller->php_self == 'orderopc'
				|| $this->context->controller->php_self == 'order-opc'
				|| $this->context->controller->php_self == 'order-confirmation'
			) {
				$this->context->controller->registerStylesheet(
					'pagbank-css',
					'modules/' . $this->name . '/css/pagbank.css',
					[
						'media' => 'all',
						'priority' => 150
					]
				);
				$this->context->controller->registerStylesheet(
					'google-font',
					'https://fonts.googleapis.com/css2?family=Inconsolata:wght@400;700&display=swap',
					[
						'server' => 'remote',
						'media' => 'all',
						'priority' => 150
					]
				);
				$this->context->controller->addJqueryPlugin('fancybox');
				$this->context->controller->registerJavascript(
					'pagbank-mascara',
					'modules/' . $this->name . '/js/mascara.js',
					[
						'position' => 'bottom',
						'priority' => 150
					]
				);
				$this->context->controller->registerJavascript(
					'pagbank-clipboard',
					'modules/' . $this->name . '/js/clipboard.min.js',
					[
						'position' => 'bottom',
						'priority' => 150
					]
				);
				$this->context->controller->registerJavascript(
					'pagbank-purify',
					'modules/' . $this->name . '/js/purify.min.js',
					[
						'position' => 'bottom',
						'priority' => 150
					]
				);
				$this->context->controller->registerJavascript(
					'pagbank-min',
					'https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js',
					[
						'server' => 'remote',
						'media' => 'all',
						'priority' => 150
					]
				);
				$this->context->controller->registerJavascript(
					'pagbank-js',
					'modules/' . $this->name . '/js/pagbank.js',
					[
						'position' => 'bottom',
						'priority' => 150
					]
				);
			}
		} else {
			if (
				$this->context->controller->php_self == 'order'
				|| $this->context->controller->php_self == 'orderopc'
				|| $this->context->controller->php_self == 'order-opc'
				|| $this->context->controller->php_self == 'order-confirmation'
			) {
				$this->context->controller->addCSS(array(
					$this->_path . 'css/pagbank.css',
					'https://fonts.googleapis.com/css2?family=Inconsolata:wght@400;700&display=swap'
				));
				$this->context->controller->addJS(array(
					$this->_path . 'js/mascara.js',
					$this->_path . 'js/clipboard.min.js',
					$this->_path . 'js/purify.min.js',
					'https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js',
					$this->_path . 'js/pagbank.js'
				));
			}
		}
	}

	/*
	 * Exibe as opções de pagamento do módulo na loja, sem redirecionar para um controller novo
	 */
	public function hookDisplayPayment($params)
	{
		if (!$this->active || $this->ready === false) {
			return false;
		}
		$this->paymentLogic($params);

		return $this->display(__FILE__, '/views/templates/v6/hook/payment.tpl');
	}

	/*
	 * Payment Logic
	 */
	public function paymentLogic($params)
	{
		if (!$this->active || $this->ready === false) {
			return false;
		}
		if (!$this->checkCurrency($params['cart'])) {
			return false;
		}
		$cart = $params['cart'];
		$currency_id = $cart->id_currency;
		$currency = new Currency((int)$currency_id);

		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$customer = new Customer((int)($cart->id_customer));
		$address = new Address((int)($cart->id_address_invoice));
		$firstname = str_replace(' ', ' ', trim($customer->firstname));
		$lastname = str_replace(' ', ' ', trim($customer->lastname));
		$sender_name = trim($firstname . ' ' . $lastname);
		if (isset($customer->birthday) && $customer->birthday != '0000-00-00') {
			$birthday = date('d/m/Y', strtotime($customer->birthday));
			$birthday_string = DateTime::createFromFormat('d/m/Y', $birthday)->format('d/m/Y');
		}
		$phone = isset($address->phone_mobile) && !empty($address->phone_mobile) ? $address->phone_mobile : $address->phone;
		$page_name = $this->context->controller->php_self;
		$msg_console = Configuration::get('PAGBANK_SHOW_CONSOLE');
		$method = false;
		if (Tools::isSubmit('method')) {
			$method = Tools::getValue('method');
			if ($method == 'updateCarrierAndGetPayments') {
				$method = true;
			}
		}

		$id_country = (int)$address->id_country;
		if (!$id_country || $id_country < 1) {
			$id_country = Country::getIdByName($this->context->language->id, 'Brasil');
		}
		if (!$id_country || $id_country < 1) {
			$id_country = Country::getIdByName($this->context->language->id, 'Brazil');
		}
		if (!$id_country || $id_country < 1) {
			$id_country = 58;
		}
		$states = State::getStatesByIdCountry($id_country);

		$pix_expiration = Configuration::get('PAGBANK_PIX_TIME_LIMIT');
		$current_hour = date("H", time());

		$alternate_time = false;
		if (((int)$current_hour > 20 || (int)$current_hour < 6) && (int)$total > 999) {
			$alternate_time = true;
		}

		$active_payments = array(
			'credit_card' => (bool)Configuration::get('PAGBANK_CREDIT_CARD'),
			'bankslip' => (bool)Configuration::get('PAGBANK_BANKSLIP'),
			'pix' => (bool)Configuration::get('PAGBANK_PIX')
		);

		$discounts = array(
			'credit_card' => Configuration::get('PAGBANK_DISCOUNT_CREDIT') > 0 ? (bool)Configuration::get('PAGBANK_DISCOUNT_CREDIT') : false,
			'bankslip' => Configuration::get('PAGBANK_DISCOUNT_BANKSLIP') > 0 ? (bool)Configuration::get('PAGBANK_DISCOUNT_BANKSLIP') : false,
			'pix' => Configuration::get('PAGBANK_DISCOUNT_PIX') > 0 ? (bool)Configuration::get('PAGBANK_DISCOUNT_PIX') : false,
			'discount_type' => Configuration::get('PAGBANK_DISCOUNT_TYPE'),
			'discount_value' => Configuration::get('PAGBANK_DISCOUNT_VALUE'),
			'credit_card_value' => $this->calculateDiscounts('credit_card', 1),
			'bankslip_value' => $this->calculateDiscounts('bankslip'),
			'pix_value' => $this->calculateDiscounts('pix')
		);

		$this->smarty->assign(array(
			'self' => $this->context->controller->php_self,
			'page_name' => $page_name,
			'msg_console' => (bool)$msg_console,
			'payments' => $active_payments,
			'discounts' => $discounts,
			'save_credit_card' => (int)Configuration::get('PAGBANK_SAVE_CREDIT_CARD'),
			'ps_max_installments' => (int)Configuration::get('PAGBANK_MAX_INSTALLMENTS'),
			'ps_installments_min_value' => (int)Configuration::get('PAGBANK_MINIMUM_INSTALLMENTS'),
			'ps_installments_min_type' => (int)Configuration::get('PAGBANK_INSTALLMENTS_TYPE'),
			'device' => $this->device,
			'module_dir' => $this->_path,
			'tpl_dir' => _PS_MODULE_DIR_ . $this->name . '/views/templates/v6/hook',
			'url_img' => $this->urls['img'],
			'currency' => $currency,
			'idmodule' => $this->id,
			'checkout' => (bool)Configuration::get('PS_ORDER_PROCESS_TYPE'),
			'pix_timeout' => $this->calculateDeadline((int)$pix_expiration),
			'alternate_time' => $alternate_time,
			'bankslip_date_limit' => (int)Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT'),
			'bankslip_text' => (int)Configuration::get('PAGBANK_BANKSLIP_TEXT'),
			'total' => number_format($total, 2, '.', ''),
			'id_cart' => $cart->id,
			'phone' => $phone,
			'method' => $method,
			'address_invoice' => $address,
			'number_invoice' => $address->{$this->number_field},
			'compl_invoice' => $address->{$this->compl_field},
			'states' => $states,
			'sender_name' => $sender_name,
			'birthday' => isset($birthday_string) && $birthday_string != '' ? $birthday_string : '',
			'customer_token' => $this->getCustomerToken((int)($cart->id_customer)),
			'ps_version' => substr(_PS_VERSION_, 0, 3),
			'pagbank_version' => $this->version,
			'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
			'url_update' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/update.php'
		));
	}


	/*
	 * Exibe as opções de pagamento do módulo na loja, sem redirecionar para um controller novo
	 */
	public function hookPaymentOptions($params)
	{
		if (!$this->active || $this->ready === false) {
			return false;
		}
		$this->paymentLogic($params);

		$active_payments = array(
			'credit_card' => (bool)Configuration::get('PAGBANK_CREDIT_CARD'),
			'bankslip' => (bool)Configuration::get('PAGBANK_BANKSLIP'),
			'pix' => (bool)Configuration::get('PAGBANK_PIX')
		);

		$pay_options = array();

		if ($active_payments['credit_card']) {
			$pay_text = 'Cartão de Crédito'.$this->messageDiscounts('credit_card');
			$pay_tpl = 'credit_card.tpl';
			$ps_option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
			$ps_option->setModuleName($this->name)
				->setCallToActionText($pay_text)
				->setLogo($this->urls['img'] . 'pagbank-logo-animado_35px.gif')
				->setAction($this->context->link->getModuleLink($this->name, 'validation', array('ptype' => 'credit_card'), true))
				->setForm($this->fetch('module:pagbank/views/templates/v8/hook/' . $pay_tpl));
			$pay_options[] = $ps_option;
		}

		if ($active_payments['bankslip']) {
			$pay_text = 'Boleto'.$this->messageDiscounts('bankslip');
			$pay_tpl = 'bankslip.tpl';
			$ps_option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
			$ps_option->setModuleName($this->name)
				->setCallToActionText($pay_text)
				->setLogo($this->urls['img'] . 'pagbank-logo-animado_35px.gif')
				->setAction($this->context->link->getModuleLink($this->name, 'validation', array('ptype' => 'bankslip'), true))
				->setForm($this->fetch('module:pagbank/views/templates/v8/hook/' . $pay_tpl));
			$pay_options[] = $ps_option;
		}

		if ($active_payments['pix']) {
			$pay_text = 'Pix'.$this->messageDiscounts('pix');
			$pay_tpl = 'pix.tpl';
			$ps_option = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
			$ps_option->setModuleName($this->name)
				->setCallToActionText($pay_text)
				->setLogo($this->urls['img'] . 'pagbank-logo-animado_35px.gif')
				->setAction($this->context->link->getModuleLink($this->name, 'validation', array('ptype' => 'pix'), true))
				->setForm($this->fetch('module:pagbank/views/templates/v8/hook/' . $pay_tpl));
			$pay_options[] = $ps_option;
		}

		return $pay_options;
	}


	/*
	 * Exibe mensagens de confirmação e de erro na parte superior da página de pagamento
	 */
	public function hookDisplayPaymentTop($params)
	{
		if (Tools::getIsset('pagbank_msg') && Tools::getIsset('pagbank_msg') !== '') {
			$pagbank_msg = Tools::getValue('pagbank_msg');
		} else {
			$pagbank_msg = $this->context->cookie->pagbank_msg;
		}

		if (!$this->active || (!$this->checkCurrency($params['cart'])) || $this->ready === false) {
			$pagbank_msg = $this->l('PagBank: Módulo não disponível. Por favor, verifique se o mesmo está ativo e cheque as Configurações do App.');
		}

		$this->callRefreshToken();

		if (_PS_VERSION_ >= '1.7.0') {
			$cart = $params['cart'];
			$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
			$currency_id = $cart->id_currency;
			$currency = new Currency((int)$currency_id);
			$msg_console = Configuration::get('PAGBANK_SHOW_CONSOLE');
			$active_payments = array(
				'credit_card' => (bool)Configuration::get('PAGBANK_CREDIT_CARD'),
				'bankslip' => (bool)Configuration::get('PAGBANK_BANKSLIP'),
				'pix' => (bool)Configuration::get('PAGBANK_PIX')
			);

			$discounts = array(
				'credit_card' => Configuration::get('PAGBANK_DISCOUNT_CREDIT') > 0 ? (bool)Configuration::get('PAGBANK_DISCOUNT_CREDIT') : false,
				'bankslip' => Configuration::get('PAGBANK_DISCOUNT_BANKSLIP') > 0 ? (bool)Configuration::get('PAGBANK_DISCOUNT_BANKSLIP') : false,
				'pix' => Configuration::get('PAGBANK_DISCOUNT_PIX') > 0 ? (bool)Configuration::get('PAGBANK_DISCOUNT_PIX') : false,
				'discount_type' => Configuration::get('PAGBANK_DISCOUNT_TYPE'),
				'discount_value' => Configuration::get('PAGBANK_DISCOUNT_VALUE'),
				'credit_card_value' => $this->calculateDiscounts('credit_card', 1),
				'bankslip_value' => $this->calculateDiscounts('bankslip'),
				'pix_value' => $this->calculateDiscounts('pix')
			);

			$method = false;
			if (Tools::isSubmit('method')) {
				$method = Tools::getValue('method');
				if ($method == 'updateCarrierAndGetPayments') {
					$method = true;
				}
			}

			$this->context->smarty->assign(array(
				'pagbank_msg' => $pagbank_msg,
				'public_key' => $this->public_key,
				'msg_console' => (bool)$msg_console,
				'payments' => $active_payments,
				'discounts' => $discounts,
				'save_credit_card' => (int)Configuration::get('PAGBANK_SAVE_CREDIT_CARD'),
				'ps_max_installments' => (int)Configuration::get('PAGBANK_MAX_INSTALLMENTS'),
				'ps_installments_min_value' => (int)Configuration::get('PAGBANK_MINIMUM_INSTALLMENTS'),
				'ps_installments_min_type' => (int)Configuration::get('PAGBANK_INSTALLMENTS_TYPE'),
				'device' => $this->device,
				'module_dir' => $this->_path,
				'url_img' => $this->urls['img'],
				'currency' => $currency,
				'ps_version' => substr(_PS_VERSION_, 0, 3),
				'pagbank_version' => $this->version,
				'total' => number_format($total, 2, '.', ''),
				'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
				'url_update' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/update.php'
			));
			$this->context->cookie->pagbank_msg = false;
			return $this->display(__FILE__, '/views/templates/v8/hook/payment_top.tpl');
		} else {
			$this->context->smarty->assign(array(
				'pagbank_msg' => $pagbank_msg,
				'public_key' => $this->public_key,
			));
			$this->context->cookie->pagbank_msg = false;
			return $this->display(__FILE__, '/views/templates/v6/hook/payment_top.tpl');
		}
	}

	/*
	 * Exibe a página de confirmação de pagamento com os parâmetros do pedido na loja e no pagbank
	 */
	public function hookDisplayPaymentReturn($params)
	{
		if (!$this->active || $this->ready === false) {
			return false;
		}

		$id_cart = Tools::getValue('id_cart');
		$id_order = Order::getOrderByCartId($id_cart);
		$order = new Order($id_order);

		$info = $this->getOrderData($id_cart, 'id_cart');
		$cod_status = $info['status'];
		$transaction_code = $info['transaction_code'];

		$transaction = $this->getTransaction($transaction_code, $id_cart);

		//Charges
		if (isset($transaction->charges)) {
			$payment = end($transaction->charges);
			$payment_type = $payment->payment_method->type;
			$payment_status = $payment->status;

			if ($cod_status != $payment_status) {
				$this->updateOrderStatus($id_cart, $cod_status, $id_order, date("Y-m-d H:i:s"));
			}
		} else {
			$payment_status = 'WAITING';
			if (isset($transaction->qr_codes) && $transaction->qr_codes[0]->arrangements[0] == 'PIX') {
				$pix = array();
				$payment_type = 'PIX';
				$pix_expiration = Configuration::get('PAGBANK_PIX_TIME_LIMIT');
				$qr = end($transaction->qr_codes);
				$pix['id'] = $qr->id;
				$pix['text'] = $qr->text;
				$pix['deadline'] = $this->calculateDeadline($pix_expiration);
				$pix['expiration_date'] = $qr->expiration_date;
				foreach ($qr->links as $qr_link) {
					if ($qr_link->media == 'image/png') {
						$pix['link'] = $qr_link->href;
					}
				}
			}
		}

		$current_hour = date("H", time());

		$alternate_time = false;
		if (((int)$current_hour > 20 || (int)$current_hour < 6) && $order->total_paid > 999) {
			$alternate_time = true;
		}

		$customer_name = $this->context->customer->firstname;

		$this->smarty->assign(array(
			'device' => $this->device,
			'customer_name' => $customer_name,
			'info' => $info,
			'ps_link' => isset($info['url']) && $info['url'] != '' ? $info['url'] : false,
			'ps_transaction_code' => $transaction_code,
			'ps_order_id' => $id_order,
			'ps_order_reference' => $order->reference,
			'ps_order_value' => number_format($order->total_paid, 2, ',', '.'),
			'ps_order_products' => $order->getProducts(),
			'transaction' => $transaction,
			'payment_status' => $this->parseStatus($payment_status),
			'pix' => isset($pix) && is_array($pix) ? $pix : false,
			'current_hour' => $current_hour,
			'alternate_time' => $alternate_time,
			'payment_type' => $payment_type,
			'ps_order' => $order,
			'ps_paid_state' => Configuration::get('PAGBANK_AUTHORIZED'),
			'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
			'url_update' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/update.php',
		));

		if (_PS_VERSION_ >= '1.7.0') {
			return $this->display(__FILE__, '/views/templates/v8/hook/payment_return.tpl');
		} else {
			return $this->display(__FILE__, '/views/templates/v6/hook/payment_return.tpl');
		}
	}

	/*
	 * Exibe os dados do pedido no pagbank na aba de detalhes do pedido no BackOffice
	 */
	public function hookDisplayAdminOrder()
	{
		if (!$this->active) {
			return;
		}

		$id_order = Tools::getValue('id_order');
		$order = new Order((int)$id_order);
		$info = $this->getOrderData((int)$id_order, 'id_order');

		if (!$info) {
			return;
		}

		$transaction = $this->getTransaction($info['transaction_code'], $order->id_cart);
		$status_pagbank = isset($transaction->charges) ? $transaction->charges[0]->status : $info['status'];
		$payment_description = isset($transaction->charges) ? $info['payment_description'] : $info['payment_description'];
		if (isset($transaction->charges) && !empty($transaction->charges)) {
			$transaction_code_charge = str_replace('CHAR_', '', $transaction->charges[0]->id);
		}

		if (Tools::isSubmit('refundOrderPagBank')) {
			if (in_array($status_pagbank, array('AUTHORIZED', 'PAID', 'AVAILABLE', 'DISPUTE'))) {
				if ($value > 0) {
					$formatted_value = preg_replace('/\D/', '', $value);
				} else {
					$formatted_value = $transaction->charges[0]->amount->value;
				}
				$api_response = $this->refundTransaction(
					$transaction_code_charge,
					$formatted_value,
					$order
				);
				if (!$api_response['errors']) {
					$this->context->smarty->assign('pagbank_msg', $this->l('Pedido Estornado no PagBank.'));
				} else {
					$this->context->smarty->assign('pagbank_msg', $this->l('Erro ao tentar Estornar o Pedido no PagBank.'));
				}
			} else {
				$this->context->smarty->assign('pagbank_msg', $this->l('Status do Pedido no PagBank não permite Estorno.'));
			}
		}

		$this->context->smarty->assign(array(
			'order' => $order,
			'transaction' => $transaction,
			'payment_description' => $payment_description,
			'info' => $info,
			'version' => _PS_VERSION_,
			'status' => $status_pagbank,
			'desc_status' => $this->parseStatus($status_pagbank),
			'currency' => new Currency($this->context->currency->id),
			'this_page' => $_SERVER['REQUEST_URI'],
			'this_path' => $this->_path,
			'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
		));

		if (_PS_VERSION_ >= '1.7.0') {
			return $this->display(__FILE__, '/views/templates/v8/hook/admin_order.tpl');
		} else {
			return $this->display(__FILE__, '/views/templates/v6/hook/admin_order.tpl');
		}
	}

	/*
	 * Valida a moeda do cliente na loja
	 */
	public function checkCurrency($cart)
	{
		$currency_order = new Currency($cart->id_currency);
		$currencies_module = $this->getCurrency($cart->id_currency);

		if (is_array($currencies_module)) {
			foreach ($currencies_module as $currency_module) {
				if ($currency_order->id == $currency_module['id_currency']) {
					return true;
				}
			}
		}
		return false;
	}

	/*
	 * Verifica a credencial utilizada p/ consultar o pedido
	 */
	public function currentCredential($credential = false, $environment = false)
	{
		if($credential){
			$credential_type = $credential;
		} else {
			$credential_type = $this->credential_type;
		}
		
		if($environment == 1 || $environment == '' || $environment == 'NULL'){
			$token_code = Configuration::get('PAGBANK_TOKEN_'.$credential_type.'');
		} else {
			$token_code = Configuration::get('PAGBANK_TOKEN_SANDBOX_'.$credential_type.'');
		}

		return $token_code;
	}

	/* 
	 * Pega os dados dos campos de configuração do módulo
	 */
	public function getConfigFormValues()
	{
		return array(
			'PAGBANK_ENVIRONMENT' => Tools::getValue('PAGBANK_ENVIRONMENT', Configuration::get('PAGBANK_ENVIRONMENT')),
			'PAGBANK_CREDENTIAL' => Tools::getValue('PAGBANK_CREDENTIAL', Configuration::get('PAGBANK_CREDENTIAL')),

			'PAGBANK_CREDIT_CARD' => Tools::getValue('PAGBANK_CREDIT_CARD', Configuration::get('PAGBANK_CREDIT_CARD')),
			'PAGBANK_SAVE_CREDIT_CARD' => Tools::getValue('PAGBANK_SAVE_CREDIT_CARD', Configuration::get('PAGBANK_SAVE_CREDIT_CARD')),
			'PAGBANK_MAX_INSTALLMENTS' => Tools::getValue('PAGBANK_MAX_INSTALLMENTS', Configuration::get('PAGBANK_MAX_INSTALLMENTS')),
			'PAGBANK_NO_INTEREST' => Tools::getValue('PAGBANK_NO_INTEREST', Configuration::get('PAGBANK_NO_INTEREST')),
			'PAGBANK_MINIMUM_INSTALLMENTS' => Tools::getValue('PAGBANK_MINIMUM_INSTALLMENTS', Configuration::get('PAGBANK_MINIMUM_INSTALLMENTS')),
			'PAGBANK_INSTALLMENTS_TYPE' => Tools::getValue('PAGBANK_INSTALLMENTS_TYPE', Configuration::get('PAGBANK_INSTALLMENTS_TYPE')),
			'PAGBANK_BANKSLIP' => Tools::getValue('PAGBANK_BANKSLIP', Configuration::get('PAGBANK_BANKSLIP')),
			'PAGBANK_BANKSLIP_DATE_LIMIT' => Tools::getValue('PAGBANK_BANKSLIP_DATE_LIMIT', Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT')),
			'PAGBANK_BANKSLIP_TEXT' => Tools::getValue('PAGBANK_BANKSLIP_TEXT', Configuration::get('PAGBANK_BANKSLIP_TEXT')),
			'PAGBANK_PIX' => Tools::getValue('PAGBANK_PIX', Configuration::get('PAGBANK_PIX')),
			'PAGBANK_PIX_TIME_LIMIT' => Tools::getValue('PAGBANK_PIX_TIME_LIMIT', Configuration::get('PAGBANK_PIX_TIME_LIMIT')),

			'PAGBANK_AUTHORIZED' => Tools::getValue('PAGBANK_AUTHORIZED', Configuration::get('PAGBANK_AUTHORIZED')),
			'PAGBANK_CANCELED' => Tools::getValue('PAGBANK_CANCELED', Configuration::get('PAGBANK_CANCELED')),
			'PAGBANK_REFUNDED' => Tools::getValue('PAGBANK_REFUNDED', Configuration::get('PAGBANK_REFUNDED')),
			'PAGBANK_IN_ANALYSIS' => Tools::getValue('PAGBANK_IN_ANALYSIS', Configuration::get('PAGBANK_IN_ANALYSIS')),
			'PAGBANK_AWAITING_PAYMENT' => Tools::getValue('PAGBANK_AWAITING_PAYMENT', Configuration::get('PAGBANK_AWAITING_PAYMENT')),

			'PAGBANK_DISCOUNT_TYPE' => Tools::getValue('PAGBANK_DISCOUNT_TYPE', Configuration::get('PAGBANK_DISCOUNT_TYPE')),
			'PAGBANK_DISCOUNT_VALUE' => Tools::getValue('PAGBANK_DISCOUNT_VALUE', Configuration::get('PAGBANK_DISCOUNT_VALUE')),
			'PAGBANK_DISCOUNT_CREDIT' => Tools::getValue('PAGBANK_DISCOUNT_CREDIT', Configuration::get('PAGBANK_DISCOUNT_CREDIT')),
			'PAGBANK_DISCOUNT_BANKSLIP' => Tools::getValue('PAGBANK_DISCOUNT_BANKSLIP', Configuration::get('PAGBANK_DISCOUNT_BANKSLIP')),
			'PAGBANK_DISCOUNT_PIX' => Tools::getValue('PAGBANK_DISCOUNT_PIX', Configuration::get('PAGBANK_DISCOUNT_PIX')),

			'PAGBANK_SHOW_CONSOLE' => Tools::getValue('PAGBANK_SHOW_CONSOLE', Configuration::get('PAGBANK_SHOW_CONSOLE')),
			'PAGBANK_FULL_LOG' => Tools::getValue('PAGBANK_FULL_LOG', Configuration::get('PAGBANK_FULL_LOG')),
			'PAGBANK_DELETE_DB' => Tools::getValue('PAGBANK_DELETE_DB', Configuration::get('PAGBANK_DELETE_DB')),

		);
	}

	/*
	 * Atualiza os campos de configuração do módulo
	*/
	protected function postProcess()
	{
		$form_values = $this->getConfigFormValues();

		$erro = false;
		$env_post = (int)Tools::getValue('PAGBANK_ENVIRONMENT');
		$env_banco = (int)Configuration::get('PAGBANK_ENVIRONMENT');
		$cred_post = Tools::getValue('PAGBANK_CREDENTIAL');
		$cred_banco = Configuration::get('PAGBANK_CREDENTIAL');

		foreach (array_keys($form_values) as $key) {
			if ($key == 'PAGBANK_CREDENTIAL' && $env_post != $env_banco) {
				Configuration::updateValue('PAGBANK_CREDENTIAL', '');
			} elseif ($key == 'PAGBANK_CREDENTIAL' && $cred_post != $cred_banco) {
				Configuration::updateValue('PAGBANK_CREDENTIAL', $cred_post);
				$this->getPublicKey();
			} else {
				if (!Configuration::updateValue($key, Tools::getValue($key))) {
					$erro = true;
				}
			}
		}

		if ($erro) {
			return false;
		} else {
			return true;
		}
	}

	/* 
	 * Adiciona Status do PagBank. 
	 * Podem ser utilizados ou alterados na configuração do módulo 
	 */
	public function addStatus()
	{
		return $this->pag_controller->addStatus();
	}

	/* 
	 * Adiciona Status do PagBank. 
	 * Podem ser utilizados ou alterados na configuração do módulo 
	 */
	public function deleteStatus()
	{
		Db::getInstance()->execute("
			UPDATE `" . _DB_PREFIX_ . "order_state` SET `deleted` = 1, `hidden` = 1 WHERE `module_name` = '" . $this->name . "'
		");
		return true;
	}

	/* 
	 * Pega dados do pedido no banco
	 */
	public function getApiInfo($env, $app)
	{
		$result = Db::getInstance()->getRow("
			SELECT `id_api_credential`, `environment`, `app`, `credit_tax`, `bankslip_tax`, `pix_tax`, `client_id`, `cipher_text`, `date_add`
			FROM `" . _DB_PREFIX_ . "pagbank_api_credentials`
			WHERE `environment` = " . $env . " AND `app` = '" . $app . "'
		");
		return $result;
	}

	/* 
	 * Pega dados do pedido no banco
	 */
	public function getOrderData($id_op, $field)
	{
		$result = Db::getInstance()->getRow("
			SELECT * FROM `" . _DB_PREFIX_ . "pagbank`
			WHERE `" . $field . "` = '" . $id_op . "'
			ORDER BY `id_pagbank` DESC
		");
		return $result;
	}

	/* 
	 * Atualiza dados do pedido no banco
	 */
	public function updatePagBankData($data)
	{
		$updateQuery = 'UPDATE `' . _DB_PREFIX_ . 'pagbank` SET ';
		if (isset($data['status']) && $data['status'] != "") {
			$updateQuery .= '`status` = "' . $data['status'] . '", ';
		}
		if (isset($data['status_description']) && $data['status_description'] != "") {
			$updateQuery .= '`status_description` = "' . $data['status_description'] . '", ';
		}
		if (isset($data['date_upd']) && $data['date_upd'] != "") {
			$updateQuery .= '`date_upd` = "' . $data['date_upd'] . '" ';
		} else {
			$updateQuery .= '`date_upd` = "' . date("Y-m-d H:i:s") . '" ';
		}
		$updateQuery .= ' WHERE `transaction_code` = "' . $data['transaction_code'] . '"';
		if (!Db::getInstance()->execute($updateQuery)) {
			$this->saveLog('error', 'update', false, $updateQuery, 'Pedido nao atualizado no banco.');
			return false;
		}
		return true;
	}

	/* 
	 * Insere dados do pedido no banco
	 */
	public function insertPagBankData($data)
	{
		if ($this->getOrderData($data['transaction_code'], 'transaction_code')) {
			return $this->updatePagBankData($data);
		} else {
			foreach ($data as $k => $item) {
				if ($k != 'id_order') {
					if (!$data[$k] || $data[$k] === false) {
						$data[$k] = '';
					}
				}
			}

			$shop_id = 1;
			if (!is_null($this->context->shop->id)) {
				$shop_id = $this->context->shop->id;
			}

			$ins_query = 'INSERT INTO `' . _DB_PREFIX_ . 'pagbank` (`id_shop`, `id_customer`, `cpf_cnpj`, `id_cart`, `id_order`, `reference`, `transaction_code`, `buyer_ip`, `status`, `status_description`, `payment_type`, `payment_description`, `installments`, `nsu`, `url`, `credential`, `environment`, `date_add`, `date_upd`)';
			$ins_query .= ' VALUES (' . (int)$shop_id . ', "' . $data['id_customer'] . '", "' . $data['cpf_cnpj'] . '", ' . $data['id_cart'] . ', ' . $data['id_order'] . ', "' . $data['reference'] . '", "' . $data['transaction_code'] . '", "' . $data['buyer_ip'] . '", "' . $data['status'] . '", "' . $data['status_description'] . '", "' . $data['payment_type'] . '", "' . $data['payment_description'] . '", ' . (int)$data['installments'] . ', "' . $data['nsu'] . '", "' . $data['url'] . '", "' . $this->credential_type . '", "' .$data['environment']. '", "' . $data['date_add'] . '", "' . date("Y-m-d H:i:s") . '")';
			$insert = Db::getInstance()->execute($ins_query);
			if (!$insert || (bool)$insert !== true) {
				$this->saveLog('error', 'insertPagBankData', $data['id_cart'], $ins_query, 'Pedido nao inserido no banco.');
				return false;
			}
			return true;
		}
	}

	/*
	 * Calcula descontos antes de gerar o pedido
	*/
	public function calculateDiscounts($payment_type, $installment = false)
	{
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

		$discount_type = Configuration::get('PAGBANK_DISCOUNT_TYPE');
		$discount_value = (float)Configuration::get('PAGBANK_DISCOUNT_VALUE');
		$total_products = $this->context->cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
		$total_shipping = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
		$total_discounts = $this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
		$total_wrapping = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);

		if (in_array($payment_type, $discount_options) && $discount_type >= 1 && $discount_value >= 1) {
			if($payment_type == 'credit_card' && $installment == 1 || $payment_type != 'credit_card') {
				if ($discount_type == 1) {
					$total_partial = (($total_products - $total_discounts) * (1 - ($discount_value / 100)));
				} else {
					$total_partial = (($total_products - $total_discounts) - $discount_value);
				}
				$total_paid = round(($total_partial + $total_shipping + $total_wrapping), 2, PHP_ROUND_HALF_DOWN);
			}
		} else {
			$total_paid = $this->context->cart->getOrderTotal(true, Cart::BOTH);
		}

		return $total_paid;

	}

	/*
	 * Exibe mensagem de desconto na opção de pagamento
	*/
	public function messageDiscounts($payment_type)
	{
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

		$discount_type = Configuration::get('PAGBANK_DISCOUNT_TYPE');
		$discount_value = (float)Configuration::get('PAGBANK_DISCOUNT_VALUE');
		if (in_array($payment_type, $discount_options) && $discount_type >= 1 && $discount_value >= 1) {
			if($discount_type == 1){
				$discount_msg = ' (- '.$discount_value.'%)';
			}else{
				$discount_msg = ' (- R$ '.$discount_value.')';
			}
		} else {
			$discount_msg = '';
		}

		return $discount_msg;
	}
	
	/*
	 * Processa pagamento com PIX ou PagBank
	*/
	public function processPixPayment($form_data)
	{
		$this->ps_errors = array();
		$this->ps_params = array();
		$this->processBasicData();
		$this->processFormData($form_data);
		$order_products = $this->processOrderProducts();
		$pix_expiration = Configuration::get('PAGBANK_PIX_TIME_LIMIT');
		$current_hour = date("H", time());

		$total_paid = $this->calculateDiscounts('pix');

		if (((int)$current_hour > 20 || (int)$current_hour < 6) && (int)$total_paid > 999) {
			$pix_expiration = ((24 - (int)$current_hour) + 9) * 60;
		}

		$json_pix = '{
			"reference_id": "' . $this->ps_params['reference'] . '",
			"customer": {
				"name": "' . $this->ps_params['holderName'] . '",
				"email": "' . $this->ps_params['senderEmail'] . '",
				"tax_id": "' . $this->ps_params['senderCPFCNPJ'] . '", 
				"phones": [
					{
						"country": "+55",
						"area": "' . $this->ps_params['senderAreaCode'] . '",
						"number": "' . $this->ps_params['senderPhone'] . '",
						"type": "' . $this->ps_params['senderPhoneType'] . '"
					}
				]
				  
			},
			"items": ' . $order_products . ',
			"qr_codes": [
				{
					"amount": {
						"value": ' . number_format($total_paid, 2, "", "") . '
					},
					"expiration_date": "' . date(DATE_ATOM, strtotime("+{$pix_expiration} minutes")) . '"
				}
			],
			"shipping": {
				"address": {
					"street": "' . $this->ps_params['billingAddressStreet'] . '",
					"number": "' . $this->ps_params['billingAddressNumber'] . '",
					"complement": "' . $this->ps_params['billingAddressComplement'] . '",
					"locality": "' . $this->ps_params['billingAddressDistrict'] . '",
					"city": "' . $this->ps_params['billingAddressCity'] . '",
					"region": "' . $this->ps_params['billingAddressState'] . '",
					"region_code": "' . $this->ps_params['billingAddressStateCode'] . '",
					"country": "BRA",
					"postal_code": "' . $this->ps_params['billingAddressPostalCode'] . '"
				}
			},
			"notification_urls": [
				"' . $this->ps_params['notificationURL'] . '"
			]
		}';

		$api_response = $this->curl_send('POST', $this->urls['api'] . 'orders', preg_replace('!\?\\n?\\t!', "", $json_pix), 30, $this->context->cart->id);
		if (!$api_response['errors']) {
			return $api_response;
		} else {
			$this->ps_errors[] = 'Erro no processamento do PIX.';
			return $api_response;
		}
	}

	/* 
	 * Consulta as condições comerciais para a bin do cartão
	 */
	public function callGetInstallments($value, $bin, $id_cart)
	{
		$max_installments = (int)Configuration::get('PAGBANK_MAX_INSTALLMENTS');
		$max_installments_no_interest = (int)Configuration::get('PAGBANK_NO_INTEREST');
		if($max_installments_no_interest == 1) {
			$no_interest = 0;
		} else {
			$no_interest = $max_installments_no_interest;
		}
		$params = array(
			'value' => $value,
			'max_installments' => $max_installments,
			'max_installments_no_interest' => $no_interest,
			'credit_card_bin' => $bin,
			'payment_methods' => 'credit_card',
		);
		
		return $this->curl_send('GET', $this->urls['installments'] . '?' . http_build_query($params, '', '&'), false, 30, $id_cart);
	}

	/* 
	 * Processa pagamento com Cartão de crédito no PagBank
	 */
	public function processCardPayment($form_data)
	{
		$this->ps_errors = array();
		$this->ps_params = array();
		$this->processBasicData();
		$this->processFormData($form_data);
		$this->processCreditCardData($form_data);
		$order_products = $this->processOrderProducts();

		if (isset($form_data['ps_save_customer_card']) && (int)$form_data['ps_save_customer_card'] > 0) {
			$store_card = true;
		} else {
			$store_card = false;
		}

		$saved_card = $form_data['saved_card'];
		$id_customer = (int)$this->context->cart->id_customer;
		$id_cart = (int)$this->context->cart->id;
		$total_card = $this->context->cart->getOrderTotal(true, Cart::BOTH);
		$fees = json_decode($form_data['get_installments_fees']);

		if ((int)$saved_card > 0) {
			$check_saved = $this->getCustomerToken($id_customer, (int)$form_data['cardTokenId']);
			$bin = $check_saved[0]['card_first_digits'];
			$card_brand = $check_saved[0]['card_brand'];
			$payment_method_card = array(
				"id" => $this->ps_params['creditCardToken']
			);
		} else {
			$bin = $form_data['card_bin'];
			$card_brand = $form_data['card_brand'];
			$payment_method_card = array(
				"encrypted" => $this->ps_params['creditCardToken'],
				"store" => $store_card
			);
		}

		if((int)$this->ps_params['installmentQuantity'] == 1){
			$amount_value = $this->calculateDiscounts('credit_card', 1);
			$formatted_value = number_format($amount_value, 2, "", "");
			$amount = '{
				"value": ' . $formatted_value. ',
				"currency": "BRL"
			}';
			
			$first_install = reset($fees);
			$raw_amount_value = $first_install->amount->value;
			$final_value = number_format($total_card, 2, "", "");
		} else {
			foreach($fees as $fee) {
				if($fee->installments == (int)$this->ps_params['installmentQuantity']) {
					if((int)$this->ps_params['installmentQuantity'] <= (int)Configuration::get('PAGBANK_NO_INTEREST')){
						$amount = '{
							"value": ' . $fee->amount->value. ',
							"currency": "BRL"
						}';
					}else{
						$amount = json_encode($fee->amount);
					}
					$amount_value = $fee->amount->value/100;
					$raw_amount_value = $fee->amount->value;
				}
			}

			$fomatted_value = number_format($total_card, 2, "", "");
			$installments = $this->callGetInstallments($fomatted_value, $bin, $id_cart);
			$inst_array = json_decode(json_encode($installments['response']));
			$plans = $inst_array->payment_methods->credit_card->{$card_brand}->installment_plans;
			foreach($plans as $p){
				if($p->installments == (int)$this->ps_params['installmentQuantity']) {
					$selected_install = $p;
				}
			}
			$final_value = $selected_install->amount->value;
		}

		// Validação dos valores c/ margem de segurança
		if ((int)$raw_amount_value+10 < $final_value) {
			return array('errors');
		}

		$json_credit_card = '{
			"reference_id": "' . $this->ps_params['reference'] . '",
			"customer": {
				"name": "' . $this->ps_params['sender_name'] . '",
				"email": "' . $this->ps_params['senderEmail'] . '",
				"tax_id": "' . $this->ps_params['senderCPFCNPJ'] . '", 
				"phones": [
					{
						"country": "+55",
						"area": "' . $this->ps_params['senderAreaCode'] . '",
						"number": "' . $this->ps_params['senderPhone'] . '",
						"type": "' . $this->ps_params['senderPhoneType'] . '"
					}
				]
			},
			"items": ' . $order_products . ',
			"shipping": {
				"address": {
					"street": "' . $this->ps_params['billingAddressStreet'] . '",
					"number": "' . $this->ps_params['billingAddressNumber'] . '",
					"complement": "' . $this->ps_params['billingAddressComplement'] . '",
					"locality": "' . $this->ps_params['billingAddressDistrict'] . '",
					"city": "' . $this->ps_params['billingAddressCity'] . '",
					"region": "' . $this->ps_params['billingAddressState'] . '",
					"region_code": "' . $this->ps_params['billingAddressStateCode'] . '",
					"country": "BRA",
					"postal_code": "' . $this->ps_params['billingAddressPostalCode'] . '"
				}
			},
			"notification_urls": [
				"' . $this->ps_params['notificationURL'] . '"
			],
			"charges": [
				{
					"reference_id": "' . $this->ps_params['reference'] . '",
					"description": "Pedido realizado na loja ' . Configuration::get('PS_SHOP_NAME') . ', em ' . date("d/m/Y") . ', no valor total de R$ ' . number_format($amount_value, 2, ",", ".") . '",
					"amount": ' . $amount . ',
					"payment_method": {
						"type": "CREDIT_CARD",
						"installments": ' . (int)$this->ps_params['installmentQuantity'] . ',
						"capture": true,
						"card": ' . json_encode($payment_method_card) . '
					},
					"metadata": {},
					"notification_urls": [
						"' . $this->ps_params['notificationURL'] . '"
					]
				}
			]
		}';

		$json_card = preg_replace("!\?\\n?\\t!", "", $json_credit_card);
		$api_response = $this->curl_send('POST', $this->urls['api'] . 'orders', stripslashes($json_card), 30, $this->context->cart->id);
		if ((bool)$api_response['errors'] == true) {
			$this->ps_errors[] = 'Erro no processamento do cartão.';
		} else {
			$payment = end($api_response['response']->charges);
			$payment_status = $payment->status;

			//Sucesso
			if (in_array($payment_status, array('AVAILABLE', 'AUTHORIZED', 'PAID', 'IN_ANALYSIS'))) {
				if (
					isset($form_data['ps_save_customer_card']) &&
					(int)$form_data['ps_save_customer_card'] > 0 &&
					isset($payment->payment_method->card->id)
				) {
					$info = array(
						'id_customer' => (int)$this->context->cart->id_customer,
						'card_name' => $payment->payment_method->card->holder->name,
						'card_brand' => $payment->payment_method->card->brand,
						'card_first_digits' => $payment->payment_method->card->first_digits,
						'card_last_digits' => $payment->payment_method->card->last_digits,
						'card_month' => $payment->payment_method->card->exp_month,
						'card_year' => $payment->payment_method->card->exp_year,
						'card_token' => $payment->payment_method->card->id
					);
					$this->insertCustomerToken($info);
				}
			}
		}
		return $api_response;
	}

	/* 
	 * Processa pagamento com Boleto no PagBank
	 */
	public function processBankSlipPayment($form_data)
	{
		$this->ps_errors = array();
		$this->ps_params = array();
		$this->processBasicData();
		$this->processFormData($form_data);
		$order_products = $this->processOrderProducts();

		if ((int)Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT') == 0) {
			$aditional_time = '';
		} elseif ((int)Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT') == 1) {
			$aditional_time = '+' . (int)Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT') . ' day';
		} elseif ((int)Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT') > 1) {
			$aditional_time = '+' . (int)Configuration::get('PAGBANK_BANKSLIP_DATE_LIMIT') . ' days';
		}

		$total_paid = $this->calculateDiscounts('bankslip');

		$json_bankslip = '{
			"reference_id": "' . $this->ps_params['reference'] . '",
			"customer": {
				"name": "' . $this->ps_params['sender_name'] . '",
				"email": "' . $this->ps_params['senderEmail'] . '",
				"tax_id": "' . $this->ps_params['senderCPFCNPJ'] . '", 
				"phones": [
					{
						"country": "+55",
						"area": "' . $this->ps_params['senderAreaCode'] . '",
						"number": "' . $this->ps_params['senderPhone'] . '",
						"type": "' . $this->ps_params['senderPhoneType'] . '"
					}
				]
				  
			},
			"items": ' . $order_products . ',
			"shipping": {
				"address": {
					"street": "' . $this->ps_params['billingAddressStreet'] . '",
					"number": "' . $this->ps_params['billingAddressNumber'] . '",
					"complement": "' . $this->ps_params['billingAddressComplement'] . '",
					"locality": "' . $this->ps_params['billingAddressDistrict'] . '",
					"city": "' . $this->ps_params['billingAddressCity'] . '",
					"region": "' . $this->ps_params['billingAddressState'] . '",
					"region_code": "' . $this->ps_params['billingAddressStateCode'] . '",
					"country": "BRA",
					"postal_code": "' . $this->ps_params['billingAddressPostalCode'] . '"
				}
			},
			"notification_urls": [
				"' . $this->ps_params['notificationURL'] . '"
			],
			"charges": [
				{
					"reference_id": "' . $this->ps_params['reference'] . '",
					"description": "' . Configuration::get('PAGBANK_BANKSLIP_TEXT') . '",
					"amount": {
						"value": ' . number_format($total_paid, 2, "", "") . ',
						"currency": "BRL"
					},
					"payment_method": {
					  "type": "BOLETO",
					  "boleto": {
						  "due_date": "' . date("Y-m-d", strtotime($aditional_time)) . '",
						  "instruction_lines": {
							"line_1": "' . Configuration::get('PAGBANK_BANKSLIP_TEXT') . '",
							"line_2": "Processado pelo PagBank em ' . date('d/m/Y') . '"
						  },
						  "holder": {
							"name": "' . $this->ps_params['holderName'] . '",
							"tax_id": "' . $this->ps_params['senderCPFCNPJ'] . '",
							"email": "' . $this->ps_params['senderEmail'] . '",
							"address": {
								"street": "' . $this->ps_params['billingAddressStreet'] . '",
								"number": "' . $this->ps_params['billingAddressNumber'] . '",
								"complement": "' . $this->ps_params['billingAddressComplement'] . '",
								"locality": "' . $this->ps_params['billingAddressDistrict'] . '",
								"city": "' . $this->ps_params['billingAddressCity'] . '",
								"region": "' . $this->ps_params['billingAddressState'] . '",
								"region_code": "' . $this->ps_params['billingAddressStateCode'] . '",
								"country": "BRA",
								"postal_code": "' . $this->ps_params['billingAddressPostalCode'] . '"
							}
						  }
						}
					},
					"notification_urls": [
						"' . $this->ps_params['notificationURL'] . '"
					]
				}
			]
		}';

		$api_response = $this->curl_send('POST', $this->urls['api'] . 'orders', preg_replace('!\?\\n?\\t!', "", $json_bankslip), 30, $this->context->cart->id);
		if (!$api_response['errors']) {
			return $api_response;
		} else {
			$this->ps_errors[] = 'Erro no processamento do boleto.';
			return $api_response;
		}
	}

	/* 
	 * Retorna dados da transação no PagBank
	 * $code = Código da transação
	 */
	public function getTransaction($code, $id_cart = false)
	{
		if (isset($id_cart)) {
			$info = $this->getOrderData($id_cart, 'id_cart');
			if (!empty($info) && $info['environment'] == 1 ||
			!empty($info) && $info['environment'] == '' ||
			!empty($info) && $info['environment'] == 'NULL') {
				$this->urls['api'] = 'https://api.pagseguro.com/';
			} else {
				$this->urls['api'] = 'https://sandbox.api.pagseguro.com/';
			}
		}

		$api_response = $this->curl_send('GET', $this->urls['api'] . 'orders/' . $code, false, 30, $id_cart);
		if (!$api_response['errors']) {
			return $api_response['response'];
		} else {
			$this->ps_errors[] = 'Erro ao consultar Transação.';
			return false;
		}
	}

	/* 
	 * Devolve o valor da transação ao cliente
	 * $code = Código da transação
	 * $value = valor devolvido, total ou parcial
	 */
	public function refundTransaction($code, $value, $order)
	{
		$json_refund = '{
		  "amount": {
			"value": ' . $value . '
		  }
		}';
		$api_response = $this->curl_send('POST', $this->urls['api'] . 'charges/' . $code . '/cancel', $json_refund, 20, $order->id_cart);
		if (!$api_response['errors']) {
			if ($this->updateOrderStatus($order->id_cart, 'REFUNDED', $order->id, date("Y-m-d H:i:s"))) {
				if (Db::getInstance()->execute(
					'UPDATE `' . _DB_PREFIX_ . 'pagbank` set `refund`="' . ($value / 100) . '", `status`="REFUNDED", `status_description`="' . $this->parseStatus('REFUNDED') . '" WHERE `id_cart`="' . $order->id_cart . '"'
				)) {
					return true;
				} else {
					$this->ps_errors[] = 'Erro ao salvar o estorno no banco.';
					return false;
				}
			}
		} else {
			$this->ps_errors[] = 'Erro ao estornar Transação.';
			return false;
		}
	}

	/* 
	 * Atualiza Status do pedido na loja
	 */
	public function updateOrderStatus($id_cart, $status_code, $id_order = null, $date_update = null)
	{
		if (!$id_order || $id_order == '' || $id_order < 1) {
			$id_order = Order::getOrderByCartId($id_cart);
		}

		if (!$id_order || $id_order == '' || $id_order < 1) {
			return false;
		}

		$order = new Order($id_order);
		$current_status = (int)$order->getCurrentState();
		$status_ps = (int)$this->correspondStatus($status_code);

		if ($current_status == $status_ps) {
			return;
		} else {
			$status_history = $order->getHistory($this->context->language->id);
			$s_history = array();
			foreach ($status_history as $status) {
				$s_history[] = $status['id_order_state'];
			}
			if (isset($status_ps) && $status_ps != false && $current_status != $status_ps) {
				$info = $this->getOrderData($id_order, 'id_order');
				if (in_array($status_ps, $s_history)) {
					die('Status já existe');
				} else {
					$history = new OrderHistory();
					$history->id_order = (int)$id_order;
					$history->changeIdOrderState($status_ps, (int)$id_order);
					$template_vars = array(
						'{transaction_code}' => $info['transaction_code'],
						'{status}' => $status_code,
						'{status_description}' => $this->parseStatus($status_code)
					);
					if (!$history->addWithemail(true, $template_vars)) {
						$this->saveLog('error', 'Atualiza Status', $id_cart, 'Status PagBank: ' . $status_ps . ' / Status Loja: ' . (int)$current_status, 'Status do pedido não atualizado na loja.');
					}
					$history->save();
				}
				$data = array(
					'transaction_code' => $info['transaction_code'],
					'status' => $status_code,
					'status_description' => $this->parseStatus($status_code),
					'date_upd' => isset($date_update) && $date_update ? $date_update : ''
				);

				$this->updatePagBankData($data);

				return true;
			}
		}
	}

	/* 
	 * Processa parâmetros comuns de envio de transações
	 */
	private function processBasicData()
	{
		$this->ps_params['receiverEmail'] = Configuration::get('PAGBANK_EMAIL');
		$this->ps_params['notificationURL'] = $this->urls['notification'];
		$this->ps_params['currency'] = 'BRL';
		$this->ps_params['paymentMode'] = 'default';
		$this->ps_params['reference'] = $this->context->cart->id . '.' . Configuration::get('PAGBANK_CREDENTIAL') . '.' . $this->sortRefNumber();
		if (empty($this->ps_params['receiverEmail']) || empty($this->ps_params['notificationURL']) || empty($this->ps_params['currency']) || empty($this->ps_params['paymentMode']) || empty($this->ps_params['reference'])) {
			$this->ps_errors[] = 'Erro ao Processar Padrões.';
		}
	}

	/*
	 * Processa produtos do carrinho para envio ao PagBank
	*/
	private function processOrderProducts()
	{
		$order_products = $this->context->cart->getProducts();

		$products_array = array();
		foreach ($order_products as $product) {
			if ($product['price_wt'] <= 0) {
				continue;
			}

			$products_array[] = array(
				"reference_id" => $product['id_product'],
				"name" => $this->replaceSpecialChars(substr($product['name'], 0, 64)),
				"quantity" => $product['cart_quantity'],
				"unit_amount" => number_format($product['price_wt'], 2, "", ""),
				"dimensions" => array(
					"length" => $product['depth'],
					"width" => $product['width'],
					"height" => $product['height']
				),
				"weight" => $product['weight']
			);
		}
		if (empty($products_array) || count($products_array) < 1) {
			return false;
			$this->ps_errors[] = 'Erro ao Processar Produtos.';
		} else {
			return json_encode($products_array);
		}
	}

	/*
	 * Processa dados do formulário de pagamento para envio ao PagBank
	*/
	private function processFormData($form_data)
	{
		//Dados do Cliente
		$cpf_cnpj = preg_replace('/[^0-9]/', '', $form_data['cpf_cnpj']);
		$telephone = $this->formatPhoneNumber($form_data['telephone']);
		$email = $this->context->customer->email;
		$name = trim($this->context->customer->firstname) . ' ' . trim($this->context->customer->lastname);
		$name = preg_replace('/\s(?=\s)/', '', $name);
		if (strlen($name) > 50) {
			$name = substr($name, 0, 50);
		}
		if (isset($form_data['card_name']) && strlen($form_data['card_name']) > 3) {
			$holder_name = trim($form_data['card_name']);
		} elseif (isset($form_data['bankslip_name']) && strlen($form_data['bankslip_name']) > 3) {
			$holder_name = trim($form_data['bankslip_name']);
		} elseif (isset($form_data['pix_name']) && strlen($form_data['pix_name']) > 3) {
			$holder_name = trim($form_data['pix_name']);
		}
		$holder_name = preg_replace('/\s(?=\s)/', '', $holder_name);
		if (strlen($holder_name) > 50) {
			$holder_name = substr($holder_name, 0, 50);
		}

		$this->ps_params['senderEmail'] = $email;
		$this->ps_params['sender_name'] = $name;
		$this->ps_params['holderName'] = $holder_name;
		$this->ps_params['senderCPFCNPJ'] = $cpf_cnpj;
		$this->ps_params['senderAreaCode'] = $telephone['area_code'];
		$this->ps_params['senderPhone'] = $telephone['telephone_number'];
		$this->ps_params['senderPhoneType'] = (int)substr($telephone['telephone_number'], 0, 1) == 9 ? 'MOBILE' : 'BUSINESS'; //CELLPHONE ?

		//Endereço do cliente
		$address = new Address((int)$this->context->cart->id_address_delivery);
		$city = $address->city;
		if (strlen($city) > 60) {
			$city = substr($city, 0, 60);
		}
		$postcode = preg_replace('/[^0-9]/', '', $address->postcode);
		$state = new State((int)$address->id_state);
		$uf = $state->iso_code;
		$uf_name = $state->name;
		$frete = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
		if ($frete > 0) {
			$this->ps_params['shippingType'] = '3';
			$this->ps_params['shippingCost'] = number_format($frete, 2, '.', '');
		}
		$this->ps_params['shippingAddressCountry'] = 'BRA';
		$this->ps_params['shippingAddressState'] = $uf_name;
		$this->ps_params['shippingAddressStateCode'] = $uf;
		$this->ps_params['shippingAddressCity'] = $city;
		$this->ps_params['shippingAddressPostalCode'] = $postcode;
		$this->ps_params['shippingAddressDistrict'] = $address->address2;
		$this->ps_params['shippingAddressStreet'] = $address->address1;
		$this->ps_params['shippingAddressNumber'] = isset($address->{$this->number_field}) && strlen($address->{$this->number_field}) > 0 ? substr($address->{$this->number_field}, 0, 20) : '1';
		$this->ps_params['shippingAddressComplement'] = isset($this->compl_field) && $address->{$this->compl_field} != '' ? substr($address->{$this->compl_field}, 0, 40) : 'N/A';

		if ((empty($this->ps_params['shippingAddressState']) && empty($this->ps_params['shippingAddressCity'])) || empty($this->ps_params['shippingAddressPostalCode']) || empty($this->ps_params['shippingAddressDistrict']) || empty($this->ps_params['shippingAddressStreet']) || empty($this->ps_params['shippingAddressNumber'])) {
			$this->ps_errors[] = 'Erro ao Processar Endereço de Entrega.';
		}

		//Endereço de Cobrança
		$address_invoice = new Address((int)$this->context->cart->id_address_invoice);
		$city = $address_invoice->city;
		if (strlen($city) > 60) {
			$city = substr($city, 0, 60);
		}
		$postcode = preg_replace('/[^0-9]/', '', $address_invoice->postcode);
		$state = new State((int)$address_invoice->id_state);
		$uf_name = $state->name;
		$uf_sigla = $state->iso_code;
		$this->ps_params['billingAddressPostalCode'] = isset($form_data['invoice_postcode']) && $form_data['invoice_postcode'] != '' ? preg_replace('/[^0-9]/', '', $form_data['invoice_postcode']) : $postcode;
		$this->ps_params['billingAddressStreet'] = isset($form_data['invoice_address']) && $form_data['invoice_address'] != '' ? $form_data['invoice_address'] : $address_invoice->address1;
		$this->ps_params['billingAddressNumber'] = isset($form_data['invoice_number']) && $form_data['invoice_number'] != '' ? substr($form_data['invoice_number'], 0, 20) : substr($address_invoice->{$this->number_field}, 0, 20);
		$this->ps_params['billingAddressComplement'] = isset($form_data['invoice_complement']) && $form_data['invoice_complement'] != '' ? substr($form_data['invoice_complement'], 0, 40) : 'N/A';
		$this->ps_params['billingAddressDistrict'] = isset($form_data['invoice_district']) && $form_data['invoice_district'] != '' ? $form_data['invoice_district'] : $address_invoice->address2;
		$this->ps_params['billingAddressCity'] = isset($form_data['invoice_city']) && $form_data['invoice_city'] != '' ? $form_data['invoice_city'] : $city;
		$this->ps_params['billingAddressState'] = isset($form_data['invoice_state']) && $form_data['invoice_state'] != '' ? $this->getStateByIsoCode($form_data['invoice_state'], 'name') : $uf_name;
		$this->ps_params['billingAddressStateCode'] = isset($form_data['invoice_state']) && $form_data['invoice_state'] != '' ? $form_data['invoice_state'] : $uf_sigla;
		$this->ps_params['billingAddressCountry'] = 'BRA';
		if (
			(empty($this->ps_params['billingAddressState']) && empty($this->ps_params['billingAddressCity'])) ||
			empty($this->ps_params['billingAddressPostalCode']) ||
			empty($this->ps_params['billingAddressDistrict']) ||
			empty($this->ps_params['billingAddressStreet']) ||
			empty($this->ps_params['billingAddressNumber'])
		) {
			$this->ps_errors[] = 'Erro ao Processar Endereço de Cobrança.';
		}
	}

	/* 
	 * Processa dados do cartão de crédito para envio ao PagBank
	 */
	public function processCreditCardData($form_data)
	{
		$cpf_cnpj = preg_replace('/[^0-9]/', '', $form_data['cpf_cnpj']);
		$card_holder = trim($form_data['card_name']);
		$card_holder = preg_replace('/\s(?=\s)/', '', $card_holder);
		$this->ps_params['creditCardHolderName'] = $card_holder;
		$telephone = $this->formatPhoneNumber($form_data['telephone']);
		$this->ps_params['creditCardHolderCPF'] = $cpf_cnpj;
		if (isset($form_data['cardTokenId']) && (int)$form_data['cardTokenId'] > 0) {
			$this->ps_params['creditCardToken'] = $this->getCardToken($form_data['cardTokenId']);
		} else {
			$this->ps_params['creditCardToken'] = $form_data['encryptedCard'];
		}
		$this->ps_params['installmentQuantity'] = $form_data['ps_card_installments'];
		$this->ps_params['installmentValue'] = str_replace(",", "", $form_data['ps_card_installment_value']);
		if (
			empty($this->ps_params['creditCardToken'])
			|| (empty($this->ps_params['installmentQuantity']) && empty($this->ps_params['installmentValue']))
			|| empty($this->ps_params['creditCardHolderName'])
			|| empty($this->ps_params['creditCardHolderCPF'])
		) {
			$this->ps_errors[] = 'Erro ao Processar Dados do Cartão.';
		}
	}

	/* 
	 * Padroniza e valida os dados do telephone do cliente para envio ao PagBank
	 */
	public function formatPhoneNumber($phone)
	{
		$cod_area = '';
		$tel = '';
		$telephone = preg_replace('/[^0-9]/', '', $phone);
		$len = strlen($telephone);
		$cod_area = substr($telephone, 0, 2);
		if ($len == 10) {
			$tel = substr($telephone, 2, 8);
		} else {
			$tel = substr($telephone, 2, 9);
		}
		return array(
			'phone' => $phone,
			'area_code' => $cod_area,
			'telephone_number' => $tel
		);
	}

	/* 
	 * Pega ID do Carrinho a partir da referência do pedido no PagBank
	 */
	public function getIdCart($reference)
	{
		$ref_array = explode(".", $reference);
		$id_cart = $ref_array[0];
		return $id_cart;
	}

	/* 
	 * Parse HTTP Status
	 */
	public function parseHttpStatus($http)
	{
		switch ((int)$http) {
			case 200:
				$return = 'OK';
				break;
			case 400:
				$return = 'BAD_REQUEST';
				break;
			case 401:
				$return = 'UNAUTHORIZED';
				break;
			case 403:
				$return = 'FORBIDDEN';
				break;
			case 404:
				$return = 'NOT_FOUND';
				break;
			case 500:
				$return = 'INTERNAL_SERVER_ERROR';
				break;
			case 502:
				$return = 'BAD_GATEWAY';
				break;
		}
		return $return;
	}

	/* 
	 * Retorna texto do Status do PagBank a partir do código
	 */
	public function parseStatus($status_code)
	{
		$status = array(
			'AUTHORIZED' => 'Pagamento autorizado',
			'AVAILABLE' => 'Pagamento autorizado',
			'PAID' => 'Pagamento autorizado',
			'IN_ANALYSIS' => 'Pagamento em análise',
			'WAITING' => 'Aguardando pagamento',
			'CANCELED' => 'Pagamento cancelado',
			'DECLINED' => 'Pagamento não autorizado',
			'DISPUTE' => 'Em disputa',
			'REFUNDED' => 'Estono total ou parcial realizado',
		);
		return (array_key_exists($status_code, $status) ? $status[$status_code] : 'Aguardando pagamento');
	}

	/* 
	 * Corresponde o Status do pedido no PagBank com o Status do pedido na loja
	 */
	public function correspondStatus($status_code)
	{
		switch ($status_code) {
			case 'WAITING':
				$status_loja = Configuration::get('PAGBANK_AWAITING_PAYMENT');
				break;
			case 'IN_ANALYSIS':
				$status_loja = Configuration::get('PAGBANK_IN_ANALYSIS');
				break;
			case 'PAID':
			case 'AVAILABLE':
			case 'AUTHORIZED':
				$status_loja = Configuration::get('PAGBANK_AUTHORIZED');
				break;
			case 'REFUNDED':
				$status_loja = Configuration::get('PAGBANK_REFUNDED');
				break;
			case 'CANCELED':
			case 'DECLINED':
				$status_loja = Configuration::get('PAGBANK_CANCELED');
				break;
		}
		return isset($status_loja) && $status_loja ? $status_loja : false;
	}

	/*
	 * Apaga Logs do banco de dados
	 */
	public function delete()
	{
		$id_log = Tools::getValue('id_log');
		$ps_logs_box = Tools::getValue('pagbank_logsBox');
		if (!$id_log && !$ps_logs_box) {
			return;
		}
		if (isset($ps_logs_box) && !is_array($ps_logs_box)) {
			$ps_logs_box = array($ps_logs_box);
		}
		$del_query = '';
		if (isset($id_log) && !empty($id_log)) {
			$del_query = 'DELETE FROM `' . _DB_PREFIX_ . 'pagbank_logs` WHERE `id_log` = ' . $id_log . ';';
		} else {
			foreach ($ps_logs_box as $id_logbox) {
				$del_query .= 'DELETE FROM `' . _DB_PREFIX_ . 'pagbank_logs` WHERE `id_log` = ' . $id_logbox . ';';
			}
		}
		if (!Db::getInstance()->execute($del_query)) {
			return false;
		}
		return true;
	}

	/*
	 * Envia chamada para a API
	*/
	public function curl_send($method, $post_url, $json_data = false, $timeout = 10, $id_cart = false)
	{
		if ($json_data !== false && !$this->isJson($json_data)) {
			$this->saveLog('error', $method, $id_cart, $json_data, 'Invalid JSON String.', $post_url);
			return array(
				'errors' => 'JSON Inválido!',
				'json_data' => $json_data
			);
		}
		
		if (isset($id_cart) && strtoupper($method) === 'GET') {
			$info = $this->getOrderData($id_cart, 'id_cart');
			if (!empty($info) && $info['credential'] != '') {
				$credential = $info['credential'];
				$environment = $info['environment'];
				$token_api = $this->currentCredential($credential, $environment);
			} else {
				$token_api = $this->token;
			}
		} else {
			$token_api = $this->token;
		}

		$curl = curl_init();
		if ($json_data !== false && (strtoupper($method) === 'POST' || strtoupper($method) === 'PUT')) {
			$post_fields = ($json_data ? $json_data : '');
			if (strtoupper($method) === 'POST') {
				curl_setopt($curl, CURLOPT_POST, true);
			} else {
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
			}
			curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
		} else {
			curl_setopt($curl, CURLOPT_HTTPGET, true);
		}
		$header = array(
			'Authorization: Bearer ' . $token_api,
			'Accept: application/json', 
			'Content-Type: application/json'
		);

		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_URL, $post_url);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $timeout);
		$resp = curl_exec($curl);
		$info = curl_getinfo($curl);
		$error = curl_errno($curl);
		$error_message = curl_error($curl);
		$curl_header = $header;
		$resp_object = json_decode($resp);

		curl_close($curl);

		if (Configuration::get('PAGBANK_FULL_LOG') == 1) {
			if (isset($resp_object->error_messages)) {
				$this->saveLog('error', $method, $id_cart, $json_data, $resp, $post_url);
			} else {
				$this->saveLog('success', $method, $id_cart, $json_data, $resp, $post_url);
			}
		}

		if (in_array((int)$info['http_code'], array(200, 201, 204))) {
			$api_response = array(
				'errors' => false,
				'response' => $resp_object,
				'status' => $info['http_code'],
				'info' => $info,
			);
		} else {
			$this->ps_errors[] = 'A conexão com o PagBank retornou com erro: ' . $error . ' - ' . $error_message . ' (HTTP: ' . $info['http_code'] . ')';
			$errors = array();
			if (isset($resp_object->error_messages) && is_array($resp_object->error_messages)) {
				foreach ($resp_object->error_messages as $erro) {
					$errors[] = array(
						'error' => isset($erro->code) && $erro->code != '' ?  $erro->code : $erro->error,
						'description' => (string)$erro->description,
						'parameter' => isset($erro->parameter_name) ? (string)$erro->parameter_name : false
					);
				}
			}

			$api_response = array(
				'response' => $resp,
				'status' => $info['http_code'],
				'curl_header' => $curl_header,
				'json_data' => $json_data,
				'info' => $info,
				'errors' => $errors
			);
		}
		return $api_response;
	}

	/*
	 * Cria / Salva a Chave Pública da API V4 a partir do token
	*/
	public function getPublicKey()
	{
		if (!$this->token || $this->token == '') {
			return false;
		} else {
			$api_response = $this->curl_send('POST', $this->urls['api'] . 'public-keys', '{"type": "card"}', 30);
			$public_key_obj = $api_response['response'];
			if ($public_key_obj->public_key != '') {
				if ($this->environment == 1) {
					Configuration::updateValue('PAGBANK_PUBLIC_KEY', $public_key_obj->public_key);
				} else {
					Configuration::updateValue('PAGBANK_PUBLIC_KEY_SANDBOX', $public_key_obj->public_key);
				}
				return true;
			} else {

				return false;
			}
		}
	}

	/*
	* Verifica o Json
	*/
	private function isJson($string)
	{
		json_decode($string);
		return json_last_error() === JSON_ERROR_NONE;
	}

	/*
	 * Salva log com informações para análise/debug
	 */
	public function saveLog($type, $method, $id_cart = false, $data = false, $response = false, $url = false, $cron = 0)
	{
		if (!$response) {
			$response = '';
		}
		if (!$url) {
			$url = '';
		}
		if (!$id_cart) {
			$id_cart = 'NULL';
		}

		$clear_data = $this->removeSensitiveData($data);
		$clear_response = $this->removeSensitiveData($response);

		$query = 'INSERT INTO `' . _DB_PREFIX_ . 'pagbank_logs` (`datetime`, `type`, `method`, `id_cart`, `data`, `response`, `url`, `cron`) VALUES ';
		$query .= ' (NOW(), "' . $type . '", "' . $method . '", ' . $id_cart . ', "' . addslashes($clear_data) . '", "' . addslashes($clear_response) . '" , "' . addslashes($url) . '", ' . $cron . ')';
		if (Db::getInstance()->execute($query) === false) {
			return false;
		}
		return true;
	}

	/*
	 * Remove Dados Sensíveis do Log
	 */
	public function removeSensitiveData($string)
	{
		$obj = json_decode($string);
		if (!is_object($obj)) {
			$clear_string = $string;
		} else {
			if (property_exists($obj, 'customer')) {
				$obj->customer = '##########';
			}
			if (property_exists($obj, 'shipping')) {
				$obj->shipping = '##########';
			}
			if (property_exists($obj, 'access_token')) {
				$obj->access_token = '##########';
			}
			if (property_exists($obj, 'refresh_token')) {
				$obj->refresh_token = '##########';
			}
			if (property_exists($obj, 'account_id')) {
				$obj->account_id = '##########';
			}
			if (property_exists($obj, 'charges')) {
				if ($obj->charges[0]->payment_method->type == 'CREDIT_CARD') {
					$obj->charges[0]->payment_method->card = '##########';
				}
				if ($obj->charges[0]->payment_method->type == 'BOLETO') {
					$obj->charges[0]->payment_method->boleto->holder = '##########';
				}
			}
			$clear_string = json_encode($obj);
		}

		return $clear_string;
	}

	/*
	 * Gera número aleatório na referência do pedido 
	 */
	public function sortRefNumber()
	{
		$numbers = '0123456789';
		$max = strlen($numbers) - 1;
		$result = null;
		for ($i = 0; $i < 6; $i++) {
			$result .= $numbers[mt_rand(0, $max)];
		}
		return $result;
	}

	/*
	 * Retorna IP do usuário
	 */
	public function getUserIp()
	{
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		} elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		} elseif (isset($_SERVER['HTTP_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		} elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		} else {
			$ipaddress = Tools::getRemoteAddr();
		}
		return $ipaddress;
	}

	/*
	 * Gera o desconto no carrinho de compras p/ pedido via Boleto
	 */
	public function generateCartRule($cart)
	{
		if (!$this->active) {
			return;
		}
		if (!$cart) {
			$cart = $this->context->cart;
		}
		$code = (int)($cart->id_customer) . 'PAGBANK_' . $cart->id;

		if (CartRule::cartRuleExists($code))
			return;

		$total = (float)Configuration::get('PAGBANK_DISCOUNT_VALUE');
		$discount_type = Configuration::get('PAGBANK_DISCOUNT_TYPE');
		$languages = Language::getLanguages();

		foreach ($languages as $key => $language) {
			if ($discount_type == 1) {
				$array_name[$language['id_lang']] = "Desconto de $total% no pedido";
			} else {
				$array_name[$language['id_lang']] = "Desconto de R$ $total no pedido";
			}
		}

		$voucher = new CartRule();
		$voucher->reduction_amount = ($discount_type == 2 ? $total : '');
		$voucher->reduction_percent = ($discount_type == 1 ? $total : '');
		$voucher->name = $array_name;
		$voucher->code = $code;
		$voucher->id_customer = (int)($cart->id_customer);
		$voucher->id_currency = (int)($cart->id_currency);
		$voucher->quantity = 1;
		$voucher->quantity_per_user = 1;
		$voucher->cumulable = 1;
		$voucher->cumulable_reduction = 1;
		$voucher->minimum_amount = 0;
		$voucher->active = 1;
		$now = time();
		$voucher->date_from = date("Y-m-d H:i:s", $now);
		$voucher->date_to = date("Y-m-d H:i:s", $now + (3600 * 24));
		if (!$voucher->validateFieldsLang(false) or !$voucher->add())
			die('Cupom não criado.');
		if (!$voucher->update())
			die('Cupom não atualizado.');
		if (!$cart->addCartRule((int)$voucher->id))
			die('Cupom não incluído no carrinho.');
	}

	/*
	 * Solicita a autorização da Aplicação e salva no banco
	 */
	public function getAppAuthorization($code, $app)
	{
		$app = (string)$app;
		if ($app == 'TAX') {
			$code_verifier = Configuration::get('PAGBANK_CODE_VERIFIER_TAX');
		} elseif ($app == 'D14') {
			$code_verifier = Configuration::get('PAGBANK_CODE_VERIFIER_D14');
		} elseif ($app == 'D30') {
			$code_verifier = Configuration::get('PAGBANK_CODE_VERIFIER_D30');
		}
		$api_info = $this->getApiInfo((int)$this->environment, $app);
		$criptogram = $api_info['cipher_text'];
		$redirect_uri = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/update.php';
		$json_authorization = '{
			"grant_type": "authorization_code",
			"code": "' . $code . '",
			"redirect_uri": "' . $redirect_uri . '",
			"code_verifier": "' . $code_verifier . '"
		}';
		$post_url = $this->urls['appauth'];
		$header = array(
			'Authorization: Pub ' . $criptogram,
			'Accept: application/json', 
			'Content-Type: application/json'
		);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $post_url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_authorization);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		$resp = curl_exec($curl);
		$info = curl_getinfo($curl);
		$error = curl_errno($curl);
		$error_message = curl_error($curl);
		$resp_obj = json_decode($resp);
		curl_close($curl);

		$expires = ((int)$resp_obj->expires_in / 86400);
		$expire_date = date('Y-m-d', strtotime("+$expires days"));

		if ($this->environment == 1){
			if ($app == 'TAX') {
				Configuration::updateValue('PAGBANK_TOKEN_TAX', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_EXPIRES_TAX', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_REFRESH_TAX', $resp_obj->refresh_token, false);
			} elseif ($app == 'D14') {
				Configuration::updateValue('PAGBANK_TOKEN_D14', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_EXPIRES_D14', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_REFRESH_D14', $resp_obj->refresh_token, false);
			} elseif ($app == 'D30') {
				Configuration::updateValue('PAGBANK_TOKEN_D30', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_EXPIRES_D30', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_REFRESH_D30', $resp_obj->refresh_token, false);
			}
		} else {
			if ($app == 'TAX') {
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_TAX', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_EXPIRES_TAX', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_REFRESH_TAX', $resp_obj->refresh_token, false);
			} elseif ($app == 'D14') {
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_D14', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_EXPIRES_D14', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_REFRESH_D14', $resp_obj->refresh_token, false);
			} elseif ($app == 'D30') {
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_D30', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_EXPIRES_D30', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_REFRESH_D30', $resp_obj->refresh_token, false);
			}
		}

		$this->saveLog('app authorization', $app, false, json_encode($json_authorization), $resp, $post_url);

		$this->getPublicKey();

		return array(
			'info' => $info,
			'error' => $error,
			'error_msg' => $error_message,
			'resp' => $resp,
			'json_authorization' => $json_authorization,
		);
	}

	/*
	 * Solicita a autorização da Aplicação e salva no banco
	 */
	public function refreshToken($refreshToken, $app)
	{
		$api_info = $this->getApiInfo((int)$this->environment, $app);
		$criptogram = $api_info['cipher_text'];
		$json_refresh = '{
			"grant_type": "refresh_token",
			"refresh_token": "' . $refreshToken . '"
		}';
		$post_url = $this->urls['refresh'];
		$header = array(
			'Authorization: Pub ' . $criptogram,
			'Accept: application/json', 
			'Content-Type: application/json'
		);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $post_url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $json_refresh);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
		$resp = curl_exec($curl);
		$info = curl_getinfo($curl);
		$error = curl_errno($curl);
		$error_message = curl_error($curl);
		$resp_obj = json_decode($resp);
		curl_close($curl);

		$expires = ((int)$resp_obj->expires_in / 86400);
		$expire_date = date('Y-m-d', strtotime("+$expires days"));

		if ($this->environment == 1){
			if ($app == 'TAX') {
				Configuration::updateValue('PAGBANK_TOKEN_TAX', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_EXPIRES_TAX', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_REFRESH_TAX', $resp_obj->refresh_token, false);
			} elseif ($app == 'D14') {
				Configuration::updateValue('PAGBANK_TOKEN_D14', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_EXPIRES_D14', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_REFRESH_D14', $resp_obj->refresh_token, false);
			} elseif ($app == 'D30') {
				Configuration::updateValue('PAGBANK_TOKEN_D30', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_EXPIRES_D30', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_REFRESH_D30', $resp_obj->refresh_token, false);
			}
		} else {
			if ($app == 'TAX') {
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_TAX', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_EXPIRES_TAX', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_REFRESH_TAX', $resp_obj->refresh_token, false);
			} elseif ($app == 'D14') {
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_D14', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_EXPIRES_D14', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_REFRESH_D14', $resp_obj->refresh_token, false);
			} elseif ($app == 'D30') {
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_D30', $resp_obj->access_token, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_EXPIRES_D30', $expire_date, false);
				Configuration::updateValue('PAGBANK_TOKEN_SANDBOX_REFRESH_D30', $resp_obj->refresh_token, false);
			}	
		}

		$this->saveLog('refresh token', $app, false, json_encode($json_refresh), $resp, $post_url);

		$this->getPublicKey();

		return array(
			'info' => $info,
			'error' => $error,
			'error_msg' => $error_message,
			'resp' => $resp,
			'json_authorization' => $json_refresh,
		);
	}

	/*
	* Verifica se o token precisa ser atualizado
	*/
	public function callRefreshToken()
	{
		$today = date('Y-m-d', time());

		if ($this->environment == 1){
			$token_expires_tax = Configuration::get('PAGBANK_TOKEN_EXPIRES_TAX');
			$token_refresh_tax = Configuration::get('PAGBANK_TOKEN_REFRESH_TAX');
			$token_expires_d14 = Configuration::get('PAGBANK_TOKEN_EXPIRES_D14');
			$token_refresh_d14 = Configuration::get('PAGBANK_TOKEN_REFRESH_D14');
			$token_expires_d30 = Configuration::get('PAGBANK_TOKEN_EXPIRES_D30');
			$token_refresh_d30 = Configuration::get('PAGBANK_TOKEN_REFRESH_D30');
		} else {
			$token_expires_tax = Configuration::get('PAGBANK_TOKEN_SANDBOX_EXPIRES_TAX');
			$token_refresh_tax = Configuration::get('PAGBANK_TOKEN_SANDBOX_REFRESH_TAX');
			$token_expires_d14 = Configuration::get('PAGBANK_TOKEN_SANDBOX_EXPIRES_D14');
			$token_refresh_d14 = Configuration::get('PAGBANK_TOKEN_SANDBOX_REFRESH_D14');
			$token_expires_d30 = Configuration::get('PAGBANK_TOKEN_SANDBOX_EXPIRES_D30');
			$token_refresh_d30 = Configuration::get('PAGBANK_TOKEN_SANDBOX_REFRESH_D30');
		}

		if (!empty($token_expires_tax) && !empty($token_refresh_tax) && (strtotime($today) >= strtotime($token_expires_tax))) {
			$this->refreshToken($token_refresh_tax, 'TAX');
		}
		if (!empty($token_expires_d14) && !empty($token_refresh_d14) && (strtotime($today) >= strtotime($token_expires_d14))) {
			$this->refreshToken($token_refresh_d14, 'D14');
		}
		if (!empty($token_expires_d30) && !empty($token_refresh_d30) && (strtotime($today) >= strtotime($token_expires_d30))) {
			$this->refreshToken($token_refresh_d30, 'D30');
		}
	}

	/*
	* Valida o nome do estado
	*/
	public function getStateByIsoCode($uf = false, $param = false)
	{
		$id_country = Country::getIdByName($this->context->language->id, 'Brasil');
		if (!$id_country || (int)$id_country < 1) {
			$id_country = Country::getIdByName($this->context->language->id, 'Brazil');
		}
		if (!$id_country || (int)$id_country < 1) {
			$id_country = 58;
		}
		$states = State::getStatesByIdCountry($id_country);
		$estados = array();
		foreach ($states as $item) {
			$estados[$item['iso_code']] = $item;
		}

		if ($uf != false) {
			if ($param != false) {
				$api_response = $estados[$uf][$param];
			} else {
				$api_response = $estados[$uf];
			}
		} else {
			$api_response = $estados;
		}
		return isset($api_response) && $api_response ? $api_response : false;
	}

	/*
	* Calcula horas/minutos
	*/
	public function hoursMinutes($time_minutes)
	{
		if ($time_minutes < 1) {
			return;
		}
		$hours = floor($time_minutes / 60);
		$minutes = ($time_minutes % 60);
		return array('hours' => $hours, 'minutes' => $minutes);
	}

	/*
	* Verifica se o prazo fornecido é em horas ou minutos
	*/
	public function calculateDeadline($deadline)
	{
		$time = array();
		if ($deadline > 60) {
			$array_time = $this->hoursMinutes($deadline);
			$time['deadline'] = array(
				'hours' => $array_time['hours'],
				'minutes' => $array_time['minutes']
			);
		} else {
			$time['deadline'] = array(
				'hours' => 0,
				'minutes' => $deadline
			);
		}

		return $time['deadline'];
	}

	/*
	* Insere no banco o cartão criptografado
	*/
	public function insertCustomerToken($info)
	{
		$ins_query = 'INSERT INTO `' . _DB_PREFIX_ . 'pagbank_customer_token` (`id_customer`, `card_name`, `card_brand`, `card_first_digits`, `card_last_digits`, `card_month`, `card_year`, `card_token`, `date_add`) ';
		$ins_query .= ' VALUES (' . (int)$info['id_customer'] . ', "' . $info['card_name'] . '", "' . $info['card_brand'] . '", ' . (int)$info['card_first_digits'] . ', ' . (int)$info['card_last_digits'] . ', "' . $info['card_month'] . '", ' . (int)$info['card_year'] . ', "' . $info['card_token'] . '", "' . date("Y-m-d H:i:s") . '")';
		if (!Db::getInstance()->execute($ins_query)) {
			$this->saveLog('error', 'insertCustomerToken', $info['id_customer'], $ins_query, 'Cartão Criptografado não salvo no banco.');
			return false;
		}
		return true;
	}

	/*
	* Seleciona o cliente pelo cartão criptografado
	*/
	public function getCustomerToken($id_customer, $id_card_token = false)
	{
		$get_query = 'SELECT * FROM `' . _DB_PREFIX_ . 'pagbank_customer_token` WHERE `id_customer` = ' . (int)$id_customer;
		if ($id_card_token && (int)$id_card_token > 0) {
			$get_query .= ' AND `id_customer_token` = ' . (int)$id_card_token;
		}
		$return = Db::getInstance()->executeS($get_query);
		if (!$return) {
			return false;
		}
		return $return;
	}

	/*
	* Seleciona o cartão criptografado do cliente
	*/
	public function getCardToken($id_card_token)
	{
		$get_query = 'SELECT `card_token` FROM `' . _DB_PREFIX_ . 'pagbank_customer_token` WHERE `id_customer_token` = ' . (int)$id_card_token;
		$return = Db::getInstance()->getValue($get_query);
		if (!$return) {
			$this->saveLog('error', 'getCardToken', $id_card_token, $get_query, 'Erro ao consultar os dados de cartão salvos para o cliente.');
			return false;
		}
		return $return;
	}

	/*
	* Deleta o cartão criptografado do cliente
	*/
	public function deleteCustomerToken($id_customer_token)
	{
		$delete_query = 'DELETE FROM `' . _DB_PREFIX_ . 'pagbank_customer_token` WHERE `id_customer_token` = ' . (int)$id_customer_token;
		if (!Db::getInstance()->execute($delete_query)) {
			$this->saveLog('error', 'delete', $id_customer_token, $delete_query, 'Erro ao apagar cartão criptografado do banco.');
			return false;
		}
		return 'OK';
	}

	/*
	* Gera o code challenge a partir da chave criptográfica do sistema
	*/
	public function getPkceData($type, $item)
	{
		$verifier_bytes = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '.' . $type . '.' . Tools::getAdminTokenLite("AdminPagBank");
		$code_verifier = rtrim(strtr(base64_encode($verifier_bytes), "+/", "-_"), "=");
		$challenge_bytes = hash("sha256", $code_verifier, true);
		$code_challenge = rtrim(strtr(base64_encode($challenge_bytes), "+/", "-_"), "=");

		$pkce = array(
			'code_verifier' => $code_verifier,
			'code_challenge' => $code_challenge,
		);
		return $pkce[$item];
	}

	/*
	* Remove caracteres especiais da string
	*/
	public function replaceSpecialChars($str)
	{

		$a = array(
			'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ',
			'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í',
			'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć',
			'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ',
			'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ',
			'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ',
			'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š',
			'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ',
			'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ',
			'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ', 'Ά', 'ά', 'Έ', 'έ', 'Ό', 'ό', 'Ώ', 'ώ', 'Ί',
			'ί', 'ϊ', 'ΐ', 'Ύ', 'ύ', 'ϋ', 'ΰ', 'Ή', 'ή', '(', ')', 'º', 'ª', '"'
		);

		$b = array(
			'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O',
			'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i',
			'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C',
			'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G',
			'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ',
			'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n',
			'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's',
			'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y',
			'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U',
			'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o', 'Α', 'α', 'Ε', 'ε', 'Ο', 'ο', 'Ω', 'ω', 'Ι',
			'ι', 'ι', 'ι', 'Υ', 'υ', 'υ', 'υ', 'Η', 'η', '- ', '', 'o', 'a', ''
		);

		return str_replace($a, $b, $str);
	}
}
