<?php

namespace App\Helpers;

/**
 * Csv Handling Class
 *
 */
class CsvHelper {

    /**
     * Get all records from Model Resource
     */
    public static function loadData($dataSource) {

        $records = array();
        $record = null;

        $row = 0;
        if (($handle = fopen($dataSource, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $num = count($data);

                if ($row > 0) {
                    for ($c=0; $c < $num; $c++) {
                        $record[$c] = $data[$c];
                    }
                    $records[] = $record;
                    unset($record);
                }

                $row++;
            }
            fclose($handle);
        }

        return $records;
    }

    /**
     * Helper to replace currency Symbol
     */
    public static function replaceCurrencySymbol($string) {

        $currencySymbols= array('£','€','$');
        $currencyAbbr = array('GBP_','EUR_','USD_');

        return str_replace($currencySymbols,$currencyAbbr,$string);
    }
}