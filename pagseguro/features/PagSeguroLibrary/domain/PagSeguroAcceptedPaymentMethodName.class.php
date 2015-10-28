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
 * Represent available name list for payment method
 */
class PagSeguroPaymentMethodName
{

    private static $availableNameList = array(
        'DEBITO_BRADESCO' => 'Bradesco debit',
        'DEBITO_ITAU' => 'Itaú debit',
        'DEBITO_UNIBANCO' => 'Unibanco debit',
        'DEBITO_BANCO_BRASIL' => 'Banco do Brasil debit',
        'DEBITO_BANRISUL' => 'Banrisul debit',
        'DEBITO_HSBC' => 'HSBC bank debit',
        'BOLETO' => 'Boleto',
        'VISA' => 'Visa brand',
        'MASTERCARD' => 'MasterCard brand',
        'AMEX' => 'Amex brand',
        'DINERS' => 'Diners brand',
        'HIPERCARD' => 'Hipercard brand',
        'AURA' => 'Aura brand',
        'ELO' => 'ELO brand',
        'PLENOCARD' => 'PlenoCard brand',
        'PERSONALCARD' => 'PersonalCard brand',
        'JCB' => 'JCB brand',
        'DISCOVER' => 'Discover brand',
        'BRASILCARD' => 'BrasilCard brand',
        'FORTBRASIL' => 'FortBrasil brand',
        'CARDBAN' => 'CardBAN brand',
        'VALECARD' => 'ValeCard brand',
        'CABAL' => 'Cabal brand',
        'MAIS' => 'MAIS brand',
        'AVISTA' => 'AVISTA brand',
        'GRANDCARD' => 'GrandCard brand',
        'SOROCRED' => 'Sorocred brand'
    );


    /***
     * Get available list for accepted payment methods
     * @return array
     */
    public static function getAvailableKeysList()
    {
        return self::$availableNameList;
    }

    /***
     * Check if a name is available for accepted payment methods.
     * @param string $name
     * @return boolean
     */
    public static function isNameAvailable($name)
    {
        $key = Tools::strtoupper($name);
        return (isset(self::$availableNameList[$key]));
    }

    /***
     * Gets description by name
     * @param string $name
     * @return string
     */
    public static function getDescriptionByName($name)
    {
        $key = Tools::strtoupper($name);
        if (isset(self::$availableNameList[$key])) {
            return self::$availableNameList[$key];
        } else {
            return false;
        }
    }

    /***
     * Gets name key by description
     * @param string $description
     * @return string
     */
    public static function getKeyByDescription($description)
    {
        return array_search(Tools::strtolower($description), array_map('strtolower', self::$availableNameList));
    }
}
