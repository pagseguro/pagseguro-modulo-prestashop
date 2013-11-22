<?php

/*
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2013 PrestaShop SA
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */ 

include_once(dirname(__FILE__).'/../../../../config/config.inc.php');
include_once(dirname(__FILE__).'/../../../../init.php');
include_once(dirname(__FILE__).'/../../pagseguro.php');

if (version_compare(_PS_VERSION_, '1.5.0.3', '<')) {
    
    include_once ('../../../../header.php');
    $pagNotification = new ModuleNotificationPagSeguro();
    $pagNotification->postProcess($_POST);
    include_once ('../../../../footer.php');
    
} else {
    
    class PagSeguroNotificationModuleFrontController extends ModuleFrontController
    {
        
        public function postProcess()
        {
            parent::postProcess();
            $pagNotification = new ModuleNotificationPagSeguro();
            $pagNotification->postProcess($_POST);
        }
        
    }
}

class ModuleNotificationPagSeguro
{

    private $obj_transaction;

    private $obj_notification_type;

    private $array_st_cms;

    private $obj_credential;

    private $notification_type;

    private $notification_code;

    private $reference;

    /**
     * Post data process function
     */
    public function postProcess($_POST)
    {
        
        $caminho = _PS_ROOT_DIR_.'/error/log.txt';
        $arquivo = fopen($caminho, 'a');
        fwrite($arquivo, serialize($_POST));
        fclose($arquivo);
        
        $this->createNotification($_POST);
        $this->createCredential();
        $this->inicializeObjects();
        
        if ($this->obj_notification_type->getValue() == $this->notification_type) {
            $this->createTransaction();
        }
        
        if ($this->obj_transaction) {
            $this->updateCms();
        }
    }

    /**
     *
     * @param array $post            
     */
    private function createNotification(Array $post)
    {

//         $this->notification_type = 'transaction';
        
//         $this->notification_code = 'FF69FE-36F65CF65CCB-4994ED2FB274-0F9B9C';

       $this->notification_type = (isset($post['notificationType']) && trim($post['notificationType']) !== '' ?
           trim($post['notificationType']) : null);
        
       $this->notification_code = (isset($post['notificationCode']) && trim($post['notificationCode']) !== '' ?
           trim($post['notificationCode']) : null);
    }

    /**
     * Create Credential
     */
    private function createCredential()
    {   $email = Configuration::get('PAGSEGURO_EMAIL');
        $token = Configuration::get('PAGSEGURO_TOKEN');
        $this->obj_credential = new PagSeguroAccountCredentials($email, $token);
    }

    /**
     * Inicialize Objects
     */
    private function inicializeObjects()
    {
        $this->createNotificationType();
        $this->createArrayStatusCms();
    }

    /**
     * Create Notification Type
     */
    private function createNotificationType()
    {
        $this->obj_notification_type = new PagSeguroNotificationType();
        $this->obj_notification_type->setByType('TRANSACTION');
    }

    /**
     * Create Array Status Cms
     */
    private function createArrayStatusCms()
    {
        $this->array_st_cms = array(
            0 => 'Iniciado',
            1 => 'Aguardando pagamento',
            2 => 'Em análise',
            3 => 'Paga',
            4 => 'Disponível',
            5 => 'Em disputa',
            6 => 'Devolvida',
            7 => 'Cancelada'
        );
    }

    /**
     * Create Transaction
     */
    private function createTransaction()
    {
        $this->obj_transaction = PagSeguroNotificationService::checkTransaction(
            $this->obj_credential,
            $this->notification_code
        );
        
        $transaction = $this->isNotNull($this->obj_transaction);
        $this->reference = $transaction ? (int) $this->obj_transaction->getReference() : null;
    }

    /**
     * Update Cms
     */
    private function updateCms()
    {
        $id_status = ($this->isNotNull($this->obj_transaction->getStatus()->getValue()))
            ? (int) $this->obj_transaction->getStatus()->getValue() : null;
        
        if ($this->isNotNull($id_status)) {
            $id_st_transaction = (int) $this->returnIdOrderByStatusPagSeguro($this->array_st_cms[$id_status]);
        }

        if ($this->isNotNull($id_st_transaction)) {
            $this->createAddOrderHistory($id_st_transaction);
        }
        $this->saveTransactionId($this->obj_transaction->getCode(), $this->obj_transaction->getReference());
    }
    
    /**
     * Return Id Oder by Status PagSeguro
     *
     * @param type $value
     * @return type
     */
    private function returnIdOrderByStatusPagSeguro($value)
    {
        $isDeleted = version_compare(_PS_VERSION_, '1.5.0.3', '>') ? ' WHERE deleted = 0' : '';
        
        $sql = 'SELECT distinct os.`id_order_state`
            FROM `' . _DB_PREFIX_ . 'order_state` os
            INNER JOIN `' . _DB_PREFIX_ .
            'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \'' .
            pSQL($value) . '\' and os.id_order_state <> 6)' . $isDeleted;
        
        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql));
        
        return $id_order_state[0]['id_order_state'];
    }
    
    /**
     * Create add order history
     *
     * @param type $id_st_transaction            
     */
    private function createAddOrderHistory($id_st_transaction)
    {

        if ($this->isNotNull($this->reference)) {

            $order_history = new OrderHistory();
            $order_history->id_order = (int) $this->reference;
            $order_history->changeIdOrderState((int) $id_st_transaction, $order_history->id_order);
            $order_history->addWithemail();
            
//            $this->obj_orders = new Order((int) $this->reference);
//            $this->obj_order_history = new OrderHistory();
            
//            $this->obj_order_history->id_order = 1;//$this->obj_orders->id;
//            $this->obj_order_history->changeIdOrderState(6, $this->obj_order_history->id_order);
//            $this->obj_order_history->update();
            
//              $this->obj_order_history = new OrderHistory();
//              $this->obj_order_history->id_order = $this->reference;
//              $this->obj_order_history->id_employee = 0;
//              $this->obj_order_history->id_order_state = (int) $id_st_transaction;
//              $this->updateOrders((int) $id_st_transaction);
//	      $this->obj_order_history->update();
//             $this->addOrderHistory();
        }
    }

    /**
     * Update Orders
     *
     * @param type $id_st_transaction
     */
    private function updateOrders($id_st_transaction)
    {
        $this->obj_orders = new Order((int) $this->reference);
        $this->obj_orders->current_state = (int) $id_st_transaction;
        $this->obj_orders->update();
    }

    /**
     * Add Order History
     */
    private function addOrderHistory()
    {
        try {
            $this->obj_order_history->add();
        } catch (PagSeguroServiceException $exc) {
            echo $exc->getMessage();
        }
    }

    /**
     * Is not null
     *
     * @param type $value            
     * @return type
     */
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
        
        if (! Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql)) {
            die(Tools::displayError('Error when updating Transaction Code from PagSeguro in database'));
        }
    }

    private function updateOrder($id_order, $transaction, $pagseguro_order)
    {
        $sql = 'UPDATE `' . _DB_PREFIX_ . 'pagseguro_order`
        SET `id_transaction` = \'' . pSQL($transaction) . '\',
        `id_order` = \'' . (int) $id_order . '\'
        WHERE `id` = \'' . (int) $pagseguro_order . '\';';
        
        if (! Db::getInstance(_PS_USE_SQL_SLAVE_)->Execute($sql)) {
            die(Tools::displayError('Error when updating Transaction Code from PagSeguro in database'));
        }
    }
}
