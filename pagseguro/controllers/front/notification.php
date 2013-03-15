<?php

/*
 * ***********************************************************************
  Copyright [2011] [PagSeguro Internet Ltda.]

  Licensed under the Apache License, Version 2.0 (the "License");
  you may not use this file except in compliance with the License.
  You may obtain a copy of the License at

  http://www.apache.org/licenses/LICENSE-2.0

  Unless required by applicable law or agreed to in writing, software
  distributed under the License is distributed on an "AS IS" BASIS,
  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
  See the License for the specific language governing permissions and
  limitations under the License.
 * ***********************************************************************
 */

/*
 * Class Notification
 */
class PagSeguroNotificationModuleFrontController extends ModuleFrontController {

    private $obj_transaction;
    private $obj_notification_type;
    private $obj_order_history;
    private $array_st_cms;
    private $obj_credential;
    private $obj_orders;
    private $notification_type;
    private $notification_code;
    private $reference;

    /**
     *  Post data process function
     */
    public function postProcess() {
        parent::postProcess();

        $this->_createNotification($_POST);
        $this->_createCredential();
        $this->_inicializeObjects();

        if ($this->obj_notification_type->getValue() == $this->notification_type)
            $this->_createTransaction();

        if ($this->obj_transaction)
            $this->_updateCms();
    }

    /**
     * 
     * @param array $post
     */
    private function _createNotification(Array $post) {
        $this->notification_type = (isset($post['notificationType']) && trim($post['notificationType']) !== "" ? trim($post['notificationType']) : null);
        $this->notification_code = (isset($post['notificationCode']) && trim($post['notificationCode']) !== "" ? trim($post['notificationCode']) : null);
    }

    /**
     * Create Credential 
     */
    private function _createCredential() {
        $this->obj_credential = new PagSeguroAccountCredentials(Configuration::get("PAGSEGURO_EMAIL"), Configuration::get("PAGSEGURO_TOKEN"));
    }

    /**
     * Inicialize Objects 
     */
    private function _inicializeObjects() {
        $this->_createNotificationType();
        $this->_createArrayStatusCms();
    }

    /**
     * Create Notification Type 
     */
    private function _createNotificationType() {
        $this->obj_notification_type = new PagSeguroNotificationType();
        $this->obj_notification_type->setByType('TRANSACTION');
    }

    /**
     * Create Array Status Cms 
     */
    private function _createArrayStatusCms() {
        $this->array_st_cms = array(0 => "Iniciado", 1 => "Aguardando pagamento", 2 => "Em análise", 3 => "Paga", 4 => "Disponível", 5 => "Em disputa", 6 => "Devolvida", 7 => "Cancelada");
    }

    /**
     * Create Transaction
     */
    private function _createTransaction() {
        $this->obj_transaction = PagSeguroNotificationService::checkTransaction($this->obj_credential, $this->notification_code);
        $this->reference = ( $this->_isNotNull($this->obj_transaction) ) ? (int) $this->obj_transaction->getReference() : null;
    }
    
    /**
     * Update Cms
     */
    private function _updateCms() {
        $id_status = ( $this->_isNotNull($this->obj_transaction->getStatus()->getValue()) ) ? (int) $this->obj_transaction->getStatus()->getValue() : null;
       
        if ($this->_isNotNull($id_status)) 
            $id_st_transaction = (int) $this->_returnIdOrderByStatusPagSeguro($this->array_st_cms[$id_status]);
        
            if ($this->_isNotNull($id_st_transaction)) 
                $this->_createAddOrderHistory($id_st_transaction);
    }

    /**
     * Return Id Oder by Status PagSeguro
     * @param type $value
     * @return type
     */
    private function _returnIdOrderByStatusPagSeguro($value) {
        $id_order_state = (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
		SELECT distinct os.`id_order_state`
		FROM `' . _DB_PREFIX_ . 'order_state` os
		INNER JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`name` = \'' . $value . '\' AND os.`module_name` = \'pagseguro\')
		WHERE deleted = 0'));

        return $id_order_state[0]['id_order_state'];
    }

    /**
     * Create add order history
     * @param type $id_st_transaction
     */
    private function _createAddOrderHistory($id_st_transaction) {
        if ($this->_isNotNull($this->reference)) {
            $this->obj_order_history = new OrderHistory();
            $this->obj_order_history->id_order = $this->reference;
            $this->obj_order_history->id_employee = (int) 0;
            $this->obj_order_history->id_order_state = $id_st_transaction;
            $this->_updateOrders($id_st_transaction);
            $this->_addOrderHistory();
        }
    }

    /**
     * Update Orders
     * @param type $id_st_transaction
     */
    private function _updateOrders($id_st_transaction) {
        $this->obj_orders = new Order($this->reference);
        $this->obj_orders->current_state = $id_st_transaction;
        $this->obj_orders->update();
    }

    /**
     * Add Order History
     */
    private function _addOrderHistory() {
        try {
            $this->obj_order_history->add();
        } catch (PagSeguroServiceException $exc) {
            echo $exc->getMessage();
        }
    }

    /**
     * Is not null
     * @param type $value
     * @return type
     */
    private function _isNotNull($value) {
        return ( isset($value) ) ? true : false;
    }

}

