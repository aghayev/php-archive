<?php

require_once ( '../config/bootstrap.php');

$handles = Handles::selectAll();    	    

foreach ($handles as $handle) {

$handleObj = (object) $handle;

$statuses = wget::fetch($handleObj);

	if (is_array($statuses)) {

		$index = 0;
		foreach ($statuses as $status) {

			if ($index == 0) {
			$handle = new Handle($handleObj);
			$handle->setLast_Text_Id($status->id_str);
			$handle->save();
			}

			$text = new Text();
			$text->setId($status->id_str);
			$text->setHandle_Id($handleObj->id);			
			$text->setText($status->text);
			$text->save();

		$index++;	
		}
	}
}