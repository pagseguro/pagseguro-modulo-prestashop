<?php

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

if (!defined('_PS_VERSION_'))
	exit;

class PagSeguro extends PaymentModule {

    protected $errors = array();
    private $_html;
    private $_charset_options = array('1' => 'ISO-8859-1', '2' =>'UTF-8');
    private $_active_log = array('0' => 'NÃO', '1' => 'SIM');
    
    function __construct() {

        $this->name = 'pagseguro';
        $this->tab = 'payments_gateways';
        $this->version = '1.1';
        $this->author = 'PagSeguro Internet LTDA.';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        
        parent::__construct();

        $this->displayName = $this->l('PagSeguro');
        $this->description = $this->l('Receba pagamentos por cartão de crédito, transferência bancária e boleto.');
        $this->confirmUninstall = $this->l('Tem certeza que deseja remover este módulo ?');
        
        $this->_addPagSeguroLibrary();
        
    }
    
    /**
     * Perform instalation of PagSeguro module
     * 
     * @return boolean
     */
    public function install() {
        // case an error here, instalation will abort
        
        if (    !parent::install() ||
                !$this->registerHook('payment') || 
                !$this->registerHook('paymentReturn') ||
                !Configuration::updateValue('PAGSEGURO_EMAIL', 'informe seu e-mail cadastrado no PagSeguro') ||
                !Configuration::updateValue('PAGSEGURO_TOKEN', 'informe seu token de segurança') ||
                !Configuration::updateValue('PAGSEGURO_URL_REDIRECT', '') ||
                !Configuration::updateValue('PAGSEGURO_CHARSET', PagSeguroConfig::getData('application', 'charset')) ||
                !Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', PagSeguroConfig::getData('log', 'active')) ||
                !Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', PagSeguroConfig::getData('log', 'fileLocation')) ||
                !Configuration::updateValue('PS_OS_PAGSEGURO', 0) ||
                !$this->_generatePagSeguroOrderStatus())
            return false;
        
        return true;
    }

    /**
     * Perform uninstalation of PagSeguro module
     * 
     * @return boolean
     */
    public function uninstall(){
        if (    !Configuration::deleteByName('PAGSEGURO_EMAIL') ||
                !Configuration::deleteByName('PAGSEGURO_TOKEN') ||
                !Configuration::deleteByName('PAGSEGURO_URL_REDIRECT') ||
                !Configuration::deleteByName('PAGSEGURO_CHARSET') ||
                !Configuration::deleteByName('PAGSEGURO_LOG_ACTIVE') ||
                !Configuration::deleteByName('PAGSEGURO_LOG_FILELOCATION') ||
                !Configuration::deleteByName('PS_OS_PAGSEGURO') ||
                !$this->_deleteOrderState() ||
                !parent::uninstall())
            return false;
        
        return true;
    }
    
    /**
     * Perform deletion of PS_OS_PAGSEGURO configuration from database
     * where module will uninstalled
     * 
     * @return bool
     */
    private function _deleteOrderState(){
        $id = Configuration::get('PS_OS_PAGSEGURO');
        $order_state = new OrderState($id);
        return $order_state->delete();
    }

    /**
     * Gets configuration view content
     * 
     * @return string
     */
    public function getContent() {
        
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            
            // if no errors in form
            if (!count($this->errors))
                $this->_postProcess();
            // if errors
            else
                foreach ($this->errors as $error)
                    $this->_html .= '<div class="alert error">'.$error.'</div>';
        }
        
        $this->_displayForm();
        
