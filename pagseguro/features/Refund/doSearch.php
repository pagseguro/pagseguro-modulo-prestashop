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
include_once dirname(__FILE__) . '/../../features/library/vendor/autoload.php';

if (function_exists('__autoload')) {
    spl_autoload_register('__autoload');
}

/**
 * Class doSearch
 */
class doSearch {

    /**
     * @var
     */
    private $days;
    /**
     * @var Helper
     */
    private $helper;
    /**
     * @var
     */
    private $PagSeguroPaymentList;
    /**
     * @var array
     */
    private $paymentList = array();

    /**
     *
     */
    public function __construct() {
        $this->version = '2.2.0';

        \PagSeguro\Library::initialize();
        \PagSeguro\Configuration\Configure::setCharset(Configuration::get('PAGSEGURO_CHARSET'));
        \PagSeguro\Configuration\Configure::setLog(
            Configuration::get('PAGSEGURO_LOG_ACTIVE'),
            _PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION')
        );
        \PagSeguro\Library::cmsVersion()->setName("'prestashop-v.'")->setRelease(_PS_VERSION_);
        \PagSeguro\Library::moduleVersion()->setName('prestashop-v.')->setRelease($this->version);
        \PagSeguro\Configuration\Configure::setAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'), Configuration::get('PAGSEGURO_TOKEN'));
        \PagSeguro\Configuration\Configure::setEnvironment(Configuration::get('PAGSEGURO_ENVIRONMENT'));

        $this->helper = new Helper();
    }

