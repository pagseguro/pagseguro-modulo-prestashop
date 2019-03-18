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
include_once dirname(__FILE__) . '/../util/PagSeguroModuleAuthTools.php';
include_once dirname(__FILE__) . '/doSearch.php';
include_once dirname(__FILE__) . '/doCancel.php';

if (!PagSeguroModuleAuthTools::adminLogged())
    die();

/**
 * Configure a cancel request
 */
class CancelConfig {
    
    private $PSOC;
    private $PSOS;
    
    /**
     * Constructor
     * Instance a new doSearch object.
     * Instance a new doCancel object.
     */
    public function __construct() {
        $this->PSOS = new doSearch();
        $this->PSOC = new doCancel();
    }
    
    /**
     *  Get POST from ajax request
     * @param string $param
     * @return string of a $_POST
     */
    private function getPost($param) {
        return Tools::getValue($param);
    }
    
    /**
     * Get ajax request amount of days.
     * @return int of a amount of days.
     */
    private function getDays() {
        return $this->getPost('days');
    }
    
    /**
     * Get ajax request PrestaShop order identifier
     * @return int of PrestaShop order identifier
     */
    private function getOrderId() {
        return $this->getPost('order_id');
    }
    
    /**
     * Get ajax request PagSeguro transaction code
     * @return string of PagSeguro transaction code
     */
    private function getTransactionCode() {
        return $this->getPost('transaction_code');
    }

    /**
     * Get ajax request action
     * @return string of actions
     */
    private function getAction() {
        return $this->getPost('action');
    }
    
    /**
     * Search all cancellable orders
     * @param boolean $callback
     * @return array of list of cancellable orders.
     */
    private function doSearch($callback = null)
    {
        try {
            if ($callback) {
                return $this->PSOS->goSearch( $this->getDays() );
            } else {
                $this->toString( $this->PSOS->goSearch( $this->getDays() ) );
            }
        } catch (Exception $e) {
            $this->toString(
                array(
                    "error" => true,
                    "message" => trim($e->getMessage())
                )
            );
        }
    }
    
    /**
     * Do cancel action
     */
    private function doCancel()
    {
        try {
            $this->PSOC->goCancel($this->getOrderId(), $this->getTransactionCode());
            $this->toString(
                array(
                    "error" => false,
                    "data" => $this->doSearch(true)
                )
            );
        } catch (Exception $e) {
            $this->toString(
                array(
                    "error" => true,
                    "message" => trim($e->getMessage())
                )
            );
        } 
    }
    
    /**
     * Get information from ajax request and redirect to correct flow
     * @return function 
     */
    public function ajaxAction() {
        switch ($this->getAction()) {
            case 'doSearch':
                return $this->doSearch();

            case 'doCancel':
                return $this->doCancel();
        }
    }
    
    /**
     * Override to string method for json encode.
     * @param array $data
     */
    private function toString(array $data) {
        echo Tools::jsonEncode(array("data" =>$data));
    }
}

/**
 * Instanciate a new CancelConfig object,
 * and call for an ajax request action.
 */
$cancelConfig = new CancelConfig();
$cancelConfig->ajaxAction();