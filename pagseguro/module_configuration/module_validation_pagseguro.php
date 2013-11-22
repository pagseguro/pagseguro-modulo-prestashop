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
include_once(dirname(__FILE__).'/backward_compatibility/backward.php');
include_once(dirname(__FILE__).'/../module_configuration/pagseguro_controller.php');
include_once(dirname(__FILE__).'/../module_configuration/pagseguro_controller_1.4.php');
include_once(dirname(__FILE__).'/../module_configuration/pagseguro_controller_1.5.php');

class ModuleValidationPagSeguro
{

    private $payment_request;

    private $context;

    private $module;

    /**
     * Post data process function
     */
    public function postProcess(PaymentModule $module)
    {
        $this->module = new PagSeguro();
        $this->module->setModulo($module);
        
        $this->context = $this->module->getModulo()->context;
        
        $this->verifyPaymentOptionAvailability();
        echo '1';
        $this->validateCart();
        echo '2';
        $this->generatePagSeguroRequestData();
        echo '3';
        $additional_infos = $this->validateOrder();
        echo '4';
        $this->setAdditionalRequestData($additional_infos);
        echo '5';
        $this->setNotificationUrl();
        echo '6';
        $this->performPagSeguroRequest();
        echo '7';
    }

    /**
     * Set additional infos to PagSeguroPaymentRequest object
     *
     * @param array $additional_infos            
     */
    private function setAdditionalRequestData(Array $additional_infos)
    {
        
        /* Setting reference */
        $this->payment_request->setReference($additional_infos['id_order']);
        
        $redirectUrl = $this->payment_request->getRedirectURL();
        $this->payment_request->setRedirectURL($this->generateRedirectUrl($additional_infos, $redirectUrl));
    }

    /**
     * set notification url
     */
    private function setNotificationUrl()
    {
        $obj_ps = PagSeguroController::instaceVersionPreConfig(_PS_VERSION_);
        $this->payment_request->setNotificationURL($obj_ps->getNotificationUrl());
    }

