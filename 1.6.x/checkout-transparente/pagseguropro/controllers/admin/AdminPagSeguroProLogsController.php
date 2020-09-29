<?php
/*
 * 2020 PrestaBR
 * 
 * Módulo de Pagamento para Integração com o PagSeguro
 *
 * Pagamento com Cartão de Crédito, Boleto e Transferência Bancária
 * Checkout Transparente - Módulo Oficial - PS 1.6.x
 *
 */

class AdminPagSeguroProLogsController extends ModuleAdminController
{
    public $module = 'pagseguropro';
    private $_html = '';
	private $type;
	
    public function __construct()
    {
        $this->className = "PagSeguroPro";
        $this->context = Context::getContext();
        $this->identifier = "id_log";
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
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash'
            )
        );
		
        $this->default_form_language = $this->context->language->id;
        $this->_use_found_rows = true;
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
        $this->_html .= $this->context->smarty->fetch('../modules/pagseguropro/views/templates/admin/view_log.tpl');
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
		$results = $db->getRow('SELECT * FROM `'._DB_PREFIX_.'pagseguropro_logs` WHERE `id_log` = '.$id_log.';');

		return json_decode(json_encode($results));
	}

}
