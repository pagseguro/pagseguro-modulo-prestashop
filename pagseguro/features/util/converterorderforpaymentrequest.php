<?php
/**
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

include_once dirname(__FILE__) . '/../../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../../features/util/util.php';
include_once dirname(__FILE__) . '/../../features/util/encryptionIdPagSeguro.php';

class ConverterOrderForPaymentRequest
{

    private $paymentRequest;

    private $module;

    private $context;

    private $urlToRedirect;

    public function __construct($module)
    {
        $this->paymentRequest = new \PagSeguro\Domains\Requests\Payment();
        $this->module = $module;
        $this->context = Context::getContext();
    }

    public function convertToRequestData()
    {
        $this->generatePagSeguroRequestData();
    }

    public function setAdditionalRequest($additional_infos)
    {
        try {
            $this->setAdditionalRequestData($additional_infos);
            //$this->setNotificationUrl();//useless?
        } catch (PagSeguroServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function request($isLightBox)
    {
        try {
            return $this->performPagSeguroRequest($isLightBox);
        } catch (PagSeguroServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }

    }

    private function generatePagSeguroRequestData()
    {
        $redirectURL = Util::getRedirectUrl();

        /** Currency */
        $this->paymentRequest->setCurrency("BRL");

        /** Extra amount */
        $this->paymentRequest->setExtraAmount($this->getExtraAmountValues());

        /** Products */
        $this->generateProductsData();

        /** Sender */
        $this->generateSenderData();

        /** Shipping */
        $this->generateShippingData();

        /** Redirect URL */
        if (! Tools::isEmpty($redirectURL)) {
            $this->paymentRequest->setRedirectUrl($redirectURL);
        }

        /** Discount */
        $this->getDiscountData($this->paymentRequest);
    }

    private function getExtraAmountValues()
    {
        return Tools::convertPrice($this->getCartDiscounts() + $this->getWrappingValues());
    }

    private function getCartDiscounts()
    {
        $cart_discounts = version_compare(_PS_VERSION_, '1.5', '<') ?
            $this->context->cart->getDiscounts() :
            $this->context->cart->getCartRules();

        $totalDiscouts = (float) 0;

        if (count($cart_discounts) > 0) {
            foreach ($cart_discounts as $discount) {
                $totalDiscouts += $discount['value_real'];
            }
        }

        return number_format(Tools::ps_round($totalDiscouts, 2), 2, '.', '') * - 1;
    }

    private function getWrappingValues()
    {
        $value = $this->context->cart->getOrderTotal(true, Cart::ONLY_WRAPPING);
        return number_format(Tools::ps_round($value, 2), 2, '.', '');
    }

    private function generateProductsData()
    {
        $cont = 1;
        $id_currency = PagSeguro::returnIdCurrency();

        foreach ($this->context->cart->getProducts() as $product) {
            if ($this->context->cart->id_currency != $id_currency && ! is_null($id_currency)) {
                $itemAmount = Util::convertPriceFull(
                    $product['price_wt'],
                    new Currency($this->context->cart->id_currency),
                    new Currency($id_currency)
                );
            } else {
                $itemAmount = $product['price_wt'];
            }
            
            $this->paymentRequest->addItems()->withParameters(
                $cont ++,
                Tools::truncate($product['name'], 255),
                $product['quantity'],
                $itemAmount,
                $product['weight'] * 1000,
                ($product['additional_shipping_cost'] > 0) ? $product['additional_shipping_cost'] : null
            );
        }
    }

    private function generateSenderData()
    {
        if (isset($this->context->customer) && ! is_null($this->context->customer)) {
            $this->paymentRequest->setSender()->setEmail($this->context->customer->email);
   
            $firstName = $this->generateName($this->context->customer->firstname);
            $lastName = $this->generateName($this->context->customer->lastname);
            $name = $firstName . ' ' . $lastName;
            $this->paymentRequest->setSender()->setName(Tools::truncate($name, 50));
        }
    }

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

    private function generateShippingData()
    {
        $cost = 00.00;

        $id_currency = PagSeguro::returnIdCurrency();
        $this->generateShippingAddressData();
        $this->generateShippingType();

        if ($this->context->cart->id_currency != $id_currency && ! is_null($id_currency)) {

            $totalOrder = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
            $current_currency = new Currency($this->context->cart->id_currency);
            $new_currency = new Currency($id_currency);

            $cost = Util::convertPriceFull($totalOrder, $current_currency, $new_currency);
        } else {
            $cost = $this->context->cart->getOrderTotal(true, Cart::ONLY_SHIPPING);
        }

        $this->paymentRequest->setShipping()->setCost()
            ->withParameters(number_format(Tools::ps_round($cost, 2), 2, '.', ''));
    }

    private function generateShippingAddressData()
    {
        $delivery_address = new Address((int) $this->context->cart->id_address_delivery);

        if (! is_null($delivery_address)) {

            $fullAddress = $this->addressConfig($delivery_address->address1);

            $street = (is_null($fullAddress[0]) || empty($fullAddress[0])) ?
                $delivery_address->address1 :
                $fullAddress[0];

            $number = is_null($fullAddress[1]) ? '' : $fullAddress[1];
            $complement = is_null($fullAddress[2]) ? '' : $fullAddress[2];
            
            $state = new State((int) $delivery_address->id_state);
            $country = new Country((int) $delivery_address->id_country);

            $this->paymentRequest->setShipping()->setAddress()->withParameters(
                $street,
                $number,
                $delivery_address->address2,
                $delivery_address->postcode,
                $delivery_address->city,
                $state->iso_code,
                $country->iso_code,
                $complement
            );
        }
    }

    private function getDiscountData($paymentRequest)
    {
        if (Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_CREDITCARD'))) {
            $this->paymentRequest->addPaymentMethod()->withParameters(
                PagSeguro\Enum\PaymentMethod\Group::CREDIT_CARD,
                PagSeguro\Enum\PaymentMethod\Config\Keys::DISCOUNT_PERCENT,
                Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_CREDITCARD_VL'))
            );
        }

        if (Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_EFT'))) {
            $this->paymentRequest->addPaymentMethod()->withParameters(
                PagSeguro\Enum\PaymentMethod\Group::EFT,
                PagSeguro\Enum\PaymentMethod\Config\Keys::DISCOUNT_PERCENT,
                Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_EFT_VL'))
            );
        }

        if (Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_BOLETO'))) {
            $this->paymentRequest->addPaymentMethod()->withParameters(
                PagSeguro\Enum\PaymentMethod\Group::BOLETO,
                PagSeguro\Enum\PaymentMethod\Config\Keys::DISCOUNT_PERCENT,
                Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_BOLETO_VL'))
            );
        }

        if (Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_DEPOSIT'))) {
            $this->paymentRequest->addPaymentMethod()->withParameters(
                PagSeguro\Enum\PaymentMethod\Group::DEPOSIT,
                PagSeguro\Enum\PaymentMethod\Config\Keys::DISCOUNT_PERCENT,
                Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_DEPOSIT_VL'))
            );
        }

        if (Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_BALANCE'))) {
            $this->paymentRequest->addPaymentMethod()->withParameters(
                PagSeguro\Enum\PaymentMethod\Group::BALANCE,
                PagSeguro\Enum\PaymentMethod\Config\Keys::DISCOUNT_PERCENT,
                Tools::safeOutput(Configuration::get('PAGSEGURO_DISCOUNT_BALANCE_VL'))
            );
        }
    }

    private function addressConfig($fullAddress)
    {
        require_once (dirname(__FILE__) . '/addressutil.php');
        return AddressUtil::treatAddress($fullAddress);
    }

    private function generateShippingType()
    {
        $this->paymentRequest->setShipping()->setType()
            ->withParameters(\PagSeguro\Enum\Shipping\Type::NOT_SPECIFIED);
    }

    private function setAdditionalRequestData($additionalInfos)
    {
        $this->urlToRedirect = $this->setRedirectUrl($additionalInfos);

        $this->setReference($additionalInfos['id_order']);

        $this->paymentRequest->setRedirectURL($this->urlToRedirect);

        $this->paymentRequest->setNotificationURL($this->setNotificationUrl());
    }

    private function setNotificationUrl()
    {
        return version_compare(_PS_VERSION_, '1.5.0.3', '<') ?
            Util::urlToNotificationPS14() :
            Util::urlToNotificationPS15();
    }

    private function setRedirectUrl(Array $additional_infos)
    {
        return version_compare(_PS_VERSION_, '1.5.0.3', '<') ?
            Util::urlToRedirectPS14($additional_infos) :
            Util::urlToRedirectPS15($additional_infos);
    }

    private function setReference($reference)
    {
         $referenceToPagSeguro = EncryptionIdPagSeguro::encrypt($reference);
         $this->paymentRequest->setReference($referenceToPagSeguro);
    }

    private function performPagSeguroRequest($isLightBox)
    {

        $code = "";
        try {
            /** Retrieving PagSeguro configurations */
            //$this->retrievePagSeguroConfiguration();

            $credentials = $this->module->getPagSeguroCredentials();

            $url = $this->paymentRequest->register(
                $credentials,
                $this->module->isLightboxCheckoutType()
            );

            if ($this->module->isLightboxCheckoutType()) {
                $resultado = parse_url($url);
                parse_str($resultado['query']);

                return Tools::jsonEncode(
                    array(
                        'code' => $code,
                        'redirect' => $this->urlToRedirect,
                        'urlCompleta' => $url
                    )
                );
            }
            /** Redirecting to PagSeguro */
            if (Validate::isUrl($url)) {
                return Tools::truncate($url, 255, '');
            }
        } catch (PagSeguroServiceException $e) {
            throw $e;
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function retrievePagSeguroConfiguration()
    {

        /** Retrieving configurated default charset */
        //PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));

        /** Retrieving configurated default log info */
        //if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
        //    PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
        //}
    }

//    private function setPagSeguroModuleVersion()
//    {
//        PagSeguroLibrary::setModuleVersion('prestashop-v.' . $this->module->version);
//    }
//
//    private function setPagSeguroCMSVersion()
//    {
//        PagSeguroLibrary::setCMSVersion('prestashop-v.' . _PS_VERSION_);
//    }
}
