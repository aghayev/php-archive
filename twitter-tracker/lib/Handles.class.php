<?php

/**
 *  Handles table :: Table Data Gateway
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class Handles extends baseModel {

       /**
        * SelectAll
        */
	public static function selectAll() {
	
		$dal = self::getInstance();

		$table = strtolower(__CLASS__);
		$where = 'status = 1';

		if ($dal->select($table, $where)) {
			return $dal->fetchAll();
		}

	return null;
	}

}