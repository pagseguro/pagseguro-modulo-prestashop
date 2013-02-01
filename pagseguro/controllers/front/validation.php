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

/**
 * @since 1.5.2
 */
class PagSeguroValidationModuleFrontController extends ModuleFrontController {

    private $_paymentRequest;
    
    /**
     *  Post data process function
     */
    public function postProcess() {
        
        $this->_verifyPaymentOptionAvailability();
        $this->_validateCart();
        $this->_generatePagSeguroRequestData();
        $redirectData = $this->_validateOrder();
        $this->_performPagSeguroRequest();
        $this->_redirectToOrderConfirmationPage($redirectData);
        
    }

    /**
     *  Verify if PagSeguro payment module still available
     */
    private function _verifyPaymentOptionAvailability(){
        
        $authorized = false;
        foreach (Module::getPaymentModules() as $module)
            if ($module['name'] == 'pagseguro') {
                $authorized = true;
                break;
            }

        if (!$authorized)
            die($this->module->l('Este método de pagamento não está disponível', 'validation'));
    }

    /**
     *  Validate Cart
     */
    private function _validateCart(){
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
            Tools::redirect('index.php?controller=order&step=1');
    }
    
    /**
     *  Validate order
     */
    private function _validateOrder(){
        $cart = $this->context->cart;
        $customer = new Customer($cart->id_customer);
        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        if (!Validate::isLoadedObject($customer))
            Tools::redirect('index.php?controller=order&step=1');
        
        $this->module->validateOrder((int) $cart->id, Configuration::get('PS_OS_PAGSEGURO'), $total, $this->module->displayName, null, null, (int) $currency->id, false, $customer->secure_key);

        return array(   'id_cart' => (int) $cart->id,
                        'id_module' => (int) $this->module->id,
                        'id_order' => $this->module->currentOrder,
                        'key' => $customer->secure_key);
    }
    
    /**
     *  After system and PagSeguro validations and notification about order,
     *  client will be redirected to order confirmation view with a button that
     *  allows client to access PagSeguro and perform him order payment
     * 
     * @param array $arrayData
     */
    private function _redirectToOrderConfirmationPage(Array $arrayData){
        Tools::redirect('index.php?controller=order-confirmation&id_cart=' . $arrayData['id_cart'] . '&id_module=' . $arrayData['id_module'] . '&id_order=' . $arrayData['id_order'] . '&key=' . $arrayData['key']);
    }
    
    /**
     *  Perform PagSeguro request and return url from PagSeguro
     *  if ok, $this->module->pagSeguroReturnUrl is created with url returned from Pagseguro
     */
    private function _performPagSeguroRequest(){
        
        try {
            // retrieving PagSeguro configurations
            $this->_retrievePagSeguroConfiguration();
            
            // retrieving PagSeguro Prestashop module version
            $this->_retrievePagSeguroModuleVersion();
            
            // performing request
            $credentials = new PagSeguroAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'), Configuration::get('PAGSEGURO_TOKEN'));
            $url = $this->_paymentRequest->register($credentials);
            
            if (Validate::isUrl($url))
                $this->context->cookie->__set('pagseguroResponseUrl', $url);
            
        }
        catch(PagSeguroServiceException $e){
            die($e->getMessage());
        }
    }
    
