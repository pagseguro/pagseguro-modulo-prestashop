<?php
/**
 * 2019 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - PrestaShop 1.7.x
 *
 */

require_once _PS_MODULE_DIR_ . 'pagseguropro/models/PagSeguroProLog.php';

class AdminPagSeguroProLogsController extends ModuleAdminController
{
    public $module;
	private $type;
	
    public function __construct()
    {
        $this->className = "PagSeguroProLog";
        $this->context = Context::getContext();
        $this->identifier = "id_log";
		$this->module = Module::getInstanceByName('pagseguropro');
        $this->table = "pagseguropro_logs";
        $this->token = Tools::getAdminTokenLite("AdminPagSeguroProLogs");
        $this->_defaultOrderBy = "datetime";
        $this->_defaultOrderWay = "DESC";
        $this->lang = false;
        $this->templateDir = _PS_MODULE_DIR_ . "pagseguropro/views/templates/admin/";
        $this->bootstrap = true;
        $this->allow_export = true;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Delete selected', 'AdminPagSeguroProLogs'),
                'confirm' => $this->module->l('Delete selected items?', 'AdminPagSeguroProLogs'),
                'icon' => 'icon-trash',
            )
        );
		
        $this->default_form_language = $this->context->language->id;
        $this->_use_found_rows = true;
        $this->fields_list = array(
            'id_log' => array(
                'title' => $this->module->l('ID', 'AdminPagSeguroProLogs'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ),
            'id_cart' => array(
                'title' => $this->module->l('ID Cart', 'AdminPagSeguroProLogs'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
                'orderby' => true,
            ),
            'datetime' => array(
                'title' => $this->module->l('Date Time', 'AdminPagSeguroProLogs'),
                'type' => 'datetime',
                'class' => 'fixed-width-lg',
                'orderby' => true,
            ),
            'type' => array(
                'title' => $this->module->l('Type', 'AdminPagSeguroProLogs'),
                'type' => 'text',
                'class' => 'fixed-width-sm',
                'orderby' => true,
            ),
            "method" => array(
                "title" => $this->module->l('Method', 'AdminPagSeguroProLogs'),
                "type" => "text",
                'class' => 'fixed-width-sm',
                "orderby" => true,
            ),
            "url" => array(
                "title" => $this->module->l('URL', 'AdminPagSeguroProLogs'),
                "type" => "text",
                'class' => 'fixed-width-sm',
                "orderby" => true,
				//"callback" => stripslashes(),
            )
        );
		
		$type = Tools::getValue('type');
		if(Tools::getIsset('type')) {
			if($type == 'other') {
				$this->_select .= ' WHERE `type` != "error" AND `type` != "curl";';
			}elseif($type == 'other') {
				$this->_select .= ' WHERE `type` == "'.$type.'";';
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
		
		$clean_response = $log->response;
		$xml = new DOMDocument;
		$xml->preserveWhiteSpace = false;
		$xml->loadXML($log->response);
		$xml->formatOutput = true;
		$response = $xml->saveXML();
		
		$this->context->smarty->assign(array(
			'log' => $log,
			'id_cart' => $log->id_cart,
			'type' => $log->type,
			'data' => stripslashes($json),
			'response' => $response,
			'token_admin_cart' => Tools::getAdminToken('AdminCarts'.(int)(Tab::getIdFromClassName('AdminCarts')).(int)$this->context->employee->id),
			'url' => stripslashes($log->url),
			'back_link' => $this->context->link->getAdminLink("AdminPagSeguroProLogs", false)."&token=".$this->token
		));
        return $this->context->smarty->fetch('../modules/pagseguropro/views/templates/admin/view_log.tpl');
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
		$results = $db->getRow('SELECT * FROM `'._DB_PREFIX_.'pagseguropro_logs` WHERE `id_log` = '.$id_log.';');

		return json_decode(json_encode($results));
	}

}
