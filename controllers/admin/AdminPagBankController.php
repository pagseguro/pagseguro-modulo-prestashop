<?php
/*
 * PagBank
 * 
 * Módulo Oficial para Integração com o PagBank via API v.4
 * Checkout Transparente para PrestaShop 1.6.x ao 9.x
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

class AdminPagBankController extends ModuleAdminController
{
    public $module = 'pagbank';
    private $_html = '';
    private $type;

    public function __construct()
    {
        $this->bootstrap = true;

        $this->className = "PagBank";
        $this->context = Context::getContext();
        $this->table = 'pagbank';
        $this->identifier = 'id_pagbank';
        $this->default_form_language = $this->context->language->id;
        $this->_use_found_rows = false;
        $this->list_no_link = true;
        $this->token = Tools::getAdminTokenLite("AdminPagBank");
        $this->templateDir = _PS_MODULE_DIR_ . "pagbank/views/templates/admin/";
        $this->allow_export = true;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->_select = 'CONCAT(b.firstname, " ", b.lastname) as cliente, c.id_order, c.reference, c.total_paid_tax_incl';
        $this->_join = 'LEFT JOIN ' . _DB_PREFIX_ . 'customer b ON (b.id_customer = a.id_customer)
                        LEFT JOIN ' . _DB_PREFIX_ . 'orders c ON (c.id_cart = a.id_cart)';
        $this->_filter = 'AND (a.id_shop = ' . (int)$this->context->shop->id . ' OR a.id_shop IS NULL)';
        $this->_orderBy = 'c.id_order';
        $this->_orderWay = 'DESC';
        $this->fields_list = array(
            'id_cart' => array(
                'title' => $this->l('Carrinho'),
                'align' => 'left',
                'filter_key' => 'a!id_cart'
            ),
            'id_order' => array(
                'title' => $this->l('Pedido'),
                'align' => 'left',
                'filter_key' => 'a!id_order'
            ),
            'cliente' => array(
                'title' => $this->l('Cliente'),
                'align' => 'left',
                'width' => 100
            ),
            'transaction_code' => array(
                'title' => $this->l('Transação'),
                'align' => 'left',
                'width' => 140
            ),
            'reference' => array(
                'title' => $this->l('Referência'),
                'align' => 'left',
                'width' => 80
            ),
            'order_date' => array(
                'title' => $this->l('Data'),
                'width' => 80,
                'align' => 'left',
                'type' => 'datetime'
            ),
            'status_description' => array(
                'title' => $this->l('Status'),
                'align' => 'left',
                'width' => 80,
                'prefix' => '<b>',
                'suffix' => '</b>'
            ),
            'payment_description' => array(
                'title' => $this->l('Pagamento'),
                'align' => 'left',
                'width' => 80,
            ),
            'refund' => array(
                'title' => $this->l('Estorno'),
                'align' => 'left',
                'width' => 80,
                'filter_key' => 'a!refund',
            ),
            'date_update' => array(
                'title' => $this->l('Últ. Atualiz.'),
                'width' => 100,
                'align' => 'left',
                'type' => 'datetime'
            )
        );
        AdminController::initShopContext();
        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        if ($this->display == 'view') {
            $this->page_header_toolbar_btn['retornar'] = array(
                'href' => $this->context->link->getAdminLink('AdminPagBank'),
                'desc' => $this->l('Retornar', null, null, false),
                'icon' => 'process-icon-back'
            );
        }
        parent::initPageHeaderToolbar();
    }

    public function initToolbar()
    {
        parent::initToolbar();

        unset($this->toolbar_btn['new']);

        $this->toolbar_title = $this->l('PagBank - Transações');
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

    public function setMedia()
    {
        parent::setMedia();
        $this->addCSS(_PS_MODULE_DIR_ . 'pagbank/css/pagbank_admin.css');
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
        $payment_description = isset($transaction->charges) ? $info['payment_description'] : $info['payment_description'];

        $this->context->smarty->assign(array(
            'info' => $info,
            'customer' => $customer,
            'customer_address' => isset($customer_address) && $customer_address ? $customer_address : '',
            'number_field' => $this->module->number_field,
            'compl_field' => $this->module->compl_field,
            'order' => isset($order) && $order != false ? $order : '',
            'transaction' => $transaction,
			'status' => $status_pagbank,
			'desc_status' => $pagbank->parseStatus($status_pagbank),
            'payment_description' => $payment_description,
            'back_link' => $this->context->link->getAdminLink("AdminPagBank", false) . "&token=" . $this->token
        ));
        $this->_html .= $this->context->smarty->fetch($this->templateDir . 'details.tpl');
        return $this->_html;
    }
}
