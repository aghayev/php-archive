<?php

/**
 *  This code flow and creates pay dates for each month in the current year.
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class Salary {

    /***
      * Make middle of month
      * 
      * @return integer
      */
      private function makeMiddleDate($timestamp)
      {
      	      	$currentMonth = date('m', $timestamp);
      	      	$currentYear = date('Y', $timestamp);

      	      	return mktime(0, 0, 0, $currentMonth, 15, $currentYear);
      }

    /***
      * Make last date of month
      * 
      * @return integer
      */
      private function makeLastDate($timestamp)
      {
      	      	$currentMonth = date('m', $timestamp);
      	      	$lastDay = date('t', $timestamp);
      	      	$currentYear = date('Y', $timestamp);

      	      	return mktime(0, 0, 0, $currentMonth, $lastDay, $currentYear);
      }
	
    /***
      * Get Last Business Day of the Month
      * 
      * @return string
      */
      private function getLastBusinessDay($timestamp)
      {
      	      $dayofWeek = date("l", $timestamp);
      	      $dayofWeek = strtolower($dayofWeek);

      		if ($dayofWeek == "saturday")
      		{
      		$day = strtotime( '-1 day', $timestamp);       			
      		}
      		else if ($dayofWeek == "sunday")
      		{
      		$day = strtotime( '-2 day', $timestamp); 
      		}
      		else {
      		$day = 	$timestamp;
      		}

      return date ('d/m/Y', $day);      
      }

    /**
     * Generate Payrolls
     *
     * @return Array
     */
     public function getPayrolls($year)
     {
     	     $payrolls = array();
     	     $start = $month = strtotime($year.'-01-01');
     	     $end = strtotime($year.'-12-01');

     	     while($month <= $end)
     	     {
     	     	     //name
     	     	     $payroll[0] = date('F', $month);
     	     	     
     	     	     //1st
     	     	     $payroll[1] = date('d/m/Y', strtotime('next monday', $month));

     	     	     //15th
     	     	     $middledate = $this->makeMiddleDate($month);
     	     	     $payroll[2] = date('d/m/Y', strtotime('next monday', $middledate));

     	     	     //lastdate
     	     	     $lastdate = $this->makeLastDate($month);
     	     	     $payroll[3] =  $this->getLastBusinessDay($lastdate);

     	     	     $payrolls[] = $payroll;
     	     	     $month = strtotime("+1 month", $month);
     	     }

    	return $payrolls;
     }

    /**
     * Print output
     *
     * @return void
     */
     public function output($payrolls)
     {
     	$newline = "\n";
     	$comma = ',';

        $output = '';
        $columns = array('Month Name', '1st expenses day', '2nd expenses day', 'Salary day');
	$output = implode($comma, $columns).$newline;
	
        $cnt = count($columns);
        foreach ($payrolls as $payroll) {
        $output .= implode($comma, $payroll).$newline;	
        }

        print $output;
     }

}

$salary = new Salary();
$payrolls = $salary->getPayrolls(date('Y'));
$salary->output($payrolls);


