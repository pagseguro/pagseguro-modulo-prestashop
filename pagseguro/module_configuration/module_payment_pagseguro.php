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
include_once(dirname(__FILE__).'/../pagseguro.php');
include_once(dirname(__FILE__).'/../backward_compatibility/backward.php');

class ModulePaymentPagSeguro
{

    public function setVariablesPaymentExecutionView($context)
    {
        global $smarty;
        
        $id_currency = PagSeguro::returnIdCurrency();
        if ($context->cart->id_currency != $id_currency && ! is_null($id_currency)) {
            
            $totalOrder = $context->cart->getOrderTotal(true, Cart::BOTH);
            $current_currency = new Currency($context->cart->id_currency);
            $new_currency = new Currency($id_currency);
            $smarty->assign(
                array(
                    'total_real' => $this->convertPriceFull($totalOrder, $current_currency, $new_currency),
                    'currency_real' => $id_currency
                ));
        }
        
        $older_url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/pagseguro/validation.php"';
        $new_url = _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?fc=module&module=pagseguro&controller=validation';
        
        $smarty->assign(
            array(
                'version' => _PS_VERSION_,
                'image_payment' => __PS_BASE_URI__ . 'modules/pagseguro/assets/images/logops_86x49.png',
                'nbProducts' => $context->cart->nbProducts(),
                'current_currency_id' => $context->currency->id,
                'current_currency_name' => $context->currency->name,
                'cust_currency' => $context->cart->id_currency,
                'total' => $context->cart->getOrderTotal(true, Cart::BOTH),
                'isocode' => $context->language->iso_code,
                'this_path' => __PS_BASE_URI__,
                'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/pagseguro/',
                'action_url' => version_compare(_PS_VERSION_, '1.5.0.3', '<=') ? $older_url : $new_url
            ));
    }

    private function convertPriceFull($amount, Currency $currency_from = null, Currency $currency_to = null)
    {
        if ($currency_from === $currency_to) {
            return $amount;
        }
        if ($currency_from === null) {
            $currency_from = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        if ($currency_to === null) {
            $currency_to = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        if ($currency_from->id == Configuration::get('PS_CURRENCY_DEFAULT')) {
            $amount *= $currency_to->conversion_rate;
        } else {
            $conversion_rate = ($currency_from->conversion_rate == 0 ? 1 : $currency_from->conversion_rate);
            
            // Convert amount to default currency (using the old currency rate)
            $amount = Tools::ps_round($amount / $conversion_rate, 2);
            
            // Convert to new currency
            $amount *= $currency_to->conversion_rate;
        }
        return Tools::ps_round($amount, 2);
    }
}
