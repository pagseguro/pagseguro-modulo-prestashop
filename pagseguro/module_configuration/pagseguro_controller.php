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

include_once ('../../../config/config.inc.php');
include_once ('../../../init.php');
include_once ('../backward_compatibility/Context.php');
include_once ('../PagSeguroLibrary/PagSeguroLibrary.php');
include_once ('pagseguro_controller_1.4.php');
include_once ('pagseguro_controller_1.5.php');

abstract class PagSeguroController
{

    protected $payment_module;

    public $charset_options = array(
        '1' => 'ISO-8859-1',
        '2' => 'UTF-8'
    );

    public $active_log = array(
        '0' => 'NÃO',
        '1' => 'SIM'
    );

    protected $order_status = array(
        'INITIATED' => 'Iniciado',
        'WAITING_PAYMENT' => 'Aguardando pagamento',
        'IN_ANALYSIS' => 'Em análise',
        'PAID' => 'Paga',
        'AVAILABLE' => 'Disponível',
        'IN_DISPUTE' => 'Em disputa',
        'REFUNDED' => 'Devolvida',
        'CANCELLED' => 'Cancelada'
    );

    public static function instaceVersionPreConfig($version)
    {
        return $version >= '1.5' ? new PagSeguroControllerVersion15() : new PagSeguroControllerVersion14();
    }

