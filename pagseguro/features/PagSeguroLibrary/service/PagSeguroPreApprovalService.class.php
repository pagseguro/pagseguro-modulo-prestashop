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
 * Encapsulates web service calls regarding PagSeguro payment requests
 */
class PagSeguroPreApprovalService
{

    /***
     *
     */
    const SERVICE_NAME = 'preApproval';
    /**
     * @var
     */
    private static $service;
    /**
     * @var
     */
    private static $connectionData;

    /***
     * @param PagSeguroConnectionData $connectionData
     * @return string
     */
    private static function buildPreApprovalUrl(PagSeguroConnectionData $connectionData)
    {
        return $connectionData->getServiceUrl();
    }

    /***
     * @param PagSeguroConnectionData $connectionData
     * @param $code
     * @return string
     */
    private static function buildPreApprovalRequestUrl(PagSeguroConnectionData $connectionData, $code)
    {
        return $connectionData->getPaymentUrl() . $connectionData->getResource('requestUrl') . "?code=$code";
    }

    /***
     * @param PagSeguroConnectionData $connectionData
     * @param $code
     * @return string
     */
    private static function buildPreApprovalPaymentUrl(PagSeguroConnectionData $connectionData)
    {
        return $connectionData->getWebserviceUrl() . $connectionData->getResource('paymentUrl');
    }

    /***
     * @param PagSeguroConnectionData $connectionData
     * @param $code
     * @return string
     */
    private static function buildPreApprovalCancelUrl(PagSeguroConnectionData $connectionData, $code)
    {
        return $connectionData->getWebserviceUrl() . $connectionData->getResource('cancelUrl') .
            "$code?".$connectionData->getCredentialsUrlQuery();
    }

    /**
     * @param PagSeguroCredentials $credentials
     * @param PagSeguroPaymentRequest $request
     * @return null|PagSeguroParserData
     * @throws Exception
     * @throws PagSeguroServiceException
     */
    public static function createPreApprovalRequest(
        PagSeguroCredentials $credentials,
        PagSeguroPreApprovalRequest $request
    ) {

        LogPagSeguro::info(
            "PagSeguroPreApprovalService.PreApprovalRequest(" .
            $request->toString() . ") - begin"
        );

        self::$connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);
        $data = array_merge(
            self::$connectionData->getCredentials()->getAttributesMap(),
            PagSeguroPreApprovalParser::getData($request)
        );

