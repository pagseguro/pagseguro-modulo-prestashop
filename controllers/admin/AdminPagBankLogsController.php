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

class AdminPagBankLogsController extends ModuleAdminController
{
    public $module = 'pagbank';
    private $_html = '';
    private $type;

    public function __construct()
    {
        $this->className = "PagBank";
        $this->context = Context::getContext();
        $this->identifier = "id_log";
        $this->table = "pagbank_logs";
        $this->token = Tools::getAdminTokenLite("AdminPagBankLogs");
        $this->_defaultOrderBy = "datetime";
        $this->_defaultOrderWay = "DESC";
        $this->lang = false;
        $this->templateDir = _PS_MODULE_DIR_ . "pagbank/views/templates/admin/";
        $this->bootstrap = true;
        $this->allow_export = true;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->default_form_language = $this->context->language->id;
        $this->_use_found_rows = false;
        $type = Tools::getValue('type');
        if (Tools::getIsset('type')) {
            if ($type == 'other') {
                $this->_select .= ' WHERE `type` != "error" AND `type` != "curl";';
            } elseif ($type == 'other') {
                $this->_select .= ' WHERE `type` == "' . $type . '";';
            }
        }
        $this->_filter = 'AND (a.id_shop = ' . (int)$this->context->shop->id . ' OR a.id_shop IS NULL)';
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
        $this->fields_list = array(
            'id_log' => array(
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs'
            ),
            'id_cart' => array(
                'title' => $this->l('ID Cart'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'orderby' => true
            ),
            'datetime' => array(
                'title' => $this->l('Date Time'),
                'type' => 'datetime',
                'class' => 'fixed-width-lg',
                'orderby' => true
            ),
            'type' => array(
                'title' => $this->l('Type'),
                'type' => 'text',
                'class' => 'fixed-width-sm',
                'orderby' => true
            ),
            "method" => array(
                "title" => $this->l("Method"),
                "type" => "text",
                'class' => 'fixed-width-sm',
                "orderby" => true
            ),
            "url" => array(
                "title" => $this->l("URL"),
                "type" => "text",
                'class' => 'fixed-width-sm',
                "orderby" => true,
            )
        );
        AdminController::initShopContext();
        parent::__construct();
    }

    /*
	 * Apresenta dados do log 
	 */
    public function renderView()
    {
        $id_log = Tools::getValue('id_log');
        if (!($log = $this->getLog($id_log))) {
            return;
        }

        $clean_json = json_decode($log->data);
        $json = json_encode($clean_json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $response = json_encode(json_decode($log->response), JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $this->context->smarty->assign(array(
            'log' => $log,
            'id_cart' => $log->id_cart,
            'type' => $log->type,
            'data' => stripslashes($json),
            'response' => $response,
            'token_admin_cart' => Tools::getAdminToken('AdminCarts' . (int)(Tab::getIdFromClassName('AdminCarts')) . (int)$this->context->employee->id),
            'url' => stripslashes($log->url),
            'back_link' => $this->context->link->getAdminLink("AdminPagBankLogs", false) . "&token=" . $this->token
        ));
        $this->_html .= $this->context->smarty->fetch($this->templateDir . 'view_log.tpl');
        return $this->_html;
    }

    /*
	 * Busca dados do log no Banco de Dados
	 */
    public function getLog($id_log)
    {
        if (!$id_log) {
            $id_log = Tools::getValue('id_log');
        }
        $db = Db::getInstance();
        $results = $db->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'pagbank_logs` WHERE `id_log` = ' . $id_log . ';');

        return json_decode(json_encode($results));
    }
}
