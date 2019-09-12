<?php
/*
 * 2019 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class PagSeguroPro extends PaymentModule
{
	private $_html = '';
	public $ps_params;
	public $urls;
	public $credentials;
	public $ambiente;
	public $number_field;
	public $compl_field;
	public $ps_errors;
	public $appId14;
	public $appKey14;
	public $appId30;
	public $appKey30;
	public $appCode;
	public $tipo_credencial;
    
	/*
	 * Função inicial da classe
	 * Define os parâmetros básicos e eventuais 
	 * validações do módulo
	 */
    public function __construct()
    {
        $this->name = 'pagseguropro';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'PrestaBR';
        $this->need_instance = 1;
        $this->controllers = array('payment', 'validation');

	$this->urls = array(
		'notificacao' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/notification.php',
		'img' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/views/img/',
		'redireciona_autorizacao' => 'https://pagseguro.uol.com.br/userapplication/v2/authorization/preregistration.jhtml?code=',
	);
	$this->ambiente = Configuration::get('PAGSEGUROPRO_MODO');
	if($this->ambiente == 0){
		$this->urls['session'] = 'https://ws.sandbox.pagseguro.uol.com.br/v2/sessions';
		$this->urls['transaction'] = 'https://ws.sandbox.pagseguro.uol.com.br/v2/transactions';
		$this->urls['notification'] = 'https://ws.sandbox.pagseguro.uol.com.br/v3/transactions/notifications';
		$this->urls['js'] = 'https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js';
		$this->urls['consulta'] = 'https://ws.sandbox.pagseguro.uol.com.br/v3/transactions';
	}else{
		$this->urls['session'] = 'https://ws.pagseguro.uol.com.br/v2/sessions';
    		$this->urls['transaction'] = 'https://ws.pagseguro.uol.com.br/v2/transactions';
    		$this->urls['notification'] = 'https://ws.pagseguro.uol.com.br/v3/transactions/notifications';
    		$this->urls['js'] = 'https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js';
		$this->urls['consulta'] = 'https://ws.pagseguro.uol.com.br/v3/transactions';
	}

	$this->appId14 = '';
	$this->appKey14 = '';
	$this->appId30 = '';
	$this->appKey30 = '';

        $this->tipo_credencial = Configuration::get('PAGSEGUROPRO_CREDENTIAL');
	if ($this->tipo_credencial == 'TOKEN' || !$this->tipo_credencial || $this->tipo_credencial == ''){
		$this->credentials = array(
			'email' => Configuration::get('PAGSEGUROPRO_EMAIL'),
			'token' => Configuration::get('PAGSEGUROPRO_TOKEN')
		);
	}elseif($this->tipo_credencial == 'D14'){
		$this->credentials = array(
			'appId' => $this->appId14,
			'appKey' => $this->appKey14,
			'authorizationCode' => Configuration::get('PAGSEGUROPRO_AUTHCODE_D14')
		);
	}elseif($this->tipo_credencial == 'D30'){
		$this->credentials = array(
			'appId' => $this->appId30,
			'appKey' => $this->appKey30,
			'authorizationCode' => Configuration::get('PAGSEGUROPRO_AUTHCODE_D30')
		);
	}
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->trans('PagSeguro - Checkout Transparente', array(), 'Modules.PagSeguroPro.Admin');
        $this->description = $this->trans('PagSeguro - Checkout Transparente - Módulo Oficial - PS 1.7.x', array(), 'Modules.PagSeguroPro.Admin');
	$this->description_full = $this->trans('PagSeguro - Checkout Transparente - Módulo Oficial - PS 1.7.x', array(), 'Modules.PagSeguroPro.Admin');
        $this->confirmUninstall = $this->trans('Tem certeza que deseja desinstalar o módulo PagSeguro - Checkout Transparente?', array(), 'Modules.PagSeguroPro.Admin');
        $this->limited_countries = array('BR');
        $this->limited_currencies = array('BRL');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);

	$this->number_field = 'company';
	if (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = "'._DB_PREFIX_.'address" AND COLUMN_NAME = "number"')) {
		$this->number_field = 'number';
	} elseif (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = "'._DB_PREFIX_.'address" AND COLUMN_NAME = "numend"')) {
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
        if (extension_loaded('curl') == false)
        {
            $this->_errors[] = $this->trans('You have to enable the cURL extension on your server to install this module', array(), 'Modules.PagSeguroPro.Admin');
            return false;
        }

        $iso_code = Country::getIsoById(Configuration::get('PS_COUNTRY_DEFAULT'));
        if (in_array($iso_code, $this->limited_countries) == false)
        {
            $this->_errors[] = $this->trans('This module is not available in your country', array(), 'Modules.PagSeguroPro.Admin');
            return false;
        }

	$this->addStatus();

        Configuration::updateValue('PAGSEGUROPRO_MODO', '1');
        Configuration::updateValue('PAGSEGUROPRO_EMAIL', '');
        Configuration::updateValue('PAGSEGUROPRO_TOKEN', '');
        Configuration::updateValue('PAGSEGUROPRO_PAGAMENTO', 'cartao, boleto, transf');
        Configuration::updateValue('PAGSEGUROPRO_MAX_PARCELAS', '12');
        Configuration::updateValue('PAGSEGUROPRO_PARCELAS_SEM_JUROS', '12');
        Configuration::updateValue('PAGSEGUROPRO_AUTORIZADO', _PS_OS_PAYMENT_);
        Configuration::updateValue('PAGSEGUROPRO_CANCELADO', _PS_OS_CANCELED_);
	Configuration::updateValue('PAGSEGUROPRO_ESTORNADO', _PS_OS_REFUND_);
	Configuration::updateValue('PAGSEGUROPRO_EM_ANALISE', Configuration::get('_PS_OS_PAGSEGUROPRO_1'));
	Configuration::updateValue('PAGSEGUROPRO_AGUARDANDO_PAGAMENTO', Configuration::get('_PS_OS_PAGSEGUROPRO_2'));
	Configuration::updateValue('PAGSEGUROPRO_SHOW_CONSOLE', 1);
	Configuration::updateValue('PAGSEGUROPRO_FULL_LOG', 1);
        Configuration::updateValue('PAGSEGUROPRO_DELETE_DB', 0);
        Configuration::updateValue('PAGSEGUROPRO_TIPO_DESCONTO_BOLETO', 0);
        Configuration::updateValue('PAGSEGUROPRO_VALOR_DESCONTO_BOLETO', '0.00');
        Configuration::updateValue('PAGSEGUROPRO_CREDENTIAL', 'TOKEN'); //D14 ou D30
	Configuration::updateValue('PAGSEGUROPRO_AUTHCODE_D14', '');
	Configuration::updateValue('PAGSEGUROPRO_AUTHCODE_D30', '');

        include(dirname(__FILE__).'/sql/install.php');

        return parent::install() &&
	    $this->installTabs() &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionFrontControllerSetMedia') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayPaymentTop');
    }

	/*
	 * Remove abas no menu e tabelas no banco (caso tenha escolhido a opção na configuração do módulo)
	 * Remove os Hooks da aplicação e parâmetros de configuração
	 */
    public function uninstall()
    {
	if((bool)Configuration::get('PAGSEGUROPRO_DELETE_DB') === true) {
		include(dirname(__FILE__).'/sql/uninstall.php');
	}
	if (!Db::getInstance()->delete("configuration", "name LIKE 'PAGSEGUROPRO_%'")) {
	    	return false;
	}
	return parent::uninstall() && $this->uninstallTabs();
    }

	/*
	 * Conteúdo da página de configuração do módulo
	 */
    public function getContent()
    {
		$output = '';

		if ((bool)Tools::isSubmit('submitPagSeguroproModule') === true) {
			$update = $this->postProcess();
			if ($update !== false) {
				$output .= $this->displayConfirmation($this->trans('Configurações atualizadas!', array(), 'Modules.PagSeguroPro.Admin'));
			}
		}
		//Autorizacao
		$gerarAutorizacao = Tools::getValue('gerarAutorizacao');
		$tipoAutorizacao = Tools::getValue('tipoAutorizacao');
		if (isset($gerarAutorizacao) && $gerarAutorizacao == 1 && isset($tipoAutorizacao) && $tipoAutorizacao != ''){
			$getCode = $this->getAppCode($tipoAutorizacao);
			$this->appCode = (string)$getCode['resp_xml']->code;
		}

		$this->context->smarty->assign(array(
		    'module_dir' => $this->_path,
		    'module_version' => $this->version,
		    'link_transacoes' => $this->context->link->getAdminLink("AdminPagSeguroPro", false).'&token='.Tools::getAdminTokenLite("AdminPagSeguroPro"),
		    'link_logs' => $this->context->link->getAdminLink("AdminPagSeguroProLogs", false).'&token='.Tools::getAdminTokenLite("AdminPagSeguroProLogs"),
			'link_page' => $this->context->link->getAdminLink("AdminModules", false).'&token='.Tools::getAdminTokenLite("AdminModules").'&configure=pagseguropro&tab_module=payments_gateways&module_name=pagseguropro',
			'link_app' => $this->urls['redireciona_autorizacao'],
			'appCode' => $this->appCode,
			'this_page' => $_SERVER['REQUEST_URI'],
			'credential' => Configuration::get('PAGSEGUROPRO_CREDENTIAL'),
			'authd14' => Configuration::get('PAGSEGUROPRO_AUTHCODE_D14'),
			'authd30' => Configuration::get('PAGSEGUROPRO_AUTHCODE_D30'),
		));

		$this->getConfigFormValues();
		
		if (((!$this->credentials['email'] || $this->credentials['email'] == '') || (!$this->credentials['token'] || $this->credentials['token'] == '')) 
			|| ((!$this->credentials['appId'] || $this->credentials['appId'] == '') || (!$this->credentials['appKey'] || $this->credentials['appKey'] == '') || (!$this->credentials['authorizationCode'] || $this->credentials['authorizationCode'] == ''))
		) {
			$this->warning = $this->trans('Você precisa cofigurar suas credenciais para que este módulo funcione corretamente.', array(), 'Modules.PagSeguroPro.Admin');
		}
		if (!function_exists('curl_init'))
		{
       		 $output .= $this->displayError($this->trans('Este módulo requer CURL ativado no servidor para funcionar corretamente.', array(), 'Modules.PagSeguroPro.Admin'));
       	}
        if (Configuration::get('PS_DISABLE_NON_NATIVE_MODULE') == '1')
		{
			$output .= $this->displayError($this->trans('Este módulo requer a execução de Módulos não Nativos.', array(), 'Modules.PagSeguroPro.Admin'));
       	}
       	$output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');
		
       	return $output.$this->renderForm();
    }
	
	/* 
	 * Gera o Formulário de configuração do módulo
	 */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPagSeguroproModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

		$fields_value = $this->getConfigFormValues();
		$payments = Configuration::get('PAGSEGUROPRO_PAGAMENTO');
		$array_payments = explode(',', $payments);
		$fields_value['PAGSEGUROPRO_PAGAMENTO[]'] = $array_payments;

        $helper->tpl_vars = array(
            'fields_value' => $fields_value, 
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );
        return $helper->generateForm(array($this->getConfigForm()));
    }

	/* 
	 * Gera os campos de configuração do módulo
	 */
    protected function getConfigForm()
    {
		$statuses = OrderState::getOrderStates($this->context->language->id);
		$array_parcelas = array();
		for ($x = 1; $x <= 12; $x++) {
			$array_parcelas[] = array(
				'id' => $x,
				'name' => $x.'x',
			);
		} 
		
		$array_credentials = array();
		$array_credentials[] = array(
			'id' => 'TOKEN',
			'name' => $this->trans('Padrão (E-mail + Token)', array(), 'Modules.PagSeguroPro.Admin'),
		);
		$d14 = Configuration::get('PAGSEGUROPRO_AUTHCODE_D14', array(), 'Modules.PagSeguroPro.Admin');
		if (isset($d14) && $d14 != ''){
			$array_credentials[] =  array(
				'id' => 'D14',
				'name' => $this->trans('Receber em 14 dias (PrestaShop 1.7 14D)', array(), 'Modules.PagSeguroPro.Admin'),
			);
		}
		$d30 = Configuration::get('PAGSEGUROPRO_AUTHCODE_D30');
		if (isset($d30) && $d30 != ''){
			$array_credentials[] =  array(
				'id' => 'D30',
				'name' => $this->trans('Receber em 30 dias (PrestaShop 1.7 30D)', array(), 'Modules.PagSeguroPro.Admin'),
			);
		}
		$cred_params = array(
			'd14' => array(
				'cartao' => '3,19',
				'boleto' => '3,19',
				'nome' => 'Receber em 14 dias (PrestaShop 1.7 14D).',
			),
			'd30' => array(
				'cartao' => '2,99',
				'boleto' => '2,99',
				'nome' => 'Receber em 30 dias (PrestaShop 1.7 30D).',
			),
		);
		$user_credential = strtolower(Configuration::get('PAGSEGUROPRO_CREDENTIAL'));
		$texto_credencial = '';
		if ($user_credential != '' && $user_credential != 'token') {
			$texto_credencial .= '<br><div class="alert alert-danger"><p>'.$this->trans('Você está utilizando a credencial ', array(), 'Modules.PagSeguroPro.Admin').' <b>'.$cred_params[$user_credential]['nome'].'</b></p>';
			$texto_credencial .= '<p>'.$this->trans('Crédito e Débito Online:', array(), 'Modules.PagSeguroPro.Admin').' '.$cred_params[$user_credential]['cartao'].'% '.$this->trans('por transação.', array(), 'Modules.PagSeguroPro.Admin').'</p>';
			$texto_credencial .= '<p>'.$this->trans('Boleto:', array(), 'Modules.PagSeguroPro.Admin').' R$'.$cred_params[$user_credential]['boleto'].' '.$this->trans('por transação.', array(), 'Modules.PagSeguroPro.Admin').'</p></div>';
		}
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->trans('Configurações', array(), 'Modules.PagSeguroPro.Admin'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Loja em ambiente de Produção?', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_MODO',
                        'bool' => false,
                        'desc' => $this->trans('Você pode utilizar o Ambiente de Testes (Sandbox)  e testar tudo antes de colocar em Produção.', array(), 'Modules.PagSeguroPro.Admin'),
                        'values' => array(
                            array(
                                'id' => 'PAGSEGUROPRO_MODO_on',
                                'value' => 1,
                                'label' => $this->trans('Produção', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                            array(
                                'id' => 'PAGSEGUROPRO_MODO_off',
                                'value' => 0,
                                'label' => $this->trans('Sandbox', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Tipo de Credenciais', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_CREDENTIAL',
                        'desc' => $this->trans('Defina o tipo de credencial que a sua loja irá utilizar para processar os pagamentos.', array(), 'Modules.PagSeguroPro.Admin').$texto_credencial,
						'onchange' => 'showCredentials($(this).val());',
						'class' => 'credentials',
                        'options' => array(
							'query' => $array_credentials,
							'id' => 'id', 
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-credit-card"></i>',
                        'name' => 'PAGSEGUROPRO_EMAIL',
                        'label' => $this->trans('E-mail', array(), 'Modules.PagSeguroPro.Admin'),
                        'desc' => $this->trans('Informe o e-mail cadastrado no PagSeguro (Sandbox ou Produção)', array(), 'Modules.PagSeguroPro.Admin'),
						'class' => 'normal',
                    ),
                    array(
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'name' => 'PAGSEGUROPRO_TOKEN',
                        'label' => $this->trans('Token', array(), 'Modules.PagSeguroPro.Admin'),
                        'desc' => $this->trans('Informe o Token cadastrado no PagSeguro (Sandbox ou Produção)', array(), 'Modules.PagSeguroPro.Admin'),
						'class' => 'normal',
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Opções de Pagamento', array(), 'Modules.PagSeguroPro.Admin'),
						'name' => 'PAGSEGUROPRO_PAGAMENTO[]',
						'multiple' => true,
                        'desc' => $this->trans('Escolha os meios de pagamento que serão ativos em sua loja.', array(), 'Modules.PagSeguroPro.Admin').' <br /> <b>OBS.:</b> <br />Consulte antes se o meio de pagamento está ativo em sua conta <b><a href="https://pagseguro.uol.com.br/preferences/receiving.jhtml" target="_blank" title="Configurar Meios de Pagamento">PagSeguro</a></b>. <br />Por padrão, para Boleto Bancário, é cobrada uma taxa de emissão no valor de R$ 1,00, mas você pode negociar suas taxas e tarifas para todas as formas de pagamento.',
						'options' => array(
							'query' => array(
								array(
									'id' => 'cartao',
									'name' => $this->trans('Cartão', array(), 'Modules.PagSeguroPro.Admin'),
								),
								array(
									'id' => 'boleto',
									'name' => $this->trans('Boleto', array(), 'Modules.PagSeguroPro.Admin'),
								),
								array(
									'id' => 'transf',
									'name' => $this->trans('Transferência Bancária', array(), 'Modules.PagSeguroPro.Admin'),
								),
							),
							'id' => 'id',
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Status de Pagamento Autorizado', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_AUTORIZADO',
                        'desc' => $this->trans('Defina o Status que sua loja utiliza como "Pagamento Autorizado".', array(), 'Modules.PagSeguroPro.Admin'),
                        'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state', 
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Status de Pedido Cancelado', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_CANCELADO',
                        'desc' => $this->trans('Defina o Status que sua loja utiliza como "Pedido Cancelado".', array(), 'Modules.PagSeguroPro.Admin'),
                        'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state', 
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Status de Pedido Estornado'),
                        'name' => 'PAGSEGUROPRO_ESTORNADO',
                        'desc' => $this->l('Defina o Status que sua loja utiliza como \"Pedido Estornado\".'),
                        'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state', 
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Status de Pagamento Em Análise', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_EM_ANALISE',
                        'desc' => $this->trans('Defina o Status que sua loja utiliza como "Pagamento Em Análise".', array(), 'Modules.PagSeguroPro.Admin'),
                        'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state', 
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Status de Aguardando Pagamento do Boleto', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_AGUARDANDO_PAGAMENTO',
                        'desc' => $this->trans('Defina o Status que sua loja utiliza como "Aguardando Pagamento do Boleto".', array(), 'Modules.PagSeguroPro.Admin'),
                        'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state', 
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Quantidade máxima de parcelas', array(), 'Modules.PagSeguroPro.Admin'),
			'class' => 'fixed-width-xs',
                        'name' => 'PAGSEGUROPRO_MAX_PARCELAS',
                        'desc' => $this->trans('Defina a quantidade máxima de parcelas para seus clientes.', array(), 'Modules.PagSeguroPro.Admin'),
						'options' => array(
							'query' => $array_parcelas,
							'id' => 'id',
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Quantidade de parcelas sem juros', array(), 'Modules.PagSeguroPro.Admin'),
			'class' => 'fixed-width-xs',
                        'name' => 'PAGSEGUROPRO_PARCELAS_SEM_JUROS',
                        'desc' => $this->trans('Defina a quantidade de parcelas sem juros para seus clientes.', array(), 'Modules.PagSeguroPro.Admin'),
						'options' => array(
							'query' => $array_parcelas,
							'id' => 'id',
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->trans('Desconto no Boleto Bancário?', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_TIPO_DESCONTO_BOLETO',
                        'desc' => $this->trans('Defina o tipo de desconto que será aplicado no boleto bancário.', array(), 'Modules.PagSeguroPro.Admin'),
						'options' => array(
							'query' => array(
								array(
									'id' => '0',
									'name' => $this->trans('Nenhum Desconto', array(), 'Modules.PagSeguroPro.Admin'),
								),
								array(
									'id' => '1',
									'name' => $this->trans('Percentual', array(), 'Modules.PagSeguroPro.Admin'),
								),
								array(
									'id' => '2',
									'name' => $this->trans('Valor Fixo', array(), 'Modules.PagSeguroPro.Admin'),
								),
							),
							'id' => 'id',
							'name' => 'name',
						),
                    ),
                    array(
                        'type' => 'text',
						'class' => 'fixed-width-xs',
                        'label' => $this->trans('Valor do desconto', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_VALOR_DESCONTO_BOLETO',
                        'desc' => $this->trans('Defina o valor do desconto. Exemplo: 15.00', array(), 'Modules.PagSeguroPro.Admin'),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Exibir parâmetros no Console do navegador?', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_SHOW_CONSOLE',
                        'is_bool' => true,
                        'desc' => $this->trans('Mostrar mensagens do JavaScript no console do navegador para fins de depuração.', array(), 'Modules.PagSeguroPro.Admin'),
                        'values' => array(
                            array(
                                'id' => 'console_on',
                                'value' => 1,
                                'label' => $this->trans('Sim', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                            array(
                                'id' => 'console_off',
                                'value' => 0,
                                'label' => $this->trans('Não', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Gerar LOGs completos?', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_FULL_LOG',
                        'is_bool' => true,
                        'desc' => $this->trans('Logs completos registram tudo que é enviado e recebido pela loja.', array(), 'Modules.PagSeguroPro.Admin'),
                        'values' => array(
                            array(
                                'id' => 'logs_on',
                                'value' => 1,
                                'label' => $this->trans('Sim', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                            array(
                                'id' => 'logs_off',
                                'value' => 0,
                                'label' => $this->trans('Não', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->trans('Apagar tabelas do banco?', array(), 'Modules.PagSeguroPro.Admin'),
                        'name' => 'PAGSEGUROPRO_DELETE_DB',
                        'is_bool' => true,
                        'desc' => $this->trans('Recomendamos deixar esta opção desabilitada. Ative apenas se tiver certeza de que não vai mais precisar das informações.', array(), 'Modules.PagSeguroPro.Admin'),
                        'values' => array(
                            array(
                                'id' => 'deletebd_on',
                                'value' => 1,
                                'label' => $this->trans('Sim', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                            array(
                                'id' => 'deletebd_off',
                                'value' => 0,
                                'label' => $this->trans('Não', array(), 'Modules.PagSeguroPro.Admin'),
                            ),
                        ),
                    ),
                ),
                'submit' => array(
                    'title' => $this->trans('Salvar', array(), 'Modules.PagSeguroPro.Admin'),
                ),
            ),
        );
    }

	/* 
	 * Pega os dados dos campos de configuração do módulo
	 */
    protected function getConfigFormValues()
    {
		$pagtos = Tools::getValue('PAGSEGUROPRO_PAGAMENTO');

        return array(
            'PAGSEGUROPRO_MODO' => Tools::getValue('PAGSEGUROPRO_MODO', Configuration::get('PAGSEGUROPRO_MODO')),
            'PAGSEGUROPRO_EMAIL' => Tools::getValue('PAGSEGUROPRO_EMAIL', Configuration::get('PAGSEGUROPRO_EMAIL')),
            'PAGSEGUROPRO_TOKEN' => Tools::getValue('PAGSEGUROPRO_TOKEN', Configuration::get('PAGSEGUROPRO_TOKEN')),
			'PAGSEGUROPRO_PAGAMENTO' => is_array($pagtos) ? $pagtos : explode(',', $pagtos),
            'PAGSEGUROPRO_AUTORIZADO' => Tools::getValue('PAGSEGUROPRO_AUTORIZADO', Configuration::get('PAGSEGUROPRO_AUTORIZADO')),
            'PAGSEGUROPRO_CANCELADO' => Tools::getValue('PAGSEGUROPRO_CANCELADO', Configuration::get('PAGSEGUROPRO_CANCELADO')),
            'PAGSEGUROPRO_ESTORNADO' => Tools::getValue('PAGSEGUROPRO_ESTORNADO', Configuration::get('PAGSEGUROPRO_ESTORNADO')),
            'PAGSEGUROPRO_EM_ANALISE' => Tools::getValue('PAGSEGUROPRO_EM_ANALISE', Configuration::get('PAGSEGUROPRO_EM_ANALISE')),
            'PAGSEGUROPRO_AGUARDANDO_PAGAMENTO' => Tools::getValue('PAGSEGUROPRO_AGUARDANDO_PAGAMENTO', Configuration::get('PAGSEGUROPRO_AGUARDANDO_PAGAMENTO')),
            'PAGSEGUROPRO_MAX_PARCELAS' => Tools::getValue('PAGSEGUROPRO_MAX_PARCELAS', Configuration::get('PAGSEGUROPRO_MAX_PARCELAS')),
            'PAGSEGUROPRO_PARCELAS_SEM_JUROS' => Tools::getValue('PAGSEGUROPRO_PARCELAS_SEM_JUROS', Configuration::get('PAGSEGUROPRO_PARCELAS_SEM_JUROS')),
            'PAGSEGUROPRO_TIPO_DESCONTO_BOLETO' => Tools::getValue('PAGSEGUROPRO_TIPO_DESCONTO_BOLETO', Configuration::get('PAGSEGUROPRO_TIPO_DESCONTO_BOLETO')),
            'PAGSEGUROPRO_VALOR_DESCONTO_BOLETO' => Tools::getValue('PAGSEGUROPRO_VALOR_DESCONTO_BOLETO', Configuration::get('PAGSEGUROPRO_VALOR_DESCONTO_BOLETO')),
            'PAGSEGUROPRO_SHOW_CONSOLE' => Tools::getValue('PAGSEGUROPRO_SHOW_CONSOLE', Configuration::get('PAGSEGUROPRO_SHOW_CONSOLE')),
            'PAGSEGUROPRO_FULL_LOG' => Tools::getValue('PAGSEGUROPRO_FULL_LOG', Configuration::get('PAGSEGUROPRO_FULL_LOG')),
            'PAGSEGUROPRO_DELETE_DB' => Tools::getValue('PAGSEGUROPRO_DELETE_DB', Configuration::get('PAGSEGUROPRO_DELETE_DB')),
            'PAGSEGUROPRO_CREDENTIAL' => Tools::getValue('PAGSEGUROPRO_CREDENTIAL', Configuration::get('PAGSEGUROPRO_CREDENTIAL')),
        );
    }

	/* 
	 * Atualiza os campos de configuração do módulo
	 */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();
		$pagtos = implode(',', Tools::getValue('PAGSEGUROPRO_PAGAMENTO'));
		$erro = false;
        foreach (array_keys($form_values) as $key) 
		{
			if ($key == 'PAGSEGUROPRO_PAGAMENTO') {
				if (!Configuration::updateValue('PAGSEGUROPRO_PAGAMENTO', $pagtos)) {
					$erro = true;
				}
			}else {
				if (!Configuration::updateValue($key, Tools::getValue($key))) {
					$erro = true;
				}
			}
        }
		if($erro) {
			return false;
		}else {
			return true;
		}
    }

	/* 
	 * Adiciona Status do PagSeguro. 
	 * Podem ser utilizados ou alterados na configuração do módulo 
	 */
    private function addStatus() 
	{
		$os = array(
			'0' => $this->trans('PagSeguro - Iniciado', array(), 'Modules.PagSeguroPro.Admin'),
			'1' => $this->trans('PagSeguro - Em Análise', array(), 'Modules.PagSeguroPro.Admin'),
			'2' => $this->trans('PagSeguro - Aguardando Pagamento', array(), 'Modules.PagSeguroPro.Admin'),
			'3' => $this->trans('PagSeguro - Autorizado', array(), 'Modules.PagSeguroPro.Admin'),
		);

		foreach($os AS $k=>$value)
		{
			$orderState=new OrderState();
			$orderState->name=array();
			$orderState->template=array();
			foreach(Language::getLanguages() AS $key=>$language)
			{
				$orderState->name[$language['id_lang']]=(string)$value;
				$orderState->template[$language['id_lang']]='pagseguropro';
			}
			$orderState->send_email=false;
			$orderState->invoice=false;
			$orderState->color='#6495ED';
			$orderState->unremovable=false;
			$orderState->logable=false;
			$orderState->delivery=false;
			$orderState->hidden=false;
			if($orderState->add()) {
				@copy(dirname(__FILE__).'/logo.gif',_PS_IMG_DIR_.'os/'.$orderState->id.'.gif');
			}
			Configuration::updateValue('_PS_OS_PAGSEGUROPRO_'.$k,$orderState->id);
		}
		return true;
    }

	/* 
	 * Adiciona Abas no menu do BackOffice
	 */
    private function installTabs() 
	{
		$menuItems = array(
			0 => array(
				'parent_class' => 'SELL',
				'class_name' => 'PagSeguroPro',
				'name' => 'PagSeguro',
				'icon' => 'pagseguro',
			),
			1 => array(
				'parent_class' => 'PagSeguroPro',
				'class_name' => 'AdminPagSeguroPro',
				'name' => 'PagSeguro - Transações',
			),
			2 => array(
				'parent_class' => 'PagSeguroPro',
				'class_name' => 'AdminPagSeguroProLogs',
				'name' => 'PagSeguro - Logs',
			),
		);
		
		foreach ($menuItems as $newMenu) 
		{
            $tab = new Tab();
            $tab->module = $this->name;
            $tab->id_parent = (int) Tab::getIdFromClassName($newMenu['parent_class']);
            $tab->class_name = $newMenu['class_name'];

            foreach (Language::getLanguages(false) as $lang) {
                $tab->name[(int) $lang['id_lang']] = $newMenu['name'];
            }
            if (!empty($newMenu['icon'])) {
				$tab->icon = $newMenu['icon'];
			}
            if (!$tab->add()) {
                return false;
            }
		}
        return true;
    }

	/* 
	 * Remove Abas no menu do BackOffice
	 */
    private function uninstallTabs() 
	{
		$tabs = array(
			Tab::getIdFromClassName('PagSeguroPro'),
			Tab::getIdFromClassName('AdminPagSeguroPro'),
			Tab::getIdFromClassName('AdminPagSeguroProLogs')
		);	
		foreach ($tabs as $id_tab) {
			$tab = new Tab($id_tab);
			$tab->delete();
		}
        return true;
    }

	/* 
	 * Pega dados do pedido no banco
	 */
	public function getOrderData($id_op,$field)
	{
		$result =Db::getInstance()->getRow('
			SELECT * FROM `'._DB_PREFIX_.'pagseguropro`
			WHERE `'.$field.'` = "'.$id_op.'"
			ORDER BY `id_pagseguro` DESC
		');
		return $result;
	}
	
	/* 
	 * Atualiza dados do pedido no banco
	 */
	public function updatePagSeguroData($data)
	{
		if(!$this->getOrderData($data['cod_transacao'], 'cod_transacao')) {
			return $this->insertPagSeguroData($data);
		}else{
			$updateQuery = 'UPDATE `'._DB_PREFIX_.'pagseguropro` SET ';
			if (isset($data['cod_cliente']) && $data['cod_cliente'] != "") {
				$updateQuery .= '`cod_cliente` = '.$data['cod_cliente'].', ';
			}
			if (isset($data['cpf_cnpj']) && $data['cpf_cnpj'] != "") {
				$updateQuery .= '`cpf_cnpj` = "'.$data['cpf_cnpj'].'", ';
			}
			if (isset($data['status']) && $data['status'] != "") {
				$updateQuery .= '`status` = "'.$data['status'].'", ';
			}
			if (isset($data['desc_status']) && $data['desc_status'] != "") {
				$updateQuery .= '`desc_status` = "'.$data['desc_status'].'", ';
			}
			if (isset($data['pagto']) && $data['pagto'] != "") {
				$updateQuery .= '`pagto` = "'.$data['pagto'].'",';
			}
			if (isset($data['desc_pagto']) && $data['desc_pagto'] != "") {
				$updateQuery .= '`desc_pagto` = "'.$data['desc_pagto'].'", ';
			}
			if (isset($data['parcelas']) && (int)$data['parcelas'] > 0) {
				$updateQuery .= '`parcelas` = "'.(int)$data['parcelas'].'", ';
			}
			if (isset($data['data_pedido']) && $data['data_pedido'] != "") {
				$updateQuery .= '`data_pedido` = "'.$data['data_pedido'].'", ';	
			}
			if (isset($data['url']) && $data['url'] != "") {
				$updateQuery .= '`url` = "'.$data['url'].'", ';
			}
			if (isset($data['credencial']) && $data['credencial'] != "") {
				$updateQuery .= '`credencial` = "'.$data['credencial'].'", ';
			}
			if (isset($data['token_codigo']) && $data['token_codigo'] != "") {
				$updateQuery .= '`token_codigo` = "'.$data['token_codigo'].'", ';
			}
			if (isset($data['data_atu']) && $data['data_atu'] != "") {
				$updateQuery .= '`data_atu` = "'.$data['data_atu'].'" ';	
			}else{
			    $updateQuery .= '`data_atu` = "'.date("Y-m-d H:i:s").'" ';
			}
			$updateQuery .= ' WHERE `cod_transacao` = "'.$data['cod_transacao'].'"';
			if (!Db::getInstance()->execute($updateQuery)) {
				$this->saveLog('error', 'update', false, $updateQuery, 'Pedido nao atualizado no banco.');
				return false;
			}
			return true;
		}
	}
	
	/* 
	 * Insere dados do pedido no banco
	 */
	public function insertPagSeguroData($data)
	{
		if($this->getOrderData($data['cod_transacao'], 'cod_transacao')) {
			return $this->updatePagSeguroData($data);
		}else{
			foreach ($data as $k=>$item) 
			{
				if ($k != 'id_order') {
					if(!$data[$k] || $data[$k] === false) {
						$data[$k] = '';
					}
				}
			}
			$shop_id = 1;
			if (!is_null($this->context->shop->id)) {
				$shop_id = $this->context->shop->id;
			}

			if ($this->tipo_credencial == 'TOKEN' || !$this->tipo_credencial || $this->tipo_credencial == ''){
				$token_codigo = Configuration::get('PAGSEGUROPRO_TOKEN');
			}elseif($this->tipo_credencial == 'D14'){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D14');
			}elseif($this->tipo_credencial == 'D30'){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D30');
			}
			$insQuery = 'INSERT INTO `'._DB_PREFIX_.'pagseguropro` (`id_shop`, `cod_cliente`, `cpf_cnpj`, `id_cart`, `id_order`, `referencia`, `cod_transacao`, `buyer_ip`, `status`, `desc_status`, `pagto`, `desc_pagto`, `parcelas`, `url`, `credencial`, `token_codigo`, `data_pedido`, `data_atu`) VALUES ('.(int)$shop_id.', "'.$data['cod_cliente'].'", "'.$data['cpf_cnpj'].'", '.$data['id_cart'].', '.$data['id_order'].', "'.$data['referencia'].'", "'.$data['cod_transacao'].'", "'.$data['buyer_ip'].'", "'.$data['status'].'", "'.$data['desc_status'].'", "'.$data['pagto'].'", "'.$data['desc_pagto'].'", '.(int)$data['parcelas'].', "'.$data['url'].'", "'.$this->tipo_credencial.'", "'.$token_codigo.'", "'.$data['data_pedido'].'", "'.date("Y-m-d H:i:s").'")';
			if (!Db::getInstance()->execute($insQuery)) {
				$this->saveLog('error', 'insert', $data['id_cart'], $insQuery, 'Pedido nao inserido no banco.');
				return false;
			}
			return true;
		}
	}
	
	/* 
	 * Formata número com decimais
	 */
	public function formatarDecimais($valorStr)
	{
		$valorStr =str_ireplace('.','',$valorStr);
		$valor=$valorStr/100;
		return $valor;
	}
	
	/* 
	 * Cria Session ID no PagSeguro
	 */
    public function getSessionId() 
	{
		$retorno = $this->curl_send('POST', $this->urls['session'], $this->credentials, 10, $this->context->cart->id);
        if (!$retorno['error'] || $retorno['error'] == 0) {
            $xml = simplexml_load_string($retorno['resposta']);
            return json_encode($xml);
        }else {
			$this->ps_errors[] = 'Erro ao gerar Session ID';
            $this->processaRetornoErro($retorno);
            return false;
        }
    }
    
	/* 
	 * Processa pagamento com Cartão de crédito no PagSeguro
	 */
    public function processarCartao($dados_form) 
	{
		$this->ps_errors = array();
        $this->ps_params = array();

		if ($this->tipo_credencial == 'TOKEN' || !$this->tipo_credencial || $this->tipo_credencial == ''){
			$this->ps_params['email'] = $this->credentials['email'];
			$this->ps_params['token'] = $this->credentials['token'];
	        $this->ps_params['receiverEmail'] = Configuration::get('PAGSEGUROPRO_EMAIL');
		}elseif($this->tipo_credencial == 'D14' || $this->tipo_credencial == 'D30'){
			$this->ps_params['appId'] = $this->credentials['appId'];
			$this->ps_params['appKey'] = $this->credentials['appKey'];
			$this->ps_params['authorizationCode'] = $this->credentials['authorizationCode'];
		}

        $this->ps_params['paymentMethod'] = 'creditCard';
        $this->procPadroes();
        $this->procProdutos();
        $this->procValoresExtras();
        $this->procDadosComprador($dados_form);
        $this->procDadosEntrega();
        $this->procDadosCartao($dados_form);
        $this->procDadosCobranca($dados_form);

		$retorno = $this->curl_send('POST',$this->urls['transaction'], $this->ps_params, 30, $dados_form['cart_id']);
        if ($retorno['error'] == 0 || $retorno['error'] == '') {
            $xml = simplexml_load_string($retorno['resposta']);
            return $xml;
        }else {
			$this->ps_errors[] = 'Erro no processamento do cartão.';
            $this->processaRetornoErro($retorno);
			return false;
        }
    }
    
	/* 
	 * Processa pagamento com Boleto no PagSeguro
	 */
    public function processarBoleto($dados_form) 
	{
		$this->ps_errors = array();
        $this->ps_params = array();

		if ($this->tipo_credencial == 'TOKEN' || !$this->tipo_credencial || $this->tipo_credencial == ''){
			$this->ps_params['email'] = $this->credentials['email'];
			$this->ps_params['token'] = $this->credentials['token'];
	        $this->ps_params['receiverEmail'] = Configuration::get('PAGSEGUROPRO_EMAIL');
		}elseif($this->tipo_credencial == 'D14' || $this->tipo_credencial == 'D30'){
			$this->ps_params['appId'] = $this->credentials['appId'];
			$this->ps_params['appKey'] = $this->credentials['appKey'];
			$this->ps_params['authorizationCode'] = $this->credentials['authorizationCode'];
		}

        $this->ps_params['paymentMethod'] = 'boleto';
        $this->procPadroes();
        $this->procProdutos();
        $this->procValoresExtras();
        $this->procDadosComprador($dados_form);
        $this->procDadosEntrega();
        
		$retorno = $this->curl_send('POST',$this->urls['transaction'], $this->ps_params, 30, $dados_form['cart_id']);
        if ($retorno['error'] == 0) {
            $xml = simplexml_load_string($retorno['resposta']);
            return $xml;
        }else {
			$this->ps_errors[] = 'Erro no processamento do boleto.';
            $this->processaRetornoErro($retorno);
            return false;
        }

    }
    
	/* 
	 * Processa pagamento com Transferência no PagSeguro
	 */
    public function processarTransf($dados_form) 
	{
		$this->ps_errors = array();
        $this->ps_params = array();
		if ($this->tipo_credencial == 'TOKEN' || !$this->tipo_credencial || $this->tipo_credencial == ''){
			$this->ps_params['email'] = $this->credentials['email'];
			$this->ps_params['token'] = $this->credentials['token'];
	        $this->ps_params['receiverEmail'] = Configuration::get('PAGSEGUROPRO_EMAIL');
		}elseif($this->tipo_credencial == 'D14' || $this->tipo_credencial == 'D30'){
			$this->ps_params['appId'] = $this->credentials['appId'];
			$this->ps_params['appKey'] = $this->credentials['appKey'];
			$this->ps_params['authorizationCode'] = $this->credentials['authorizationCode'];
		}

        $this->ps_params['paymentMethod'] = 'eft';
        $this->ps_params['bankName'] = $dados_form['banco'];
        $this->procPadroes();
        $this->procProdutos();
        $this->procValoresExtras();
        $this->procDadosComprador($dados_form);
        $this->procDadosEntrega();
    
		$retorno = $this->curl_send('POST',$this->urls['transaction'], $this->ps_params, 30, $dados_form['cart_id']);
        if ($retorno['error'] == 0) {
            $xml = simplexml_load_string($retorno['resposta']);
            return $xml;
        }else {
			$this->ps_errors[] = 'Erro no processamento da transferência.';
            $this->processaRetornoErro($retorno);
            return false;
        }
    }
    
	/* 
	 * Retorna dados da notificação enviada pelo PagSeguro
	 * $code = Código da notificação
	 */
    public function getNotification($code) 
	{
		$retorno = $this->curl_send('GET',$this->urls['notification'].'/'.$code, $this->credentials, 30, false);
        if ($retorno['error'] == 0) {
            $xml = simplexml_load_string($retorno['resposta']);
            return $xml;
        }else{
			$this->ps_errors[] = 'Erro ao consultar Notificação.';
            $this->processaRetornoErro($retorno);
            return false;
		}
		return $retorno;
    }
    
	/* 
	 * Retorna dados da transação no PagSeguro
	 * $code = Código da transação
	 */
    public function getTransaction($code, $credential = false, $token_codigo = false)
	{
		$credentials = strtoupper($credential);
		if ($credentials === false || $credentials == '' || $credentials == 'TOKEN') { 
			$order_credentials = array(
				'email' => Configuration::get('PAGSEGUROPRO_EMAIL'),
				'token' => Configuration::get('PAGSEGUROPRO_TOKEN')
			);
		}elseif($credentials == 'D14'){
			if ($token_codigo === false || $token_codigo == ''){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D14');
			}
			$order_credentials = array(
				'appId' => $this->appId14,
				'appKey' => $this->appKey14,
				'authorizationCode' => $token_codigo
			);
		}elseif($credentials == 'D30'){
			if ($token_codigo === false || $token_codigo == ''){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D30');
			}
			$order_credentials = array(
				'appId' => $this->appId30,
				'appKey' => $this->appKey30,
				'authorizationCode' => $token_codigo
			);
		}
		$retorno = $this->curl_send('GET',$this->urls['transaction'].'/'.$code, $order_credentials, 30, false);
        if ($retorno['error'] == 0) {
            $xml = simplexml_load_string($retorno['resposta']);
            return $xml;
        }else{
			$this->ps_errors[] = 'Erro ao consultar Transação.';
            $this->processaRetornoErro($retorno);
            return false;
		}
    }
    
	/* 
	 * Cancela a transação no PagSeguro
	 * $code = Código da transação
	 */
    public function cancelTransaction($code, $credential = false)
	{
		$credentials = strtoupper($credential);
		if ($credentials === false || $credentials == '' || $credentials == 'TOKEN') { 
			$order_credentials = array(
				'email' => Configuration::get('PAGSEGUROPRO_EMAIL'),
				'token' => Configuration::get('PAGSEGUROPRO_TOKEN')
			);
		}elseif($credentials == 'D14'){
			if ($token_codigo === false || $token_codigo == ''){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D14');
			}
			$order_credentials = array(
				'appId' => $this->appId14,
				'appKey' => $this->appKey14,
				'authorizationCode' => $token_codigo
			);
		}elseif($credentials == 'D30'){
			if ($token_codigo === false || $token_codigo == ''){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D30');
			}
			$order_credentials = array(
				'appId' => $this->appId30,
				'appKey' => $this->appKey30,
				'authorizationCode' => $token_codigo
			);
		}
		$retorno = $this->curl_send('POST',$this->urls['transaction'].'/cancels?transactionCode='.$code, $order_credentials, 20, false);
        if ($retorno['error'] == 0) {
            $xml = simplexml_load_string($retorno['resposta']);
            return $xml;
        }else{
			$this->ps_errors[] = 'Erro ao cancelar Transação.';
            $this->processaRetornoErro($retorno);
            return false;
		}
    }
    
	/* 
	 * Devolve o valor da transação ao cliente
	 * $code = Código da transação
	 * $value = valor devolvido, caso seja devolução parcial
	 */
    public function refundTransaction($code, $value = false, $credential = false) 
	{
		$credentials = strtoupper($credential);
		if ($credentials === false || $credentials == '' || $credentials == 'TOKEN') { 
			$order_credentials = array(
				'email' => Configuration::get('PAGSEGUROPRO_EMAIL'),
				'token' => Configuration::get('PAGSEGUROPRO_TOKEN')
			);
		}elseif($credentials == 'D14'){
			if ($token_codigo === false || $token_codigo == ''){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D14');
			}
			$order_credentials = array(
				'appId' => $this->appId14,
				'appKey' => $this->appKey14,
				'authorizationCode' => $token_codigo
			);
		}elseif($credentials == 'D30'){
			if ($token_codigo === false || $token_codigo == ''){
				$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D30');
			}
			$order_credentials = array(
				'appId' => $this->appId30,
				'appKey' => $this->appKey30,
				'authorizationCode' => $token_codigo
			);
		}
		if($value !== false){
			$retorno = $this->curl_send('POST',$this->urls['transaction'].'/refunds?transactionCode='.$code.'&refundValue='.$value, $order_credentials, 20, false);
		}else{
			$retorno = $this->curl_send('POST',$this->urls['transaction'].'/refunds?transactionCode='.$code, $order_credentials, 20, false);
		}
        if ($retorno['error'] == 0) {
            $xml = simplexml_load_string($retorno['resposta']);
            return $xml;
        }else{
			$this->ps_errors[] = 'Erro ao estornar Transação.';
            $this->processaRetornoErro($retorno);
            return false;
		}
    }
    
	/* 
	 * Atualiza Status do pedido na loja
	 */
    public function updateOrderStatus($id_cart, $cod_status, $id_order = null, $data_atu = null) 
	{
		if (!$id_order || $id_order == '' || $id_order < 1){
			$id_order = Order::getOrderByCartId($id_cart);
		}
		if (!$id_order || $id_order == '' || $id_order < 1){
			return false;
		}
		
		$order = new Order($id_order);
		$current_status = (int)$order->getCurrentState();
		$status_ps = $this->correspondStatus((int)$cod_status);
		if ($current_status == $status_ps) {
			return true;
		}

		$status_history = $order->getHistory($this->context->language->id);
		$s_history = array();
		foreach ($status_history as $status){
			$s_history[] = $status['id_order_state'];
		}
        if (isset($status_ps) && $status_ps != false && $current_status != $status_ps) {
			if (in_array(Configuration::get("PS_OS_PAYMENT"), $s_history) != false || in_array(Configuration::get("PAGSEGUROPRO_AUTORIZADO"), $s_history) != false || ((bool)$order->valid === true && $cod_status != 7)) {
				$this->saveLog('status', '', $id_cart, 'Status PagSeguro: '.$this->parseStatus($cod_status), 'Status Loja: '.$current_status);
				return true;
			}else{
				$history = new OrderHistory();
				$history->id_order = (int)$id_order;
				if (!$history->changeIdOrderState($status_ps,(int)$id_order)) {
					$this->saveLog('error', 'Atualiza Status', $id_cart, 'Status PagSeguro: '.$status_ps.' / Status Loja: '.(int)$current_status, 'Status do pedido não atualizado na loja.');
				}
				$info = $this->getOrderData($id_order, 'id_order');
				$templateVars = array(
					'{cod_transacao}'=>$info['cod_transacao'],
					'{status}'=>$cod_status,
					'{desc_status}'=>$this->parseStatus($cod_status)
				);
				$history->addWithemail(true, $templateVars);				
				$data = array(
					'cod_transacao' => $info['cod_transacao'],
					'status' => $cod_status,
					'desc_status' => $this->parseStatus($cod_status), 
					'data_atu' => isset($data_atu) && $data_atu ? $data_atu : ''
				);
				if (!$this->updatePagSeguroData($data)) {
					$this->saveLog('status', '', $id_cart, 'Status PagSeguro: '.$this->parseStatus($cod_status), 'Status não atualizado na tabela do PagSeguro.');
				}
    			return true;
			}
			return true;
        }
    }
    
	/* 
	 * Processa parâmetros comuns de envio de transações
	 */
    private function procPadroes() 
	{
        $this->ps_params['notificationURL'] = $this->urls['notificacao'];
        $this->ps_params['currency'] = 'BRL';
        $this->ps_params['paymentMode'] = 'default';
        $this->ps_params['reference'] = $this->context->cart->id.'.'.$this->gerarChave();
		if (empty($this->ps_params['receiverEmail']) || empty($this->ps_params['notificationURL']) || empty($this->ps_params['currency']) || empty($this->ps_params['paymentMode']) || empty($this->ps_params['reference'])){
			$this->ps_errors[] = 'Erro ao Processar Padrões.';
		}
    }
    
	/* 
	 * Processa produtos do carrinho para envio ao PagSeguro
	 */
    private function procProdutos() 
	{
        $i = 0;
        $produtos = $this->context->cart->getProducts();
        
        foreach($produtos as $produto) 
		{
            if ($produto['price_wt'] <= 0) {
                continue;
            }
            $i++;            
            $this->ps_params['itemId'.$i] = $produto['id_product'];
            $this->ps_params['itemDescription'.$i] = substr($produto['name'], 0, 100);
            $this->ps_params['itemAmount'.$i] = number_format($produto['price_wt'], 2, '.', '');
            $this->ps_params['itemQuantity'.$i] = $produto['cart_quantity'];            
        }
		if (empty($this->ps_params['itemId1']) || empty($this->ps_params['itemDescription1']) || empty($this->ps_params['itemAmount1']) || empty($this->ps_params['itemQuantity1'])){
			$this->ps_errors[] = 'Erro ao Processar Produtos.';
		}
    }
    
	/* 
	 * Processa valores extra (descontos e embalagem para presente) para envio ao PagSeguro
	 */
    private function procValoresExtras() 
	{        
        $acrescimo = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
        $desconto = $this->context->cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS);
        
        if ($desconto > 0) {
            $desconto *= -1;
        }
        $valor_extra = $acrescimo + $desconto;        
        if ($valor_extra != 0) {
            $this->ps_params['extraAmount'] = number_format($valor_extra, 2, '.', '');
        }        
    }
    
	/* 
	 * Processa dados do cliente para envio ao PagSeguro
	 */
    private function procDadosComprador($dados_form) 
	{
        $doc = $dados_form['doc'];
		if (isset($dados_form['cpf'])){
	        $cpf = preg_replace('/[^0-9]/','', $dados_form['cpf']);
		}
		if (isset($dados_form['cnpj'])){
	        $cnpj = preg_replace('/[^0-9]/','', $dados_form['cnpj']);        
		}
        $telefone = $this->trataTelefone($dados_form['telefone']);        
        $email = $this->context->customer->email;
		if ($this->ambiente == 0){
			$emailArr = explode("@", $email);
			$email = $emailArr[0].'@sandbox.pagseguro.com.br';
		}
        if (strlen($email) > 60) {
            $email = substr($email, 0, 60);
        }        
        $nome = trim($this->context->customer->firstname).' '.trim($this->context->customer->lastname);
        $nome = preg_replace('/\s(?=\s)/', '', $nome);
        if (strlen($nome) > 50) {
            $nome = substr($nome, 0, 50);
        }        
        $this->ps_params['senderEmail'] = $email;
        $this->ps_params['senderName'] = $nome;
        if ($doc == 'cpf') {
            $this->ps_params['senderCPF'] = $cpf;
        }else {
            $this->ps_params['senderCNPJ'] = $cnpj;
        }
        $this->ps_params['senderAreaCode'] = $telefone['cod_area'];
        $this->ps_params['senderPhone'] = $telefone['telefone'];
        $this->ps_params['senderHash'] = $dados_form['hash'];

		if (empty($this->ps_params['senderEmail']) || (empty($this->ps_params['senderCPF']) && empty($this->ps_params['senderCNPJ'])) || empty($this->ps_params['senderAreaCode']) || empty($this->ps_params['senderPhone']) || empty($this->ps_params['senderHash'])){
			$this->ps_errors[] = 'Erro ao Processar Dados do Comprador.';
		}
    }

	/* 
	 * Processa dados do cartão de crédito para envio ao PagSeguro
	 */
    private function procDadosCartao($dados_form) 
	{
        $cpf = preg_replace('/[^0-9]/','', $dados_form['cpf']);
        $titularCartao = trim($dados_form['titular_cartao']);
        $titularCartao = preg_replace('/\s(?=\s)/', '', $titularCartao);
        $telefone = $this->trataTelefone($dados_form['telefone']);        
        $this->ps_params['creditCardToken'] = $dados_form['token'];
        $this->ps_params['installmentQuantity'] = $dados_form['total_parcelas_cartao'];
        $this->ps_params['installmentValue'] = str_replace(",", "",$dados_form['valor_parcelas_cartao']);
        if ((int)Configuration::get('PAGSEGUROPRO_PARCELAS_SEM_JUROS') > 1) {
		    $this->ps_params['noInterestInstallmentQuantity'] = (int)Configuration::get('PAGSEGUROPRO_PARCELAS_SEM_JUROS');
        }
        $this->ps_params['creditCardHolderName'] = $titularCartao;
        $this->ps_params['creditCardHolderBirthDate'] = $dados_form['data_nasc'];
        $this->ps_params['creditCardHolderCPF'] = $cpf;
        $this->ps_params['creditCardHolderAreaCode'] = $telefone['cod_area'];
        $this->ps_params['creditCardHolderPhone'] = $telefone['telefone'];
		if (empty($this->ps_params['creditCardToken']) || (empty($this->ps_params['installmentQuantity']) && empty($this->ps_params['installmentValue'])) || empty($this->ps_params['creditCardHolderName']) || empty($this->ps_params['creditCardHolderBirthDate']) || empty($this->ps_params['creditCardHolderCPF']) || empty($this->ps_params['creditCardHolderAreaCode']) || empty($this->ps_params['creditCardHolderPhone'])){
			$this->ps_errors[] = 'Erro ao Processar Dados do Cartão.';
		}        
    }
    
	/* 
	 * Processa dados do endereço de entrega para envio ao PagSeguro
	 */
    private function procDadosEntrega()
	{
        $address = new Address((int)$this->context->cart->id_address_delivery);
        $cidade = $address->city;
        if (strlen($cidade) > 60) {
            $cidade = substr($cidade, 0, 60);
        }
        $cep = preg_replace('/[^0-9]/','', $address->postcode);
        $state = new State((int)$address->id_state);
        $uf = $state->iso_code;
        $frete = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        if ($frete > 0) {
            $this->ps_params['shippingType'] = '3';
            $this->ps_params['shippingCost'] = number_format($frete, 2, '.', '');
        }
        $this->ps_params['shippingAddressCountry'] = 'BRA';
        $this->ps_params['shippingAddressState'] = $uf;
        $this->ps_params['shippingAddressCity'] = $cidade;
        $this->ps_params['shippingAddressPostalCode'] = $cep;
        $this->ps_params['shippingAddressDistrict'] = $address->address2;
        $this->ps_params['shippingAddressStreet'] = $address->address1;
        $this->ps_params['shippingAddressNumber'] = isset($address->{$this->number_field}) && strlen($address->{$this->number_field}) > 0 ? substr($address->{$this->number_field}, 0, 20) : '1';
		$this->ps_params['shippingAddressComplement'] = substr($address->{$this->compl_field}, 0, 40);

		if ((empty($this->ps_params['shippingAddressState']) && empty($this->ps_params['shippingAddressCity'])) || empty($this->ps_params['shippingAddressPostalCode']) || empty($this->ps_params['shippingAddressDistrict']) || empty($this->ps_params['shippingAddressStreet']) || empty($this->ps_params['shippingAddressNumber'])){
			$this->ps_errors[] = 'Erro ao Processar Endereço de Entrega.';
		}        
    }
    
	/* 
	 * Processa dados do endereço de cobrança para envio ao PagSeguro
	 */
    private function procDadosCobranca($dados_form) 
	{
        $address = new Address((int)$this->context->cart->id_address_invoice);
        $cidade = $address->city;
        if (strlen($cidade) > 60) {
            $cidade = substr($cidade, 0, 60);
        }
        $cep = preg_replace('/[^0-9]/','', $address->postcode);
        $state = new State((int)$address->id_state);
        $uf = $state->iso_code;
        $this->ps_params['billingAddressPostalCode'] = $cep;
        $this->ps_params['billingAddressStreet'] = isset($dados_form['endereco_cobranca']) && $dados_form['endereco_cobranca'] != '' ? $dados_form['endereco_cobranca'] : $address->address1;
        $this->ps_params['billingAddressNumber'] = isset($dados_form['numero_cobranca']) && $dados_form['numero_cobranca'] != '' ? substr($dados_form['numero_cobranca'],0 ,20) : substr($address->{$this->number_field}, 0, 20);
		$this->ps_params['billingAddressComplement'] = isset($dados_form['complemento_cobranca']) && $dados_form['complemento_cobranca'] != '' ? substr($dados_form['complemento_cobranca'], 0, 40) : substr($address->{$this->compl_field}, 0, 40);
        $this->ps_params['billingAddressDistrict'] = isset($dados_form['bairro_cobranca']) && $dados_form['bairro_cobranca'] != '' ? $dados_form['bairro_cobranca'] : $address->address2;
        $this->ps_params['billingAddressCity'] = isset($dados_form['cidade_cobranca']) && $dados_form['cidade_cobranca'] != '' ? $dados_form['cidade_cobranca'] : $cidade;
        $this->ps_params['billingAddressState'] = isset($dados_form['uf_cobranca']) && $dados_form['uf_cobranca'] != '' ? $dados_form['uf_cobranca'] : $uf;
        $this->ps_params['billingAddressCountry'] = 'BRA';
		if ((empty($this->ps_params['billingAddressState']) && empty($this->ps_params['billingAddressCity'])) || empty($this->ps_params['billingAddressPostalCode']) || empty($this->ps_params['billingAddressDistrict']) || empty($this->ps_params['billingAddressStreet']) || empty($this->ps_params['billingAddressNumber'])){
			$this->ps_errors[] = 'Erro ao Processar Endereço de Cobrança.';
		}        
    }
    
	/* 
	 * Padroniza e valida os dados do telefone do cliente para envio ao PagSeguro
	 */
    public function trataTelefone($telefone) 
	{
        $codArea = '';
        $tel = '';
        $telefone = preg_replace('/[^0-9]/','', $telefone);
        $len = strlen($telefone);
		$codArea = substr($telefone, 0, 2);
        if ($len == 10) {
            $tel = substr($telefone, 2, 8);
        }else {
			$tel = substr($telefone, 2, 9);				
        }
        return array('cod_area' => $codArea, 'telefone' => $tel);
    }
    
	/* 
	 * Processa dados de erro do retorno do PagSeguro
	 * Switch HTTP Status 
	 */
    public function processaRetornoErro($retorno) 
	{
		$erro = $retorno['error'];
        switch ((int)$retorno['status']) 
		{
            case 400:
				$this->ps_errors[] = array(
					'codigo' => $erro['code'],
					'descricao' => $this->parseDescricao($erro['code'], $erro['msg'])
				);
                break;
            case 401:
                $this->ps_errors[] = array(
                    'codigo' => '00000',
                    'descricao' => 'Credenciais inválidas'
                );
                break;
            case 405:
                $this->ps_errors[] = array(
                    'codigo' => '00000',
                    'descricao' => 'Método não permitido (somente permitido GET ou POST).'
                );
                break;
            case 415:
                $this->ps_errors[] = array(
                    'codigo' => '00000',
                    'descricao' => 'Não enviado Content-Type na chamada.'
                );
                break;
            default:
                $this->ps_errors[] = array(
                    'codigo' => $retorno['status'],
                    'descricao' => 'Erro não definido.'
                );
                break;
        }
    }
	
	/* 
	 * Pega ID do Carrinho a partir da referência do pedido no PagSeguro
	 */
	public function getIdCart($reference){
		$ref_array = explode(".", $reference);
		$id_cart = $ref_array[0];
		return $id_cart;
	}

	/*
	 * Envia chamada para a API 	
	 */
    public function curl_send($method, $post_url, array $data = null, $timeout = 10, $id_cart = false) 
	{
		$charset = 'UTF-8'; //'ISO-8859-1'
        $curl = curl_init();
        if (strtoupper($method) === 'POST') {
            $postFields = ($data ? http_build_query($data, '', '&') : '');
            $contentLength = 'Content-length: '.strlen($postFields);            
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        }else {
            $post_url = $post_url.'?'.http_build_query($data, '', '&');
            $contentLength = null;            
            curl_setopt($curl, CURLOPT_HTTPGET, true);
        }
		$header = array(
		    	'Content-Type: application/x-www-form-urlencoded; charset='.$charset,
			'cms-description: prestashop-oficial-v.1.7',
       			'module-description: prestashop-v.1.7 - Checkout Transparente',
       			'module-version:'.$this->version,
		   	 $contentLength,
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
        $errorMessage = curl_error($curl);
		$resp_xml = simplexml_load_string(utf8_encode(str_replace('ISO-8859-1', 'UTF-8', $resp)));
        curl_close($curl);
		
		if (Configuration::get('PAGSEGUROPRO_FULL_LOG') !== false) {
			$this->saveLog('curl', $method, $id_cart, json_encode($data), utf8_encode(str_replace('ISO-8859-1', 'UTF-8', $resp)), $post_url);
		}
        if ($info['http_code'] == 200 || $info['http_code'] == 201 || $info['http_code'] == 204) {
			$retorno = array(
				'error' => 0,
				'resposta' =>$resp, 
				'status' => $info['http_code'],
				'info' => $info,
				'xml' => $resp_xml
			);
        }else {
			$this->ps_errors[] = 'A conexão com o Pagseguro retornou com erro: '.$error.' - '.$errorMessage.' (HTTP: '.$info['http_code'].')';
			$this->saveLog('error', $method, $id_cart, json_encode($data), json_encode($this->ps_errors), $post_url);
			$retorno = array(
				'resposta' =>$resp, 
				'status' => $info['http_code'],
				'info' => $info,
				'error' => array(
					'code' => (int)$resp_xml->error[0]->code,
					'msg' => (string)$resp_xml->error[0]->message,
				)
			);
        }
		return $retorno;
    }

	/*
	 * Salva log com informações para análise/debug
	 */
	public function saveLog($type, $method, $id_cart = false, $data, $response = false, $url = false)
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
		$query = 'INSERT INTO `'._DB_PREFIX_.'pagseguropro_logs` (`datetime`, `type`, `method`, `id_cart`, `data`, `response`, `url`) VALUES (NOW(), "'.$type.'", "'.$method.'", '.$id_cart.', "'.addslashes($data).'", "'.addslashes($response).'" , "'.addslashes($url).'")';
		if (Db::getInstance()->execute($query) === false) {
			return false;
		}
		return true;
	}
	
	/*
	 * Gera chave aleatória para a referência do pedido 
	 */
	public function gerarChave()
	{
		$numbers='0123456789';
		$max=strlen($numbers)-1;
		$result=null;
		for($i=0;$i<8;$i++)
		{
			$result.=$numbers{mt_rand(0,$max)};
		}
		return $result;
	}
	
	/*
	 * Retorna IP do usuário
	 */
	public function getUserIp() {
		$ipaddress = '';
		if (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}elseif(isset($_SERVER['HTTP_X_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
		}elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
		}elseif(isset($_SERVER['HTTP_FORWARDED'])) {
			$ipaddress = $_SERVER['HTTP_FORWARDED'];
		}elseif(isset($_SERVER['REMOTE_ADDR'])) {
			$ipaddress = $_SERVER['REMOTE_ADDR'];
		}else{
			$ipaddress = Tools::getRemoteAddr();
		}
		return $ipaddress;
	}

	/*
	 * Gera o desconto no carrinho de compras p/ pedido via Boleto
	 */
	public function geraDesconto($cart)
	{
		if (!$this->active)
			return;

		$cart = $this->context->cart;
		$code = (int)($cart->id_customer).'BOLETOPAGSEGURO'.$cart->id;

		if(CartRule::cartRuleExists($code))
			return;

		$total = (float)Configuration::get('PAGSEGUROPRO_VALOR_DESCONTO_BOLETO');
		$tipoDesconto = Configuration::get('PAGSEGUROPRO_TIPO_DESCONTO_BOLETO');
		$languages=Language::getLanguages();

		foreach ($languages as $key => $language)
		{
			if($tipoDesconto == 1)
			{
				$arrayName[$language['id_lang']] = "Desconto de $total% no Boleto";
			}else{
				$arrayName[$language['id_lang']] = "Desconto de R$ $total no Boleto";
			}
		}

		$voucher = new CartRule();
		$voucher->reduction_amount = ($tipoDesconto == 2 ? $total : '');
		$voucher->reduction_percent = ($tipoDesconto == 1 ? $total : '');
		$voucher->name = $arrayName;
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
		$voucher->date_from = date("Y-m-d H:i:s",$now);
		$voucher->date_to = date("Y-m-d H:i:s",$now+(3600*24));
		if(!$voucher->validateFieldsLang(false) OR !$voucher->add())
			die('Cupom não criado.');
		if(!$voucher->update())
			die('Cupom não atualizado.');
		if (!$cart->addCartRule((int)$voucher->id))
			die('Cupom não incluído no carrinho.');

	}

	/*
	 * Gera a requisição de autorização para o Modelo de Aplicações
	 */
	public function getAppCode($tipo) 
	{
		//Adiciona Log de Geração da Aplicação
		$log_msg = 'Foi gerado um novo Código de Aplicação para o PagSeguro ('.$tipo.')';
		PrestaShopLogger::addLog($log_msg, 2); 

		$loja = substr(Configuration::get('PS_SHOP_NAME'), 0, 11); //max 11 caracteres
		if(strtolower($tipo) == 'd14') {
			$appId = $this->appId14;
			$appKey = $this->appKey14;
		}else{
			$appId = $this->appId30;
			$appKey = $this->appKey30;
		}
		$url_retorno = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/update.php?acao=app&tipo='.$tipo;
		
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<authorizationRequest>
    <reference><![CDATA[PRESTABR.'.$loja.']]></reference>
    <permissions>
        <code>CREATE_CHECKOUTS</code>
        <code>RECEIVE_TRANSACTION_NOTIFICATIONS</code>
        <code>SEARCH_TRANSACTIONS</code>
        <code>MANAGE_PAYMENT_PRE_APPROVALS</code>
        <code>DIRECT_PAYMENT</code>
	<code>CANCEL_TRANSACTIONS</code>
        <code>REFUND_TRANSACTIONS</code>
    </permissions>
    <redirectURL><![CDATA['.$url_retorno.']]></redirectURL>
    <notificationURL><![CDATA['.$url_retorno.']]></notificationURL>
</authorizationRequest>';
		$charset = 'UTF-8';
        $curl = curl_init();
		$post_url = 'https://ws.pagseguro.uol.com.br/v2/authorizations/request/?appId='.$appId.'&appKey='.$appKey;
		$contentLength = null;
		curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml; charset='.$charset, $contentLength));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        $resp = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_errno($curl);
        $errorMessage = curl_error($curl);
		$resp_xml = simplexml_load_string($resp);
        curl_close($curl);
		
		return array('info' => $info, 'xml'=> $xml, 'error' => $error, 'error_msg' => $errorMessage, 'resp' => $resp, 'resp_xml' => $resp_xml);
	}
	
	/*
	 * Consulta a autorização da Aplicação e salva no banco
	 */
	public function getAppAuthorization($notificationCode, $tipo) 
	{
		if(strtolower($tipo) == 'd14') {
			$appId = $this->appId14;
			$appKey = $this->appKey14;
		}else{
			$appId = $this->appId30;
			$appKey = $this->appKey30;
		}
		$charset = 'UTF-8';
        $curl = curl_init();
		$post_url = 'https://ws.pagseguro.uol.com.br/v2/authorizations/notifications/'.$notificationCode.'?appId='.$appId.'&appKey='.$appKey;
		$contentLength = null;
		curl_setopt($curl, CURLOPT_HTTPGET, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/xml; charset='.$charset, $contentLength));
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        $resp = curl_exec($curl);
        $info = curl_getinfo($curl);
        $error = curl_errno($curl);
        $errorMessage = curl_error($curl);
		$resp_xml = simplexml_load_string($resp);
        curl_close($curl);
		
		return array('info' => $info, 'error' => $error, 'error_msg' => $errorMessage, 'resp' => $resp, 'resp_xml' => $resp_xml);
	}
	
	/*
	 * Cria pedido a partir da Notificação, caso o pedido não exista
	 */
    public function criaPedidoRetorno($transaction)
    {
		$id_cart = (int)$this->getIdCart($transaction->reference); 
		$id_order = Order::getOrderByCartId($id_cart);
		if ((int)$id_order > 0){
			return (int)$id_order;
		}else{
			$cart = new Cart($id_cart);
			$customer = new Customer($cart->id_customer);
			$cod_status = (int)$transacao->status;
			$secure_key = $customer->secure_key;
			$total_compra = (float)$transaction->grossAmount;
			$payment_option = $this->parseTipoPagamento($transaction->paymentMethod->code);
	
			if (!$this->validateOrder($id_cart, Configuration::get('_PS_OS_PAGSEGUROPRO_0'), $total_compra, $payment_option, NULL, NULL, $this->context->currency->id, false, $secure_key)) {
				$this->saveLog('error', 'Criar Pedido', $id_cart, json_encode($transaction), 'Erro ao criar pedido pelo callback.');
			}else{
				sleep(3);
				$id_order = $this->currentOrder;
				if (!$this->updateOrderStatus($id_cart, $cod_status, (int)$id_order)){
					$this->saveLog('error', 'Atualizar Status do Pedido', $id_order, json_encode($transaction), 'Erro ao atualizar status do pedido criado pelo callback.');
				}
			}
			return (int)$id_order;
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
            foreach ($currencies_module as $currency_module)
			{
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
				}
			}
		}
        return false;
    }
	
    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
		$this->context->controller->addCSS($this->_path.'views/css/pagseguropro_admin.css');
    }

	public function hookActionFrontControllerSetMedia($params)
	{
		$this->context->controller->registerStylesheet(
			'pagseguropro-css',
			'modules/'.$this->name.'/views/css/pagseguropro.css',
			array('media' => 'all', 'priority' => 200)
		);
		// Apenas no checkout
		if ($this->context->controller->php_self === 'order') {
			$this->context->controller->addJqueryPlugin('fancybox');
			$this->context->controller->registerJavascript(
				'pagseguropro-direct-payment', 
				$this->urls['js'],
				array('server' => 'remote', 'position' => 'bottom', 'priority' => 150)
			);
			$this->context->controller->registerJavascript(
				'pagseguropro-js', 
				'modules/'.$this->name.'/views/js/pagseguropro.js',
				array('position' => 'bottom', 'priority' => 150)
			);
			$this->context->controller->registerJavascript(
				'pagseguropro-mascara', 
				'modules/'.$this->name.'/views/js/mascara.js',
				array('position' => 'bottom', 'priority' => 150)
			);
		}
	}

	/*
	 * Exibe as opções de pagamento do módulo na loja, sem redirecionar para um controller novo
	 */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

		$pagamentos = explode(',', Configuration::get('PAGSEGUROPRO_PAGAMENTO'));
				
		$device = new Mobile_Detect();
		if($device->isTablet()) {
			$dispositivo = "t";
		} elseif($device->isMobile()) {
			$dispositivo = "m";
		} else {
			$dispositivo = "d";
		}
		
        $currency_id = $params['cart']->id_currency;
        $currency = new Currency((int)$currency_id);
        if (in_array($currency->iso_code, $this->limited_currencies) == false) {
            return false;
		}
		
		$cart = $params['cart'];
		$total = (float)$params['cart']->getOrderTotal(true,Cart::BOTH);
		$customer=new Customer((int)($params['cart']->id_customer));
		$address=new Address((int)($params['cart']->id_address_invoice));
		$firstname=str_replace(' ',' ',trim($customer->firstname));
		$lastname=str_replace(' ',' ',trim($customer->lastname));
		$senderName=trim($firstname.' '.$lastname);
		if (isset($customer->birthday) && $customer->birthday != '0000-00-00') {
		    $birthday = date('d/m/Y',strtotime($customer->birthday));
		    $birthdaystring = DateTime::createFromFormat('d/m/Y', $birthday)->format('Y-m-d');
		}
		$phone=isset($address->phone_mobile) && !empty($address->phone_mobile) ? $address->phone_mobile : $address->phone;
		$page_name = $this->context->controller->php_self;
		$msg_console = Configuration::get('PAGSEGUROPRO_SHOW_CONSOLE');
		$method = false;
		if (Tools::isSubmit('method')) {
        	$method = Tools::getValue('method');
            if ($method == 'updateCarrierAndGetPayments') {
				$method = true;
            }
        }

		$doc = false;
		if (isset($customer->siret) && $customer->siret != '') {
			$doc = $customer->siret;
		}elseif (isset($customer->cnpj) && $customer->cnpj != '') {
			$doc = $customer->cnpj;
		}elseif (isset($customer->cpf) && $customer->cpf != '') {
			$doc = $customer->cpf;
		}elseif (isset($customer->cpf_cnpj) && $customer->cpf_cnpj != '') {
			$doc = $customer->cpf_cnpj;
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
		
        $this->smarty->assign(array(
			'page_name' => $this->context->controller->php_self,
			'credentials' => http_build_query($this->credentials, '', '&'),
			'msg_console' => (bool)$msg_console,
			'pagamentos' => $pagamentos,
			'parcelasSemJuros' => (int)Configuration::get('PAGSEGUROPRO_PARCELAS_SEM_JUROS'),
			'max_parcelas' => (int)Configuration::get('PAGSEGUROPRO_MAX_PARCELAS'),
			'device' => $dispositivo,
			'module_dir' => $this->_path,
			'tpl_dir' => _PS_MODULE_DIR_.$this->name.'/views/templates/hook',
			'url_img' => $this->urls['img'],
			'url_js' => $this->urls['js'],
			'currency' => $currency,
			'idmodule'=>$this->id,
			'checkout' => (bool)Configuration::get('PS_ORDER_PROCESS_TYPE'),
			'total'=>number_format($total, 2, '.', ''),
			'id_cart'=>$params['cart']->id,
			'phone'=>$phone,
			'method' => $method,
			'address_invoice' => $address,
			'number_invoice' => $address->{$this->number_field},
			'compl_invoice' => $address->{$this->compl_field},
			'states' =>$states,
			'senderName'=>$senderName,
			'birthday'=> isset($birthdaystring) && $birthdaystring != '' ? $birthdaystring : '',
			'cpf'=> isset($doc) && $doc !='' ? $doc : '',
			'this_path'=>Tools::getShopDomainSsl(true,true).__PS_BASE_URI__.'modules/'.$this->name.'/',
			'url_update'=>Tools::getShopDomainSsl(true,true).__PS_BASE_URI__.'modules/'.$this->name.'/update.php'
		));
		
		$payOptions = array();

		foreach ($pagamentos as $payOption) { 
			if ($payOption == 'boleto'){
				$payText = $this->trans('Boleto', array(), 'Modules.PagSeguroPro.Admin');
				$payTpl = $payOption.'.tpl';
			}elseif ($payOption == 'cartao'){
				$payText = $this->trans('Cartão de Crédito', array(), 'Modules.PagSeguroPro.Admin');
				$payTpl = $payOption.'.tpl';
			}elseif ($payOption == 'transf'){
				$payText = $this->trans('Transferência Bancária', array(), 'Modules.PagSeguroPro.Admin');
				$payTpl = $payOption.'erencia.tpl';
			}
			$psOption = new PaymentOption();
			$psOption->setModuleName($this->name)
				->setCallToActionText($payText)
				->setLogo($this->urls['img'].'logo_pagseguro_mini.png')
				->setAction($this->context->link->getModuleLink($this->name, 'validation', array('ptype' => $payOption), true))
				->setForm($this->fetch('module:pagseguropro/views/templates/hook/'.$payTpl));
			$payOptions[] = $psOption;
		}
			
        return $payOptions;
    }

    /*
	 * Exibe a página de confirmação de pagamento com os parâmetros do pedido na loja e no pagseguro
	 */
    public function hookPaymentReturn($params) 
	{
        if (!$this->active) {
            return false;
        }
		$this->context->controller->addCSS($this->_path.'views/css/pagseguropro.css');

        $id_cart = Tools::getValue('id_cart');
        $id_order = (int)$params['order']->id;
		$order = new Order($id_order);
		
		$info = $this->getOrderData($id_cart, 'id_cart');
        $cod_status = $info['status'];
        $cod_transacao = $info['cod_transacao'];
		$link_transf = false;
		$link_boleto = false;
		if(isset($info['url']) && $info['url'] != ''){
			if (strpos(strtolower($info['desc_pagto']), 'boleto') !== false) {
				$link_boleto = $info['url'];
			}else{
				$link_transf = $info['url'];
			}
		}

		if ($this->tipo_credencial == 'TOKEN' || !$this->tipo_credencial || $this->tipo_credencial == ''){
			$token_codigo = Configuration::get('PAGSEGUROPRO_TOKEN');
		}elseif($this->tipo_credencial == 'D14'){
			$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D14');
		}elseif($this->tipo_credencial == 'D30'){
			$token_codigo = Configuration::get('PAGSEGUROPRO_AUTHCODE_D30');
		}
        $transaction = $this->getTransaction($cod_transacao, $this->tipo_credencial, $token_codigo);
        if ($cod_status != $transaction->status){
            $this->updateOrderStatus($id_cart, $cod_status, $id_order, date("Y-m-d H:i:s", strtotime($transaction->lastEventDate)));
        }
		
		//Passa os parâmetros pro template
        $this->smarty->assign(array(
			'info' => $info,
			'url_img' => $this->urls['img'],
            'ps_link_boleto' => $link_boleto,
            'ps_link_transf' => $link_transf,
            'ps_cod_transacao' => $cod_transacao,
            'ps_pedido' => $params['order']->id,
            'ps_referencia' => $params['order']->reference,
            'ps_valor' => number_format($params['order']->total_paid, 2, ',', '.'),
			'produtos' => $order->getProducts(),
			'pedido' => $order
        ));
        return $this->fetch('module:pagseguropro/views/templates/hook/payment_return.tpl');
    }
    
	/*
	 * Exibe os dados do pedido no pagseguro na aba de detalhes do pedido no BackOffice
	 */
    public function hookDisplayAdminOrder()
    {
		if(!$this->active) {
			return;
		}
		$id_order=Tools::getValue('id_order');
		$order = new Order((int)$id_order);
		$info = $this->getOrderData((int)$id_order,'id_order');
		if(!$info) {
			$info = $this->getOrderData($order->id_cart,'id_cart');
		}
		if(!$info) {
			return;
		}
		
		if (isset($info['credencial']) && $info['credencial'] != '' && isset($info['token_codigo']) && $info['token_codigo'] != ''){
	        $transaction = $this->getTransaction($info['cod_transacao'], $info['credencial'], $info['token_codigo']);
		}else{
	        $transaction = $this->getTransaction($info['cod_transacao']);
		}
		$status_pagseguro = $this->parseStatus($transaction->status);
		
		if (Tools::isSubmit('cancelarPedidoPagSeguro')) {
			if($transaction->status < 3) {
				if (isset($info['credencial']) && $info['credencial'] != '' && isset($info['token_codigo']) && $info['token_codigo'] != ''){
					$retorno = $this->cancelTransaction($info['cod_transacao'], $info['credencial']);
				}else{
					$retorno = $this->cancelTransaction($info['cod_transacao']);
				}
				$this->saveLog('cancelamento', 'pedido', (int)$order->id_cart, json_encode($info), json_encode($retorno), '');
				if ($retorno['error'] == 0) {
					$data = array(
						"cod_transacao" => (string)$info['cod_transacao'], 
						"status" => 7, 
						"desc_status" => $this->parseStatus(7),
						"data_atu" => date("Y-m-d H:i:s"),
					);
					$this->updatePagSeguroData($data);
					$this->context->smarty->assign(array(
						'pagseguro_msg' => $this->trans('Pedido Cancelado no PagSeguro.', array(), 'Modules.PagSeguroPro.Admin'),
						'resposta' => $retorno,
					));
				}else{
					$this->context->smarty->assign(array(
						'pagseguro_msg' => $this->trans('Erro ao tentar Cancelar o Pedido no PagSeguro.', array(), 'Modules.PagSeguroPro.Admin'),
						'resposta' => $retorno,
					));
				}
			}else{
				$this->context->smarty->assign(array(
					'pagseguro_msg' => $this->trans('Status do Pedido no PagSeguro não permite Cancelamento.', array(), 'Modules.PagSeguroPro.Admin'),
				));
			}
		}
		if (Tools::isSubmit('estornarPedidoPagSeguro')) {
			if($transaction->status == 3 || $transaction->status == 4 || $transaction->status == 5) {
				$valor = Tools::getValue('refundValue');
				if (isset($info['credencial']) && $info['credencial'] != '' && isset($info['token_codigo']) && $info['token_codigo'] != ''){
					if(isset($valor) && (int)$valor > 0){
						$retorno = $this->refundTransaction($info['cod_transacao'], $valor, $info['credencial']);
					}else{
						$retorno = $this->refundTransaction($info['cod_transacao'], false, $info['credencial']);
					}
				}else{
					if(isset($valor) && (int)$valor > 0){
						$retorno = $this->refundTransaction($info['cod_transacao'], $valor);
					}else{
						$retorno = $this->refundTransaction($info['cod_transacao']);
					}
				}
				$this->saveLog('estorno', 'pedido', (int)$order->id_cart, json_encode($info), json_encode($retorno), '');
				if ($retorno['error'] == 0) {
					$data = array(
						"cod_transacao" => (string)$info['cod_transacao'], 
						"status" => 8, 
						"desc_status" => $this->parseStatus(8),
						"data_atu" => date("Y-m-d H:i:s"),
					);
					$this->updatePagSeguroData($data);
					$this->context->smarty->assign(array(
						'pagseguro_msg' => $this->trans('Pedido Estornado no PagSeguro.', array(), 'Modules.PagSeguroPro.Admin'),
						'resposta' => $retorno,
					));
				}else{
					$this->context->smarty->assign(array(
						'pagseguro_msg' => $this->trans('Erro ao tentar Estornar o Pedido no PagSeguro.', array(), 'Modules.PagSeguroPro.Admin'),
						'resposta' => $retorno,
					));
				}
			}else{
				$this->context->smarty->assign(array(
					'pagseguro_msg' => $this->trans('Status do Pedido no PagSeguro não permite Estorno.', array(), 'Modules.PagSeguroPro.Admin'),
				));
			}
		}
		$this->context->smarty->assign(array(
			'pedido' =>$order, 
			'transaction' => $transaction,
			'formaPagamento' => isset($transaction) && $transaction !== false ? $this->parsePagamento((int)$transaction->paymentMethod->type) : '',
			'tipoPagamento' => isset($transaction) && $transaction !== false ? $this->parseTipoPagamento((int)$transaction->paymentMethod->code) : '',
			'info'=>$info,
			'version' => _PS_VERSION_,
			'status_pagseguro' => $status_pagseguro,
			'currency' => new Currency($this->context->currency->id), 
			'this_page'=>$_SERVER['REQUEST_URI'],
			'this_path'=>$this->_path,
			'this_path_ssl'=>Tools::getShopDomainSsl(true,true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
        //return $this->fetch('module:pagseguropro/views/templates/hook/admin_order.tpl');
		return $this->display(__FILE__, 'views/templates/hook/admin_order.tpl');
	}

	/*
	 * Exibe mensagens de confirmação e de erro na parte superior da página de pagamento
	 */
    public function hookDisplayPaymentTop($params)
    {
		if (Tools::getIsset('pagseguro_msg') && Tools::getIsset('pagseguro_msg') !== '') {
			$pagseguro_msg = Tools::getValue('pagseguro_msg'); 
		}else{
		 	$pagseguro_msg = $this->context->cookie->pagseguro_msg;
		}

		$this->context->smarty->assign('pagseguro_msg', $pagseguro_msg);
		$this->context->cookie->pagseguro_msg = false;
		$total = (float)$params['cart']->getOrderTotal(true,Cart::BOTH);		

		$msg_console = Configuration::get('PAGSEGUROPRO_SHOW_CONSOLE');
        $this->smarty->assign(array(
			'msg_console' => (bool)$msg_console,
			'valorPedido' => $total,
			'parcelasSemJuros' => (int)Configuration::get('PAGSEGUROPRO_PARCELAS_SEM_JUROS'),
			'max_parcelas' => (int)Configuration::get('PAGSEGUROPRO_MAX_PARCELAS'),
			'url_img' => $this->urls['img'],
			'url_js' => $this->urls['js'],
			'url_update'=>Tools::getShopDomainSsl(true,true).__PS_BASE_URI__.'modules/'.$this->name.'/update.php'
		));
		//return $this->display(__FILE__, 'payment_top.tpl');
        return $this->fetch('module:pagseguropro/views/templates/hook/payment_top.tpl');

    }

	/* 
	 * Parse HTTP Status
	 */
	public function parseHttpStatus($http)
	{
		switch((int)$http)
		{
			case 200:
				$return='OK';
				break;
			case 400:
				$return='BAD_REQUEST';
				break;
			case 401:
				$return ='UNAUTHORIZED';
				break;
			case 403:
				$return='FORBIDDEN';
				break;
			case 404:
				$return='NOT_FOUND';
				break;
			case 500:
				$return='INTERNAL_SERVER_ERROR';
				break;
			case 502:
				$return='BAD_GATEWAY';
			break;
		}
		return $return;
	}
	
	/* 
	 * Retorna texto da mensagem de erro do PagSeguro a partir do código
	 */
    public function parseDescricao($cod, $msg) 
	{        
        $descricao = array(
			10000 => 'bandeira do cartão de crédito inválida.',
        	10001 => 'número do cartão de crédito com comprimento inválido.',
        	10002 => 'formato de data inválido.',
        	10003 => 'campo de segurança inválido.',
        	10004 => 'cvv é obrigatório.',
        	10006 => 'campo de segurança com comprimento inválido.',
        	53004 => 'quantidade de itens inválida.',
        	53005 => 'moeda corrente é necessária.',
        	53006 => 'moeda corrente inválida.',
        	53007 => 'referência com comprimento inválido.',
        	53008 => 'URL de notificação com comprimento inválido.',
        	53009 => 'URL de notificação com valor inválido.',
        	53010 => 'remetente de e-mail é necessário.',
        	53011 => 'remetente de e-mail com comprimento inválido.',
        	53012 => 'remetente de e-mail com valor inválido.',
        	53013 => 'nome do remetente é necessário.',
        	53014 => 'nome do remetente com comprimento inválido.',
        	53015 => 'nome do remetente com valor inválido.',
        	53017 => 'cpf do remetente inválido.',
        	53018 => 'código de área do remetente é necessário.',
        	53019 => 'código de área do remetente inválido.',
        	53020 => 'telefone do remetente é necessário.',
        	53021 => 'telefone do remetente é inválido.',
        	53022 => 'código postal do endereço de entrega é necessário.',
        	53023 => 'código postal do endereço de entrega é inválido.',
        	53024 => 'rua do endereço de entrega é necessário.',
        	53025 => 'rua do endereço de entrega com comprimento inválido.',
        	53026 => 'número do endereço de entrega é necessário.',
        	53027 => 'número do endereço de entrega com comprimento inválido.',
        	53028 => 'complemento do endereço de entrega com comprimento inválido.',
        	53029 => 'bairro do endereço de entrega é necessário.',
        	53030 => 'bairro do endereço de entrega com comprimento inválido.',
        	53031 => 'cidade do endereço de entrega é necessário.',
        	53032 => 'cidade do endereço de entrega com comprimento inválido.',
        	53033 => 'estado do endereço de entrega é necessário.',
        	53034 => 'estado do endereço de entrega é inválido.',
        	53035 => 'país do endereço de entrega é necessário.',
        	53036 => 'país do endereço de entrega com comprimento inválido.',
        	53037 => 'token do cartão de crédito é necessário.',
        	53038 => 'quantidade de parcelas é necessária.',
        	53039 => 'quantidade de parcelas com valor inválido.',
        	53040 => 'valor da parcela é necessário.',
        	53041 => 'valor da parcela com valor inválido.',
        	53042 => 'nome do titular do cartão de crédito é necessário.',
        	53043 => 'nome do titular do cartão de crédito com comprimento inválido.',
        	53044 => 'nome do titular do cartão de crédito com valor inválido.',
        	53045 => 'cpf do titular do cartão de crédito é necessário.',
        	53046 => 'cpf do titular do cartão de crédito com valor inválido.',
        	53047 => 'data de nascimento do titular do cartão de crédito é necessária.',
        	53048 => 'data de nascimento do titular do cartão de crédito com valor inválido.',
        	53049 => 'código de área do titular do cartão de crédito é necessário.',
        	53050 => 'código de área do titular do cartão de crédito com valor inválido.',
        	53051 => 'telefone do titular do cartão de crédito é necessário.',
        	53052 => 'telefone do titular do cartão de crédito com valor inválido.',
        	53053 => 'código postal do endereço de cobrança é necessário.',
        	53054 => 'código postal do endereço de cobrança com valor inválido.',
        	53055 => 'rua do endereço de cobrança é necessária.',
        	53056 => 'rua do endereço de cobrança com comprimento inválido.',
        	53057 => 'número do endereço de cobrança é necessário.',
        	53058 => 'número do endereço de cobrança com comprimento inválido.',
        	53059 => 'complemento do endereço de cobrança com comprimento inválido.',
        	53060 => 'bairro do endereço de cobrança é necessário.',
        	53061 => 'bairro do endereço de cobrança com comprimento inválido.',
        	53062 => 'cidade do endereço de cobrança é necessária.',
        	53063 => 'cidade do endereço de cobrança com comprimento inválido.',
        	53064 => 'estado do endereço de cobrança é necessário.',
        	53065 => 'estado do endereço de cobrança com valor inválido.',
        	53066 => 'país do endereço de cobrança é necessário.',
        	53067 => 'país do endereço de cobrança com comprimento inválido.',
        	53068 => 'email do destinatário com comprimento inválido.',
        	53069 => 'email do destinatário com valor inválido.',
        	53070 => 'id do item é necessário.',
        	53071 => 'id do item com comprimento inválido.',
        	53072 => 'descrição do item é necessária.',
        	53073 => 'descrição do item com comprimento inválido.',
        	53074 => 'quantidade do item é necessária.',
        	53075 => 'quantidade do item fora da faixa.',
        	53076 => 'quantidade do item com valor inválido.',
        	53077 => 'montante do item é necessário.',
        	53078 => 'montante do item com padrão inválido.',
        	53079 => 'montante do item fora da faixa.',
        	53081 => 'o remetente está relacionado com o destinatário.',
        	53084 => 'destinatário inválido.',
        	53085 => 'forma de pagamento indisponível.',
        	53086 => 'montante total da compra fora da faixa.',
        	53087 => 'cartão de crédito com data inválida.',
        	53091 => 'hash de remetente inválido.',
        	53092 => 'bandeira do cartão de crédito não é aceita.',
        	53095 => 'tipo de transporte padrão inválido.',
        	53096 => 'custo de transporte padrão inválido.',
        	53097 => 'custo de transporte fora da faixa.',
        	53098 => 'valor total da compra é negativo.',
        	53099 => 'montante extra padrão inválido.',
        	53101 => 'modo de pagamento valor inválido, os valores válidos são padrão e um gateway.',
        	53102 => 'forma de pagamento valor inválido, os valores válidos são cartão de crédito, boleto e eft.',
        	53104 => 'custo de transporte foi fornecido, endereço de envio deve estar completo.',
        	53105 => 'informações sobre o remetente foram fornecidas, o e-mail deve ser fornecido também.',
        	53106 => 'titular do cartão de crédito está incompleto.',
        	53109 => 'informações sobre o endereço de envio foram fornecidas, o email do remetente deve ser fornecido também.',
        	53110 => 'eft bancário é necessário.',
        	53111 => 'eft bancário não foi aceito.',
        	53115 => 'data de nascimento do remetente com valor inválido.',
        	53117 => 'cnpj do remetente com valor inválido.',
        	53118 => 'cpf é obrigatório.',
        	53122 => 'o domínio do email do remetente é inválido.',
        	53140 => 'quantidade de parcelas for a da faixa.',
        	53141 => 'remetente está bloqueado.',
        	53142 => 'token do cartão de crédito inválido.'
		);
        return (array_key_exists((int)$cod, $descricao) ? $descricao[$cod].' (.'.$msg.')' : $msg);
        
    }
    
	/* 
	 * Retorna texto do Status do PagSeguro a partir do código
	 */
    public function parseStatus($cod_status) 
	{
	    $cod_status = (int)$cod_status;
        $status = array(
            1 => 'Aguardando pagamento',
            2 => 'Em análise',
            3 => 'Pagamento confirmado',
            4 => 'Valor disponível',
            5 => 'Em disputa',
            6 => 'Valor pago devolvido ao comprador',
            7 => 'Transação cancelada',
			8 => 'Devolvido',
			9 => 'Retido (chargeback)',
			10 => 'Não definido',
			11 => 'Não definido',
			12 => 'Não definido',
        );
        return (array_key_exists($cod_status, $status) ? $status[$cod_status] : 'Não definido');
    }
    
	/* 
	 * Corresponde o Status do pedido no PagSeguro com o Status do pedido na loja
	 */
    public function correspondStatus($cod_status) 
	{
        switch ((int)$cod_status) 
		{
            case 1: // Aguardando pagamento
				$status_loja = Configuration::get('PAGSEGUROPRO_AGUARDANDO_PAGAMENTO');
                break;
            case 2: // Em análise
			$status_loja = Configuration::get('PAGSEGUROPRO_EM_ANALISE');
                break;
            case 3: // Pagamento confirmado
			$status_loja = Configuration::get('PAGSEGUROPRO_AUTORIZADO');
                break;
            case 6: // Valor pago devolvido ao comprador
			$status_loja = Configuration::get('PAGSEGUROPRO_ESTORNADO');
		break;
	    case 7: //Transação cancelada
	   //case 8: //Devolvido
	    case 11: //Transação cancelada
			$status_loja = Configuration::get('PAGSEGUROPRO_CANCELADO');
                break;
		}
        return isset($status_loja) && $status_loja ? $status_loja : false;
    }
    
	/* 
	 * Retorna texto da forma de pagamento do PagSeguro a partir do código
	 */
    public function parsePagamento($cod_pagto) 
	{
	    $cod_pagto = (int)$cod_pagto;
        $pagamento = array(
            1 => 'Cartão de crédito',
            2 => 'Boleto',
            3 => 'Débito online (TEF)',
            4 => 'Saldo PagSeguro',
            5 => 'Oi Paggo',
            7 => 'Depósito em conta'
        );
        return (array_key_exists($cod_pagto, $pagamento) ? $pagamento[$cod_pagto] : 'Não definido');
    }
    
	/* 
	 * Retorna texto do tipo de pagamento utilizado no PagSeguro a partir do código
	 */
    public function parseTipoPagamento($cod_tipo_pagto) 
	{
	    $cod_tipo_pagto = (int)$cod_tipo_pagto;
        $tipo = array(
			101 => 'Cartão de crédito Visa',
			102 => 'Cartão de crédito MasterCard',
			103 => 'Cartão de crédito American Express',
			104 => 'Cartão de crédito Diners',
			105 => 'Cartão de crédito Hipercard',
			106 => 'Cartão de crédito Aura',
			107 => 'Cartão de crédito Elo',
			108 => 'Cartão de crédito PLENOCard',
			109 => 'Cartão de crédito PersonalCard',
			110 => 'Cartão de crédito JCB',
			111 => 'Cartão de crédito Discover',
			112 => 'Cartão de crédito BrasilCard',
			113 => 'Cartão de crédito FORTBRASIL',
			114 => 'Cartão de crédito CARDBAN',
			115 => 'Cartão de crédito VALECARD',
			116 => 'Cartão de crédito Cabal',
			117 => 'Cartão de crédito Mais!',
			118 => 'Cartão de crédito Avista',
			119 => 'Cartão de crédito GRANDCARD',
			120 => 'Cartão de crédito Sorocred',
			122 => 'Cartão de crédito Up Policard',
			123 => 'Cartão de crédito Banese Card',
			201 => 'Boleto Bradesco',
			202 => 'Boleto Santander',
			301 => 'Débito online Bradesco',
			302 => 'Débito online Itaú',
			303 => 'Débito online Unibanco',
			304 => 'Débito online Banco do Brasil',
			305 => 'Débito online Banco Real',
			306 => 'Débito online Banrisul',
			307 => 'Débito online HSBC',
			401 => 'Saldo PagSeguro',
			501 => 'Oi Paggo',
			701 => 'Depósito em conta - Banco do Brasil'
        );
        return (array_key_exists($cod_tipo_pagto, $tipo) ? $tipo[$cod_tipo_pagto] : 'PagSeguro');
    }

    /*
	 * Apaga Logs do banco de dados
	 */
    public function delete()
    {
        $id_log = Tools::getValue('id_log');
        $ps_logsBox = Tools::getValue('pagseguropro_logsBox');
        if (!$id_log && !$ps_logsBox) {
            return;
		}
        if (isset($ps_logsBox) && !is_array($ps_logsBox)) {
            $ps_logsBox = array($ps_logsBox);
		}
        $del_query = '';
        if (isset($id_log) && !empty($id_log)) {
            $del_query = 'DELETE FROM `'._DB_PREFIX_.'pagseguropro_logs` WHERE `id_log` = '.$id_log.';';
        }else{
            foreach ($ps_logsBox as $id_logbox)
            {
                $del_query .= 'DELETE FROM `'._DB_PREFIX_.'pagseguropro_logs` WHERE `id_log` = '.$id_logbox.';';
            }
        }
        if (!Db::getInstance()->execute($del_query)) {
            return false;
        }
        return true;
    }

}
