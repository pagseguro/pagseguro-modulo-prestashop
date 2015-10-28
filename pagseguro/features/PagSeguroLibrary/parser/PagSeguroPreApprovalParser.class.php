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
 * Class PagSeguroPreApprovalParser
 */
class PagSeguroPreApprovalParser
{

    /**
     * @param $payment
     * @return mixed
     */
    public static function getData($preApproval)
    {

        $data = array();

        if ($preApproval->getReviewURL() != null) {
            $data["reviewURL"] = $preApproval->getReviewURL();
        }

        if ($preApproval->getRedirectURL() != null) {
            $data["redirectURL"] = $preApproval->getRedirectURL();
        }

        if ($preApproval->getReference() != null) {
            $data["reference"] = $preApproval->getReference();
        }

        if ($preApproval->getPreApprovalMaxTotalAmount() != null) {
            $data["preApprovalMaxTotalAmount"] = $preApproval->getPreApprovalMaxTotalAmount();
        }

        if ($preApproval->getPreApprovalMaxAmountPerPeriod() != null) {
            $data["preApprovalMaxAmountPerPeriod"] = $preApproval->getPreApprovalMaxAmountPerPeriod();
        }

        if ($preApproval->getPreApprovalInitialDate() != null) {
            $data["preApprovalInitialDate"] = $preApproval->getPreApprovalInitialDate();
        }

        if ($preApproval->getPreApprovalFinalDate() != null) {
            $data["preApprovalFinalDate"] = $preApproval->getPreApprovalFinalDate();
        }

        if ($preApproval->getPreApprovalDayOfMonth() != null) {
            $data["preApprovalDayOfMonth"] = $preApproval->getPreApprovalDayOfMonth();
        }

        if ($preApproval->getPreApprovalDayOfWeek() != null) {
            $data["preApprovalDayOfWeek"] = $preApproval->getPreApprovalDayOfWeek();
        }

        if ($preApproval->getPreApprovalDayOfYear() != null) {
            $data["preApprovalDayOfYear"] = $preApproval->getPreApprovalDayOfYear();
        };

        if ($preApproval->getPreApprovalPeriod() != null) {
            $data["preApprovalPeriod"] = $preApproval->getPreApprovalPeriod();
        }

        if ($preApproval->getPreApprovalAmountPerPayment() != null) {
            $data["preApprovalAmountPerPayment"] = $preApproval->getPreApprovalAmountPerPayment();
        }

        if ($preApproval->getPreApprovalMaxAmountPerPayment() != null) {
            $data["preApprovalMaxAmountPerPayment"] = $preApproval->getPreApprovalMaxAmountPerPayment();
        }

        if ($preApproval->getPreApprovalMaxPaymentsPerPeriod() != null) {
            $data["preApprovalMaxPaymentsPerPeriod"] = $preApproval->getPreApprovalMaxPaymentsPerPeriod();
        }

        if ($preApproval->getPreApprovalDetails() != null) {
            $data["preApprovalDetails"] = $preApproval->getPreApprovalDetails();
        }

        if ($preApproval->getPreApprovalName() != null) {
            $data["preApprovalName"] = $preApproval->getPreApprovalName();
        }

        if ($preApproval->getPreApprovalCharge() != null) {
            $data["preApprovalCharge"] = $preApproval->getPreApprovalCharge();
        }

        return $data;
    }

    /**
     * @param $preApproval
     * @return array
     */
    public static function getCharge($preApproval)
    {
        $data = array();

        if ($preApproval->getReference() != null) {
            $data["reference"] = $preApproval->getReference();
        };

        if ($preApproval->getPreApprovalCode() != null) {
            $data["preApprovalCode"] = $preApproval->getPreApprovalCode();
        };

        // items
        $items = $preApproval->getItems();
        if (count($items) > 0) {
            $i = 0;

            foreach ($items as $key => $value) {
                $i++;
                if ($items[$key]->getId() != null) {
                    $data["itemId$i"] = $items[$key]->getId();
                }
                if ($items[$key]->getDescription() != null) {
                    $data["itemDescription$i"] = $items[$key]->getDescription();
                }
                if ($items[$key]->getQuantity() != null) {
                    $data["itemQuantity$i"] = $items[$key]->getQuantity();
                }
                if ($items[$key]->getAmount() != null) {
                    $amount = PagSeguroHelper::decimalFormat($items[$key]->getAmount());
                    $data["itemAmount$i"] = $amount;
                }
                unset($value);
            }
        }

        return $data;
    }

