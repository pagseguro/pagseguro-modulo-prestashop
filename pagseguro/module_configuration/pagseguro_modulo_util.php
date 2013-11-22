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

class PagSeguroModuloUtil
{
    
    private static $charset_options = array(
        '1' => 'ISO-8859-1',
        '2' => 'UTF-8'
    );
    
    private static $active_log = array(
        '0' => 'NÃO',
        '1' => 'SIM'
    );
    
    private static $order_status = array(
        'INITIATED' => 'Iniciado',
        'WAITING_PAYMENT' => 'Aguardando pagamento',
        'IN_ANALYSIS' => 'Em análise',
        'PAID' => 'Paga',
        'AVAILABLE' => 'Disponível',
        'IN_DISPUTE' => 'Em disputa',
        'REFUNDED' => 'Devolvida',
        'CANCELLED' => 'Cancelada'
    );
    
    private static $order_status_pagseguro = array(
        'INITIATED' => array('name' => 'Iniciado',
            'send_email' => false,
            'template' => '',
            'hidden' => true,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false),
        'WAITING_PAYMENT' => array('name' => 'Aguardando pagamento',
            'send_email' => true,
            'template' => 'awaiting_payment',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false),
        'IN_ANALYSIS' => array('name' => 'Em análise',
            'send_email' => true,
            'template' => 'in_analysis',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false),
        'PAID' => array('name' => 'Paga',
            'send_email' => true,
            'template' => 'payment',
            'hidden' => true,
            'delivery' => false,
            'logable' => true,
            'invoice' => true,
            'unremovable' => false,
            'shipped' => false,
            'paid' => true),
        'AVAILABLE' => array('name' => 'Disponível',
            'send_email' => false,
            'template' => '',
            'hidden' => true,
            'delivery' => false,
            'logable' => true,
            'invoice' => true,
            'unremovable' => false,
            'shipped' => false,
            'paid' => true),
        'IN_DISPUTE' => array('name' => 'Em disputa',
            'send_email' => false,
            'template' => '',
            'hidden' => true,
            'delivery' => false,
            'logable' => true,
            'invoice' => true,
            'unremovable' => false,
            'shipped' => false,
            'paid' => true),
        'REFUNDED' => array('name' => 'Devolvida',
            'send_email' => true,
            'template' => 'refund',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false),
        'CANCELLED' => array('name' => 'Cancelada',
            'send_email' => true,
            'template' => 'order_canceled',
            'hidden' => false,
            'delivery' => false,
            'logable' => false,
            'invoice' => false,
            'unremovable' => false,
            'shipped' => false,
            'paid' => false),
    );
    
    private static $update_config_versio_14 = array(
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
    
    public static function getCharsetOptions(){
        return self::$charset_options;
    }
    
    public static function getActiveLog(){
        return self::$active_log;
    }
    
    public static function getOrderStatus(){
        return self::$order_status;
    }
    
    public static function getCustomOrderStatusPagSeguro(){
        return self::$order_status_pagseguro;
    }
    
    public  static function getUpdateConfigVersion14(){
        return self::$update_config_versio_14;
    }
}
