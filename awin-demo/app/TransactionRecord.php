<?php

namespace App;
use App\Helpers\CsvHelper;

/**
 * Class TransactionRecord
 * @package App
 *
 * This class is Transaction Data Object.
 */
class TransactionRecord
{
    private $merchantId = null;
    private $date = null;
    private $currency;
    private $amount;

    /**
     * Setup Transaction Record
     */
    public function setRecord($record) {

        if (!isset($record[0])) {
         throw new \Exception('Merchant Id is not present within record');
        }

        $this->merchantId = $record[0];
        $this->date = $record[1];
        $record[2] = CsvHelper::replaceCurrencySymbol($record[2]);
        $currencyParts = explode('_',$record[2]);
        $this->currency = $currencyParts[0];
        $this->amount = $currencyParts[1];
    }

    public function getMerchantId() {
        return $this->merchantId;
    }

    public function getDate() {
        return $this->date;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function setAmount($amount) {
        $this->amount = $amount;
    }

    public function setCurrency($currency) {
        $this->currency = $currency;
    }
}