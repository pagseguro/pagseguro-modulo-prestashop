<?php

/*
 * 2007-2013 PrestaShop
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once ('PagSeguroLibrary/PagSeguroLibrary.php');
include_once ('module_configuration/pagseguro_controller_1.4.php');
include_once ('module_configuration/pagseguro_controller_1.5.php');
include_once ('module_configuration/pagseguro_controller.php');

if (! defined('_PS_VERSION_'))
    exit();

class PagSeguro extends PaymentModule
{

    private $module_config;

    protected $errors = array();

    private $_html;

    public $context;

    function __construct()
    {
        $this->name = 'pagseguro';
        $this->tab = 'payments_gateways';
        $this->version = '1.0';
        $this->author = 'PagSeguro Internet LTDA.';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        
        parent::__construct();
        
        $this->displayName = $this->l('PagSeguro');
        $this->description = $this->l('Receba pagamentos por cartõo de crédito, transferência bancária e boleto.');
        $this->confirmUninstall = $this->l('Tem certeza que deseja remover este módulo ?');
        
        if (_PS_VERSION_ < '1.5')
            include_once ('backward_compatibility/Context.php');
        
        $this->module_config = PagSeguroController::instaceVersionPreConfig(_PS_VERSION_);
        $this->module_config->setPaymetnModule($this);
    }

    /**
     * Perform instalation of PagSeguro module
     *
     * @return boolean
     */
    public function install()
    {
        if (! $this->module_config->doInstall()) {
            return false;
        }
        
        if (! parent::install() || ! $this->registerHook('payment') || ! $this->registerHook('paymentReturn') ||
             ! Configuration::updateValue('PAGSEGURO_EMAIL', '') || ! Configuration::updateValue('PAGSEGURO_TOKEN', '') ||
             ! Configuration::updateValue('PAGSEGURO_URL_REDIRECT', '') ||
             ! Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', '') || ! Configuration::updateValue(
                'PAGSEGURO_CHARSET', PagSeguroConfig::getData('application', 'charset')) || ! Configuration::updateValue(
                'PAGSEGURO_LOG_ACTIVE', PagSeguroConfig::getData('log', 'active')) || ! Configuration::updateValue(
                'PAGSEGURO_LOG_FILELOCATION', PagSeguroConfig::getData('log', 'fileLocation')) ||
             ! Configuration::updateValue('PS_OS_PAGSEGURO', 13)) {
            return false;
        }
        
        return true;
    }

    /**
     * Perform uninstalation of PagSeguro module
     *
     * @return boolean
     */
    public function uninstall()
    {
        if (! $this->module_config->doUnistall())
            return false;
        
        if (! Configuration::deleteByName('PAGSEGURO_EMAIL') || ! Configuration::deleteByName('PAGSEGURO_TOKEN') ||
             ! Configuration::deleteByName('PAGSEGURO_URL_REDIRECT') ||
             ! Configuration::deleteByName('PAGSEGURO_NOTIFICATION_URL') ||
             ! Configuration::deleteByName('PAGSEGURO_CHARSET') || ! Configuration::deleteByName('PAGSEGURO_LOG_ACTIVE') ||
             ! Configuration::deleteByName('PAGSEGURO_LOG_FILELOCATION') ||
             ! Configuration::deleteByName('PS_OS_PAGSEGURO') || ! parent::uninstall())
            return false;
        
        return true;
    }

    /**
     * Gets configuration view content
     *
     * @return string
     */
    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            
            if (! count($this->errors))
                $this->_postProcess();
            else
                foreach ($this->errors as $error)
                    $this->_html .= '<div class="alert error">' . $error . '</div>';
        }
        
        $currency = $this->returnIdCurrency();
        /* Currency validation */
        if (! $currency)
            $this->_html .= '<div class="alert warn">' . $this->_missedCurrencyMessage() . '</div>';
        
        $this->_html .= $this->module_config->displayForm();
        return $this->_html;
    }

    /**
     * Realize post validations according with PagSeguro standards
     * case any inconsistence, an item is added to $_postErrors
     */
    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $email = Tools::getValue('pagseguro_email');
            $token = Tools::getValue('pagseguro_token');
            $pagseguro_url_redirect = Tools::getValue('pagseguro_url_redirect');
            $pagseguro_notification_url = Tools::getValue('pagseguro_notification_url');
            $charset = Tools::getValue('pagseguro_charset');
            $pagseguro_log = Tools::getValue('pagseguro_log');
            
            /* E-mail validation */
            if (! $email)
                $this->errors[] = $this->_errorMessage('E-MAIL');
            elseif (strlen($email) > 60)
                $this->errors[] = $this->_invalidFieldSizeMessage('E-MAIL');
            elseif (! Validate::isEmail($email))
                $this->errors[] = $this->_invalidMailMessage('E-MAIL');
                
                /* Token validation */
            if (! $token)
                $this->errors[] = $this->_errorMessage('TOKEN');
            elseif (strlen($token) != 32)
                $this->errors[] = $this->_invalidFieldSizeMessage('TOKEN');
                
                /* URL redirect validation */
            if ($pagseguro_url_redirect && ! filter_var($pagseguro_url_redirect, FILTER_VALIDATE_URL))
                $this->errors[] = $this->_invalidUrl('URL DE REDIRECIONAMENTO');
                
                /* Notification url validation */
            if ($pagseguro_notification_url && ! filter_var($pagseguro_notification_url, FILTER_VALIDATE_URL))
                $this->errors[] = $this->_invalidUrl('URL DE NOTIFICAÃ‡ÃƒO');
                
                /* Charset validation */
            if (! array_key_exists($charset, $this->module_config->_charset_options))
                $this->errors[] = $this->_invalidValue('CHARSET');
                
                /* Log validation */
            if (! array_key_exists($pagseguro_log, $this->module_config->_active_log))
                $this->errors[] = $this->_invalidValue('LOG');
        }
    }

    /**
     * Realize PagSeguro database keys values
     */
    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            
            Configuration::updateValue('PAGSEGURO_EMAIL', Tools::getValue('pagseguro_email'));
            Configuration::updateValue('PAGSEGURO_TOKEN', Tools::getValue('pagseguro_token'));
            Configuration::updateValue('PAGSEGURO_URL_REDIRECT', Tools::getValue('pagseguro_url_redirect'));
            Configuration::updateValue('PAGSEGURO_NOTIFICATION_URL', Tools::getValue('pagseguro_notification_url'));
            Configuration::updateValue('PAGSEGURO_CHARSET', 
                $this->module_config->_charset_options[Tools::getValue('pagseguro_charset')]);
            Configuration::updateValue('PAGSEGURO_LOG_ACTIVE', Tools::getValue('pagseguro_log'));
            Configuration::updateValue('PAGSEGURO_LOG_FILELOCATION', Tools::getValue('pagseguro_log_dir'));
            
            /* Verify if log file exists, case not try create */
            if (Tools::getValue('pagseguro_log'))
                $this->_verifyLogFile(Tools::getValue('pagseguro_log_dir'));
        }
        $this->_html .= '<div class="conf confirm">' . $this->l('Dados atualizados com sucesso') . '</div>';
    }

    /**
     * Create error messages
     *
     * @param String $field            
     * @return String
     */
    private function _errorMessage($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> deve ser informado.'), $field);
    }

    /**
     * Create error currency messages
     *
     * @return String
     */
    private function _missedCurrencyMessage()
    {
        return sprintf(
            $this->l(
                'Verifique se a moeda <strong>REAL</strong> esta instalada e ativada.
            Para importar a moeda vá em Localização e importe "Brazil" no Pacote de Localização, 
            após isso, vá em localização, moedas, e habilite o <strong>REAL</strong>.<br>
            Lembre-se, o pagseguro só aceita REAL, se essa moeda não estiver habilitada, 
            não garatimos que o valor dos produtos será pago corretamente.'));
    }

    /**
     * Create invalid mail messages
     *
     * @param String $field            
     * @return String
     */
    private function _invalidMailMessage($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> deve ser conter um email válido.'), $field);
    }

    /**
     * Create invalid field size messages
     *
     * @param String $field            
     * @return String
     */
    private function _invalidFieldSizeMessage($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> está com um tamanho inválido'), $field);
    }

    /**
     * Create invalid value messages
     *
     * @param String $field            
     * @return String
     */
    private function _invalidValue($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> contém um valor inválido.'), $field);
    }

    /**
     * Create invalid url messages
     *
     * @param String $field            
     * @return String
     */
    private function _invalidUrl($field)
    {
        return sprintf($this->l('O campo <strong>%s</strong> deve conter uma url válida.'), $field);
    }

    /**
     * Return Id currency (Standard value is BRL)
     *
     * @param type $value            
     * @return type
     */
    public static function returnIdCurrency($value = 'BRL')
    {
        $id_currency = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            '
		SELECT `id_currency`
		FROM `' . _DB_PREFIX_ . 'currency`
		WHERE `deleted` = 0 
                AND `iso_code` = "' . $value . '"'));
        
        return $id_currency[0]['id_currency'];
    }

    /**
     * Perform Payment hook
     *
     * @param array $params            
     * @return string
     */
    public function hookPayment($params)
    {
        return $this->module_config->configPayment($params);
    }

    /**
     * Perform Payment Return hook
     *
     * @param array $params            
     * @return string
     */
    public function hookPaymentReturn($params)
    {
        return $this->module_config->configPayment($params);
    }

    /**
     * Verify if PagSeguro log file exists.
     * Case log file not exists, try create
     * else create PagSeguro.log into PagseguroLibrary folder into module
     */
    private function _verifyLogFile($file)
    {
        try {
            $f = @fopen(_PS_ROOT_DIR_ . $file, 'a');
            fclose($f);
        } catch (Exception $e) {
            die($e->getMessage());
        }
    }
}
