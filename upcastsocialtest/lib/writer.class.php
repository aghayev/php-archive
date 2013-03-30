<?php

/**
 *  The csvWriter class
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class csvWriter {

    private static $myFile = 'log/schedule.csv';

    /**
     * Write to Csv File
     *
     * @param array
     * @return string
     */
     public static function generate($columns, $rows)
     {
     	$comma = ',';
     	$newline = "\n";

        $cnt = count($columns);
	$outputData = implode($comma, $columns).$newline;

        foreach ($rows as $row) {
        $outputData .= implode($comma, $row).$newline;	
        }

        $fh = @fopen(self::$myFile, 'w');
        @fwrite($fh, $outputData);
        @fclose($fh);
        
        if (!file_exists(self::$myFile)) {
        	throw new Exception('Csv file not generated');
        }

        return self::$myFile;
     }

}