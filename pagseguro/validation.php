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

include_once(dirname(__FILE__) . '/../../config/config.inc.php');
include_once(dirname(__FILE__) . '/../../header.php');
include_once(dirname(__FILE__) . '/pagseguro.php');
include_once(dirname(__FILE__) . '/backward_compatibility/backward.php');

$pag_seguro = new PagSeguro();
$pag_seguro->setModulo(new PagSeguroModulo14());

$cart = $pag_seguro->getModulo()->context->cart;
$payment_request = new PagSeguroPaymentRequest();

$customer = new Customer((int) $cart->id_customer);

postProcess();

include_once (dirname(__FILE__) . '/../../footer.php');

/**
 * Post data process function
 */
function postProcess()
{
    try {
        verifyPaymentOptionAvailability();
        validateCart();
        generatePagSeguroRequestData();
        $additional_infos = validateOrder();
        setAdditionalRequestData($additional_infos);
        setNotificationUrl();
        performPagSeguroRequest();
    } catch (PagSeguroServiceException $exc) {
        canceledOrderForErro();
        redirectToErroPage();
    } catch (Exception $e) {
        redirectToErroPage();
    }
}

/**
 * Set additional infos to PagSeguroPaymentRequest object
 * 
 * @param array $additional_infos
 */
function setAdditionalRequestData(Array $additional_infos)
{
    global $payment_request;
    /* Setting reference */
    $payment_request->setReference($additional_infos['id_order']);
    $payment_request->setRedirectURL(
        generateRedirectUrl(
            $additional_infos,
            $payment_request->getRedirectURL()
        )
    );
}

/**
 * set notification url
 */
function setNotificationUrl()
{
    global $payment_request, $pag_seguro;
    $payment_request->setNotificationURL($pag_seguro->getNotificationUrl());
}

/**
 * Verify if PagSeguro payment module still available
 */
function verifyPaymentOptionAvailability()
{
    global $pag_seguro;
    
    $authorized = false;
    foreach (Module::getPaymentModules() as $module) {
        if ($module['name'] == 'pagseguro') {
            $authorized = true;
            break;
        }
    }
    if (! $authorized) {
        die($pag_seguro->l('Este método de pagamento não está disponível', 'validation'));
    }
}

/**
 * Validate Cart
 */
function validateCart()
{
    global $pag_seguro, $cart;
    
    if ($cart->id_customer == 0
    or $cart->id_address_delivery == 0
    or $cart->id_address_invoice == 0
    or ! $pag_seguro->active) {
        Tools::redirect('order.php?step=1');
    }
}

/**
 * Validate order
 */
function validateOrder()
{
    global $pag_seguro, $cart, $customer;
    
    if (! Validate::isLoadedObject($customer)) {
        Tools::redirect('order.php?step=1');
    }
    
    $pag_seguro->validateOrder(
        (int) $cart->id,
        Configuration::get('PS_OS_PAGSEGURO'),
        (float) $cart->getOrderTotal(true, Cart::BOTH),
        $pag_seguro->displayName,
        null,
        null,
        (int) $cart->id_currency,
        false,
        $customer->secure_key
    );
    
    return array(
        'id_cart' => (int) $cart->id,
        'id_module' => (int) $pag_seguro->id,
        'id_order' => $pag_seguro->currentOrder,
        'key' => $customer->secure_key
    );
}

/**
 * After system and PagSeguro validations and notification about order,
 * client will be redirected to order confirmation view with a button that
 * allows client to access PagSeguro and perform him order payment
 *
 * @param array $arrayData            
 */
function generateRedirectUrl(Array $arrayData, $url)
{
    global $pag_seguro;
    
    if (Tools::isEmpty($url)) {
        $url = $pag_seguro->getDefaultRedirectionUrl();
    }
    
    return $url . 'order-confirmation.php?id_cart=' . $arrayData['id_cart'] . '&id_module=' .
    $arrayData['id_module'] . '&id_order=' . $arrayData['id_order'] . '&key=' . $arrayData['key'];
}

/**
 * Perform PagSeguro request and return url from PagSeguro
 * if ok, $pag_seguro->pagSeguroReturnUrl is created with url returned from Pagseguro
 */
