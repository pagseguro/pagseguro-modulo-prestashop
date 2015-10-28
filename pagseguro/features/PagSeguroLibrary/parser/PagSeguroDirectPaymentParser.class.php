<?php
/**
 * 2007-2014 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 *  @author    PagSeguro Internet Ltda.
 *  @copyright 2007-2014 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/***
 * Class PagSeguroDirectPaymentParser
 */
class PagSeguroDirectPaymentParser extends PagSeguroPaymentParser
{

    /***
     * @param $payment PagSeguroDirectPaymentRequest
     * @return mixed
     */
    public static function getData($payment)
    {

        $data = null;
        
        $data = parent::getData($payment);

        // paymentMode
        if ($payment->getPaymentMode() != null) {
            $data["paymentMode"] = $payment->getPaymentMode()->getValue();
        }

        // paymentMethod
        if ($payment->getPaymentMethod()->getPaymentMethod() != null) {
            $data["paymentMethod"] = $payment->getPaymentMethod()->getPaymentMethod();
        }

        // senderHash
        if ($payment->getSenderHash() != null) {
            $data["senderHash"] = $payment->getSenderHash();
        }

         // receiverEmail
        if ($payment->getReceiverEmail() != null) {
            $data["receiverEmail"] = $payment->getReceiverEmail();
        }

        // Bank name
        if ($payment->getOnlineDebit() != null) {
            $data["bankName"] = $payment->getOnlineDebit()->getBankName();
        }

        //Credit Card
        if ($payment->getCreditCard() != null) {
            //Token
            if ($payment->getCreditCard()->getToken() != null) {
                $data['creditCardToken'] = $payment->getCreditCard()->getToken();
            }

            //Installments
            if ($payment->getCreditCard()->getInstallment() != null) {
                $installment = $payment->getCreditCard()->getInstallment();
                if ($installment->getQuantity() != null && $installment->getValue()) {
                    $data['installmentQuantity'] = $installment->getQuantity();
                    $data['installmentValue']    = PagSeguroHelper::decimalFormat($installment->getValue());
                }
            }

            //Holder
            if ($payment->getCreditCard()->getHolder() != null) {
                $holder = $payment->getCreditCard()->getHolder();
                if ($holder->getName() != null) {
                    $data['creditCardHolderName'] = $holder->getName();
                }
                 // documents
                /*** @var $document PagSeguroDocument */
                if ($payment->getCreditCard()->getHolder()->getDocuments() != null) {
                    $documents = $payment->getCreditCard()->getHolder()->getDocuments();
                        $data['creditCardHolderCPF'] = $documents->getValue();
                }
                if ($holder->getBirthDate() != null) {
                    $data['creditCardHolderBirthDate'] = $holder->getBirthDate();
                }
                // phone
                if ($holder->getPhone() != null) {
                    if ($holder->getPhone()->getAreaCode() != null) {
                        $data['creditCardHolderAreaCode'] = $holder->getPhone()->getAreaCode();
                    }
                    if ($holder->getPhone()->getNumber() != null) {
                        $data['creditCardHolderPhone'] = $holder->getPhone()->getNumber();
                    }
                }
            }

            //Billing Address
            if ($payment->getCreditCard()->getBilling() != null) {
                $billingAddress = $payment->getCreditCard()->getBilling()->getAddress();
                if ($billingAddress->getStreet() != null) {
                    $data['billingAddressStreet'] = $billingAddress->getStreet();
                }
                if ($billingAddress->getNumber() != null) {
                    $data['billingAddressNumber'] = $billingAddress->getNumber();
                }
                if ($billingAddress->getComplement() != null) {
                    $data['billingAddressComplement'] = $billingAddress->getComplement();
                }
                if ($billingAddress->getCity() != null) {
                    $data['billingAddressCity'] = $billingAddress->getCity();
                }
                if ($billingAddress->getState() != null) {
                    $data['billingAddressState'] = $billingAddress->getState();
                }
                if ($billingAddress->getDistrict() != null) {
                    $data['billingAddressDistrict'] = $billingAddress->getDistrict();
                }
                if ($billingAddress->getPostalCode() != null) {
                    $data['billingAddressPostalCode'] = $billingAddress->getPostalCode();
                }
                if ($billingAddress->getCountry() != null) {
                    $data['billingAddressCountry'] = $billingAddress->getCountry();
                }
            }

        }
        
        return $data;
    }
}
