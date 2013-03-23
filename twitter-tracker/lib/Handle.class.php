<?php

/**
 *  Handles table :: Row Data Gateway
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class Handle {

private $fields = array();
private $row = null;

  	/**
  	 * Class constructor
  	 */
	public function __construct($row) {
		$this->row = $row;
	}

       /**
        * Save
        */	
	public function save() 
	{
		$dal = baseModel::getInstance();

		$table = strtolower(__CLASS__).'s';
		$where = 'id = '.$this->row->id;
		return $dal->update($table, $this->fields, $where);
	}

  /**
   * Setter
   */	
    public function __call($m, $a)
    {
    	    $this->fields[substr(strtolower($m), 3)] = $a[0];    	    
    }

}