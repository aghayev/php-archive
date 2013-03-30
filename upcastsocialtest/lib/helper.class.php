<?php

/**
 *  Helpers
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class helper {

     /***
      * Mktime wrapper
      *
      * @param string
      * @param string
      * @param string
      * @return integer
      */
      public static function getTimestamp($day, $month, $year)
      {	
      	  $hour = $min = $sec = 0;
	  $day = (int) $day;
	  $month = (int) $month;
	  $year = (int) $year;

	  return mktime($hour, $min, $sec, $month, $day, $year);
      }

    /***
      * Make meeting date
      *
      * This meeting is planned for the 14th of every month.
      * If the 14th falls on a Saturday or Sunday then
      * it should be arranged for the following Monday 
      * 
      * @param integer
      * @return string
      */
      public static function mkMeetingDate($startTs)
      {
      	      $midTs = self::getTimestamp('14', date('m', $startTs), date('Y', $startTs));

      	      $dw = date('w', $midTs);

      	      switch ($dw) {
      	      case 0:
      	      case 6:
      	      	   $midTs =  strtotime('next monday', $midTs);
      	      }

      	      return date('d/m/Y', $midTs);
      }

    /***
      * Make testing date
      *
      * Testing should be done on the last day of the month
      * If the testing day falls on a Friday, Saturday or Sunday 
      * then testing should be set for the previous Thursday
      * 
      * @param integer
      * @return string
      */
      public static function mkTestingDate($startTs)
      {
      	      $lastTs = self::getTimestamp(date('t', $startTs), date('m', $startTs), date('Y', $startTs));

      	      $dw = date('w', $lastTs);

      	      switch ($dw)
      	      {
      	      case 0:
      	      case 5;
      	      case 6;
      	      	      $lastTs = strtotime('-3 day', $lastTs);
      	      break;
      	      }
      	      
      	      return date('d/m/Y', $lastTs);
      }

}