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
 * Represents a preApproval request
 */
class PagSeguroPreApprovalRequest extends PagSeguroRequest
{

    /**
     * @var
     */
    private $reviewURL;
    /**
     * @var
     */
    private $receiverEmail;
    /**
     * @var
     */
    private $preApprovalMaxTotalAmount;
    /**
     * @var
     */
    private $preApprovalMaxAmountPerPeriod;
    /**
     * @var
     */
    private $preApprovalFinalDate;
    /**
     * @var
     */
    private $preApprovalInitialDate;
    /**
     * @var
     */
    private $preApprovalDayOfMonth;
    /**
     * @var
     */
    private $preApprovalDayOfWeek;
    /**
     * @var
     */
    private $preApprovalDayOfYear;
    /**
     * @var
     */
    private $preApprovalPeriod;
    /**
     * @var
     */
    private $preApprovalAmountPerPayment;
    /**
     * @var
     */
    private $preApprovalMaxAmountPerPayment;
    /**
     * @var
     */
    private $preApprovalMaxPaymentsPerPeriod;
    /**
     * @var
     */
    private $preApprovalDetails;
    /**
     * @var
     */
    private $preApprovalName;
    /**
     * @var
     */
    private $preApprovalCharge;

    /***
     * Sets the review URL
     *
     * Uri to where the PagSeguro payment page should redirect the user if they want to change the Pre Approval rules
     *
     * @param String $reviewURL
     */
    public function setReviewURL($reviewURL)
    {
        $this->reviewURL = $this->verifyURLTest($reviewURL);
    }
    /**
     * @return mixed the Review Url
     */
    public function getReviewURL()
    {
        return $this->reviewURL;
    }

    /**
     * Sets the preApprovalReceiverEmail for this pre approval
     * @param string $receiverEmail
     */
    public function setReceiverEmail($receiverEmail)
    {
        $this->receiverEmail = $receiverEmail;
    }
    /**
     * @return string
     */
    public function getReceiverEmail()
    {
        return $this->receiverEmail;
    }

    /***
     * Sets the preApprovalMaxTotalAmount for this pre approval
     * @param double $value
     */
    public function setPreApprovalMaxTotalAmount($value)
    {
        $this->preApprovalMaxTotalAmount = $value;
    }
    /***
     * @return double the reference of preApprovalMaxTotalAmount
     */
    public function getPreApprovalMaxTotalAmount()
    {
        return $this->preApprovalMaxTotalAmount;
    }
    
    /***
     * Sets the preApprovalMaxAmountPerPeriod for this pre approval
     * @param double $value
     */
    public function setPreApprovalMaxAmountPerPeriod($value)
    {
        $this->preApprovalMaxAmountPerPeriod = $value;
    }
    /***
     * @return double the reference of preApprovalMaxAmountPerPeriod
     */
    public function getPreApprovalMaxAmountPerPeriod()
    {
        return $this->preApprovalMaxAmountPerPeriod;
    }
    
    /***
     * Sets the preApprovalFinalDate for this pre approval
     * @param ISODate $date
     */
    public function setPreApprovalFinalDate($date)
    {
        $this->preApprovalFinalDate = $date;
    }
    /***
     * @return date the reference of preApprovalFinalDate
     */
    public function getPreApprovalFinalDate()
    {
        return $this->preApprovalFinalDate;
    }
    
    /***
     * Sets the preApprovalInitialDate for this pre approval
     * @param ISODate $date
     */
    public function setPreApprovalInitialDate($date)
    {
        $this->preApprovalInitialDate = $date;
    }
    /***
     * @return date the reference of preApprovalInitialDate
     */
    public function getPreApprovalInitialDate()
    {
        return $this->preApprovalInitialDate;
    }
    
    /***
     * Sets the preApprovalDayOfMonth for this pre approval
     * @param Number $day
     */
    public function setPreApprovalDayOfMonth($day)
    {
        $this->preApprovalDayOfMonth = $day;
    }
    /***
     * @return int the reference of preApprovalInitialDate
     */
    public function getPreApprovalDayOfMonth()
    {
        return $this->preApprovalDayOfMonth;
    }

