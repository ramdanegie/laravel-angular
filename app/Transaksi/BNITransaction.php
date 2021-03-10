<?php

/**
 * A Compatibility library with PHP 5.4's
 *
 * @author    Syaifudin Latief <latiefchan52@gmail.com>
 * @copyright 2016 The Authors
 * @license   http://www.opensource.org/licenses/mit-license.html MIT License
 */

namespace App\Transaksi;

//require_once 'Bootstrap.php';

use App\Exceptions\BillingException;
use App\Helpers\String\DclHashing;
use App\Traits\BniTrait;
use Carbon\Carbon;

class BNITransaction
{
    use BniTrait;

    protected $typeTransaction = 'createbilling';
    protected $clientId;
    protected $trxId;
    protected $trxAmount;
    protected $billingType = 'c';
    protected $customerName;
    protected $customerEmail;
    protected $customerPhone;
    protected $virtualAccount;
    protected $dateTimeExpired = 48;
    protected $description;
    protected $secretKey;
    protected $mode = 'production';
    protected $modeList = ['production', 'development'];
    protected $request;
    protected $response;

    /**
     * Get the value of A Type Transaction
     *
     * @return mixed
     */
    public function getTypeTransaction()
    {
        return $this->typeTransaction;
    }

    /**
     * Set the value of Type Transaction
     *
     * @param mixed typeTransaction
     *
     * @return self
     */
    public function setTypeTransaction($typeTransaction)
    {
        $this->typeTransaction = $typeTransaction;

        return $this;
    }

    /**
     * Get the value of Client Id
     *
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Set the value of Client Id
     *
     * @param mixed clientId
     *
     * @return self
     */
    public function setClientId($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get the value of Trx Id
     *
     * @return mixed
     */
    public function getTrxId()
    {
        return $this->trxId;
    }

    /**
     * Set the value of Trx Id
     *
     * @param mixed trxId
     *
     * @return self
     */
    public function setTrxId($trxId)
    {
        if ($trxId == null || strtolower($trxId) == 'autocreatetrxid') {
            $this->trxId = $this->autoGenerateTrxId();
        } elseif (strlen($trxId) < 30) {
            $this->trxId = $this->modifyTrxId($trxId);
        } else {
            $this->trxId = $trxId;
        }

        return $this;
    }

    /**
     * Get the value of Trx Amount
     *
     * @return mixed
     */
    public function getTrxAmount()
    {
        return $this->trxAmount;
    }

    /**
     * Set the value of Trx Amount
     *
     * @param mixed trxAmount
     *
     * @return self
     */
    public function setTrxAmount($trxAmount)
    {
        $this->trxAmount = $trxAmount;

        return $this;
    }

    /**
     * Get the value of Billing Type
     *
     * @return mixed
     */
    public function getBillingType()
    {
        return $this->billingType;
    }

    /**
     * Set the value of Billing Type
     *
     * @param mixed billingType
     *
     * @return self
     */
    public function setBillingType($billingType)
    {
        $this->billingType = $billingType;

        return $this;
    }

    /**
     * Get the value of Customer Name
     *
     * @return mixed
     */
    public function getCustomerName()
    {
        return $this->customerName;
    }

    /**
     * Set the value of Customer Name
     *
     * @param mixed customerName
     *
     * @return self
     */
    public function setCustomerName($customerName)
    {
        $this->customerName = $customerName;

        return $this;
    }

    /**
     * Get the value of Customer Email
     *
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->customerEmail;
    }

    /**
     * Set the value of Customer Email
     *
     * @param mixed customerEmail
     *
     * @return self
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->customerEmail = $customerEmail;

        return $this;
    }

    /**
     * Get the value of Customer Phone
     *
     * @return mixed
     */
    public function getCustomerPhone()
    {
        return $this->customerPhone;
    }

    /**
     * Set the value of Customer Phone
     *
     * @param mixed customerPhone
     *
     * @return self
     */
    public function setCustomerPhone($customerPhone)
    {
        $this->customerPhone = $customerPhone;

        return $this;
    }

    /**
     * Get the value of Virtual Account
     *
     * @return mixed
     */
    public function getVirtualAccount()
    {
        return $this->virtualAccount;
    }

    /**
     * Set the value of Virtual Account
     *
     * @param mixed virtualAccount
     *
     * @return self
     */
    public function setVirtualAccount($virtualAccount)
    {
        $this->virtualAccount = $virtualAccount;

        return $this;
    }

    /**
     * Get the value of Date Time Expired
     *
     * @return mixed
     */
    public function getDateTimeExpired()
    {
        return $this->dateTimeExpired;
    }

    /**
     * Set the value of Date Time Expired
     *
     * @param mixed dateTimeExpired
     *
     * @return self
     */
    public function setDateTimeExpired($dateTimeExpired)
    {
        $now = Carbon::now();

        if (is_int($dateTimeExpired)) {
            $this->dateTimeExpired = $now->addHours($dateTimeExpired)->toDateTimeString();
        } else {
            $this->dateTimeExpired = $now->addHours($this->dateTimeExpired)->toDateTimeString();
        }

        return $this;
    }

    /**
     * Get the value of Secret Key
     *
     * @return mixed
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Set the value of Secret Key
     *
     * @param mixed secretKey
     *
     * @return self
     */
    public function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;

        return $this;
    }

    /**
     * Get the value of Mode
     *
     * @return mixed
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Set the value of Mode
     *
     * @param mixed mode
     *
     * @return self
     */
    public function setMode($mode)
    {
        if (in_array($mode, $this->modeList)) {
            $this->mode = $mode;
        }

        return $this;
    }

    /**
     * Get the value of Description
     *
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of Description
     *
     * @param mixed description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of Request
     *
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Set the value of Request
     *
     * @return self
     */
    public function setRequest()
    {
        $requestArray = [
            "client_id" => $this->getClientId(),
            "trx_amount" => $this->getTrxAmount(),
            "customer_name" => $this->getCustomerName(),
            "customer_email" => $this->getCustomerEmail(),
            "customer_phone" => $this->getCustomerPhone(),
            "virtual_account" => $this->getVirtualAccount(),
            "trx_id" => $this->getTrxId(),
            "datetime_expired" => $this->getDateTimeExpired(),
            "description" => $this->getDescription(),
            "type" => $this->getTypeTransaction(),
            "billing_type" => $this->getBillingType()
        ];

        $requestHash = DclHashing::hashData($requestArray, $this->getClientId(), $this->getSecretKey());

        if (is_null($requestHash)) {
            throw new BillingException("Hashing data is fail");
        }

        $this->request = json_encode(['client_id' => $this->getClientId(), 'data' => $requestHash]);

        return $this;
    }

    /**
     * Get the value of Response
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Set the value of Response
     *
     * @param mixed response
     *
     * @return self
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    public function send()
    {
        $this->setRequest();
        $response = $this->hitBniEcollection($this->getRequest());
        return $this->responseBniEcollection($response, $this->getClientId(), $this->getSecretKey());
    }
}
