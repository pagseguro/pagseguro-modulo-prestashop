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

include_once dirname(__FILE__) . '/../../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../../features/PagSeguroLibrary/PagSeguroLibrary.php';
include_once dirname(__FILE__) . '/../../features/util/encryptionIdPagSeguro.php';
include_once dirname(__FILE__) . '/../../features/util/util.php';


class PagSeguroAbandonedOrder {

    private $credentials;

    private $messages = array();

    public function __construct() {
        $this->createLog();
    }

    public function getTransactionsJson($recoveryDays) {
        $recoveryDays = (int)$recoveryDays;
        echo Tools::jsonEncode(Array(
            'transactions'  => $this->getTransactions($recoveryDays),
            'messages'      => $this->messages
        ));
    }

    public function sendMails(Array $transactions) {
        $sendMultiple = false;
        if ($transactions) {
        	$sendMultiple = true;
            $templateData = $this->getMailTemplateData();
            foreach ($transactions as $key => $value) {
                parse_str($value);
                if (!$this->sendMail($templateData, $reference, $recovery, $customer)) {
                	$sendMultiple = false;
                    break;
                }
            }
        }
        $result = $sendMultiple ? Array('success' => true) : Array('error' => true);
        echo Tools::jsonEncode($result);
    }

    private function getTransactions($recoveryDays) {

        $resultData = array();
        
        if ($transactions = $this->getPagSeguroTransactions($recoveryDays)) {
            
            $pagseguroOrders = $this->getPagSeguroOrders();

            foreach ($transactions as $transaction) {

                if ($this->isAbandonedOrder($transaction->getReference())) {

                    $reference = ((int)EncryptionIdPagSeguro::decrypt($transaction->getReference()));
                    $order = new Order($reference);
                    $sendRecovery = isset($pagseguroOrders[$reference]) ? (int)$pagseguroOrders[$reference]['send_recovery'] : 0;

                    array_push($resultData, Array(
                        'transactionCode' => $transaction->getCode(),
                        'reference' => $reference,
                        'maskedReference' => sprintf("#%06s", $reference),
                        'expirationDate' => $this->makeExpirationDate($transaction->getDate()),
                        'orderDate' => date("d/m/Y H:i", strtotime($order->date_add)),
                        'customerId' => $order->id_customer,
                        'recoveryCode' => $transaction->getRecoveryCode(),
                        'sendRecovery' => $sendRecovery
                    ));

                }
            }

        }

        return $resultData;

    }

    private function getPagSeguroOrders() {
        
        $result = Array();
        $sql = "SELECT * FROM ". _DB_PREFIX_ ."pagseguro_order";

        if ($orders = Db::getInstance()->executeS($sql)) {
            foreach ($orders as $key => $order) {
                $result[$order['id_order']] = $order;
            }
        }

        return $result;

    }

    private function isAbandonedOrder($reference) {
    
        if (strpos($reference, Configuration::get('PAGSEGURO_ID')) !== false) {

            $initiated      = Util::getPagSeguroStatusName(0);
            $decReference   = (int)EncryptionIdPagSeguro::decrypt($reference);
            $orderState     = OrderHistory::getLastOrderState($decReference);
            
            if (strcmp($orderState->name, $initiated) != 0) {
                return false;
            }

        } else {
            return false;
        }
        
        return true;
    }

    private function makeExpirationDate($date) {
        $normalized = date("d/m/Y", strtotime($date));
        list($day, $month, $year) = explode('/', $normalized);
        return date("d/m/Y", mktime('0', '0', '0', $month, $day + 10, $year));
    }

