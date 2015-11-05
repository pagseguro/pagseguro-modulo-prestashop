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

include_once dirname(__FILE__) .'/../../../../config/config.inc.php';
include_once dirname(__FILE__) .'/../../features/PagSeguroLibrary/PagSeguroLibrary.php';
include_once dirname(__FILE__) .'/../../features/validation/pagsegurovalidateorderprestashop.php';
include_once dirname(__FILE__) .'/../../features/PagSeguroLibrary/domain/PagSeguroTransactionSearchResult.class.php';
include_once dirname(__FILE__) .'/../../features/PagSeguroLibrary/domain/PagSeguroTransactionStatus.class.php';

class PagSeguroOrderConciliation {

    private $pagSeguroCredentials;
    private $logActive;

    public function __construct() {
        $this->activeLog();
    }

    public function printUpdateStatusJsonData(Array $transactions) {

        $updated = true;

        foreach ($transactions as $transaction) {

            parse_str($transaction, $output);
            
            $orderId    = $output["reference"];
            $statusId   = $output["status"];
            $statusName = Util::getPagSeguroStatusName($statusId);

            $this->logInfo("
                PagSeguroConciliation.Register( 'Alteração de Status da compra '". $orderId . "' 
                para o Status '" . $statusName . "(". $statusId . ")' - '" . 
                date("d/m/Y H:i") . "') - end
            ");

            if (!$this->updateOrderStatus($orderId, $statusName)) {
                $updated = false;
                break;
            }

        }

        $this->printJson(  $updated ?  Array('success' => true) : Array('error' => true) );

    }

    public function printConciliationJsonData($searchDays) {
        
        $searchDays = (int)$searchDays;

        $this->logInfo("
            PagSeguroConciliation.Search( 'Pesquisa de conciliação realizada em " . date("d/m/Y H:i") . " 
            em um intervalo de ".$searchDays." dias.')
        ");
        
        $this->printJson(Array(
            'data' => $this->getConciliationData($searchDays)
        ));

    }


    private function updateOrderStatus($orderId, $statusName) {

        $moduleName = ($this->verifyVersion() === true) ? "" : "AND module_name = 'pagseguro'";
        
        $query  = '
            SELECT osl.`id_order_state`, osl.`name` FROM `'._DB_PREFIX_.'order_state_lang` osl
            JOIN `'._DB_PREFIX_.'order_state` os ON osl.`id_order_state` = os.`id_order_state` '.$moduleName.'
            WHERE osl.`name` LIKE "'.$statusName.'" GROUP BY osl.`name` LIMIT 0, 1
        ';
        
        if ($result  = Db::getInstance()->executeS($query)) {
            $status  = $result[0]['id_order_state'];
            $order   = new Order($orderId);
            $history = new OrderHistory();
            $history->id_order = (int)$order->id;
            $history->changeIdOrderState($status, $order->id);
            return (bool)$history->addWithemail();
        }
        return false;
    }

    private function getConciliationData($searchDays = 1) {

        $prestashopList = $this->getPrestashopPaymentList($searchDays);
        $pagseguroList  = $this->getPagSeguroPaymentsList($searchDays);

        $hasData = ($searchDays && $prestashopList && $pagseguroList);
        
        $resultList = Array();

        if ($hasData) {
            
            //$stateIndexName = $this->verifyVersion() ? 'id_order_state' : 'current_state';

            foreach ($prestashopList as $order) {

                $orderId = (int)$order['id_order'];
                $pagseguroData = isset($pagseguroList[ $orderId ]) ? $pagseguroList[ $orderId ] : false;

                if ($pagseguroData) {

                    $prestaShopStatus   = $order['statusName'];
                    $pagSeguroStatus    = Util::getPagSeguroStatusName($pagseguroData['status']);
                    $differentStatus    = ($prestaShopStatus != $pagSeguroStatus);

                    if ($differentStatus) {
                        array_push($resultList, Array(
                            'orderId'           => $orderId,
                            'maskedOrderId'     => sprintf("#%06s", $orderId),
                            'date'              => $this->dateToBr($order['date_add']),
                            'transactionCode'   => $pagseguroData['code'],
                            'pagSeguroStatusId' => $pagseguroData['status'],
                            'pagSeguroStatus'   => $pagSeguroStatus,
                            'prestaShopStatus'  => $prestaShopStatus
                        ));
                    }

                }

            }

        }
        return $resultList;

    }


    private function getPrestashopPaymentList($searchDays) {
        
        $currentStateCol = ($this->verifyVersion() === true) ? "" : "psord.`current_state`,";
        
        $query = '
            SELECT
                psord.`id_order`,
                psord.`date_add`,
                '.$currentStateCol.'
                osl.`name` AS statusName,
                oh.`id_order_state`,
                (SELECT COUNT(od.`id_order`) FROM `'._DB_PREFIX_.'order_detail` od
                    WHERE od.`id_order` = psord.`id_order`
                    GROUP BY `id_order`) AS product_number

              FROM `'._DB_PREFIX_.'orders` AS psord
                    LEFT JOIN `'._DB_PREFIX_.'order_history` oh
                        ON (oh.`id_order` = psord.`id_order`)
                    LEFT JOIN `'._DB_PREFIX_.'order_state` os
                        ON (os.`id_order_state` = oh.`id_order_state`)
                    LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl
                        ON (os.`id_order_state` = osl.`id_order_state`)

                 WHERE oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh
                    WHERE moh.`id_order` = psord.`id_order`
                    GROUP BY moh.`id_order`)
                    AND psord.payment = "PagSeguro"
                    AND osl.`id_lang` = psord.id_lang
                    AND psord.date_add >= DATE_SUB(CURDATE(),INTERVAL \''.((int)$searchDays).'\' DAY)
        ';

        return Db::getInstance()->ExecuteS($query);

    }


    private function getPagSeguroPaymentsList($searchDays) {

        if (!$this->createPagSeguroCredentials()) {
            return false;
        }

        $defaultTimeZone = date_default_timezone_get();
        date_default_timezone_set('America/Sao_Paulo');
        
        $finalDate = date("Y-m-d")."T".date("H:i");
        $initialDate = $this->subDayIntoDate($finalDate, (int)$searchDays);
        
        date_default_timezone_set($defaultTimeZone);

        try {

            $transactionList = Array();

            $firstSearch = $this->createPagSeguroTransactionSearch(1, $initialDate, $finalDate);
            $totalPages  = (int)$firstSearch->getTotalPages();
            $this->pushTransactionSummary($transactionList, $firstSearch);

            if ($totalPages >= 2) {
                for ($page = 2; $page <= $totalPages; $page++) {
                    $this->pushTransactionSummary($transactionList, $this->createPagSeguroTransactionSearch(
                        $page, 
                        $initialDate,
                        $finalDate
                    ));
                }
            }
            
            return $this->normalizePagSeguroTransactions($transactionList);

        } catch (PagSeguroServiceException $e) {
            return false;
        }

    }


    private function createPagSeguroCredentials() {
        
        if (!$this->pagSeguroCredentials) {
            $email = Configuration::get('PAGSEGURO_EMAIL');
            $token = Configuration::get('PAGSEGURO_TOKEN');
            if (!empty($email) && !empty($token)) {
                $this->pagSeguroCredentials = new PagSeguroAccountCredentials($email, $token);
            }
        }

        return $this->pagSeguroCredentials;
    }

    /****
    *
    * checks if the PAGSEGURO_ID is the same and returns the related transactions
    * @param PagSeguroTransactionSearchResult $result
    */
    private function normalizePagSeguroTransactions(Array $transactionList) {

        if (!$transactionList) {
            return false;
        }
            
        $normalizedList = array();
        $defaultRefPrefix = Configuration::get('PAGSEGURO_ID');

        foreach ($transactionList as $transactionSummary) {

            $reference = $transactionSummary->getReference();
            $refPrefix = $this->getRefPrefix($reference);
            $refSuffix = (int)$this->getRefSuffix($reference);

            if ($refPrefix == $defaultRefPrefix) {
                $normalizedList[$refSuffix]['reference'] = $reference;
                $normalizedList[$refSuffix]['code']      = $transactionSummary->getCode();
                $normalizedList[$refSuffix]['status']    = $transactionSummary->getStatus()->getValue();
            }

        }

        return $normalizedList;
    }

    private function getRefPrefix($reference) {
        return Tools::substr($reference, 0, 5);
    }

    private function getRefSuffix($reference) {
        return Tools::substr($reference, 5);
    }    

    private function createPagSeguroTransactionSearch($pageNum, $initialDate, $finalDate) {
        return PagSeguroTransactionSearchService::searchByDate(
            $this->pagSeguroCredentials,
            $pageNum, // initial page
            1000, // pages per page
            $initialDate,
            $finalDate
        );
    }

    private function pushTransactionSummary(Array &$transactionList, PagSeguroTransactionSearchResult $search) {
        $transactions = $search->getTransactions();
        foreach ($transactions as $transaction) {
            array_push($transactionList, $transaction);
        }
    }

    private function subDayIntoDate($date, $days) {
        $date = date("Ymd");
        $thisyear = Tools::substr($date, 0, 4);
        $thismonth = Tools::substr($date, 4, 2);
        $thisday = Tools::substr($date, 6, 2);
        $nextdate = mktime(0, 0, 0, $thismonth, $thisday - $days, $thisyear);
        $nData = strftime("%Y-%m-%d", $nextdate);
        return $nData."T00:00";
    }

    private function dateToBr($data) {
        $data = date("d/m/Y", strtotime($data));
        return $data;
    }

    private function printJson(Array $data) {
        echo Tools::jsonEncode($data);
    }

    private function activeLog() {
        if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
            PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
            $this->logActive = true;
        }
    }

    private function logInfo($logMessage) {
        if ($this->logActive) {
            LogPagSeguro::info($logMessage);
        }
    }   

    private function verifyVersion() {
        if (version_compare(_PS_VERSION_, '1.5.0.5', '<')) {
            $result = true;
        } else {
            $result = false;
        }
        return $result;
    }      

}