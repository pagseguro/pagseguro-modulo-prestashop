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
 * Represent available payment method groups.
 */
class PagSeguroPaymentMethodGroups
{

    private static $availableGroupList = array(
        'CREDIT_CARD' => 'Payment with Credit Card',
        'BOLETO' => 'Payment with Boleto',
        'EFT' => 'Payment with Online Debit',
        'BALANCE' => 'Payment with PagSeguro Balance',
        'DEPOSIT' => 'Payment with Deposit'
    );

    /***
     * Get available payment method groups list for payment method config use in PagSeguro transactions
     * @return array
     */
    public static function getAvailableGroupList()
    {
        return self::$availableGroupList;
    }

    /***
     * Check if payment method groups is available for PagSeguro
     * @param string $key
     * @return boolean
     */
    public static function isKeyAvailable($key)
    {
        $key = Tools::strtoupper($key);
        return (isset(self::$availableGroupList[$key]));
    }

    /***
     * Gets group description by key
     * @param string $key
     * @return string
     */
    public static function getDescriptionByKey($key)
    {
        $key = Tools::strtoupper($key);
        if (isset(self::$availableGroupList[$key])) {
            return self::$availableGroupList[$key];
        } else {
            return false;
        }
    }

    /***
     * Gets group type by description
     * @param string $description
     * @return string
     */
    public static function getGroupByDescription($description)
    {
        return array_search(Tools::strtolower($description), array_map('strtolower', self::$availableGroupList));
    }
}