    private function getPagSeguroTransactions($recoveryDays) {
        
        if (!$this->createCredentials()) {
            return false;
        }

        $recoveryDays = (int)$recoveryDays;
        $now = date('Y-m-d H:i:s');

        list($year, $month, $day) = explode('-', $now);
        list($hour, $minutes, $seconds) = explode(':', $now);
        $hour = explode(" ",$hour);
        $initialDay = date(DATE_ATOM, mktime($hour[1], $minutes, $seconds, $month, $day - $recoveryDays, $year));

        try {
            $serviceData = PagSeguroTransactionSearchService::searchAbandoned($this->credentials, 1, 1000, $initialDay);
        } catch (PagSeguroServiceException $e) {
            array_push($this->messages, $e->getOneLineMessage());
        } catch (Exception $e) {
            array_push($this->messages, $e->getMessage());
        }

        return $serviceData ? $serviceData->getTransactions() : false;

    }

    private function createCredentials() {
            
        if (!$this->credentials) {
            $email = Configuration::get('PAGSEGURO_EMAIL');
            $token = Configuration::get('PAGSEGURO_TOKEN');
            if (!empty($email) && !empty($token)) {
                $this->credentials = new PagSeguroAccountCredentials($email, $token);
            } else {
                array_push($this->messages, "PagSeguro credentials not set.");
            }
        }

        return (bool)$this->credentials;
    }

    private function createLog() {

        /*** Retrieving configurated default charset */
        PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));

        /*** Retrieving configurated default log info */
        if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
        }
                
        LogPagSeguro::info(
            "PagSeguroAbandoned.Search( 'Pesquisa de transações abandonadas realizada em " . date("d/m/Y H:i") . ".')"
        );

    }

    private function getMailTemplateData() {
        
        $languages = Language::getLanguages(false);
        $template  = '';
        $message   = '';
        $idLang    = '';

        foreach ($languages as $language) {
            if (strcmp($language["iso_code"], 'br') == 0) {
                $idLang = $language["id_lang"];
            }
        }

        $orderMessage = OrderMessage::getOrderMessages($idLang);

        foreach ($orderMessage as $key => $value) {
            if (strcmp($value["id_order_message"], Configuration::get('PAGSEGURO_MESSAGE_ORDER_ID')) == 0) {
                $template = $value['name'];
                $message  = $value['message'];
            }
        }

        return Array(
            'idLang'    => $idLang,
            'template'  => $template,
            'message'   => $message
        );

    }
    
    private function buildAbandonedMailUrl($recoveryCode)
    {
        
        $protocol = "https://";
        $environment = "sandbox.";
        $resource = "pagseguro.uol.com.br/checkout/v2/resume.html";
        $recovery = "?r=" . $recoveryCode;
               
        if ( Configuration::get('PAGSEGURO_ENVIRONMENT') == "sandbox") {
            $url = $protocol.$environment.$resource.$recovery;
        } else {
            $url = $protocol.$resource.$recovery;
        }
        return '<a href="'.$url.'" target="_blank"> Clique aqui para continuar sua compra </a>';
    }

    private function sendMail(Array $templateData, $reference, $recoveryCode, $customerId) {
        
        $customer = new Customer((int) $customerId);
        
        $params = array(
            '{message}' =>  $templateData['message'],
            '{link}'    =>  $this->buildAbandonedMailUrl($recoveryCode)
        );

        $sendMail = @Mail::Send(
            $templateData['idLang'],
            'recovery_cart',
            $templateData['template'],
            $params,
            $customer->email,
            $customer->firstname.' '.$customer->lastname,
            null,
            null,
            null,
            null,
            _PS_ROOT_DIR_ . '/modules/pagseguro/mails/',
            true
        );

        return $sendMail ? $this->updateSendMailCount($reference) : false;

    }

    private function updateSendMailCount($id) {
        $sql = '
            UPDATE `' . _DB_PREFIX_ . 'pagseguro_order`
            SET `send_recovery` = (send_recovery + 1)
            WHERE `id_order` = \'' . (int) $id . '\';
        ';
        return Db::getInstance()->Execute($sql) ? (int)Db::getInstance()->Affected_Rows() : false;
    }

}
