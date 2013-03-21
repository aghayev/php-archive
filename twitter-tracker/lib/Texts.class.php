<?php

/**
 *  Handles table :: Table Data Gateway
 *
 * @author     Imran Aghayev
 * @version    SVN: $Id$
 */
class Texts extends baseModel {

       /**
        * SelectAll
        */
	public static function selectAll($handleId) {

		$dal = self::getInstance();

		$table = strtolower(__CLASS__);
		$where = 'handle_id = '.$handleId;
		$fields = '*';
		$order = 'id DESC';

		if ($dal->select($table, $where, $fields, $order)) {
			return $dal->fetchAll();
		}

	return null;
	}

}