    private function findOrderStates()
    {
        return (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT osl.`id_lang`, osl.`name`
            FROM `'._DB_PREFIX_.'order_state` os
            INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl 
            ON (os.`id_order_state` = osl.`id_order_state`)
            WHERE osl.`name` in ("Iniciado","Aguardando pagamento","Em análise","Paga",
            "Disponível","Em disputa","Devolvida","Cancelada")'));
    }

    protected function checkActiveSlide()
    {
        return Tools::getValue('activeslide') ? Tools::getValue('activeslide') : 1;
    }

    protected function returnCheckedRedirectionUrl()
    {
        $url_redirect = Tools::safeOutput(Configuration::get('PAGSEGURO_URL_REDIRECT'));
        return Tools::isEmpty($url_redirect) ? $this->getDefaultRedirectionUrl() : $url_redirect;
    }

    protected function returnCheckedNotificationUrl()
    {
        $url_notification = Tools::safeOutput(Configuration::get('PAGSEGURO_NOTIFICATION_URL'));
        return Tools::isEmpty($url_notification) ? $this->_notificationURL() : $url_notification;
    }

    protected function generatePagSeguroOrderStatus()
    {
        $orders_added = true;
        $name_state = null;
        $list_states = $this->findOrderStates();
        
        foreach ($this->order_status as $key => $value) {
            
            $order_state = new OrderState();
            $order_state->module_name = $this->payment_module->name;
            $order_state->send_email = false;
            $order_state->color = '#95D061';
            $order_state->hidden = false;
            $order_state->delivery = false;
            $order_state->logable = true;
            $order_state->invoice = false;
            $order_state->name = array();
            $continue = false;
            
            foreach (Language::getLanguages() as $language) {
                
                $continue = $this->checkIfOrderStatusExists($language['id_lang'], $value, $list_states);
                
                if ($continue) {
                    
                    $order_state->name[$language['id_lang']] = $value;
                    
                    if ($order_state->add()) {
                        copy(_PS_ROOT_DIR_.'/modules/pagseguro/logo.gif',
                            _PS_ROOT_DIR_.'/img/os/'.(int) $order_state->id.'.gif');
                    }
                    
                }
            }
                
                /* getting initial state id to update PS_OS_PAGSEGURO config */
            if ($key == 'WAITING_PAYMENT') {
                $name_state = $value;
            }
        }
        
        Configuration::updateValue('PS_OS_PAGSEGURO', $this->returnIdOrderByStatusPagSeguro($name_state));
        
        return $orders_added;
    }

    /**
     * Check if PagSeguro order status already exists on database
     *
     * @param String $status            
     * @return boolean
     */
    private function checkIfOrderStatusExists($id_lang, $status_name, $list_states)
    {
        if (Tools::isEmpty($list_states)) {
            return true;
        }

        $save = true;
        foreach ($list_states as $state) {
            if ($state['id_lang'] == $id_lang && $state['name'] == $status_name) {
                $save = false;
            }
        }
        return $save;
    }

    /**
     * Return current translation for infomed status and language iso code
     *
     * @param string $status            
     * @param string $lang_iso_code            
     * @return string
     */
    private function getStatusTranslation($status)
    {
        return $this->order_status[$status]['br'];
    }

    private function returnIdOrderByStatusPagSeguro($value)
    {
        $sql ='SELECT distinct os.`id_order_state`
        FROM `' . _DB_PREFIX_.'order_state` os
        INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl 
        ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \''.pSQL($value).'\')
        WHERE deleted = 0';

        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        return $id_order_state[0]['id_order_state'];
    }

    protected function validatePagSeguroRequirements()
    {
        $condional = true;
        foreach (PagSeguroConfig::validateRequirements() as $value) {
            if (!Tools::isEmpty($value)) {
                $condional = false;
                $this->errors[] = Tools::displayError($value);
            }
        }
        if (!$condional) {
            $this->_html = $this->displayError(implode('<br />', $this->errors));
        }
        return $condional;
    }

    protected function createTables()
    {
        if (!Db::getInstance()->Execute(
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pagseguro_order` 
            (`id` int(11) unsigned NOT NULL auto_increment,
             `id_transaction` varchar(255) NOT NULL,
             `id_order` int(10) unsigned NOT NULL ,
             PRIMARY KEY  (`id`)) 
            ENGINE=' . _MYSQL_ENGINE_ . '  DEFAULT CHARSET=utf8  auto_increment=1;')) {
            return false;
        }
        return true;
    }

    public function displayForm()
    {
        global $smarty;
        
        $smarty->assign('module_dir', _PS_MODULE_DIR_ . $this->name . '/');
        $smarty->assign('action_post', Tools::htmlentitiesUTF8($_SERVER['REQUEST_URI']));
        $smarty->assign('email_user', Tools::safeOutput(Configuration::get('PAGSEGURO_EMAIL')));
        $smarty->assign('token_user', Tools::safeOutput(Configuration::get('PAGSEGURO_TOKEN')));
        $smarty->assign('redirect_url', $this->returnCheckedRedirectionUrl());
        $smarty->assign('notification_url', $this->returnCheckedNotificationUrl());
        $smarty->assign('charset_options', $this->charset_options);
        $smarty->assign('charset_selected', 
            array_search(Configuration::get('PAGSEGURO_CHARSET'), 
                $this->charset_options));
        $smarty->assign('active_log', $this->active_log);
        $smarty->assign('log_selected', Configuration::get('PAGSEGURO_LOG_ACTIVE'));
        $smarty->assign('diretorio_log', Tools::safeOutput(Configuration::get('PAGSEGURO_LOG_FILELOCATION')));
        $smarty->assign('checkActiveSlide', Tools::safeOutput($this->checkActiveSlide()));
        
        return $this->payment_module->display(__PS_BASE_URI__ . 'modules/pagseguro', 
            'admin_pagseguro.tpl');
    }

    /**
     * Gets notification url
     *
     * @return string
     */
    public function getNotificationUrl()
    {
        return (!PagSeguroHelper::isEmpty(Configuration::get('PAGSEGURO_NOTIFICATION_URL'))) ?
         Configuration::get('PAGSEGURO_NOTIFICATION_URL') : $this->_notificationURL();
    }

    /**
     * Notification Url
     *
     * @return type
     */
    private function _notificationURL()
    {
        $url_base = _PS_BASE_URL_ . __PS_BASE_URI__;
        return $this->validationVersion() ? $url_base.'index.php?fc=module&module=pagseguro&controller=notification' 
            : $url_base.'modules/pagseguro/controllers/front/notification.php';
    }

    /**
     * Gets a default redirection url
     *
     * @return string
     */
    public function getDefaultRedirectionUrl()
    {
        $url = _PS_BASE_URL_ . __PS_BASE_URI__;
        return $this->validationVersion() ? $url . 'index.php' : $url;
    }

    public function validationVersion()
    {
        return _PS_VERSION_ >= '1.5';
    }

    abstract public function doInstall();

    abstract public function doUnistall();

    abstract public function configPayment($params);

    abstract public function configReturnPayment($params);
}
