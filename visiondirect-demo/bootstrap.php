<?php

// bootstrap.php

define('LIB', __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR);

spl_autoload_register(function ($class) {

	$class = str_replace('Lib\\', '', $class);
	$file = LIB . str_replace('\\', '/', $class) . '.php';

	if (file_exists($file)) {
	require $file;
	}
});
