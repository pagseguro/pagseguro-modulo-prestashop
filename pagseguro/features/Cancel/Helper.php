<?php /**
 * 2007-2015 [PagSeguro Internet Ltda.]
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
 *  @copyright 2007-2015 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

include_once dirname(__FILE__).'/../util/util.php';

/**
 * Class Helper
 */
class Helper {

    /**
     * @var
     */
    private $credentials;
    /**
     * @var
     */
    private $log;

    /**
     *
     */
    public function __construct() {
        
        $this->log();
    }

    /**
     * Get PagSeguro credentials
     * @return PagSeguroAccountCredentials
     */
    public function getPagSeguroCredentials() {
        
        if (!$this->credentials) {
            $email = Configuration::get('PAGSEGURO_EMAIL');
            $token = Configuration::get('PAGSEGURO_TOKEN');
            if (!empty($email) && !empty($token)) {
                $this->credentials = new PagSeguroAccountCredentials($email, $token);
            }
        }       
        return $this->credentials;
    }

    /**
     * Get a status name
     * @param int $status
     * @return string of pagseguro status name
     */
    public function getStatusName($status)
    {
        return Util::getPagSeguroStatusName($status);
    }

    /**
     * Get reference prefix
     * @param string $reference
     * @return bool|string
     */
    public function getRefPrefix($reference) {
        return Tools::substr($reference, 0, 5);
    }

    /**
     * Get reference suffix
     * @param string $reference
     * @return bool|string
     */
    public function getRefSuffix($reference) {
        return Tools::substr($reference, 5);
    }

    /**
     * Get current datetime
     * @return string
     */
    public function getNow()
    {
        $this->setTimeZone();
        $date = new DateTime();
        return $date->format('Y-m-d\TH:i');
    }

    /**
     * Get subtracted date
     * @param $days
     * @return string
     */
    public function subtractDayFromDate($days) {
        
        $date = new DateTime($this->getNow());
        $date->sub(new DateInterval('P'.$days.'D'));
        return $date->format('Y-m-d') . "T00:00";
    }

    /**
     * Get timezone
     */
    public function getTimeZone()
    {
        date_default_timezone_get();
    }

    /**
     * Set timezone
     */
    public function setTimeZone()
    {
        date_default_timezone_set('America/Sao_Paulo');
    }

    /**
     * Verifies if log service was active
     */
    public function log() {
        if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
            PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
            $this->log = true;
        } else {
            $this->log = false;
        }
    }

    /**
     * Set a log info
     * @param $message
     */
    public function info($message) {
        if ($this->log) {
            LogPagSeguro::info($message);
        }
    }

    /**
     * Verify PrestaShop version
     * @return bool
     */
    public function version() {
        if (version_compare(_PS_VERSION_, '1.5.0.5', '<')) {
            return true;
        } 
        return false;
    }      
}