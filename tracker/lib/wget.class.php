<?php

class wget {

	static public function fetch($handleObj) {

	$params['screen_name'] = $handleObj->handle;

	if ($handleObj->last_text_id != null 
		&& $handleObj->last_text_id > 0) {
	$params['since_id'] = $handleObj->last_text_id;
	}

	$uri = http_build_query($params);
		
	return json_decode(file_get_contents('http://twitter.com/statuses/user_timeline.json?'.$uri));
	}

}

