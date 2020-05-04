<?php
/*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.7.x
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'pagseguropro/models/PagSeguroProModel.php';

class AdminPagSeguroProController extends ModuleAdminController
{
   	private $_html = '';
	private $type;
	
   	public function __construct() 
	{        
        $this->bootstrap = true;

        $this->className = "PagSeguroProModel";
        $this->context = Context::getContext();
        $this->table = 'pagseguropro';
        $this->identifier = 'id_pagseguro';
        $this->default_form_language = $this->context->language->id;
        $this->_use_found_rows = false;
        $this->list_no_link = true;
        $this->token = Tools::getAdminTokenLite("AdminPagSeguroPro");

		$this->module = Module::getInstanceByName('pagseguropro');
        $this->templateDir = _PS_MODULE_DIR_ . "pagseguropro/views/templates/admin/";
        $this->allow_export = true;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->_select = 'CONCAT(b.firstname, " ", b.lastname) as cliente, c.id_order, c.reference, c.total_paid_tax_incl';
        $this->_join = 'LEFT JOIN '._DB_PREFIX_.'customer b ON (b.id_customer = a.cod_cliente)
                        LEFT JOIN '._DB_PREFIX_.'orders c ON (c.id_cart = a.id_cart)';
        $this->_filter = 'AND a.id_shop = '.(int)$this->context->shop->id;
        $this->_orderBy = 'c.id_order';
        $this->_orderWay = 'DESC';

        $this->fields_list = array(
            'id_cart' => array(
                'title' => $this->module->l('Carrinho', 'AdminPagSeguroPro'),
                'align' => 'left',
		'filter_key' => 'a!id_cart'
            ),
            'id_order' => array(
                'title' => $this->module->l('Pedido', 'AdminPagSeguroPro'),
                'align' => 'left',
		'filter_key' => 'a!id_order'
            ),
            'cliente' => array(
                'title' => $this->module->l('Cliente', 'AdminPagSeguroPro'),
                'align' => 'left',
                'width' => 100
            ),
            'cod_transacao' => array(
                'title' => $this->module->l('Transação', 'AdminPagSeguroPro'),
                'align' => 'left',
                'width' => 140
            ),
            'referencia' => array(
                'title' => $this->module->l('Referência', 'AdminPagSeguroPro'),
                'align' => 'left',
                'width' => 80
            ),
            'data_pedido' => array(
                'title' => $this->module->l('Data', 'AdminPagSeguroPro'),
                'width' => 80,
                'align' => 'left',
                'type' => 'datetime'
            ),
            'desc_status' => array(
                'title' => $this->module->l('Status', 'AdminPagSeguroPro'),
                'align' => 'left',
                'width' => 80,
                'prefix' => '<b>',
                'suffix' => '</b>'
            ),
            'desc_pagto' => array(
                'title' => $this->module->l('Pagamento', 'AdminPagSeguroPro'),
                'align' => 'left',
                'width' => 80,
            ),
            'data_atu' => array(
                'title' => $this->module->l('Últ. Atualiz.', 'AdminPagSeguroPro'),
                'width' => 100,
                'align' => 'left',
                'type' => 'datetime'
            )
        );
        parent::__construct();
		
    }
    
    public function initPageHeaderToolbar() 
	{            
        if ($this->display == 'view') {
            $this->page_header_toolbar_btn['retornar'] = array(
                'href' => $this->context->link->getAdminLink('AdminPagSeguroPro'),
                'desc' => $this->module->l('Retornar', 'AdminPagSeguroPro'),
                'icon' => 'process-icon-back'
            );    
        }
        parent::initPageHeaderToolbar();
    }         
    
    public function initToolbar() 
	{
        parent::initToolbar();
        
        unset($this->toolbar_btn['new']);
        
        $this->toolbar_title = $this->module->l('PagSeguro - Transações', 'AdminPagSeguroPro');        
    }

    public function processDelete() 
	{
		$pagseguro = new PagSeguroPro();
        return $pagseguro->delete(Tools::getValue('id_pagseguro'));
    }
    
    public function processResetFilters($list_id = null) 
	{        
        parent::processResetFilters();
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminPagSeguroPro'));
    }

	/*
	 * Visualização dos dados do pedido 
	 * Relaciona os dados do pedido na loja com os dados da transação no PagSeguro
	 */
    public function renderView()
    {
		$id_pagseguro = Tools::getValue('id_pagseguro');
	
		$pagseguro = new PagSeguroPro();
		$info = $pagseguro->getOrderData($id_pagseguro, 'id_pagseguro');
		
		$id_customer = $info['cod_cliente'];
		$id_order = $info['id_order'];
		
		$cliente = new Customer((int)$id_customer);
		
		if(isset($id_order) && $id_order != ''){
			$order = new Order($id_order);
			$endereco = new Address($order->id_address_delivery);
			$state = new State($order->id_address_delivery);
			$country = new Country((int)$endereco->id_country);
			$carrier = new Carrier($order->id_carrier);
			
			$order->carrier_name = $carrier->name;
			$endereco->uf = $state->iso_code;
			$endereco->pais = $country->name[$this->context->language->id];
		}
		if (isset($info['credencial']) && $info['credencial'] != '' && isset($info['token_codigo']) && $info['token_codigo'] != ''){
	        $transaction = $pagseguro->getTransaction($info['cod_transacao'], $info['credencial'], $info['token_codigo']);
		}else{
	        $transaction = $pagseguro->getTransaction($info['cod_transacao']);
		}
		
		$this->context->smarty->assign(array(
			'info' => $info,
			'cliente' => $cliente,
			'endereco' => isset($endereco) && $endereco ? $endereco : '',
			'number_field' => $this->module->number_field,
			'compl_field' => $this->module->compl_field,
			'pedido' => isset($order) && $order != false? $order : '',
			'transacao' => isset($transaction) && $transaction !== false ? $transaction : '',
			'status' => isset($transaction) && $transaction !== false ? $this->module->parseStatus((int)$transaction->status) : '',
			'formaPagamento' => isset($transaction) && $transaction !== false ? $this->module->parsePagamento((int)$transaction->paymentMethod->type) : '',
			'tipoPagamento' => isset($transaction) && $transaction !== false ? $this->module->parseTipoPagamento((int)$transaction->paymentMethod->code) : '',
			'back_link' => $this->context->link->getAdminLink("AdminPagSeguroPro", false)."&token=".$this->token
		));
        $this->_html .= $this->context->smarty->fetch($this->templateDir.'detalhes.tpl');
        return $this->_html;
        //return $this->context->smarty->fetch('../modules/pagseguropro/views/templates/admin/detalhes.tpl');
    }

}
