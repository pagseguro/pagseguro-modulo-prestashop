<?php

/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

class PagSeguroValidationModuleFrontController extends ModuleFrontController {

    private $_payment_request;
    
    /**
     *  Post data process function
     */
    public function postProcess() {
        
        $this->_verifyPaymentOptionAvailability();
        $this->_validateCart();
        $this->_generatePagSeguroRequestData();
        $additional_infos = $this->_validateOrder();
        $this->_setAdditionalRequestData($additional_infos);
        $this->_performPagSeguroRequest();
        
    }

    /**
     * Set additional infos to PagSeguroPaymentRequest object
     * @param array $additional_infos
     */
    private function _setAdditionalRequestData(Array $additional_infos){
        
        // setting reference
        $this->_payment_request->setReference($additional_infos['id_order']);
        
        // setting redirect url
        $redirect_url = $this->_payment_request->getRedirectURL();
        if (Tools::isEmpty($redirect_url))
            $this->_payment_request->setRedirectURL($this->_generateRedirectUrl($additional_infos));
    
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
    private function _generateRedirectUrl(Array $arrayData){
        return _PS_BASE_URL_.__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$arrayData['id_cart'].'&id_module='.$arrayData['id_module'].'&id_order='.$arrayData['id_order'].'&key='.$arrayData['key'];
    }
    
    /**
     *  Perform PagSeguro request and return url from PagSeguro
     *  if ok, $this->module->pagSeguroReturnUrl is created with url returned from Pagseguro
     */
    private function _performPagSeguroRequest(){
        
        try {
            // retrieving PagSeguro configurations
            $this->_retrievePagSeguroConfiguration();
            
            // set PagSeguro Prestashop module version
            $this->_setPagSeguroModuleVersion();
            
            // set PagSeguro PrestaShop CMS version
            $this->_setPagSeguroCMSVersion();
            
            // performing request
            $credentials = new PagSeguroAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'), Configuration::get('PAGSEGURO_TOKEN'));
            $url = $this->_payment_request->register($credentials);
            
            // redirecting to PagSeguro
            if (Validate::isUrl($url))
                Tools::redirectLink (Tools::truncate($url, 255, ''));
            
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
        $log_active = Configuration::get('PAGSEGURO_LOG_ACTIVE');
        if ($log_active)
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_.Configuration::get('PAGSEGURO_LOG_FILELOCATION'));

    }
    
    /**
     * Set PagSeguro PrestaShop module version
     */
    private function _setPagSeguroModuleVersion(){
        PagSeguroLibrary::setModuleVersion('prestashop-v'.$this->module->version);
    }
    
    /**
     * Set PagSeguro CMS version
     */
    private function _setPagSeguroCMSVersion(){
        PagSeguroLibrary::setCMSVersion('prestashop-v'._PS_VERSION_);
    }
    
    /**
     *  Generates PagSeguro request data
     */
    private function _generatePagSeguroRequestData(){
        
        $payment_request = new PagSeguroPaymentRequest();
        $payment_request->setCurrency(PagSeguroCurrencies::getIsoCodeByName('Real')); // currency
        $payment_request->setExtraAmount($this->_getCartRulesValues()); // extra amount
        $payment_request->setItems($this->_generateProductsData()); // products
        $payment_request->setSender($this->_generateSenderData()); // sender
        $payment_request->setShipping($this->_generateShippingData()); // shipping
        if (!Tools::isEmpty(Configuration::get('PAGSEGURO_URL_REDIRECT'))) // redirect url
            $payment_request->setRedirectURL(Configuration::get('PAGSEGURO_URL_REDIRECT'));
        
        $this->_payment_request = $payment_request;
    }
    
    /**
     * Gets extra amount cart values
     * @return float
     */
    private function _getCartRulesValues(){
        $rules_values = (float)0;
        
        $cart_rules = $this->context->cart->getCartRules();
        if (count($cart_rules) > 0){
            foreach ($cart_rules as $rule)
                $rules_values += $rule['value_real'];
        }

        return number_format(Tools::ps_round($rules_values, 2), 2, '.', '') * (-1);
    }
    
    /**
     *  Generates products data to PagSeguro transaction
     * 
     *  @return Array PagSeguroItem
     */
    private function _generateProductsData(){
        
        $products = $this->context->cart->getProducts();
        $pagseguro_items = array();
        
        $cont = 1;
        
        foreach ($products as $product) {
            
            $pagSeguro_item = new PagSeguroItem();
            $pagSeguro_item->setId($cont++);
            $pagSeguro_item->setDescription(Tools::truncate($product['name'], 255));
            $pagSeguro_item->setQuantity($product['quantity']);
            $pagSeguro_item->setAmount($product['price_wt']);
            $pagSeguro_item->setWeight($product['weight'] * 1000); // defines weight in gramas
            
            if ($product['additional_shipping_cost'] > 0)
                $pagSeguro_item->setShippingCost($product['additional_shipping_cost']);
            
            array_push($pagseguro_items, $pagSeguro_item);
        }
        
        return $pagseguro_items;
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
            $sender->setName(trim($customer->firstname). ' ' .trim($customer->lastname));
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
        $shipping_type = new PagSeguroShippingType();
        $shipping_type->setByType('NOT_SPECIFIED');
        
        return $shipping_type;
    }
    
    /**
     *  Generates shipping address data to PagSeguro transaction
     * 
     *  @return PagSeguroAddress
     */
    private function _generateShippingAddressData(){
        
        $address = new PagSeguroAddress();
        $delivery_address = new Address($this->context->cart->id_address_delivery);
        
        if (!is_null($delivery_address)){
            $address->setCity($delivery_address->city);
            $address->setPostalCode($delivery_address->postcode);
            $address->setStreet($delivery_address->address1);
            $address->setDistrict($delivery_address->address2);
            $address->setComplement($delivery_address->other);
            $address->setCity($delivery_address->city);
            
            $country = new Country($delivery_address->id_country);
            $address->setCountry($country->iso_code);
            
            $state = new State($delivery_address->id_state);
            $address->setState($state->iso_code);
        }
        
        return $address;
    }
    
}
