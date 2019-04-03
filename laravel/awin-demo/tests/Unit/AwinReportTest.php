<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Helpers\CsvHelper;
use App\Helpers\CurrencyConverter;
use App\Helpers\CurrencyWebservice;
use App\Merchant;
use App\TransactionTable;

class AwinReportTestCase extends TestCase
{

    public function testCsvhelperLoadData()
    {
        $this->assertEquals(count(CsvHelper::loadData(\Config::get('awin.data_file_path'))), 8);
    }

    public function testCsvhelperReplaceCurrencySymbol()
    {
        $this->assertEquals(CsvHelper::replaceCurrencySymbol('£10'),'GBP_10');
    }

    public function testCurrencyConverter()
    {
        $currencyServiceMock = $this->createMock(CurrencyWebservice::class);
        $currencyServiceMock->method('getExchangeRate')->will($this->onConsecutiveCalls(1, 1.16, 1.30));

        $currencyConverter = new CurrencyConverter($currencyServiceMock);

        $this->assertEquals($currencyConverter->convert(10, 'GBP'),10);

        $this->assertEquals($currencyConverter->convert(10, 'EUR'),8.6206896551724146);

        $this->assertEquals($currencyConverter->convert(10, 'USD'),7.6923076923076916);
    }

    public function testMerchantTransactions()
    {
        $merchant = new Merchant(2);
        $transactions = $merchant->getAll();

        foreach ($transactions as $transaction) {
            $this->assertEquals($transaction->getMerchantId(),2);
        }
    }

    public function testTransactionTableTransactions()
    {
        $transactions = new TransactionTable();
        $transactionRecords = $transactions->getAll();

        foreach ($transactionRecords as $transactionRecord) {
            $this->assertContains($transactionRecord->getMerchantId(),[1, 2]);
        }
    }
}