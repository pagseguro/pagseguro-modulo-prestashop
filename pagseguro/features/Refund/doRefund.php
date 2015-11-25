<?php
/**
 * 2007-2015 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 *  @author    PagSeguro Internet Ltda.
 *  @copyright 2007-2015 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */
include_once dirname(__FILE__) .'/Helper.php';
include_once dirname(__FILE__) .'/../../features/PagSeguroLibrary/PagSeguroLibrary.php';

/**
 * Class doRefund
 */
class doRefund {

    /**
     * @var Helper
     */
    private $helper;

    /**
     *
     */
    public function __construct() {
        $this->helper = new Helper();
    }

    /**
     * Refund a payment
     * @param $orderID
     * @param $transactionCode
     * @return bool
     * @throws Exception
     */
    public function goRefund($orderID, $transactionCode)
    {

        try {  
            $this->updateOrderStatus(current($orderID));
            $this->refund(current($transactionCode));
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Update a PrestaShop order
     * @param $orderID
     * @return bool
     * @throws PrestaShopDatabaseException
     */
    private function updateOrderStatus($orderID) {

        $moduleName = ($this->helper->version() === true) ? "" : "AND module_name = 'pagseguro'";
        
        $query  = '
            SELECT osl.`id_order_state`, osl.`name` FROM `'._DB_PREFIX_.'order_state_lang` osl
            JOIN `'._DB_PREFIX_.'order_state` os ON osl.`id_order_state` = os.`id_order_state` '.$moduleName.'
            WHERE osl.`name` LIKE "Devolvida" GROUP BY osl.`name` LIMIT 0, 1
        ';
        
        $result  = Db::getInstance()->executeS($query);      
        if ($result) {
            $order   = new Order($this->helper->getRefSuffix($orderID));
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            $history->changeIdOrderState($result[0]['id_order_state'], $order->id);
            return (bool)$history->addWithemail();
        }
        return false;
    }

    /**
     * Execute a refund service
     * @param $transactionCode
     * @return bool|null|string
     * @throws Exception
     */
    private function refund($transactionCode) {
        try {
            return PagSeguroRefundService::createRefundRequest($this->helper->getPagSeguroCredentials(), $transactionCode);
        } catch (Exception $e) {
            throw $e;
        }
    }
    
}
