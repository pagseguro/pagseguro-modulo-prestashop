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
 *  @author    André da Silva Medeiros <andre@swdesign.net.br>
 *  @copyright 2007-2014 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/***
 * Class PagSeguroPreApproval
 * Represents a PagSeguro preApproval transaction
 *
 * @property    PagSeguroSender $sender
 *
 */
class PagSeguroPreApproval
{
    /***
     * Transaction name
     */
    private $name;
    
    /***
     * Transaction date
     */
    private $date;

    /***
     * Last event date
     * Date the last notification about this transaction was sent
     */
    private $lastEventDate;

    /***
     * Transaction code
     */
    private $code;

    /***
     *  Reference code
     *  You can use the reference code to store an identifier so you can
     *  associate the PagSeguro transaction to a transaction in your system.
     */
    private $reference;

    /***
     * Recovery code
     */
    private $tracker;

    /***
     * Transaction Status
     * @see PagSeguroTransactionStatus
     * @var PagSeguroTransactionStatus
     */
    private $status;

    /***
     * Pre Approval Charge
     */
    private $charge;

    /***
     * Payer information, who is sending money
     * @see PagSeguroSender
     * @var PagSeguroSender
     */
    private $sender;

    public function __construct($data = null)
    {
        if (is_array($data)) {
            $this->setName($data['name']);
            $this->setDate($data['date']);
            $this->setLastEventDate($data['lastEventDate']);
            $this->setCode($data['code']);
            $this->setReference($data['reference']);
            $this->setTracker($data['tracker']);
            $this->setStatus(new PagSeguroPreApprovalStatus($data['status']));
            $this->setCharge($data['charge']);
        }
    }

    /***
     * @return String the transaction name
     */
    public function getName()
    {
        return $this->name;
    }

    /***
     * Sets the transaction name
     *
     * @param string name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /***
     * Date the last notification about this transaction was sent
     * @return datetime the last event date
     */
    public function getLastEventDate()
    {
        return $this->lastEventDate;
    }

    /***
     * Sets the last event date
     *
     * @param lastEventDate
     */
    public function setLastEventDate($lastEventDate)
    {
        $this->lastEventDate = $lastEventDate;
    }

    /***
     * @return datetime the transaction date
     */
    public function getDate()
    {
        return $this->date;
    }

    /***
     * Sets the transaction date
     *
     * @param string date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /***
     * @return string the transaction code
     */
    public function getCode()
    {
        return $this->code;
    }

    /***
     * Sets the transaction code
     *
     * @param code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /***
     * You can use the reference code to store an identifier so you can
     *  associate the PagSeguro transaction to a transaction in your system.
     *
     * @return string the reference code
     */
    public function getReference()
    {
        return $this->reference;
    }

    /***
     * Sets the reference code
     *
     * @param reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

     /***
     * @return string the tracker code
     */
    public function getTracker()
    {
        return $this->tracker;
    }

    /***
     * Sets the tracker code
     *
     * @param code
     */
    public function setTracker($tracker)
    {
        $this->tracker = $tracker;
    }

    /***
     * @return string the charge
     */
    public function getCharge()
    {
        return $this->charge;
    }

    /***
     * Sets the charge
     *
     * @param code
     */
    public function setCharge($charge)
    {
        $this->charge = $charge;
    }

    /***
     * @return PagSeguroPreApprovalStatus the transaction status
     * @see PagSeguroPreApprovalStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /***
     * Sets the preApproval status
     * @param PagSeguroPreApprovalStatus $status
     */
    public function setStatus(PagSeguroPreApprovalStatus $status)
    {
        $this->status = $status;
    }



    /***
     * @return PagSeguroSender the sender information, who is sending money in this transaction
     * @see PagSeguroSender
     */
    public function getSender()
    {
        return $this->sender;
    }

    /***
     * Sets the sender information, who is sending money in this transaction
     * @param PagSeguroSender $sender
     */
    public function setSender(PagSeguroSender $sender)
    {
        $this->sender = $sender;
    }

    /***
     * @return String a string that represents the current object
     */
    public function toString()
    {

        $preApproval = array();
        $preApproval['code'] = $this->code;
        $preApproval['email'] = $this->sender ? $this->sender->getEmail() : "null";
        $preApproval['date'] = $this->date;
        $preApproval['reference'] = $this->reference;
        $preApproval['status'] = $this->status ? $this->status->getValue() : "null";
        $preApproval['charge'] = $this->charge;
        $preApproval['tracker'] = $this->tracker;
     
        $preApproval = "PreApproval: " . var_export($preApproval, true);

        return $preApproval;

    }
}
