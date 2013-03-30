<?php

/**
 *  Domain model
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class model {

    /**
     * Get Schedule
     *
     * @param integer
     * @return array
     */
     static public function getSchedule($months)
     {
     	     // Init
     	     $timeTables = array();

     	     // Get current month
     	     $startTs = helper::getTimestamp('1', date('m'), date('Y'));

     	     for ($m=1;$m<=$months;$m++) {

     	     	     // Set next month
     	     	    $startTs = strtotime('+1 month', $startTs);
     	     	    
     	     	    // Populate
     	     	    $timeTables[] = array(
     	     	    	    date('F', $startTs),
     	     	    	    helper::mkMeetingDate($startTs),
     	     	    	    helper::mkTestingDate($startTs)
     	     	    	    );
     	     }

    	return $timeTables;
     }

}

 