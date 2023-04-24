<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Pagamento com Cartão de Crédito, Boleto Bancário e Pix
 * Checkout Transparente para PrestaShop 1.6.x, 1.7.x e 8.x
 * 
 * @author	  2011-2023 PrestaBR - https://prestabr.com.br
 * @copyright 1996-2023 PagBank - https://pagseguro.uol.com.br
 * @license	  Open Software License 3.0 (OSL 3.0) - https://opensource.org/license/osl-3-0-php/
 *
 */

if (!defined('_PS_VERSION_')) {
	exit;
}

class PagBankV8 extends Module
{
	public $module;

	/*
	 * Cria abas no menu e tabelas no banco
	 * Registra os Hooks da aplicação e define parâmetros de configuração
	 */
	public function installation()
	{
		$this->module = Module::getInstanceByName('pagbank');
		if (
			!$this->addStatus()
			|| !$this->installTabs()
			|| !$this->module->registerHook('displayBackOfficeHeader')
			|| !$this->module->registerHook('displayAdminOrder')
			|| !$this->module->registerHook('displayHeader')
			|| !$this->module->registerHook('paymentOptions')
			|| !$this->module->registerHook('displayPaymentTop')
			|| !$this->module->registerHook('paymentReturn')
		) {
			return false;
		}
		return true;
	}

	public function addStatus()
	{
		$os = array(
			'0' =>  $this->module->trans('PagBank - Iniciado', array(), 'Modules.PagBank.Admin'),
			'1' =>  $this->module->trans('PagBank - Em Análise', array(), 'Modules.PagBank.Admin'),
			'2' =>  $this->module->trans('PagBank - Aguardando Pagamento', array(), 'Modules.PagBank.Admin')
		);

		foreach ($os as $k => $value) {
			$order_state = new OrderState();
			$order_state->name = array();
			$order_state->module_name = $this->module->name;
			$order_state->template = array();
			foreach (Language::getLanguages() as $key => $language) {
				$order_state->name[$language['id_lang']] = (string)$value;
			}
			$order_state->send_email = false;
			$order_state->invoice = false;
			$order_state->color = '#6495ED';
			$order_state->unremovable = true;
			$order_state->logable = false;
			$order_state->delivery = false;
			$order_state->hidden = false;
			$current_dir = dirname(__FILE__);
			if ($order_state->add()) {
				@copy(_PS_MODULE_DIR_ . '/' . $this->module->name . '/logo.gif', _PS_IMG_DIR_ . 'os/' . $order_state->id . '.gif');
			}
			Configuration::updateValue('_PS_OS_PAGBANK_' . $k, $order_state->id);
		}
		return true;
	}

