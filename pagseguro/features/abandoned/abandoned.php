<?php

include_once dirname(__FILE__) . '/../../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../../features/PagSeguroLibrary/PagSeguroLibrary.php';
include_once dirname(__FILE__) . '/../../features/util/encryptionIdPagSeguro.php';

$abandoned = new PagSeguroAbandoned();
return $abandoned->getTableResult();

class PagSeguroAbandoned
{

    private $objCredential = "";

    private $errorMsg = array();

    private $tableResult = "";

    private $idStatusPagseguro;
    
    private $idLang = "";
    
    private $idInitiatedState;
    
    public function __construct()
    {

        foreach (Language::getLanguages(false) as $language) {
            if (strcmp($language["iso_code"], 'br') == 0) {
                $this->idLang = $language["id_lang"];
            } else if (strcmp($language["iso_code"], 'en') == 0) {
                $this->idLang = $language["id_lang"];
            }
        }
        
        $order_state = OrderState::getOrderStates($this->idLang);
        foreach ($order_state as $key => $value) {
            if (strcmp($value["name"], Util::getStatusCMS(0)) == 0) {
                $this->idInitiatedState = $value["id_order_state"];
            }
        }
    }

    public function getTableResult()
    {
        $this->setObjCredential();
        $tableResult = $this->getTable();
        return $tableResult;
    }

    private function getTable()
    {

        try {

            $abandonedOrders = array();
            
            if (!$this->errorMsg) {
            
                $listOfAbandoned = $this->getAbandoned();

                if (is_array($listOfAbandoned->getTransactions())) {
                    
                    foreach ($listOfAbandoned->getTransactions() as $key => $value) {
                        
                        $helper = array();
                        
                        $create_date_order_pagseguro = date("d/m/Y", strtotime($value->getDate()));
                        list($day, $month, $year) = explode('/', $create_date_order_pagseguro);
    
                        $days_to_recovery = Configuration::get('PAGSEGURO_DAYS_RECOVERY');
                        $expiration_date = date(
                            "d/m/Y",
                            mktime('0', '0', '0', $month, $day + $days_to_recovery, $year)
                        );
                        
                        $params['reference'] = $value->getReference();
                        $params['data_expired'] = $expiration_date;

                        if ($this->validateOrderAbandoned($params)) {

                                $helper['data_expired'] = $expiration_date;
    
                                $reference = ((int)EncryptionIdPagSeguro::decrypt($value->getReference()));
                                $helper['reference'] = $reference;
                                
                                $order = new Order($reference);
                                $helper['data_add_cart'] = $order->date_add;
                                $helper['customer'] = $order->id_customer;
    
                                $recoveryCode = $value->getRecoveryCode();
                                $helper['recovery_code'] = $recoveryCode;
        
                                array_push($abandonedOrders, $helper);
                        }
                    }
                }
            }
        } catch (PagSeguroServiceException $e) {
            array_push($this->errorMsg, Tools::displayError($e->getOneLineMessage()));
        } catch (Exception $e) {
            array_push($this->errorMsg, Tools::displayError($e->getMessage()));
        }

        global $smarty;
        $smarty->assign('day_recovery_teste', Configuration::get('PAGSEGURO_DAYS_RECOVERY'));
        $smarty->assign('abandoned_orders', $abandonedOrders);
        
        $smarty->assign('is_recovery_cart', Configuration::get('PAGSEGURO_RECOVERY_ACTIVE'));

        return array('table' => $this->tableResult,'errorMsg' => $this->errorMsg);
    }

    private function getAbandoned()
    {
        
        $now = date('Y-m-d');
        list($year, $month, $day) = explode('-', $now);
        $initialDay = date(DATE_ATOM, mktime('0', '0', '0', $month, $day - 11, $year));

        return PagSeguroTransactionSearchService::searchAbandoned($this->objCredential, 1, 1000, $initialDay);

    }

    public function validateOrderAbandoned($params)
    {
        
        $isValidated = true;
        
        if (strpos($params['reference'], Configuration::get('PAGSEGURO_ID')) !== false) {

            $initiated = Util::getStatusCMS(0);
            $order_state = OrderHistory::getLastOrderState(((int)EncryptionIdPagSeguro::decrypt($params['reference'])));
            if (strcmp($order_state->name, $initiated) != 0) {
                $isValidated = false;
            }

        } else {
            $isValidated = false;
        }
        
        return $isValidated;
    }

    private function setObjCredential()
    {
        $email = Configuration::get('PAGSEGURO_EMAIL');
        $token = Configuration::get('PAGSEGURO_TOKEN');
        if (!empty($email) && !empty($token)) {
            $this->objCredential = new PagSeguroAccountCredentials($email, $token);
        } else {
            $this->errorMsg = true;
        }
    }
}
