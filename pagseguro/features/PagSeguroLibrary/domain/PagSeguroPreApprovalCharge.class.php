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
class PagSeguroPreApprovalCharge
{

    /***
     * Products/items in this pre approval charge
     */
    private $items;
    /**
     * @var
     */
    private $reference;
    /**
     * @var
     */
    private $preApprovalCode;

    /***
     * @return array the items/products list in this payment request
     */
    public function getItems()
    {
        return $this->items;
    }

    /***
     * Sets the items/products list in this payment request
     * @param array $items
     */
    public function setItems(array $items)
    {
        if (is_array($items)) {
            $i = array();
            foreach ($items as $key => $item) {
                if ($item instanceof PagSeguroItem) {
                    $i[$key] = $item;
                } else {
                    if (is_array($item)) {
                        $i[$key] = new PagSeguroItem($item);
                    }
                }
            }
            $this->items = $i;
        }
    }

    /***
     * Adds a new product/item in this payment request
     *
     * @param String $id
     * @param String $description
     * @param String $quantity
     * @param String $amount
     */
    public function addItem(
        $id,
        $description = null,
        $quantity = null,
        $amount = null
    ) {
        $param = $id;
        if ($this->items == null) {
            $this->items = array();
        }
        if (is_array($param)) {
            array_push($this->items, new PagSeguroItem($param));
        } else {
            if ($param instanceof PagSeguroItem) {
                array_push($this->items, $param);
            } else {
                $item = new PagSeguroItem();
                $item->setId($param);
                $item->setDescription($description);
                $item->setQuantity($quantity);
                $item->setAmount($amount);
                array_push($this->items, $item);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    /**
     * @return mixed
     */
    public function getPreApprovalCode()
    {
        return $this->preApprovalCode;
    }

    /**
     * @param mixed $preApprovalCode
     */
    public function setPreApprovalCode($preApprovalCode)
    {
        $this->preApprovalCode = $preApprovalCode;
    }

    /**
     * @param PagSeguroCredentials $credentials
     * @return array|null|PagSeguroParserData
     */
    public function register(PagSeguroCredentials $credentials)
    {
        return PagSeguroPreApprovalService::paymentCharge($credentials, $this);
    }

    /***
     * @return String a string that represents the current object
     */
    public function toString()
    {

        $request = array();
        $request['Reference'] = $this->reference;
        $request['PagSeguroPreApprovalCode'] = $this->preApprovalCode;

        return "PagSeguroPaymentCharge: " . var_export($request, true);
    }
}