	/* 
	 * Adiciona Abas no menu do BackOffice
	 */
	public function installTabs()
	{
		$this->module = Module::getInstanceByName('pagbank');
		$menuItems = array(
			0 => array(
				'parent_class' => 'SELL',
				'class_name' => 'PagBank',
				'name' => 'PagBank',
				'icon' => 'monetization_on',
			),
			1 => array(
				'parent_class' => 'PagBank',
				'class_name' => 'AdminPagBankRedirect',
				'name' => 'PagBank - Configurações',
			),
			2 => array(
				'parent_class' => 'PagBank',
				'class_name' => 'AdminPagBank8',
				'name' => 'PagBank - Transações',
			),
			3 => array(
				'parent_class' => 'PagBank',
				'class_name' => 'AdminPagBank8Logs',
				'name' => 'PagBank - Logs',
			),
		);

		foreach ($menuItems as $newMenu) {
			$tab = new Tab();
			$tab->module = $this->module->name;
			$tab->id_parent = (int)Tab::getIdFromClassName($newMenu['parent_class']);
			$tab->class_name = $newMenu['class_name'];

			foreach (Language::getLanguages(false) as $lang) {
				$tab->name[(int)$lang['id_lang']] = $newMenu['name'];
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
	public function uninstallTabs()
	{
		$tabs = array(
			(int)Tab::getIdFromClassName('PagBank'),
			(int)Tab::getIdFromClassName('AdminPagBank'),
			(int)Tab::getIdFromClassName('AdminPagBankLogs'),
			(int)Tab::getIdFromClassName('AdminPagBank8'),
			(int)Tab::getIdFromClassName('AdminPagBank8Logs')
		);
		foreach ($tabs as $id_tab) {
			if ($id_tab) {
				$tab = new Tab($id_tab);
				if (Validate::isLoadedObject($tab)) {
					$result = $tab->delete();
				} else {
					return false;
				}
			}
		}
		return true;
	}

	/* 
	 * Gera os campos de configuração do módulo
	 */
	public function getConfigForm()
	{
		$this->module = Module::getInstanceByName('pagbank');
		$statuses = OrderState::getOrderStates($this->context->language->id);

		$array_installments = array();
		for ($x = 1; $x <= 12; $x++) {
			$array_installments[] = array(
				'id' => $x,
				'name' => $x . 'x',
			);
		}

		if (Configuration::get('PAGBANK_ENVIRONMENT') !== false) {
			$tax = Configuration::get('PAGBANK_TOKEN_TAX');
			$d14 = Configuration::get('PAGBANK_TOKEN_D14');
			$d30 = Configuration::get('PAGBANK_TOKEN_D30');
		} else {
			$tax = Configuration::get('PAGBANK_TOKEN_SANDBOX_TAX');
			$d14 = Configuration::get('PAGBANK_TOKEN_SANDBOX_D14');
			$d30 = Configuration::get('PAGBANK_TOKEN_SANDBOX_D30');
		}

		$array_credentials = array(
			array(
				'id' => '',
				'name' =>  $this->module->trans('Selectione o App', array(), 'Modules.PagBank.Admin')
			),
		);
		if (isset($tax) && $tax != '') {
			$array_credentials[] = array(
				'id' => 'TAX',
				'name' =>  $this->module->trans('PrestaShop - App Tax', array(), 'Modules.PagBank.Admin')
			);
		}
		if (isset($d14) && $d14 != '') {
			$array_credentials[] =  array(
				'id' => 'D14',
				'name' =>  $this->module->trans('PrestaShop - App D14', array(), 'Modules.PagBank.Admin')
			);
		}

		if (isset($d30) && $d30 != '') {
			$array_credentials[] =  array(
				'id' => 'D30',
				'name' =>  $this->module->trans('PrestaShop - App D30', array(), 'Modules.PagBank.Admin')
			);
		}
		if (
			(!isset($d14) || $d14 == '') &&
			(!isset($d30) || $d30 == '') &&
			(!isset($tax) || $tax == '')
		) {
			$array_credentials = array(
				array(
					'id' => '',
					'name' =>  $this->module->trans('Cadastre-se em algum dos Apps', array(), 'Modules.PagBank.Admin')
				),
			);
		}

		$user_credential = Configuration::get('PAGBANK_CREDENTIAL');
		$text_credential = '';
		if ($user_credential) {
			$text_credential .= '<br /><div class="alert alert-info"><p>' . $this->module->trans('Você está utilizando a credencial ', array(), 'Modules.PagBank.Admin') . ' <b>PrestaShop - App ' . $user_credential . '.</b></p></div>';
		}

		$text_sandbox = '';
		if (Configuration::get('PAGBANK_ENVIRONMENT') == 0) {
			$text_sandbox .= '<br /><div class="alert alert-danger"><p>' . $this->module->trans('Atenção, você está em ambiente SandBox', array(), 'Modules.PagBank.Admin') . '</p></div>';
		}

		$link_order_preferences = 'index.php?controller=AdminOrderPreferences&token=' . Tools::getAdminTokenLite('AdminOrderPreferences');
		$text_min_installments = '<br />Em complemento, se preferir, você pode ativar a opção para restringir o valor mínimo de pedido aceito pela loja. Tab Preferências > Pedidos ou <a href="' . $link_order_preferences . '">clicando aqui</a>.';

		$prefix_discount_value = null;
		$sufix_discount_value = null;
		if (Configuration::get('PAGBANK_DISCOUNT_TYPE') == 1) {
			$sufix_discount_value = '%';
		} elseif (Configuration::get('PAGBANK_DISCOUNT_TYPE') == 2) {
			$prefix_discount_value = 'R$';
		}

		$fields_form_1 = array(
			'form' => array(
				'legend' => array(
					'title' =>  $this->module->trans('Configurações do App', array(), 'Modules.PagBank.Admin'),
					'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Ambiente de Produção?', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_ENVIRONMENT',
						'bool' => false,
						'desc' =>  $this->module->trans('Você pode utilizar o Ambiente de Testes (Sandbox) e testar tudo antes de colocar em Produção.', array(), 'Modules.PagBank.Admin') . $text_sandbox,
						'values' => array(
							array(
								'id' => 'PAGBANK_MODO_on',
								'value' => 1,
								'label' =>  $this->module->trans('Produção', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_MODO_off',
								'value' => 0,
								'label' =>  $this->module->trans('Sandbox', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Tipo de Credencial', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_CREDENTIAL',
						'desc' =>  $this->module->trans('Defina o tipo de credential que a sua loja irá utilizar para processar os pagamentos.', array(), 'Modules.PagBank.Admin') . $text_credential,
						'class' => 'credentials',
						'options' => array(
							'query' => $array_credentials,
							'id' => 'id',
							'name' => 'name',
						),
					),
				),
			),
		);

		$fields_form_2 = array(
			'form' => array(
				'legend' => array(
					'title' =>  $this->module->trans('Configurações de Pagamento', array(), 'Modules.PagBank.Admin'),
					'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Cartão de Crédito', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_CREDIT_CARD',
						'bool' => false,
						'values' => array(
							array(
								'id' => 'PAGBANK_CREDIT_CARD_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_CREDIT_CARD_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Quantidade máxima de parcelas', array(), 'Modules.PagBank.Admin'),
						'class' => 'credit_card_option fixed-width-xs',
						'name' => 'PAGBANK_MAX_INSTALLMENTS',
						'desc' =>  $this->module->trans('Defina a quantidade máxima de parcelas para seus clientes.', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => $array_installments,
							'id' => 'id',
							'name' => 'name',
						),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Quantidade de parcelas sem juros', array(), 'Modules.PagBank.Admin'),
						'class' => 'credit_card_option fixed-width-xs',
						'name' => 'PAGBANK_NO_INTEREST',
						'desc' =>  $this->module->trans('Defina a quantidade de parcelas sem juros para seus clientes.', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => $array_installments,
							'id' => 'id',
							'name' => 'name',
						),
					),
					array(
						'type' => 'text',
						'class' => 'credit_card_option fixed-width-xs fixed-width-sm',
						'label' =>  $this->module->trans('Valor da parcela mínima', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_MINIMUM_INSTALLMENTS',
						'desc' =>  $this->module->trans('Defina o valor da parcela mínima, exemplo: 5.00 ou 15.00. Deixe como 0 (zero) para desativar este recurso. O valor mínimo da parcela no Cartão de Crédito é de R$ 1.00.', array(), 'Modules.PagBank.Admin'),
					),
					array(
						'type' => 'radio',
						'label' =>  $this->module->trans('Comportamento da parcela mínima', array(), 'Modules.PagBank.Admin'),
						'class' => 'credit_card_option',
						'name' => 'PAGBANK_INSTALLMENTS_TYPE',
						'is_bool' => true,
						'values' => array(
							array(
								'id' => 'opcao_1',
								'value' => 0,
								'label' =>  $this->module->trans('Não processar nada abaixo do valor mínimo estipulado.', array(), 'Modules.PagBank.Admin') . $text_min_installments
							),
							array(
								'id' => 'opcao_2',
								'value' => 1,
								'label' =>  $this->module->trans('Oferecer pagamento a vista, em 1x parcela, para valores abaixo do mínimo estipulado.', array(), 'Modules.PagBank.Admin')
							),
						),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Compra com 1 Click', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_SAVE_CREDIT_CARD',
						'bool' => false,
						'desc' =>  $this->module->trans('O cliente poderá salvar o Cartão de Crédito para futuras compras. O Cartão é criptografado e armazenado pelo PagBank através do processo de Tokenização', array(), 'Modules.PagBank.Admin'),
						'values' => array(
							array(
								'id' => 'PAGBANK_SAVE_CREDIT_CARD_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_SAVE_CREDIT_CARD_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Boleto Bancário', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_BANKSLIP',
						'bool' => false,
						'values' => array(
							array(
								'id' => 'PAGBANK_BANKSLIP_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_BANKSLIP_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),

					),
					array(
						'type' => 'text',
						'class' => 'bankslip_option fixed-width-xs',
						'label' =>  $this->module->trans('Prazo de vencimento do boleto', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_BANKSLIP_DATE_LIMIT',
						'suffix' =>  $this->module->trans('dias', array(), 'Modules.PagBank.Admin'),
						'desc' =>  $this->module->trans('Defina o prazo máximo, em dias, que o usuário terá para realizar o pagamento por Boleto. O prazo mínimo é 2 dias.', array(), 'Modules.PagBank.Admin'),
					),
					array(
						'type' => 'text',
						'class' => 'bankslip_option',
						'label' =>  $this->module->trans('Texto descritivo para o boleto', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_BANKSLIP_TEXT',
						'desc' =>  $this->module->trans('Máximo de 128 caracteres.', array(), 'Modules.PagBank.Admin'),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('PIX', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_PIX',
						'bool' => false,
						'values' => array(
							array(
								'id' => 'PAGBANK_PIX_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_PIX_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'text',
						'class' => 'fixed-width-xs pix_option',
						'label' =>  $this->module->trans('Prazo limite de pagamento via PIX', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_PIX_TIME_LIMIT',
						'suffix' =>  $this->module->trans('minutos', array(), 'Modules.PagBank.Admin'),
						'desc' =>  $this->module->trans('Defina o tempo máximo, em minutos, que o usuário terá para realizar o pagamento via PIX.', array(), 'Modules.PagBank.Admin'),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Desconto no Pagamento?', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_DISCOUNT_TYPE',
						'desc' =>  $this->module->trans('Defina o tipo de desconto que será aplicado.', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => array(
								array(
									'id' => '0',
									'name' =>  $this->module->trans('Nenhum Desconto', array(), 'Modules.PagBank.Admin'),
								),
								array(
									'id' => '1',
									'name' =>  $this->module->trans('Percentual', array(), 'Modules.PagBank.Admin'),
								),
								array(
									'id' => '2',
									'name' =>  $this->module->trans('Valor Fixo', array(), 'Modules.PagBank.Admin'),
								),
							),
							'id' => 'id',
							'name' => 'name',
						),
					),
					array(
						'type' => 'text',
						'class' => 'fixed-width-xs fixed-width-sm',
						'label' =>  $this->module->trans('Valor do desconto', array(), 'Modules.PagBank.Admin'),
						'prefix' => $prefix_discount_value,
						'suffix' => $sufix_discount_value,
						'name' => 'PAGBANK_DISCOUNT_VALUE',
						'desc' =>  $this->module->trans('Defina o valor do desconto, exemplo: 5.00 ou 15.00. Deixe como 0 (zero) para desativar este recurso.', array(), 'Modules.PagBank.Admin'),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Desconto no Cartão de Crédito (1x)', array(), 'Modules.PagBank.Admin'),
						'class' => 'credit_card_option',
						'name' => 'PAGBANK_DISCOUNT_CREDIT',
						'bool' => false,
						'desc' =>  $this->module->trans('Atenção: O valor mínimo da transação no Cartão de Crédito é de R$ 1.00.', array(), 'Modules.PagBank.Admin'),
						'values' => array(
							array(
								'id' => 'PAGBANK_DISCOUNT_CREDIT_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_DISCOUNT_CREDIT_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Desconto no Boleto Bancário', array(), 'Modules.PagBank.Admin'),
						'class' => 'bankslip_option',
						'name' => 'PAGBANK_DISCOUNT_BANKSLIP',
						'bool' => false,
						'values' => array(
							array(
								'id' => 'PAGBANK_DISCOUNT_BANKSLIP_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_DISCOUNT_BANKSLIP_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Desconto no Pix', array(), 'Modules.PagBank.Admin'),
						'class' => 'pix_option',
						'name' => 'PAGBANK_DISCOUNT_PIX',
						'bool' => false,
						'desc' =>  $this->module->trans('Atenção: O valor mínimo da transação no Pix é de R$ 1.00.', array(), 'Modules.PagBank.Admin'),
						'values' => array(
							array(
								'id' => 'PAGBANK_DISCOUNT_PIX_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'PAGBANK_DISCOUNT_PIX_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
				),
			),
		);

		$fields_form_3 = array(
			'form' => array(
				'legend' => array(
					'title' =>  $this->module->trans('Status de Pedido - Mapeamento', array(), 'Modules.PagBank.Admin'),
					'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Status de Pagamento Autorizado', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_AUTHORIZED',
						'desc' =>  $this->module->trans('Defina o Status que sua loja utiliza como \"Pagamento Autorizado\".', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state',
							'name' => 'name',
						),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Status de Pedido Cancelado', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_CANCELED',
						'desc' =>  $this->module->trans('Defina o Status que sua loja utiliza como \"Pedido Cancelado\".', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state',
							'name' => 'name',
						),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Status de Pedido Estornado', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_REFUNDED',
						'desc' =>  $this->module->trans('Defina o Status que sua loja utiliza como \"Pedido Estornado\".', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state',
							'name' => 'name',
						),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Status de Pagamento Em Análise', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_IN_ANALYSIS',
						'desc' =>  $this->module->trans('Defina o Status que sua loja utiliza como \"Pagamento Em Análise\".', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state',
							'name' => 'name',
						),
					),
					array(
						'type' => 'select',
						'label' =>  $this->module->trans('Status de Aguardando Pagamento', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_AWAITING_PAYMENT',
						'desc' =>  $this->module->trans('Defina o Status que sua loja utiliza como \"Aguardando Pagamento\".', array(), 'Modules.PagBank.Admin'),
						'options' => array(
							'query' => $statuses,
							'id' => 'id_order_state',
							'name' => 'name',
						),
					),
				),
			),
		);

		$fields_form_4 = array(
			'form' => array(
				'legend' => array(
					'title' =>  $this->module->trans('Debug & Logs', array(), 'Modules.PagBank.Admin'),
					'icon' => 'icon-cogs',
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Exibir parâmetros no Console do navegador?', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_SHOW_CONSOLE',
						'is_bool' => true,
						'desc' =>  $this->module->trans('Mostrar mensagens do JavaScript no console do navegador para fins de depuração.', array(), 'Modules.PagBank.Admin'),
						'values' => array(
							array(
								'id' => 'console_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'console_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Gerar LOGs completos?', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_FULL_LOG',
						'is_bool' => true,
						'desc' =>  $this->module->trans('Logs completos registram tudo que é enviado e recebido pela loja.', array(), 'Modules.PagBank.Admin'),
						'values' => array(
							array(
								'id' => 'logs_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'logs_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
					array(
						'type' => 'switch',
						'label' =>  $this->module->trans('Apagar tabelas do banco?', array(), 'Modules.PagBank.Admin'),
						'name' => 'PAGBANK_DELETE_DB',
						'is_bool' => true,
						'desc' =>  $this->module->trans('Recomendamos deixar esta opção desabilitada. Ative apenas se tiver certeza de que não vai mais precisar das informações.', array(), 'Modules.PagBank.Admin'),
						'values' => array(
							array(
								'id' => 'deletebd_on',
								'value' => 1,
								'label' =>  $this->module->trans('Sim', array(), 'Modules.PagBank.Admin'),
							),
							array(
								'id' => 'deletebd_off',
								'value' => 0,
								'label' =>  $this->module->trans('Não', array(), 'Modules.PagBank.Admin'),
							),
						),
					),
				),
			),
		);

		$fields_form_5 = array(
			'form' => array(
				'legend' => array(
					'title' =>  $this->module->trans('Salvar Configurações', array(), 'Modules.PagBank.Admin'),
					'icon' => 'icon-save',
				),
				'submit' => array(
					'title' =>  $this->module->trans('Salvar', array(), 'Modules.PagBank.Admin'),
				),
			),
		);

		$helper = new HelperForm();

		$helper->show_toolbar = false;
		$helper->table = $this->module->table;
		$helper->module = $this->module;
		$helper->default_form_language = $this->context->language->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

		$helper->identifier = $this->module->identifier;
		$helper->submit_action = 'submitPagBankModule';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name;
		//$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=pagbank&tab_module=payments_gateways&module_name=pagbank';
		$helper->token = Tools::getAdminTokenLite('AdminModules');

		$fields_value = $this->module->getConfigFormValues();

		$helper->tpl_vars = array(
			'fields_value' => $fields_value,
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id,
		);

		return $helper->generateForm(array($fields_form_1, $fields_form_2, $fields_form_3, $fields_form_4, $fields_form_5));
	}
}