function performPagSeguroRequest()
{
    global $payment_request;
    
    try {
        /* Retrieving PagSeguro configurations */
        retrievePagSeguroConfiguration();
        
        /* Set PagSeguro Prestashop module version */
        setPagSeguroModuleVersion();
        
        /* Set PagSeguro PrestaShop CMS version */
        setPagSeguroCMSVersion();
        
        /* Performing request */
        $credentials = new PagSeguroAccountCredentials(
            Configuration::get('PAGSEGURO_EMAIL'),
            Configuration::get('PAGSEGURO_TOKEN')
        );
        
        $url = $payment_request->register($credentials);
        
        /* Redirecting to PagSeguro */
        if (Validate::isUrl($url)) {
            Tools::redirectLink(Tools::truncate($url, 255, ''));
        }
    } catch (PagSeguroServiceException $e) {
        throw $e;
    } catch (Exception $e) {
        throw $e;
    }
}

/**
 * Retrieve PagSeguro data configuration from database
 */
function retrievePagSeguroConfiguration()
{
    /* Retrieving configurated default charset */
    PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));
    
    /* Retrieving configurated default log info */
    if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
        PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
    }
}

/**
 * Set PagSeguro PrestaShop module version
 */
function setPagSeguroModuleVersion()
{
    global $pag_seguro;
    PagSeguroLibrary::setModuleVersion('prestashop' . ':' . $pag_seguro->version);
}

/**
 * Set PagSeguro CMS version
 */
function setPagSeguroCMSVersion()
{
    PagSeguroLibrary::setCMSVersion('prestashop' . ':' . _PS_VERSION_);
}

/**
 * Generates PagSeguro request data
 */
function generatePagSeguroRequestData()
{
    global $payment_request;
    
    /* Currency */
    $payment_request->setCurrency(PagSeguroCurrencies::getIsoCodeByName('REAL'));
    
    /* Extra amount */
    $payment_request->setExtraAmount(getExtraAmountValues());
    
    /* Products */
    $payment_request->setItems(generateProductsData());
    
    /* Sender */
    $payment_request->setSender(generateSenderData());
    
    /* Shipping */
    $payment_request->setShipping(generateShippingData());
    
    /* Redirect URL */
    if (! Tools::isEmpty(Configuration::get('PAGSEGURO_URL_REDIRECT'))) {
        $payment_request->setRedirectURL(Configuration::get('PAGSEGURO_URL_REDIRECT'));
    }
}

/**
 * Gets extra amount values for order
 * 
 * @return float
 */
function getExtraAmountValues()
{
    return Tools::convertPrice(getCartDiscounts() + getWrappingValues());
}

/**
 * Gets cart discounts values
 * 
 * @return float
 */
function getCartDiscounts()
{
    global $cart;
    
    $discounts_values = (float) 0;
    
    $cart_discounts = $cart->getDiscounts();
    
    if (count($cart_discounts) > 0) {
        foreach ($cart_discounts as $discount) {
            $discounts_values += $discount['value_real'];
        }
    }
    
    return number_format(Tools::ps_round($discounts_values, 2), 2, '.', '') * - 1;
}

/**
 * Gets wrapping values for order
 * 
 * @return float
 */
function getWrappingValues()
{
    global $cart;
    $value = $cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
    return number_format(Tools::ps_round($value, 2), 2, '.', '');
}

/**
 * Generates products data to PagSeguro transaction
 *
 * @return Array PagSeguroItem
 */
function generateProductsData()
{
    global $cart;
    
    $pagseguro_items = array();
    
    $cont = 1;
    
    $id_currency = PagSeguro::returnIdCurrency();
    
    foreach ($cart->getProducts() as $product) {
        
        $pagSeguro_item = new PagSeguroItem();
        $pagSeguro_item->setId($cont ++);
        $pagSeguro_item->setDescription(Tools::truncate($product['name'], 255));
        $pagSeguro_item->setQuantity($product['quantity']);
        
        if ($cart->id_currency != $id_currency && ! is_null($id_currency)) {
            
            $pagSeguro_item->setAmount(
                convertPriceFull(
                    $product['price_wt'],
                    new Currency($cart->id_currency),
                    new Currency($id_currency)
                )
            );
            
        } else {
            $pagSeguro_item->setAmount($product['price_wt']);
        }
        
        /* Defines weight in grams */
        $pagSeguro_item->setWeight($product['weight'] * 1000);
        
        if ($product['additional_shipping_cost'] > 0) {
            $pagSeguro_item->setShippingCost($product['additional_shipping_cost']);
        }
        array_push($pagseguro_items, $pagSeguro_item);
    }
    
    return $pagseguro_items;
}

/**
 * Generates sender data to PagSeguro transaction
 *
 * @return PagSeguroSender
 */