    /**
     * @param $str_xml
     * @return PagSeguroPreApproval
     */
    public static function readPreApproval($str_xml)
    {
        $parser = new PagSeguroXmlParser($str_xml);
        $data = $parser->getResult('preApproval');
        $preApproval = new PagSeguroPreApproval();

        // <preApproval> <name>
        if (isset($data["name"])) {
            $preApproval->setName($data["name"]);
        }

        // <preApproval> <lastEventDate>
        if (isset($data["lastEventDate"])) {
            $preApproval->setLastEventDate($data["lastEventDate"]);
        }

        // <preApproval> <date>
        if (isset($data["date"])) {
            $preApproval->setDate($data["date"]);
        }

        // <preApproval> <code>
        if (isset($data["code"])) {
            $preApproval->setCode($data["code"]);
        }

        // <preApproval> <tracker>
        if (isset($data["tracker"])) {
            $preApproval->setTracker($data["tracker"]);
        }

        // <preApproval> <reference>
        if (isset($data["reference"])) {
            $preApproval->setReference($data["reference"]);
        }

        // <preApproval> <charge>
        if (isset($data["charge"])) {
            $preApproval->setCharge($data["charge"]);
        }

        // <preApproval> <status>
        if (isset($data["status"])) {
            $preApproval->setStatus(new PagSeguroPreApprovalStatus($data["status"]));
        }

        if (isset($data["sender"])) {
            // <preApproval> <sender>
            $sender = new PagSeguroSender();

            // <preApproval> <sender> <name>
            if (isset($data["sender"]["name"])) {
                $sender->setName($data["sender"]["name"]);
            }

            // <preApproval> <sender> <email>
            if (isset($data["sender"]["email"])) {
                $sender->setEmail($data["sender"]["email"]);
            }

            if (isset($data["sender"]["phone"])) {
                // <preApproval> <sender> <phone>
                $phone = new PagSeguroPhone();

                // <preApproval> <sender> <phone> <areaCode>
                if (isset($data["sender"]["phone"]["areaCode"])) {
                    $phone->setAreaCode($data["sender"]["phone"]["areaCode"]);
                }

                // <preApproval> <sender> <phone> <number>
                if (isset($data["sender"]["phone"]["number"])) {
                    $phone->setNumber($data["sender"]["phone"]["number"]);
                }

                $sender->setPhone($phone);
            }

            // <preApproval><sender><documents>
            if (isset($data["sender"]['documents']) && is_array($data["sender"]['documents'])) {
                $documents = $data["sender"]['documents'];
                if (count($documents) > 0) {
                    foreach ($documents as $document) {
                        $sender->addDocument($document['type'], $document['value']);
                    }
                }
            }

            $preApproval->setSender($sender);
        }

        return $preApproval;
    }

    /**
     * @param $str_xml
     * @return PagSeguroPreApprovalSearchResult
     */
    public static function readSearchResult($str_xml)
    {

        $parser = new PagSeguroXmlParser($str_xml);
        $data = $parser->getResult('preApprovalSearchResult');

        $result = new PagSeguroPreApprovalSearchResult();

        if (isset($data['totalPages'])) {
            $result->setTotalPages($data['totalPages']);
        }

        if (isset($data['date'])) {
            $result->setDate($data['date']);
        }

        if (isset($data['resultsInThisPage'])) {
            $result->setResultsInThisPage($data['resultsInThisPage']);
        }

        if (isset($data['currentPage'])) {
            $result->setCurrentPage($data['currentPage']);
        }

        if (isset($data['preApprovals']) && is_array($data['preApprovals'])) {
            $preApprovals = array();
            if (isset($data["preApprovals"]['preApproval'][0])) {
                $i = 0;
                foreach ($data["preApprovals"]['preApproval'] as $value) {
                    $preApprovals[$i++] = new PagSeguroPreApproval($value);
                }
            } else {
                $preApprovals[0] = $data["preApprovals"]['preApproval'];
            }
            $result->setPreApprovals($preApprovals);
        }

        return $result;
    }

    /**
     * @param $str_xml
     * @return PagSeguroParserData
     */
    public static function readTransactionXml($str_xml)
    {
        $parser = new PagSeguroXmlParser($str_xml);
        $data = $parser->getResult('result');
        $preApprovalParser = new PagSeguroParserData();
        $preApprovalParser->setCode($data['transactionCode']);
        $preApprovalParser->setRegistrationDate($data['date']);
        return $preApprovalParser;
    }

    /**
     * @param $str_xml
     * @return PagSeguroParserData
     */
    public static function readSuccessXml($str_xml)
    {
        $parser = new PagSeguroXmlParser($str_xml);
        $data = $parser->getResult('preApprovalRequest');
        $preApprovalParser = new PagSeguroParserData();
        $preApprovalParser->setCode($data['code']);
        $preApprovalParser->setRegistrationDate($data['date']);
        return $preApprovalParser;
    }

    /**
     * @param $str_xml
     * @return PagSeguroParserData
     */
    public static function readCancelXml($str_xml)
    {
        $parser = new PagSeguroXmlParser($str_xml);
        $data = $parser->getResult('result');
        $preApprovalParser = new PagSeguroParserData();
        $preApprovalParser->setCode(null); // PreApproval API does not send code on cancel requests
        $preApprovalParser->setRegistrationDate($data['date']);
        $preApprovalParser->setStatus($data['status']);
        return $preApprovalParser;
    }
}