        try {
            $connection = new PagSeguroHttpConnection();
            $connection->post(
                self::buildPreApprovalUrl(self::$connectionData),
                $data,
                self::$connectionData->getServiceTimeout(),
                self::$connectionData->getCharset()
            );

            self::$service = "PreApprovalRequest";
            return self::getResult($connection);

        } catch (PagSeguroServiceException $err) {
            //Logging
            LogPagSeguro::error("PagSeguroServiceException: " . $err->getMessage());
            //Exception
            throw $err;

        } catch (Exception $err) {
            //Logging
            LogPagSeguro::error("Exception: " . $err->getMessage());
            //Exception
            throw $err;
        }

    }

    /**
     * @param PagSeguroCredentials $credentials
     * @param PagSeguroPreApprovalCharge $charge
     * @return null|PagSeguroParserData
     * @throws Exception
     * @throws PagSeguroServiceException
     */
    public static function paymentCharge(
        PagSeguroCredentials $credentials,
        PagSeguroPreApprovalCharge $charge
    ) {

        LogPagSeguro::info(
            "PagSeguroPreApprovalService.PreApprovalPaymentCharge(" .
            $charge->toString() . ") - begin"
        );

        self::$connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);
        $data = array_merge(
            self::$connectionData->getCredentials()->getAttributesMap(),
            PagSeguroPreApprovalParser::getCharge($charge)
        );

        try {
            $connection = new PagSeguroHttpConnection();
            $connection->post(
                self::buildPreApprovalPaymentUrl(self::$connectionData),
                $data,
                self::$connectionData->getServiceTimeout(),
                self::$connectionData->getCharset()
            );

            self::$service = "PreApprovalPaymentCharge";
            return self::getResult($connection);

        } catch (PagSeguroServiceException $err) {
            //Logging
            LogPagSeguro::error("PagSeguroServiceException: " . $err->getMessage());
            //Exception
            throw $err;

        } catch (Exception $err) {
            //Logging
            LogPagSeguro::error("Exception: " . $err->getMessage());
            //Exception
            throw $err;
        }
    }

    /**
     * @param PagSeguroCredentials $credentials
     * @param $code
     * @return null|PagSeguroParserData
     * @throws Exception
     * @throws PagSeguroServiceException
     */
    public static function cancelPreApproval(PagSeguroCredentials $credentials, $code)
    {

        //Logging
        $log = array();
        $log['text'] = "PagSeguroNotificationService.PreApprovalCancel($code) - begin";
        LogPagSeguro::info($log['text']);

        self::$connectionData = new PagSeguroConnectionData($credentials, self::SERVICE_NAME);

        try {
            $connection = new PagSeguroHttpConnection();
            $connection->get(
                self::buildPreApprovalCancelUrl(
                    self::$connectionData,
                    $code
                ),
                self::$connectionData->getServiceTimeout(),
                self::$connectionData->getCharset()
            );

            self::$service = "PreApprovalCancel";
            return self::getResult($connection, $code);

        } catch (PagSeguroServiceException $err) {
            //Logging
            LogPagSeguro::error("PagSeguroServiceException: " . $err->getMessage());
            //Exception
            throw $err;

        } catch (Exception $err) {
            //Logging
            LogPagSeguro::error("Exception: " . $err->getMessage());
            //Exception
            throw $err;
        }
    }

    /**
     * @param $connection
     * @param null $code
     * @return null|PagSeguroParserData
     * @throws PagSeguroServiceException
     */
    private function getResult($connection, $code = null)
    {

        $httpStatus = new PagSeguroHttpStatus($connection->getStatus());
        $response   = $connection->getResponse();

        switch ($httpStatus->getType()) {
            case 'OK':
                switch(self::$service) {
                    case "PreApprovalRequest":

                        $response = PagSeguroPreApprovalParser::readSuccessXml($response);

                        $result = array ( 'code' => $response->getCode(),
                            'cancelUrl' => self::buildPreApprovalCancelUrl(
                                self::$connectionData,
                                $response->getCode()
                            ),
                            'checkoutUrl' => self::buildPreApprovalRequestUrl(
                                self::$connectionData,
                                $response->getCode()
                            ) );

                        break;
                    case "PreApprovalCancel":
                        $result = PagSeguroPreApprovalParser::readCancelXml($response);
                        break;
                    case "PreApprovalPaymentCharge":
                        $result = PagSeguroPreApprovalParser::readTransactionXml($response);
                        break;
                }

                //Logging
                $log = array();
                if (is_null($code) && self::$service == "PreApprovalRequest") {
                    $log['text'] = sprintf(
                        "PagSeguroPreApprovalService.%s(".$response->toString().") - end ",
                        self::$service
                    );
                    LogPagSeguro::info($log['text'] . ")");
                } else {
                    $log['text'] = sprintf("PagSeguroPreApprovalService.%s($code) - end ", self::$service);
                    LogPagSeguro::info($log['text']);
                }

                break;
            case 'BAD_REQUEST':

                $errors = PagSeguroServiceParser::readErrors($response);
                $errors = new PagSeguroServiceException($httpStatus, $errors);

                //Logging
                $log['text']  = sprintf("PagSeguroPreApprovalService.%s($code) - error ", self::$service);
                LogPagSeguro::error($log['text'] . $errors->getOneLineMessage());

                //Exception
                throw $errors;
            default:

                $errors = new PagSeguroServiceException($httpStatus);

                //Logging
                $log['text'] = sprintf("PagSeguroPreApprovalService.%s($code) - error ", self::$service);
                LogPagSeguro::error($log['text'] . $errors->getOneLineMessage());

                //Exception
                throw $errors;
        }
        return isset($result) ? $result : null;
    }
}
