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

include_once ('../PagSeguroLibrary/PagSeguroLibrary.php');
include_once ('pagseguro_controller.php');
include_once ('../backward_compatibility/Context.php');

class PagSeguroController14 extends PagSeguroController
{

    public function configPayment($params)
    {
        global $smarty;

        if (! $this->payment_module->active) {
            return;
        }

        $smarty->assign(
            array(
                'version_module' => _PS_VERSION_,
                'action_url' => _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/pagseguro/controllers/front/payment.php',
                'image' => __PS_BASE_URI__ . 'modules/pagseguro/assets/images/logops_86x49.png',
                'this_path' => __PS_BASE_URI__ . 'modules/pagseguro/',
                'this_path_ssl' => Tools::getShopDomainSsl(true, true) . v .
                    'modules/' . $this->payment_module->name . '/')
        );

        return $this->payment_module->display(__PS_BASE_URI__ . 'modules/pagseguro',
            '/views/templates/hook/payment.tpl');
    }

    public function configReturnPayment($params)
    {
        global $smarty;
        
        if (! $this->payment_module->active) {
            return;
        }

        if (! Tools::isEmpty($params['objOrder']) && $params['objOrder']->module === $this->payment_module->name) {
            
            $smarty->assign(
                array(
                    'total_to_pay' => Tools::displayPrice($params['objOrder']->total_paid_real,
                        $this->context->currency->id, false),
                    'status' => 'ok',
                    'id_order' => (int) $params['objOrder']->id
            ));
            if (isset($params['objOrder']->reference) && ! empty($params['objOrder']->reference)) {
                $smarty->assign('reference', $params['objOrder']->reference);
            }
        } else {
            $smarty->assign('status', 'failed');
        }
        return $this->payment_module->display(
            __PS_BASE_URI__ . 'modules/pagseguro',
            '/views/templates/hook/payment_return.tpl');
    }

    public function doInstall()
    {
        if (! PagSeguroLibrary::getVersion() < '2.1.8') {
            if (! $this->validatePagSeguroRequirements()) {
                return false;
            }
        }
        if (! $this->generatePagSeguroOrderStatus()) {
            return false;
        }

            /* For 1.4.3 and less compatibility */
        $updateConfig = array(
            'PS_OS_CHEQUE' => 1,
            'PS_OS_PAYMENT' => 2,
            'PS_OS_PREPARATION' => 3,
            'PS_OS_SHIPPING' => 4,
            'PS_OS_DELIVERED' => 5,
            'PS_OS_CANCELED' => 6,
            'PS_OS_REFUND' => 7,
            'PS_OS_ERROR' => 8,
            'PS_OS_OUTOFSTOCK' => 9,
            'PS_OS_BANKWIRE' => 10,
            'PS_OS_PAYPAL' => 11,
            'PS_OS_WS_PAYMENT' => 12
        );
        foreach ($updateConfig as $u => $v) {
            if (! Configuration::get($u) || (int) Configuration::get($u) < 1) {
                if (defined('_' . $u . '_') && (int) constant('_' . $u . '_') > 0) {
                    Configuration::updateValue($u, constant('_' . $u . '_'));
                } else {
                    Configuration::updateValue($u, $v);
                }
            }
        }
        Configuration::updateValue('PS_OS_PAGSEGURO',13);
        return $this->createTables();
    }

    public function doUnistall()
    {
        return true;
    }

    public function setPaymetnModule($module)
    {
        $this->payment_module = $module;
        $this->payment_module->context = Context::getContext();
    }
}
