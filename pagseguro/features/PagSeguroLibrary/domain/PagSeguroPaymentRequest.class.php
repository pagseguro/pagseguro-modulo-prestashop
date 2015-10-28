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
 * Represents a payment request
 */
class PagSeguroPaymentRequest extends PagSeguroRequest
{

    /**
     * @var
     */
    private $preApproval;

    /***
     * Class constructor to make sure the library was initialized.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sets recurrence for this payment request
     * @param mixed $preApproval
     */
    public function setPreApproval($preApproval)
    {
        $this->preApproval = $preApproval;
    }
    /**
     * @return mixed
     */
    public function getPreApproval()
    {
        return $this->preApproval;
    }

    /***
     * Calls the PagSeguro web service and register this request for payment
     *
     * @param PagSeguroCredentials $credentials, lighbox
     * @return String The URL to where the user needs to be redirected to in order to complete the payment process or
     * the CODE when use lightbox
     */
    public function register(PagSeguroCredentials $credentials, $onlyCheckoutCode = false)
    {
        return PagSeguroPaymentService::checkoutRequest($credentials, $this, $onlyCheckoutCode);
    }

    /***
     * Verify if the adress of NotificationURL or RedirectURL is for tests and return empty
     * @param type $url
     * @return type
     */
    public function verifyURLTest($url)
    {
        $adress = array(
            '127.0.0.1',
            '::1'
        );

        $urlReturn = null;
        foreach ($adress as $item) {
            $find = strpos($url, $item);

            if ($find) {
                $urlReturn = '';
                break;
            } else {
                $urlReturn = $url;
            }
        }

        return $urlReturn;
    }
}