    /**
     * Verify if PagSeguro payment module still available
     */
    private function verifyPaymentOptionAvailability()
    {
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'pagseguro') {
                $authorized = true;
                break;
            }
        }
        if (! $authorized) {
            die($this->module->l('Este método de pagamento não está disponível', 'validation'));
        }
    }

    /**
     * Validate Cart
     */
    private function validateCart()
    {
        if ($this->context->cart->id_customer == 0 || $this->context->cart->id_address_delivery == 0 ||
             $this->context->cart->id_address_invoice == 0 || ! $this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }

    /**
     * Validate order
     */
    private function validateOrder()
    {
        $customer = new Customer($this->context->cart->id_customer);
        
        if (! Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }
        
        $this->module->validateOrder((int) $this->context->cart->id, Configuration::get('PS_OS_PAGSEGURO'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH), $this->module->displayName, null, null,
            (int) $this->context->currency->id, false, $customer->secure_key);
        return array(
            'id_cart' => (int) $this->context->cart->id,
            'id_module' => (int) $this->module->id,
            'id_order' => $this->module->currentOrder,
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
    private function generateRedirectUrl(Array $arrayData, $url)
    {
        $obj_ps = PagSeguroController::instaceVersionPreConfig();
        
        $redirection_url_version = version_compare(_PS_VERSION_, '1.5.0.3', '<') ?
        'order-confirmation.php?id_cart=' : '?controller=order-confirmation&id_cart=';
        
        if (Tools::isEmpty($url)) {
            $url = $obj_ps->getDefaultRedirectionUrl();
        }
        
        return $url . $redirection_url_version . $arrayData['id_cart'] . '&id_module=' . $arrayData['id_module'] .
             '&id_order=' . $arrayData['id_order'] . '&key=' . $arrayData['key'];
    }

    /**
     * Perform PagSeguro request and return url from PagSeguro
     * if ok, $this->module->pagSeguroReturnUrl is created with url returned from Pagseguro
     */
    private function performPagSeguroRequest()
    {
        try {
            
            /* Retrieving PagSeguro configurations */
            $this->retrievePagSeguroConfiguration();
            
            /* Set PagSeguro Prestashop module version */
            $this->setPagSeguroModuleVersion();
            
            /* Set PagSeguro PrestaShop CMS version */
            $this->setPagSeguroCMSVersion();
            
            /* Performing request */
            $credentials = new PagSeguroAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'),
                Configuration::get('PAGSEGURO_TOKEN'));
            
            $url = $this->payment_request->register($credentials);
            
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
    private function retrievePagSeguroConfiguration()
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
    private function setPagSeguroModuleVersion()
    {
        PagSeguroLibrary::setModuleVersion('prestashop-v.' . $this->module->version);
    }

    /**
     * Set PagSeguro CMS version
     */
    private function setPagSeguroCMSVersion()
    {
        PagSeguroLibrary::setCMSVersion('prestashop-v.' . _PS_VERSION_);
    }

    /**
     * Generates PagSeguro request data
     */
    private function generatePagSeguroRequestData()
    {
        $payment_request = new PagSeguroPaymentRequest();
        
        /* Currency */
        $payment_request->setCurrency(PagSeguroCurrencies::getIsoCodeByName('REAL'));
        
        /* Extra amount */
        $payment_request->setExtraAmount($this->getExtraAmountValues());
        
        /* Products */
        $payment_request->setItems($this->generateProductsData());
        
        /* Sender */
        $payment_request->setSender($this->generateSenderData());
        
        /* Shipping */
        $payment_request->setShipping($this->generateShippingData());
        
        /* Redirect URL */
        if (! Tools::isEmpty(Configuration::get('PAGSEGURO_URL_REDIRECT'))) {
            $payment_request->setRedirectURL(Configuration::get('PAGSEGURO_URL_REDIRECT'));
        }
        
        $this->payment_request = $payment_request;
    }

    /**
     * Gets extra amount values for order
     *
     * @return float
     */
    private function getExtraAmountValues()
    {
        $discounts = version_compare(_PS_VERSION_, '1.5.0.3', '<=') ? $this->getCartDiscounts() :
        $this->getCartRulesValues();
        
        return Tools::convertPrice($discounts + $this->getWrappingValues());
    }

    private function getCartDiscounts()
    {
        $discounts_values = (float) 0;
        
        $cart_discounts = $this->context->cart->getDiscounts();
        
        if (count($cart_discounts) > 0) {
            foreach ($cart_discounts as $discount) {
                $discounts_values += $discount['value_real'];
            }
        }
        
        return number_format(Tools::ps_round($discounts_values, 2), 2, '.', '') * - 1;
    }

    /**
     * Gets cart rules values
     *
     * @return float
     */
    private function getCartRulesValues()
    {
        $rules_values = (float) 0;
        
        $cart_rules = $this->context->cart->getCartRules();
        if (count($cart_rules) > 0) {
            foreach ($cart_rules as $rule) {
                $rules_values += $rule['value_real'];
            }
        }
        return number_format(Tools::ps_round($rules_values, 2), 2, '.', '') * - 1;
    }

    /**
     * Gets wrapping values for order
     *
     * @return float
     */
    private function getWrappingValues()
    {
        $value = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
        return number_format(Tools::ps_round($value, 2), 2, '.', '');
    }

    /**
     * Generates products data to PagSeguro transaction
     *
     * @return Array PagSeguroItem
     */
    private function generateProductsData()
    {
        $pagseguro_items = array();
        
        $cont = 1;
        
        $id_currency = PagSeguro::returnIdCurrency();
        
        foreach ($this->context->cart->getProducts() as $product) {
            
            $pagSeguro_item = new PagSeguroItem();
            $pagSeguro_item->setId($cont ++);
            $pagSeguro_item->setDescription(Tools::truncate($product['name'], 255));
            $pagSeguro_item->setQuantity($product['quantity']);
            
            if ($this->context->cart->id_currency != $id_currency && ! is_null($id_currency)) {
                $pagSeguro_item->setAmount(
                    $this->convertPriceFull($product['price_wt'], new Currency($this->context->cart->id_currency),
                        new Currency($id_currency)));
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
    private function generateSenderData()
    {
        $sender = new PagSeguroSender();
        
        if (isset($this->context->customer) && ! is_null($this->context->customer)) {
            
            $sender->setEmail($this->context->customer->email);
            
            $firstName = $this->generateName($this->context->customer->firstname);
            $lastName = $this->generateName($this->context->customer->lastname);
            
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
    private function generateName($value)
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
    private function generateShippingData()
    {
        $cost = 00.00;
        $id_currency = PagSeguro::returnIdCurrency();
        
        $shipping = new PagSeguroShipping();
        $shipping->setAddress($this->generateShippingAddressData());
        $shipping->setType($this->generateShippingType());
        
        if ($this->context->cart->id_currency != $id_currency && ! is_null($id_currency)) {
            
            $totalOrder = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
            $current_currency = new Currency($this->context->cart->id_currency);
            $new_currency = new Currency($id_currency);
            
            $cost = $this->convertPriceFull($totalOrder, $current_currency, $new_currency);
        } else {
            $cost = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        }
        
        $shipping->setCost(number_format(Tools::ps_round($cost, 2), 2, '.', ''));
        return $shipping;
    }

    /**
     * Generate shipping type data to PagSeguro transaction
     *
     * @return PagSeguroShippingType
     */
    private function generateShippingType()
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
    private function generateShippingAddressData()
    {
        $address = new PagSeguroAddress();
        $delivery_address = new Address((int) $this->context->cart->id_address_delivery);
        
        if (! is_null($delivery_address)) {
            
            $fullAddress = $this->addressConfig($delivery_address->address1);
            
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

    /**
     * Redirects to the error page if an error occurs in request at PagSeguro
     */
    private function redirectToErroPage()
    {
        global $smarty;
        
        $this->canceledOrderForErro($this->module->currentOrder);
        
        $this->module->display_column_left = false;
        
        $smarty->assign('erro_image', __PS_BASE_URI__ . 'modules/pagseguro/images/logops_86x49.png');
        $smarty->assign('version', _PS_VERSION_);
        
        include_once (dirname(__FILE__) . '/../../../header.php');
        echo $this->module->display(__PS_BASE_URI__ . 'modules/pagseguro', '/views/templates/front/error.tpl');
        include_once (dirname(__FILE__) . '/../../../footer.php');
    }

    private function addressConfig($fullAddress)
    {
        require_once (dirname(__FILE__) . '/../controllers/front/addressConfig.php');
        return AddressConfig::trataEndereco($fullAddress);
    }

    /**
     *
     * Convert amount from a currency to an other currency automatically
     *
     * @param float $amount            
     * @param Currency $currency_from
     *            if null we used the default currency
     * @param Currency $currency_to
     *            if null we used the default currency
     */
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

    private function canceledOrderForErro($id_order)
    {
        $obj_orders = new Order($id_order);
        $obj_orders->current_state = 6;
        $obj_orders->update();
        
        $obj_order_history = new OrderHistory();
        $obj_order_history->id_order = $id_order;
        $obj_order_history->id_employee = 0;
        $obj_order_history->id_order_state = (int) 6;
        
        $obj_order_history->add();
    }
}