    /**
     * Retrieve PagSeguro data configuration from database
     */
    private function _retrievePagSeguroConfiguration(){
        
        // retrieving configurated default charset
        PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));
        
        // retrieving configurated default log info
        $logActive = Configuration::get('PAGSEGURO_LOG_ACTIVE');
        if ($logActive)
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_.Configuration::get('PAGSEGURO_LOG_FILELOCATION'));

    }
    
    /**
     * Retrieve PagSeguro PrestaShop module version
     */
    private function _retrievePagSeguroModuleVersion(){
        PagSeguroLibrary::setModuleVersion('prestashop-v'.$this->module->version);
    }
    
    /**
     *  Generates PagSeguro request data
     */
    private function _generatePagSeguroRequestData(){
        
        $paymentRequest = new PagSeguroPaymentRequest();
        $paymentRequest->setCurrency($this->context->currency->iso_code); // currency
        $paymentRequest->setReference($this->module->currentOrderReference); // reference
        $paymentRequest->setItems($this->_generateProductsData()); // products
        $paymentRequest->setSender($this->_generateSenderData()); // sender
        $paymentRequest->setShipping($this->_generateShippingData()); // shipping
        if (!Tools::isEmpty(Configuration::get('PAGSEGURO_URL_REDIRECT'))) // redirect url
            $paymentRequest->setRedirectURL(Configuration::get('PAGSEGURO_URL_REDIRECT'));
        
        $this->_paymentRequest = $paymentRequest;
    }
    
    /**
     *  Generates products data to PagSeguro transaction
     * 
     *  @return Array PagSeguroItem
     */
    private function _generateProductsData(){
        
        $products = $this->context->cart->getProducts();
        $pagSeguroItems = array();
        
        $cont = 1;
        
        foreach ($products as $product) {
            
            $pagSeguroItem = new PagSeguroItem();
            $pagSeguroItem->setId($cont++);
            $pagSeguroItem->setDescription($this->_truncateValue($product['name'], 255));
            $pagSeguroItem->setQuantity($product['quantity']);
            $pagSeguroItem->setAmount($product['price_wt']);
            $pagSeguroItem->setWeight($product['weight'] * 1000); // defines weight in gramas
            
            if ($product['additional_shipping_cost'] > 0)
                $pagSeguroItem->setShippingCost($product['additional_shipping_cost']);
            
            array_push($pagSeguroItems, $pagSeguroItem);
        }
        
        return $pagSeguroItems;
    }
    
    /**
     *  Generates sender data to PagSeguro transaction
     * 
     *  @return PagSeguroSender
     */
    private function _generateSenderData(){
        $sender = new PagSeguroSender();
        $customer = $this->context->customer;
        
        if (isset($customer) && !is_null($customer)){
            $sender->setEmail($customer->email);
            $sender->setName($customer->firstname. ' ' . $customer->lastname);
        }
        
        return $sender;
    }
    
    /**
     *  Generates shipping data to PagSeguro transaction
     * 
     *  @return PagSeguroShipping
     */
    private function _generateShippingData(){
        
        $shipping = new PagSeguroShipping();
        $shipping->setAddress($this->_generateShippingAddressData());
        $shipping->setType($this->_generateShippingType());
        $shipping->setCost(number_format($this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING), 2));
        
        return $shipping;
    }
    
    /**
     *  Generate shipping type data to PagSeguro transaction
     * 
     *  @return PagSeguroShippingType
     */
    private function _generateShippingType(){
        $shippingType = new PagSeguroShippingType();
        $shippingType->setByType('NOT_SPECIFIED');
        
        return $shippingType;
    }
    
    /**
     *  Generates shipping address data to PagSeguro transaction
     * 
     *  @return PagSeguroAddress
     */
    private function _generateShippingAddressData(){
        
        $address = new PagSeguroAddress();
        $deliveryAddress = new Address($this->context->cart->id_address_delivery);
        
        if (!is_null($deliveryAddress)){
            $address->setCity($deliveryAddress->city);
            $address->setPostalCode($deliveryAddress->postcode);
            $address->setStreet($deliveryAddress->address1);
            $address->setDistrict($deliveryAddress->address2);
            $address->setComplement($deliveryAddress->other);
            $address->setCity($deliveryAddress->city);
            
            $country = new Country($deliveryAddress->id_country);
            $address->setCountry($country->iso_code);
            
            $state = new State($deliveryAddress->id_state);
            $address->setState($state->iso_code);
        }
        
        return $address;
    }
    
    /**
     *  Perform truncate of string value
     * 
     * @param string $string
     * @param type $limit
     * @param type $endchars
     * @return string
     */
    private function _truncateValue($string, $limit, $endchars = '...'){
        
        if (!is_array($string) || !is_object($string)){
            
            $stringLength = Tools::strlen($string);
            $endcharsLength  = Tools::strlen($endchars);
            
            if ($stringLength > (int)$limit){
                $cut = (int)($limit - $endcharsLength);
                $string = Tools::substr($string, 0, $cut).$endchars;
            }
        }
        return $string;
    }
}
