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

include_once(dirname(__FILE__).'/../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../init.php');
include_once(dirname(__FILE__) . '/../PagSeguroLibrary/PagSeguroLibrary.php');
include_once(dirname(__FILE__) . '/../module_configuration/module_payment_pagseguro.php');
    
if (!defined('_PS_VERSION_'))
    exit;

class PagSeguroModulo15 extends PaymentModule {
    
    public $context;
    
    function __construct() {
        parent::__construct();
    }

    /**
     * Perform instalation of PagSeguro module
     * 
     * @return boolean
     */
    public function install() {
        return true;
    }

    /**
     * Perform uninstalation of PagSeguro module
     * 
     * @return boolean
     */
    public function uninstall() {
        return true;
    }
    
    public function hookPayment($params) {
        
        global $smarty;
        
        $link = new Link();
        
        $smarty->assign(
            array(
                'version_module' => _PS_VERSION_,
                'action_url' => $link->getModuleLink('pagseguro', 'payment', array(), true),
                'index.php?fc=module&module=pagseguro&controller=payment',
                'image' => __PS_BASE_URI__ . 'modules/pagseguro/assets/images/logops_86x49.png',
                'this_path' => __PS_BASE_URI__ . 'modules/pagseguro/',
                'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/pagseguro/')
        );
        
        $pag_mater = new PagSeguro();
        
        return $pag_mater->display('modules/pagseguro', '/views/templates/hook/payment.tpl');//__PS_BASE_URI__ . 'modules/pagseguro/views/templates/hook/payment.tpl';
    }
    
    /**
     *  Perform Payment Return hook
     * 
     * @param array $params
     * @return string
     */
    public function hookPaymentReturn($params) {
        
        global $smarty;
        
        if (! Tools::isEmpty($params['objOrder']) && $params['objOrder']->module === 'pagseguro') {
        
            $smarty->assign(
                array(
                    'total_to_pay' => Tools::displayPrice(
                        $params['objOrder']->total_paid,
                        $this->context->currency->id,
                        false
                    ),
                    'status' => 'ok',
                    'id_order' => (int) $params['objOrder']->id
                )
            );
            if (isset($params['objOrder']->reference) && ! empty($params['objOrder']->reference)) {
                $smarty->assign('reference', $params['objOrder']->reference);
            }
        } else {
            $smarty->assign('status', 'failed');
        }
        
        $pag_mater = new PagSeguro();
        return $pag_mater->display('modules/pagseguro','/views/templates/hook/payment_return.tpl');
    }
    
    public function getNotificationUrl() {
        $url_notification = Configuration::get('PAGSEGURO_NOTIFICATION_URL');
        return empty($url_notification) ?
        $this->notificationURL() : $url_notification;
    }
    
    /**
     * Gets a default redirection url
     * @return string
     */
    public function getDefaultRedirectionUrl() {
        $url_redirect = Configuration::get('PAGSEGURO_URL_REDIRECT');
        return empty($url_redirect) ?
            $this->redirectURL() : $url_redirect;
    }
    
    /**
     * 
     * Notification Url
     * @return type
     */
    private function notificationURL() {
        return _PS_BASE_URL_ . __PS_BASE_URI__ .
        'index.php?fc=module&module=pagseguro&controller=notification';
    }
    
    /**
     * Gets a default redirection url
     * @return string
     */
    private function redirectURL() {
        return _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php';
    }
    
    public function getJsBehavior()
    {
        return __PS_BASE_URI__.'modules/pagseguro/assets/js/behaviors-version-15.js';
    }
    
    public function getCssDisplay()
    {
        return __PS_BASE_URI__.'modules/pagseguro/assets/css/styles-version-15.css';
    }
}
