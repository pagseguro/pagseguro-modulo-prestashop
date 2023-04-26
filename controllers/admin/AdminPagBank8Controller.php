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

require_once _PS_MODULE_DIR_ . 'pagbank/models/PagBankModel.php';

class AdminPagBank8Controller extends ModuleAdminController
{
    private $_html = '';
    private $type;

    public function __construct()
    {
        $this->bootstrap = true;

        $this->className = "PagBankModel";
        $this->context = Context::getContext();
        $this->table = 'pagbank';
        $this->identifier = 'id_pagbank';
        $this->default_form_language = $this->context->language->id;
        $this->_use_found_rows = false;
        $this->list_no_link = true;
        $this->token = Tools::getAdminTokenLite("AdminPagBank");

        $this->module = Module::getInstanceByName('pagbank');
        $this->templateDir = _PS_MODULE_DIR_ . "pagbank/views/templates/admin/";
        $this->allow_export = true;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->_select = 'CONCAT(b.firstname, " ", b.lastname) as cliente, c.id_order, c.reference, c.total_paid_tax_incl';
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'customer b ON (b.id_customer = a.id_customer)
                        LEFT JOIN ' . _DB_PREFIX_ . 'orders c ON (c.id_cart = a.id_cart)';
        $this->_filter = 'AND a.id_shop = ' . (int)$this->context->shop->id;
        $this->_orderBy = 'c.id_order';
        $this->_orderWay = 'DESC';

        $this->fields_list = array(
            'id_cart' => array(
                'title' => $this->module->l('Carrinho', 'AdminPagBank'),
                'align' => 'left',
                'filter_key' => 'a!id_cart'
            ),
            'id_order' => array(
                'title' => $this->module->l('Pedido', 'AdminPagBank'),
                'align' => 'left',
                'filter_key' => 'a!id_order'
            ),
            'cliente' => array(
                'title' => $this->module->l('Cliente', 'AdminPagBank'),
                'align' => 'left',
                'width' => 100
            ),
            'transaction_code' => array(
                'title' => $this->module->l('Transação', 'AdminPagBank'),
                'align' => 'left',
                'width' => 140
            ),
            'reference' => array(
                'title' => $this->module->l('Referência', 'AdminPagBank'),
                'align' => 'left',
                'width' => 80
            ),
            'date_add' => array(
                'title' => $this->module->l('Data', 'AdminPagBank'),
                'width' => 80,
                'align' => 'left',
                'type' => 'datetime'
            ),
            'status_description' => array(
                'title' => $this->module->l('Status', 'AdminPagBank'),
                'align' => 'left',
                'width' => 80,
                'prefix' => '<b>',
                'suffix' => '</b>'
            ),
            'payment_description' => array(
                'title' => $this->module->l('Pagamento', 'AdminPagBank'),
                'align' => 'left',
                'width' => 80,
            ),
            'refund' => array(
                'title' => $this->module->l('Estorno', 'AdminPagBank'),
                'align' => 'left',
                'width' => 80,
                'filter_key' => 'a!refund',
            ),
            'date_upd' => array(
                'title' => $this->module->l('Últ. Atualiz.', 'AdminPagBank'),
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
                'href' => $this->context->link->getAdminLink('AdminPagBank'),
                'desc' => $this->module->l('Retornar', 'AdminPagBank'),
                'icon' => 'process-icon-back'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);

        $this->toolbar_title = $this->module->l('PagBank - Transações', 'AdminPagBank');
    }

    public function processDelete()
    {
        $pagbank = new PagBank();
        return $pagbank->delete(Tools::getValue('id_pagbank'));
    }

    public function processResetFilters($list_id = null)
    {
        parent::processResetFilters();
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminPagBank'));
    }

    /*
	 * Visualização dos dados do pedido 
	 * Relaciona os dados do pedido na loja com os dados da transação no PagBank
	 */
    public function renderView()
    {
        $id_pagbank = Tools::getValue('id_pagbank');

        $pagbank = new PagBank();
        $info = $pagbank->getOrderData($id_pagbank, 'id_pagbank');

        $id_customer = $info['id_customer'];
        $id_order = $info['id_order'];

        $customer = new Customer((int)$id_customer);

        if (isset($id_order) && $id_order != '') {
            $order = new Order($id_order);
            $id_cart = $order->id_cart;
            $customer_address = new Address($order->id_address_delivery);
            $state = new State((int)$customer_address->id_state);
            $country = new Country((int)$customer_address->id_country);
            $carrier = new Carrier($order->id_carrier);

            $order->carrier_name = $carrier->name;
            $customer_address->uf = $state->iso_code;
            $customer_address->country = $country->name[$this->context->language->id];
        }

        $transaction = $pagbank->getTransaction($info['transaction_code'], $order->id_cart);
        $status_pagbank = isset($transaction->charges) ? $pagbank->parseStatus($transaction->charges[0]->status) : $pagbank->parseStatus($info['status']);
        $payment_method = isset($transaction->charges) ? $info['payment_description'] : 'PIX - PagBank';

        $this->context->smarty->assign(array(
            'info' => $info,
            'customer' => $customer,
            'customer_address' => isset($customer_address) && $customer_address ? $customer_address : '',
            'number_field' => $this->module->number_field,
            'compl_field' => $this->module->compl_field,
            'order' => isset($order) && $order != false ? $order : '',
            'transaction' => $transaction,
            'status_pagbank' => $status_pagbank,
            'payment_method' => $payment_method,
            'back_link' => $this->context->link->getAdminLink("AdminPagBank8", false) . "&token=" . $this->token
        ));
        $this->_html .= $this->context->smarty->fetch($this->templateDir . 'details.tpl');
        return $this->_html;
    }
}
