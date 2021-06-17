<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\App;

$app = new App();

$output = $app->run();

echo $output;