<?php

define(DBUSER, 'root');
define(DBPASS, 'zN4LFdVK');
define(DBBASE, 'tracker');
define(DBHOST, '127.0.0.1');
define(DBPORT, 3306);

define(CONTROLLER_PATH, '../controller');

ini_set('include_path', get_include_path() . PATH_SEPARATOR . getcwd().'/../template');

// Autoloading single directory level
define(LIB_PATH, '../lib');
$libs = new DirectoryIterator(LIB_PATH);

// Had issues autoloading this class
include_once getcwd().'/../lib/baseModel.class.php';

foreach ($libs as $lib) {
	if (!$lib->isDot()) {
		include_once LIB_PATH.'/'.$lib;
	}
}
