<?php

/*
 * 2012-2013 S2IT Solutions Consultoria LTDA.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author Wellington Camargo <wellington.camargo@s2it.com.br>
 *  @copyright  2012-2013 S2IT Solutions Consultoria LTDA
 *  @version  Release: $Revision: 1 $
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_'))
	exit;

class PagSeguro extends PaymentModule {

    private $_html;
    private $_postErrors = array();
    private $_charsetOptions = array('1' => 'ISO-8859-1', '2' =>'UTF-8');
    private $_activeLog = array('0' => 'NÃO', '1' => 'SIM');
    
    function __construct() {

        $this->name = 'pagseguro';
        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->author = 'S2IT Solutions Consultoria LTDA';

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        
        parent::__construct();

        $this->displayName = $this->l("PagSeguro");
        $this->description = $this->l("Módulo de Pagamento via PagSeguro");
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
                !Configuration::updateValue('PAGSEGURO_EMAIL', PagSeguroConfig::getData('credentials', 'email')) ||
                !Configuration::updateValue('PAGSEGURO_TOKEN', PagSeguroConfig::getData('credentials', 'token')) ||
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
        $orderState = new OrderState($id);
        return $orderState->delete();
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
            if (!count($this->_postErrors))
                $this->_postProcess();
            // if errors
            else
                foreach ($this->_postErrors as $error)
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
            $email = Tools::getValue('pagseguroEmail');
            $token = Tools::getValue('pagseguroToken');
            $pagseguroUrlRedirect = Tools::getValue('pagseguroUrlRedirect');
            $charset = Tools::getValue('pagseguroCharset');
            $pagseguroLog = Tools::getValue('pagseguroLog');
            
            // mail validations
            if (!$email)
                $this->_postErrors[] = $this->_errorMessage('E-mail');
            elseif (strlen($email)> 60)
                $this->_postErrors[] = $this->_invalidFieldSizeMessage('E-mail');
            elseif (!Validate::isEmail($email))
                $this->_postErrors[] = $this->_invalidMailMessage('E-mail');
            
            // token validations
            if (!$token)
                $this->_postErrors[] = $this->_errorMessage('Token');
            elseif (strlen($token)!= 32)
                $this->_postErrors[] = $this->_invalidFieldSizeMessage('Token');
            
            // url redirect validation
            if ($pagseguroUrlRedirect && !Validate::isUrl($pagseguroUrlRedirect))
                $this->_postErrors[] = $this->_invalidUrl('Url de redirecionamento');
            
            // charset validation
            if (!array_key_exists($charset, $this->_charsetOptions))
                $this->_postErrors[] = $this->_invalidValue('Charset');
            
            // log validation
            if (!array_key_exists($pagseguroLog, $this->_activeLog))
                $this->_postErrors[] = $this->_invalidValue('Log');
        }
    }
    
    /**
     * Realize PagSeguro database keys values
     */
    private function _postProcess(){
        if (Tools::isSubmit('btnSubmit')){
            Configuration::updateValue('PAGSEGURO_EMAIL', Tools::getValue('pagseguroEmail'));
            Configuration::updateValue('PAGSEGURO_TOKEN', Tools::getValue('pagseguroToken'));
            Configuration::updateValue('PAGSEGURO_URL_REDIRECT', Tools::getValue('pagseguroUrlRedirect'));
            Configuration::updateValue('PAGSEGURO_CHARSET', $this->_charsetOptions[Tools::getValue('pagseguroCharset')]);
            Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', Tools::getValue('pagseguroLog'));
            Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', Tools::getValue('pagseguroLogDir'));

            // verify if log file exists, case not try create
            if (Tools::getValue('pagseguroLog'))
                $this->_verifyLogFile(Tools::getValue('pagseguroLogDir'));
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
                            <table border="0" width="600px" cellpadding="0" cellspacing="0" id="form">
                                <tr>
                                    <td colspan="2">'.$this->l('Por favor, insira as informações necessárias para o funcionamento do módulo').'.<br /><br /></td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('E-mail').':</td>
                                    <td><input type="text" name="pagseguroEmail" id="pagseguroEmail" value="'.Configuration::get('PAGSEGURO_EMAIL').'" style="width:300px;" maxlength="60" /></td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Token').':</td>
                                    <td>
                                        <input type="text" name="pagseguroToken" id="pagseguroToken" value="'.Configuration::get('PAGSEGURO_TOKEN').'" style="width:300px;" maxlength="32" />
                                        <a href="https://pagseguro.uol.com.br/integracao/token-de-seguranca.jhtml" class="button" target="_blank">Gerar Token</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Url de redirecionamento').':</td>
                                    <td>
                                        <input type="text" name="pagseguroUrlRedirect" id="pagseguroUrlRedirect" value="'.Configuration::get('PAGSEGURO_URL_REDIRECT').'" style="width:300px;" maxlength="255" />
                                    </td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Charset').':</td>
                                    <td>'.$this->_generateSelectTag('pagseguroCharset', $this->_charsetOptions, array_search(Configuration::get('PAGSEGURO_CHARSET'), $this->_charsetOptions), 'class="select"').'</td>
                                </tr>
                                <tr>
                                    <td width="60" style="height: 35px;">'.$this->l('Log').':</td>
                                    <td>'.$this->_generateSelectTag('pagseguroLog', $this->_activeLog, Configuration::get('PAGSEGURO_LOG_ACTIVE'), 'class="select"').'</td>
                                </tr>
                                <tr style="display:none;" id="logDir">
                                    <td width="60" style="height: 35px;">'.$this->l('Diretório').':</td>
                                    <td><input type="text" id="pagseguroLogDir" name="pagseguroLogDir" value="'.Configuration::get('PAGSEGURO_LOG_FILELOCATION').'" style="width:300px;"/></td>
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
                'id_order' => $params['objOrder']->id,
                'pagseguro_return_url' => $this->context->cookie->__get('pagseguroResponseUrl')
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
        $currencies_module = $this->getCurrency((int) $cart->id_currency);

        if (is_array($currencies_module))
            foreach ($currencies_module as $currency_module)
                if ($currency_order->id == $currency_module['id_currency'])
                    return true;
        return false;
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
            $selectedAttr = ($selected == $key) ? 'selected="selected" ' : '';
            $select .= '<option value="'.$key.'" '.$selectedAttr.'>'.$value.'</option>';
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
        if ($f = @fopen(_PS_ROOT_DIR_.$file, "a"))
            fclose($f);
    }
    
    /**
     * Create PagSeguro order status into database
     * 
     * @return bool $ordersAdded
     */
    private function _generatePagSeguroOrderStatus(){
        
        // including PagSeguroOrderStatusTranslation.php file to generate OrderStatus names
        include_once 'PagSeguroOrderStatusTranslation.php';
        
        $ordersAdded = true;
        $initialState = 0;
        
        foreach (array_keys(PagSeguroTransactionStatus::getStatusList()) as $status) {
            
            $orderState = new OrderState();
            $orderState->module_name = $this->name;
            $orderState->send_email = false;
            $orderState->color = '#DDEEFF';
            $orderState->hidden = false;
            $orderState->delivery = false;
            $orderState->logable = true;
            $orderState->invoice = true;
            $orderState->name = array();
            
            foreach (Language::getLanguages() as $language)
                $orderState->name[$language['id_lang']] = PagSeguroOrderStatusTranslation::getStatusTranslation($status, strtolower($language['iso_code']));
            
            $ordersAdded &= $orderState->add();
            
            // getting initial state id to update PS_OS_PAGSEGURO config
            if ($status == 'WAITING_PAYMENT')
                $initialState = (int)$orderState->id;
        }
        
        if ($ordersAdded)
            Configuration::updateValue('PS_OS_PAGSEGURO', $initialState);
        
        return $ordersAdded;
    }
    
}

?>
