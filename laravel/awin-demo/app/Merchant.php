<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Merchant
 * @package App
 */
class Merchant extends Model
{
    private $merchantId = null;

    /**
     * Assign MerchantId via Constuctor
     *
     * @param array $merchantId
     */
    public function __construct($merchantId) {
        $this->merchantId = $merchantId;
    }

    /**
     * Get All Transactions For MerchantId Only
     *
     * @return array
     */
    public function getAll() {

        $transactions = new TransactionTable();
        $transactionRecords = $transactions->getAll();

        $row = 0;
        foreach ($transactionRecords as $transactionRecord) {
            if ($transactionRecord->getMerchantId() != $this->merchantId) {
                unset($transactionRecords[$row]);
            }
            $row++;
        }

        return $transactionRecords;
    }
}