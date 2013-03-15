<?php
/*
************************************************************************
Copyright [2013] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
************************************************************************
*/

/**
 * Class PagSeguro Search Transaction
 */
class PagSeguroSearchTransaction{
    
    private $transaction_code;
    
    private $obj_credential;
    
    private $obj_transaction;
  
    /**
     * Construct
     */
    public function __construct() {
        $this->transaction_code = (isset($_POST['notificationCode']) && trim($_POST['notificationCode']) !== ""  ? trim($_POST['notificationCode']) : null);
        $this->_createCredential();
        $this->_createTransaction();
    }
    
    /**
     * Create Credential
     */
    private function _createCredential(){
        $this->obj_credential = new PagSeguroAccountCredentials(Configuration::get("PAGSEGURO_EMAIL"), Configuration::get("PAGSEGURO_TOKEN"));        
    }
    
    /**
     * Create Transaction
     */
    private function _createTransaction(){
        $this->obj_transaction = PagSeguroTransactionSearchService::searchByCode($this->obj_credential, $this->transaction_code);
    } 
}


