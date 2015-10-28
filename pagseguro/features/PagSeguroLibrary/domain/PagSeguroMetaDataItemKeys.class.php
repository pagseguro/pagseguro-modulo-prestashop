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
 * Represent available metadata item keys
 */
class PagSeguroMetaDataItemKeys
{

    private static $availableItemKeysList = array(
        'PASSENGER_CPF' => 'Passenger CPF',
        'PASSENGER_PASSPORT' => 'Passager passport',
        'ORIGIN_CITY' => 'Origin city',
        'DESTINATION_CITY' => 'Destination city',
        'ORIGIN_AIRPORT_CODE' => 'Airport source code',
        'DESTINATION_AIRPORT_CODE' => 'Destination airport code',
        'GAME_NAME' => 'Game name',
        'PLAYER_ID' => 'Player identification',
        'TIME_IN_GAME_DAYS' => 'In days game time',
        'MOBILE_NUMBER' => 'Recharge cell',
        'PASSENGER_NAME' => 'Passenger name'
    );

    /***
     * Get available item key list for metadata use in PagSeguro transactions
     * @return array
     */
    public static function getAvailableItemKeysList()
    {
        return self::$availableItemKeysList;
    }

    /***
     * Check if item key is available for PagSeguro
     * @param string $itemKey
     * @return boolean
     */
    public static function isItemKeyAvailable($itemKey)
    {
        $itemKey = Tools::strtoupper($itemKey);
        return (isset(self::$availableItemKeysList[$itemKey]));
    }

    /***
     * Gets item description by key
     * @param string $itemKey
     * @return string
     */
    public static function getItemDescriptionByKey($itemKey)
    {
        $itemKey = Tools::strtoupper($itemKey);
        if (isset(self::$availableItemKeysList[$itemKey])) {
            return self::$availableItemKeysList[$itemKey];
        } else {
            return false;
        }
    }

    /***
     * Gets item key type by description
     * @param string $itemDescription
     * @return string
     */
    public static function getItemKeyByDescription($itemDescription)
    {
        return array_search(Tools::strtolower($itemDescription), array_map('strtolower', self::$availableItemKeysList));
    }
}