    /***
     * Sets the preApprovalDayOfWeek for this pre approval
     * @param String $day
     */
    public function setPreApprovalDayOfWeek($day)
    {
        $this->preApprovalDayOfWeek = $day;
    }
    /***
     * @return String the reference of preApprovalDayOfWeek
     */
    public function getPreApprovalDayOfWeek()
    {
        return $this->preApprovalDayOfWeek;
    }

    /**
     * Sets the preApprovalDayOfYear for this pre approval
     * @param mixed $preApprovalDayOfYear
     */
    public function setPreApprovalDayOfYear($preApprovalDayOfYear)
    {
        $this->preApprovalDayOfYear = $preApprovalDayOfYear;
    }
    /**
     * @return mixed
     */
    public function getPreApprovalDayOfYear()
    {
        return $this->preApprovalDayOfYear;
    }
    
    /***
     * Sets the preApprovalPeriod for this pre approval
     * @param String $period
     */
    public function setPreApprovalPeriod($period)
    {
        $this->preApprovalPeriod = $period;
    }
    /***
     * @return String the reference of preApprovalPeriod
     */
    public function getPreApprovalPeriod()
    {
        return $this->preApprovalPeriod;
    }
    
    /***
     * Sets the preApprovalAmountPerPayment value for the recurrent payment
     * @param double $value
     */
    public function setPreApprovalAmountPerPayment($value)
    {
        $this->preApprovalAmountPerPayment = $value;
    }
    /***
     * @return double the reference of preApprovalAmountPerPayment
     */
    public function getPreApprovalAmountPerPayment()
    {
        return $this->preApprovalAmountPerPayment;
    }

    /**
     * Sets the preApprovalMaxAmountPerPayment value for the recurrent payment
     * @param double $preApprovalMaxAmountPerPayment
     */
    public function setPreApprovalMaxAmountPerPayment($preApprovalMaxAmountPerPayment)
    {
        $this->preApprovalMaxAmountPerPayment = $preApprovalMaxAmountPerPayment;
    }
    /**
     * @return double
     */
    public function getPreApprovalMaxAmountPerPayment()
    {
        return $this->preApprovalMaxAmountPerPayment;
    }

    /**
     * Sets the preApprovalMaxPaymentsPerPeriod value for the recurrent period
     * @param double $preApprovalMaxPaymentsPerPeriod
     */
    public function setPreApprovalMaxPaymentsPerPeriod($preApprovalMaxPaymentsPerPeriod)
    {
        $this->preApprovalMaxPaymentsPerPeriod = $preApprovalMaxPaymentsPerPeriod;
    }
    /**
     * @return double
     */
    public function getPreApprovalMaxPaymentsPerPeriod()
    {
        return $this->preApprovalMaxPaymentsPerPeriod;
    }

    /***
     * Sets the preApprovalDetails for the transaction
     * @param String $details
     */
    public function setPreApprovalDetails($details)
    {
        $this->preApprovalDetails = $details;
    }
    /***
     * @return String the reference of preApprovalDetails
     */
    public function getPreApprovalDetails()
    {
        return $this->preApprovalDetails;
    }
    
    /***
     * Sets the preApprovalName (title) for the transaction
     * @param String $name
     */
    public function setPreApprovalName($name)
    {
        $this->preApprovalName = $name;
    }
    /***
     * @return String the reference of preApprovalName (title)
     */
    public function getPreApprovalName()
    {
        return $this->preApprovalName;
    }
    
    /***
     * Sets the preApprovalCharge type (auto, manual)
     * @param String $type
     */
    public function setPreApprovalCharge($type)
    {
        $this->preApprovalCharge = $type;
    }

    /***
     * @return String the reference of pre approval charge
     */
    public function getPreApprovalCharge()
    {
        return $this->preApprovalCharge;
    }

    /***
     * Register the preApproval
     * @param PagSeguroCredentials $credentials
     * @param bool $onlyCheckoutCode
     */
    public function register(PagSeguroCredentials $credentials)
    {
        return PagSeguroPreApprovalService::createPreApprovalRequest($credentials, $this);
    }
}
