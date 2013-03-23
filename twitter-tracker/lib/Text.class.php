<?php

/**
 *  Texts table :: Row Data Gateway
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class Text {

private $fields = array();

       /**
        * Save
        */
	public function save() 
	{
		$dal = baseModel::getInstance();

		$table = strtolower(__CLASS__).'s';
		return $dal->insert($table, $this->fields);		
	}

   /**
    * Setter
    */	
    public function __call($m, $a)
    {
    	    $this->fields[substr(strtolower($m), 3)] = $a[0];    	    
    }

}