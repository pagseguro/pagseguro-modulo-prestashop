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

require_once _PS_MODULE_DIR_ . 'pagbank/models/PagBankLog.php';

class AdminPagBank8LogsController extends ModuleAdminController
{
    public $module;
    private $type;

    public function __construct()
    {
        $this->className = "PagBankLog";
        $this->context = Context::getContext();
        $this->identifier = "id_log";
        $this->module = Module::getInstanceByName('pagbank');
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
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected', 'AdminPagBankLogs'),
                'confirm' => $this->module->l('Delete selected items?', 'AdminPagBankLogs'),
                'icon' => 'icon-trash',
            )
        );

        $this->default_form_language = $this->context->language->id;
        $this->_use_found_rows = true;
        $this->fields_list = array(
            'id_log' => array(
                'title' => $this->module->l('ID', 'AdminPagBankLogs'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'id_cart' => array(
                'title' => $this->module->l('ID Cart', 'AdminPagBankLogs'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'orderby' => true,
            ),
            'datetime' => array(
                'title' => $this->module->l('Date Time', 'AdminPagBankLogs'),
                'type' => 'datetime',
                'class' => 'fixed-width-lg',
                'orderby' => true,
            ),
            'type' => array(
                'title' => $this->module->l('Type', 'AdminPagBankLogs'),
                'type' => 'text',
                'class' => 'fixed-width-sm',
                'orderby' => true,
            ),
            "method" => array(
                "title" => $this->module->l('Method', 'AdminPagBankLogs'),
                "type" => "text",
                'class' => 'fixed-width-sm',
                "orderby" => true,
            ),
            "url" => array(
                "title" => $this->module->l('URL', 'AdminPagBankLogs'),
                "type" => "text",
                'class' => 'fixed-width-sm',
                "orderby" => true,
                //"callback" => stripslashes(),
            )
        );

        $type = Tools::getValue('type');
        if (Tools::getIsset('type')) {
            if ($type == 'other') {
                $this->_select .= ' WHERE `type` != "error" AND `type` != "curl";';
            } elseif ($type == 'other') {
                $this->_select .= ' WHERE `type` == "' . $type . '";';
            }
        }
        $this->_use_found_rows = false;
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
            'back_link' => $this->context->link->getAdminLink("AdminPagBank8Logs", false) . "&token=" . $this->token
        ));
        return $this->context->smarty->fetch('../modules/pagbank/views/templates/admin/view_log.tpl');
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
