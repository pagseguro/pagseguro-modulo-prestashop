<?php
/**
 * 2007-2013 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2014 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
include_once dirname(__FILE__) . '/../../features/library/vendor/autoload.php';

if (function_exists('__autoload')) {
    spl_autoload_register('__autoload');
}

/**
 * Class responsible by handle the pagseguro notifications
 */
class PagSeguroNotificationOrderPrestashop
{
    private $obj_transaction;
    private $reference;
    
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
        \PagSeguro\Configuration\Configure::setEnvironment(Configuration::get('PAGSEGURO_ENVIRONMENT'));
        $this->helper = new Helper();
    }

    /**
     * Capture and treats the notification http post
     * @param array $post
     */
    public function postProcess($post)
    {
        try {
            $this->createCredential();
            $this->createTransaction();

            if ($this->obj_transaction) {
                $this->updateCms();
            }
        } catch (Exception $e) {
            $this->createLog($e->getMessage());
        }

    }

    /**
     * Configure the pagseguro credentials
     * @throws Exception
     */
    private function createCredential()
    {
        if (!empty(Configuration::get('PAGSEGURO_EMAIL')) 
            && !empty(Configuration::get('PAGSEGURO_TOKEN'))) {

            \PagSeguro\Configuration\Configure::setAccountCredentials(
                Configuration::get('PAGSEGURO_EMAIL'),
                Configuration::get('PAGSEGURO_TOKEN')
            );
        } else {
            throw new Exception('Credenciais inválidas ou não configuradas corretamente.');
        }
    }

    private function createTransaction()
    {
        try {
            if (\PagSeguro\Helpers\Xhr::hasPost()) {
                $this->obj_transaction = \PagSeguro\Services\Transactions\Notification::check(
                    \PagSeguro\Configuration\Configure::getAccountCredentials()
                );
            } else {
                throw new \InvalidArgumentException($_POST);
            }
        } catch (Exception $exc) {
            throw $exc;
        }

        $transaction = $this->isNotNull($this->obj_transaction);

        if (strpos($this->obj_transaction->getReference(), Configuration::get('PAGSEGURO_ID')) === false) {
            throw new Exception("ID_PAGSEGURO_INCOMPATIVEL", 1);
        }

        $this->reference = $transaction ? (int)EncryptionIdPagSeguro::decrypt($this->obj_transaction->getReference()) : null;
    }

    /**
     * Updates the CMS pagseguro transaction status
     */
    private function updateCms()
    {
        $id_status = ($this->isNotNull($this->obj_transaction->getStatus())) ?
        (int) $this->obj_transaction->getStatus() : null;

        if ($this->isNotNull($id_status)) {
            $id_st_transaction = (int) $this->returnIdOrderByStatusPagSeguro(Util::getPagSeguroStatusName($id_status));
        }

        if ($this->isNotNull($id_st_transaction)) {
            $this->addOrderHistory((int)$this->reference, $id_st_transaction);
        }

        $this->saveTransactionId($this->obj_transaction->getCode(), $this->decryptId($this->obj_transaction->getReference()));
    }

    private function addOrderHistory($idOrder, $status) {
        $order_history = new OrderHistory();
        $order_history->id_order = $idOrder;
        $order_history->changeIdOrderState($status, $idOrder);
        $order_history->addWithemail();
        return true;
    }    

    private function returnIdOrderByStatusPagSeguro($value)
    {

        $isDeleted = version_compare(_PS_VERSION_, '1.5.0.3', '>') ? ' WHERE deleted = 0' : '';

        $sql = 'SELECT distinct os.`id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ .
            'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \''
                . pSQL($value) . '\' and os.id_order_state <> 6)' . $isDeleted;

        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));

        return $id_order_state[0]['id_order_state'];
    }

    private function isNotNull($value)
    {
        return isset($value);
    }

    private function saveTransactionId($transaction, $reference)
    {
        $sql = "SELECT `id` FROM `" . _DB_PREFIX_ . "pagseguro_order` WHERE `id_order` = $reference";

        $pagseguro_order = Db::getInstance()->getRow($sql);

        if ($pagseguro_order['id']) {
            $this->updateOrder($reference, $transaction, $pagseguro_order['id']);
        } else {
            $this->saveOrder($reference, $transaction);
        }
    }

    private function saveOrder($id_order, $transaction)
    {
        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'pagseguro_order` (`id_transaction`, `id_order`)
                VALUES (\'' . pSQL($transaction) . '\', \'' . (int) $id_order . '\')';

        if (! Db::getInstance()->Execute($sql)) {
            die(Tools::displayError('Error when updating Transaction Code from PagSeguro in database'));
        }
    }

    private function updateOrder($id_order, $transaction, $pagseguro_order)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'pagseguro_order`
        SET `id_transaction` = \'' . pSQL($transaction) . '\',
        `id_order` = \'' . (int) $id_order . '\'
        WHERE `id` = \'' . (int) $pagseguro_order . '\';';

        if (! Db::getInstance()->Execute($sql)) {
            die(Tools::displayError('Error when updating Transaction Code from PagSeguro in database'));
        }
    }

    private function createLog($e)
    {
        /** Retrieving configurated default charset */
        PagSeguroConfig::setApplicationCharset(Configuration::get('PAGSEGURO_CHARSET'));

        /** Retrieving configurated default log info */
        if (Configuration::get('PAGSEGURO_LOG_ACTIVE')) {
            PagSeguroConfig::activeLog(_PS_ROOT_DIR_ . Configuration::get('PAGSEGURO_LOG_FILELOCATION'));
        }

        LogPagSeguro::info(
            "PagSeguroService.Notification( 'Erro ao processar notificação. ErrorMessage: ".$e." ') - end"
        );
    }


    
    /****
     *
     * Grab a PAGSEGURO_ID and decrypts
     * @param string $reference
     * @return PAGSEGURO_ID
     */
    private function decryptId($reference)
    {
    	return Tools::substr($reference, 5);
    }

}
