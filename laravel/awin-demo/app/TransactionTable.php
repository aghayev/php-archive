<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\CsvHelper;
use App\Helpers\CurrencyWebservice;
use App\Helpers\CurrencyConverter;

/**
 * Class TransactionTable
 * @package App
 *
 * This class retrieves transactions from Resource database or any other storage
 */
class TransactionTable extends Model
{
    private $collection = array();

    public function getAll() {

        $dataSource = \Config::get('awin.data_file_path');

        if ($dataSource == false) {
            throw new \Exception('Unable to find data_file_path');
        }

        $records = CsvHelper::loadData($dataSource);

        foreach ($records as $record) {
           $transactionRecord = new TransactionRecord();
           $transactionRecord->setRecord($record);

            $currencyConverter = new CurrencyConverter(new CurrencyWebservice());
            $newAmount = $currencyConverter->convert($transactionRecord->getAmount(), $transactionRecord->getCurrency());
            $transactionRecord->setAmount($newAmount);
            $transactionRecord->setCurrency($newAmount);

            $this->collection[] = $transactionRecord;
        }

        return $this->collection;
    }
}