    /**
     * Search for refundable payment
     * @param $days
     * @return array of refundable payment
     * @throws Exception
     */
    public function goSearch($days)
    {
        $this->days = $days;
        
        try {
            $this->getPagSeguroPayments();
            $paymentPagSeguroList = $this->normalize($this->PagSeguroPaymentList->getTransactions());
            $paymentPrestaShopList = $this->getPrestashopPaymentList();

            foreach ($paymentPrestaShopList as $item) {
                
                if ($item['environment'] == Configuration::get("PAGSEGURO_ENVIRONMENT")) {

                    if (array_key_exists($item['id_order'], $paymentPagSeguroList))
                    {
                        $arr['date'] = date("d/m/Y H:i:s", strtotime($item['date_add']));
                        $arr['prestaShopID'] = sprintf(
                            "#%06s",
                            $this->helper->getRefSuffix($paymentPagSeguroList[$item['id_order']]['reference'])
                        );
                        $arr['pagSeguroID'] = $paymentPagSeguroList[$item['id_order']]['code'];
                        $arr['status'] = $item['statusName'];

                        if ($this->helper->getStatusName($paymentPagSeguroList[$item['id_order']]['status']) 
                            == $item['statusName']) {
                            $arr['action'] = '<a id="do-refund" class="link" href="javascript:void(0)">Estornar</a>';
                        } else {
                            $arr['action'] = '<a id="before-refund" class="link" href="javascript:void(0)">Estornar</a>';
                        }

                        array_push($this->paymentList, $arr);
                    }
                }
            }
            return $this->paymentList;
            
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Get PagSeguroTransaction from webservice in a date range.
     * @param null $page
     */
    private function getPagSeguroPayments($page = null)
    {
        if (is_null($page)) {
            $page = 1;
        }

        try {
            if (is_null($this->PagSeguroPaymentList)) {
                $this->PagSeguroPaymentList = $this->searchByDate(
                    $page,
                    1000,
                    $this->helper->subtractDayFromDate($this->days)
                );
                
            } else {
                $PagSeguroPaymentList = $this->searchByDate(
                    $page,
                    1000,
                    $this->helper->subtractDayFromDate($this->days)
                );

                $this->PagSeguroPaymentList->setDate($PagSeguroPaymentList->getDate());
                $this->PagSeguroPaymentList->setCurrentPage($PagSeguroPaymentList->getCurrentPage());
                $this->PagSeguroPaymentList->setTotalPages($PagSeguroPaymentList->getTotalPages());
                $this->PagSeguroPaymentList->setResultsInThisPage(
                    $PagSeguroPaymentList->getResultsInThisPage() + $this->PagSeguroPaymentList->getResultsInThisPage()
                );

                $this->PagSeguroPaymentList->setTransactions(
                    array_merge(
                        $this->PagSeguroPaymentList->getTransactions(),
                        $PagSeguroPaymentList->getTransactions()
                    )
                );
            }

            if ($this->PagSeguroPaymentList->getTotalPages() > $page) {
                $this->getPagSeguroPayments(++$page);
            }
        } catch (Exception $pse) {
            throw $pse;
        }
    }

    /**
     * Get all refundable payments in PrestaShop
     * @return array of refundable payment in PrestaShop
     * @throws PrestaShopDatabaseException
     */
    private function getPrestashopPaymentList() 
    {
        $refundableStatus = $this->getRefundableStatusNames();    
        $currentStateCol = ($this->helper->version() === true) ? "" : "psord.`current_state`,";     
        $query = '
            SELECT
                psord.`id_order`,
                psord.`date_add`,
                '.$currentStateCol.'
                osl.`name` AS statusName,
                oh.`id_order_state`,
                pso.`environment`,
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
                  LEFT JOIN `'._DB_PREFIX_.'pagseguro_order`pso
                      ON (pso.`id_order` = psord.`id_order`)
            WHERE oh.`id_order_history` = (SELECT MAX(`id_order_history`) FROM `'._DB_PREFIX_.'order_history` moh
            WHERE moh.`id_order` = psord.`id_order`
            GROUP BY moh.`id_order`)
               AND osl.`name` IN (' . $refundableStatus . ')
               AND psord.payment = "PagSeguro"
               AND osl.`id_lang` = psord.id_lang
               AND psord.date_add >= DATE_SUB(CURDATE(),INTERVAL \''.((int)$this->days).'\' DAY)
        ';
        
        return Db::getInstance()->ExecuteS($query);
    }

    /**
     * Get from PagSeguro ws all transactions in a range of days.
     * @param $pages
     * @param $resultsPerPage
     * @param $initialDate
     * @return PagSeguroTransactionSearchResult
     * @throws Exception
     */
    private function searchByDate($pages, $resultsPerPage, $initialDate) {
        try {
            return \PagSeguro\Services\Transactions\Search\Date::search(
                \PagSeguro\Configuration\Configure::getAccountCredentials(),
                [
                    'initial_date' => $initialDate,
                    'page' => $pages,
                    'max_per_page' => $resultsPerPage
                ]
            );
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /****
    *
    * checks if the PAGSEGURO_ID is the same and returns the related transactions
    * @param PagSeguroTransactionSearchResult $transactions
    */
    private function normalize(array $transactions) {

        if (!$transactions) {
            return false;
        }
        
        $normalizedList = array();
        $defaultRefPrefix = Configuration::get('PAGSEGURO_ID');

        foreach ($transactions as $summary) {
            $refPrefix = $this->helper->getRefPrefix($summary->getReference());
            $refSuffix = (int)$this->helper->getRefSuffix($summary->getReference());

            if ($refPrefix == $defaultRefPrefix) {
                $normalizedList[$refSuffix]['reference'] = $summary->getReference();
                $normalizedList[$refSuffix]['code']      = $summary->getCode();
                $normalizedList[$refSuffix]['status']    = $summary->getStatus();
            }
        }
        return $normalizedList;
    }

    /**
     * Get the name of the refundable statuses and return as a string list
     * @return string
     */
    private function getRefundableStatusNames()
    {
        $status = Util::getCustomOrderStatusPagSeguro();
        $refundableStatus = Array(
            $status['PAID']['name'],
            $status['AVAILABLE']['name'],
            $status['IN_DISPUTE']['name']
        );

        return "'" . implode("', '", $refundableStatus) . "'";
    }
}
