<?php

/**
 * Class PagSeguro Search Transaction
 */
class PagSeguroSearchTransaction
{
    private $transaction_code;
    private $obj_credential;
    private $obj_transaction;
  
    /**
     * Construct
     */
    public function __construct()
    {
	$this->transaction_code = (isset($_POST['notificationCode']) && trim($_POST['notificationCode']) !== ''  ? trim($_POST['notificationCode']) : null);
	$this->_createCredential();
	$this->_createTransaction();
    }
    
    /**
     * Create Credential
     */
    private function _createCredential()
    {
	$this->obj_credential = new PagSeguroAccountCredentials(Configuration::get('PAGSEGURO_EMAIL'), Configuration::get('PAGSEGURO_TOKEN'));
    }
    
    /**
     * Create Transaction
     */
    private function _createTransaction()
    {
	$this->obj_transaction = PagSeguroTransactionSearchService::searchByCode($this->obj_credential, $this->transaction_code);
    } 
}