        return $this->_html;
    }

    /**
     * Realize post validations according with PagSeguro standards
     * case any inconsistence, an item is added to $_postErrors
     */
    private function _postValidation(){
        
        if (Tools::isSubmit('btnSubmit')) {
            $email = Tools::getValue('pagseguro_email');
            $token = Tools::getValue('pagseguro_token');
            $pagseguro_url_redirect = Tools::getValue('pagseguro_url_redirect');
            $charset = Tools::getValue('pagseguro_charset');
            $pagseguro_log = Tools::getValue('pagseguro_log');
            
            // mail validations
            if (!$email)
                $this->errors[] = $this->_errorMessage('E-mail');
            elseif (strlen($email)> 60)
                $this->errors[] = $this->_invalidFieldSizeMessage('E-mail');
            elseif (!Validate::isEmail($email))
                $this->errors[] = $this->_invalidMailMessage('E-mail');
            
            // token validations
            if (!$token)
                $this->errors[] = $this->_errorMessage('Token');
            elseif (strlen($token)!= 32)
                $this->errors[] = $this->_invalidFieldSizeMessage('Token');
            
            // url redirect validation
            if ($pagseguro_url_redirect && !Validate::isUrl($pagseguro_url_redirect))
                $this->errors[] = $this->_invalidUrl('Url de redirecionamento');
            
            // charset validation
            if (!array_key_exists($charset, $this->_charset_options))
                $this->errors[] = $this->_invalidValue('Charset');
            
            // log validation
            if (!array_key_exists($pagseguro_log, $this->_active_log))
                $this->errors[] = $this->_invalidValue('Log');
        }
    }
    
    /**
     * Realize PagSeguro database keys values
     */
    private function _postProcess(){
        if (Tools::isSubmit('btnSubmit')){
            Configuration::updateValue('PAGSEGURO_EMAIL', Tools::getValue('pagseguro_email'));
            Configuration::updateValue('PAGSEGURO_TOKEN', Tools::getValue('pagseguro_token'));
            Configuration::updateValue('PAGSEGURO_URL_REDIRECT', Tools::getValue('pagseguro_url_redirect'));
            Configuration::updateValue('PAGSEGURO_CHARSET', $this->_charset_options[Tools::getValue('pagseguro_charset')]);
            Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', Tools::getValue('pagseguro_log'));
            Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', Tools::getValue('pagseguro_log_dir'));

            // verify if log file exists, case not try create
            if (Tools::getValue('pagseguro_log'))
                $this->_verifyLogFile(Tools::getValue('pagseguro_log_dir'));
        }
        $this->_html .= '<div class="conf confirm">'.$this->l('Dados atualizados com sucesso').'</div>';
    }
    
    /**
     * Create error messages
     * 
     * @param String $field
     * @return String
     */
    private function _errorMessage($field){
        return $this->l("O campo <strong>{$field}</strong> deve ser informado.");
    }
    
    /**
     * Create invalid mail messages
     * 
     * @param String $field
     * @return String
     */
    private function _invalidMailMessage($field){
        return $this->l("O campo <strong>{$field}</strong> deve ser conter um email válido.");
    }
    
    /**
     * Create invalid field size messages
     * 
     * @param String $field
     * @return String
     */
    private function _invalidFieldSizeMessage($field){
        return $this->l("O campo <strong>{$field}</strong> está com um tamanho inválido");
    }
      
    /**
     * Create invalid value messages
     * 
     * @param String $field
     * @return String
     */
    private function _invalidValue($field){
        return $this->l("O campo <strong>{$field}</strong> contém um valor inválido.");
    }
    
    /**
     * Create invalid url messages
     * 
     * @param String $field
     * @return String
     */
    private function _invalidUrl($field){
        return $this->l("O campo <strong>{$field}</strong> deve conter uma url válida.");
    }
    
    /**
     *  Display configuration form
     */
    private function _displayForm() {
        
        $this->context->controller->addJS($this->_path.'assets/js/configuration.js');
        
        $this->_html .=
                '<form action="'.Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']).'" method="post">
                    <fieldset>
			<legend><img src="../img/admin/edit.gif" />'.$this->l('Configurações').'</legend>
                            <table border="0" width="1100px" cellpadding="0" cellspacing="0" id="form">
                                <tr>
                                    <td colspan="2">'.$this->l('Você precisa informar alguns dados antes de começar a usar o módulo de integração com PagSeguro. ').'<br/>
                                </tr>
                                <tr>
                                    <td><br/></td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('E-mail').':</td>
                                    <td><input type="text" name="pagseguro_email" id="pagseguro_email" value="'.Configuration::get('PAGSEGURO_EMAIL').'" style="width:300px;" maxlength="60" /></td>
                                </tr>
                                <tr>
                                     <td></td> 
                                     <td colspan="2">'.$this->l('Não tem conta no PagSeguro?').'<a href="https://pagseguro.uol.com.br/registration/registration.jhtml?ep=5&tipo=cadastro#!vendedor" target="_blank">'.$this->l(' Crie uma nova conta').'</a>'.$this->l(' selecionando o tipo vendedor ou empresarial.').'</td>    
                                </tr>
                                 <tr>
                                    <td><br/></td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Token').':</td>
                                    <td>
                                        <input type="text" name="pagseguro_token" id="pagseguro_token" value="'.Configuration::get('PAGSEGURO_TOKEN').'" style="width:300px;" maxlength="32" />
                                    </td>
                                </tr>
                                <tr>
                                         <td></td> 
                                         <td colspan="2">'.$this->l('N&atilde;o tem ou n&atilde;o sabe seu token?').'<a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" target="_blank">'.$this->l(' Criar um novo token de segurança.').'</a></td>  
                                </tr>
                                 <tr>
                                    <td><br/></td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Url de redirecionamento').':</td>
                                    <td>
                                        <input type="text" name="pagseguro_url_redirect" id="pagseguro_url_redirect" value="'.Configuration::get('PAGSEGURO_URL_REDIRECT').'" style="width:300px;" maxlength="255" />
                                    </td>
                                </tr>
                                 <tr>
                                    <td></td>
                                    <td>Ao final do fluxo de pagamento no PagSeguro, seu cliente será redirecionado de volta para sua loja ou para a URL que você informar no campo acima.<br />Para tanto, é preciso que você ative o recebimento exclusivo de <a href="https://pagseguro.uol.com.br/integracao/pagamentos-via-api.jhtml" target="_blank" >Pagamentos via API</a>.<br />.
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Charset').':</td>
                                    <td>'.$this->_generateSelectTag('pagseguro_charset', $this->_charset_options, array_search(Configuration::get('PAGSEGURO_CHARSET'), $this->_charset_options), 'class="select"').'</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="2">'.$this->l('Defina o charset de acordo com a codificação do seu sistema.').'</td>
                                </tr>
                                 <tr>
                                    <td><br/></td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Log').':</td>
                                    <td>'.$this->_generateSelectTag('pagseguro_log', $this->_active_log, Configuration::get('PAGSEGURO_LOG_ACTIVE'), 'class="select"').'</td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td colspan="2">'.$this->l('Criar arquivo de log?').'</td>
                                </tr>
                                <tr style="display:none;" id="logDir">
                                    <td width="60" style="height: 35px;">'.$this->l('Diretório').':</td>
                                    <td><input type="text" id="pagseguro_log_dir" name="pagseguro_log_dir" value="'.Configuration::get('PAGSEGURO_LOG_FILELOCATION').'" style="width:300px;"/></td>
                                </tr>
                                <tr>
                                    <td><br/></td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Notifica&ccedil;&otilde;es de transa&ccedil;&otilde;es').':</td>
                                    <td>'.
                                        $this->l('Para receber e processar automaticamente os novos status das transações com o PagSeguro você deve ativar o serviço de ').'<a href="https://pagseguro.uol.com.br/integracao/notificacao-de-transacoes.jhtml" target="_blank">'.$this->l('Notificação de Transações.').'</a>'.
                                        $this->l('No painel de controle de sua conta PagSeguro, informe a seguinte url para receber as notificações automaticamente:').
                                    '</td>
                                </tr>
                                 <tr>
                                    <td><br/></td>
                                    <td><br/></td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td><strong>'.$this->_getNotificationUrl().'</strong></td>
                                </tr>
                                <tr>
                                    <td><br/></td>
                                    <td><br/></td>
                                </tr>
                                <tr>
                                    <td colspan="2" align="left"><br /><input class="button" name="btnSubmit" value="'.$this->l('Atualizar').'" type="submit" /></td>
                                </tr>
                            </table>
			</fieldset>
		</form>';
    }

    /**
     *  Perform Payment hook
     * 
     * @param array $params
     * @return string
     */
    public function hookPayment($params) {
        
        if (!$this->active)
            return;
        if (!$this->checkCurrency($params['cart']))
            return;

        $this->smarty->assign(array(
            'image' => $this->getPathUri().'assets/images/logops_86x49.png',
            'this_path' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/'
        ));
        
        return $this->display(__FILE__, 'payment.tpl');
    }
	
    /**
     *  Perform Payment Return hook
     * 
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params) {
        if (!$this->active)
            return;
        
        $state = $params['objOrder']->getCurrentState();
        if ($state == Configuration::get('PS_OS_PAGSEGURO') || $state == Configuration::get('PS_OS_OUTOFSTOCK')) {
            $this->smarty->assign(array(
                'total_to_pay' => Tools::displayPrice($params['total_to_pay'], $params['currencyObj'], false),
                'status' => 'ok',
                'id_order' => $params['objOrder']->id
            ));
            if (isset($params['objOrder']->reference) && !empty($params['objOrder']->reference))
                $this->smarty->assign('reference', $params['objOrder']->reference);
        }
        else
            $this->smarty->assign('status', 'failed');
        return $this->display(__FILE__, 'payment_return.tpl');
    }
    
    /**
     * Check the currency
     * 
     * @param Cart $cart
     * @return boolean
     */
    public function checkCurrency($cart) {
        $currency_order = new Currency((int) ($cart->id_currency));
        return PagSeguroCurrencies::checkCurrencyAvailabilityByIsoCode($currency_order->iso_code);
    }
    
    /**
     *  Generate select tag
     * 
     * @param string $id
     * @param array $options
     * @param string $selected
     * @param string $extra
     * @return string
     */
    private function _generateSelectTag($id, Array $options, $selected = '', $extra = ''){
        
        $select = '<select id="'.$id.'" name="'.$id.'" '.$extra.' >';
        foreach ($options as $key => $value) {
            $selected_attr = ($selected == $key) ? 'selected="selected" ' : '';
            $select .= '<option value="'.$key.'" '.$selected_attr.'>'.$value.'</option>';
        }
        $select.='</select>';
        
        return $select;
    }
    
    /**
     * Include of PagSeguroLibrary to perform transactions between 
     * Prestashop and PagSeguro
     */
    private function _addPagSeguroLibrary(){
        include_once 'PagSeguroLibrary/PagSeguroLibrary.php';
    }
    
    /**
     * Verify if PagSeguro log file exists.
     * Case log file not exists, try create
     * else create PagSeguro.log into PagseguroLibrary folder into module
     */
    private function _verifyLogFile($file){
        try {
            $f = @fopen(_PS_ROOT_DIR_.$file, "a");
            fclose($f);
        }
        catch (Exception $e){
            die($e->getMessage());
        }
    }
    
    /**
     * Create PagSeguro order status into database
     * 
     * @return bool $ordersAdded
     */
    private function _generatePagSeguroOrderStatus(){
        
        // including pagseguroorderstatustranslation file to generate OrderStatus names
        include_once 'pagseguroorderstatustranslation.php';
        
        $orders_added = true;
        $initial_state = 0;
        
        foreach (array_keys(PagSeguroTransactionStatus::getStatusList()) as $status) {
            
            $order_state = new OrderState();
            $order_state->module_name = $this->name;
            $order_state->send_email = false;
            $order_state->color = '#DDEEFF';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = true;
            $order_state->invoice = true;
            $order_state->name = array();
            
            foreach (Language::getLanguages() as $language)
                $order_state->name[$language['id_lang']] = PagSeguroOrderStatusTranslation::getStatusTranslation($status, strtolower($language['iso_code']));
            
            $orders_added &= $order_state->add();
            
            // getting initial state id to update PS_OS_PAGSEGURO config
            if ($status == 'WAITING_PAYMENT')
                $initial_state = (int)$order_state->id;
        }
        
        if ($orders_added)
            Configuration::updateValue('PS_OS_PAGSEGURO', $initial_state);
        
        return $orders_added;
    }
    
    /**
     * Gets notification url
     * @return string
     */
    private function _getNotificationUrl(){
        return _PS_BASE_URL_.__PS_BASE_URI__.'index.php?fc=module&module=pagseguro&controller=notification';
    }
}
        
        