function generateSenderData()
{
    global $customer;
    $sender = new PagSeguroSender();
    
    if (isset($customer) && ! is_null($customer)) {
        
        $sender->setEmail($customer->email);
        
        $firstName = generateName($customer->firstname);
        $lastName = generateName($customer->lastname);
        
        $name = $firstName . ' ' . $lastName;
        
        $sender->setName(Tools::truncate($name, 50));
    }
    
    return $sender;
}

/**
 * Generate name
 * 
 * @param type $value
 * @return string
 */
function generateName($value)
{
    $name = '';
    $cont = 0;
    $customer = explode(' ', $value);
    foreach ($customer as $first) {
        
        if (! Tools::isEmpty($first)) {
            
            if ($cont == 0) {
                $name .= ($first);
                $cont ++;
            } else {
                $name .= ' ' . ($first);
            }
        }
    }
    return $name;
}

/**
 * Generates shipping data to PagSeguro transaction
 *
 * @return PagSeguroShipping
 */
function generateShippingData()
{
    global $cart;
    
    $cost = 00.00;
    $id_currency = PagSeguro::returnIdCurrency();
    
    $shipping = new PagSeguroShipping();
    $shipping->setAddress(generateShippingAddressData());
    $shipping->setType(generateShippingType());
    
    if ($cart->id_currency != $id_currency && ! is_null($id_currency)) {
        
        $totalOrder = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        $current_currency = new Currency($cart->id_currency);
        $new_currency = new Currency($id_currency);
        
        $cost = convertPriceFull($totalOrder, $current_currency, $new_currency);
    } else {
        $cost = $cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
    }
    
    $shipping->setCost(number_format(Tools::ps_round($cost, 2), 2, '.', ''));
    return $shipping;
}

/**
 * Generate shipping type data to PagSeguro transaction
 *
 * @return PagSeguroShippingType
 */
function generateShippingType()
{
    $shipping_type = new PagSeguroShippingType();
    $shipping_type->setByType('NOT_SPECIFIED');
    
    return $shipping_type;
}

/**
 * Generates shipping address data to PagSeguro transaction
 *
 * @return PagSeguroAddress
 */
function generateShippingAddressData()
{
    global $cart;
    
    $address = new PagSeguroAddress();
    $delivery_address = new Address((int) $cart->id_address_delivery);
    
    if (! is_null($delivery_address)) {
        
        $fullAddress = addressConfig($delivery_address->address1);
        
        $street = (is_null($fullAddress[0]) || empty($fullAddress[0])) ?
        $delivery_address->address1 : $fullAddress[0];
        
        $number = is_null($fullAddress[1]) ? '' : $fullAddress[1];
        $complement = is_null($fullAddress[2]) ? '' : $fullAddress[2];
        
        $address->setCity($delivery_address->city);
        $address->setPostalCode($delivery_address->postcode);
        $address->setStreet($street);
        $address->setComplement($complement);
        $address->setNumber($number);
        $address->setDistrict($delivery_address->address2);
        $address->setCity($delivery_address->city);
        
        $country = new Country((int) $delivery_address->id_country);
        $address->setCountry($country->iso_code);
        
        $state = new State((int) $delivery_address->id_state);
        $address->setState($state->iso_code);
    }
    
    return $address;
}

function redirectToErroPage()
{
    global $smarty, $pag_seguro;
    
    $pag_seguro->display_column_left = false;
    
    $smarty->assign('erro_image', __PS_BASE_URI__ . 'modules/pagseguro/assets/images/logops_86x49.png');
    $smarty->assign('version', _PS_VERSION_);
    
    echo $pag_seguro->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/front/error.tpl');
}

function addressConfig($fullAddress)
{
    require_once (dirname(__FILE__) . '/controllers/front/addressConfig.php');
    return AddressConfig::trataEndereco($fullAddress);
}

/**
 *
 *
 * Convert amount from a currency to an other currency automatically
 *
 * @param float $amount            
 * @param Currency $currency_from
 *            if null we used the default currency
 * @param Currency $currency_to
 *            if null we used the default currency
 */
function convertPriceFull($amount, Currency $currency_from = null, Currency $currency_to = null)
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

function canceledOrderForErro()
{
    global $pag_seguro;
    
    $history = new OrderHistory();
    $history->id_order = (int) ($pag_seguro->currentOrder);
    $history->changeIdOrderState(6, (int) ($pag_seguro->currentOrder));
    $history->save();
}
