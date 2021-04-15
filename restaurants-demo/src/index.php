<?php

use App\App;
use Domain\Vendor\HardcodedVendorDataSource;

require __DIR__.'/../vendor/autoload.php';

// Endpoints middleware
$middleware = [
    'search' => [
        'App\Middleware\SearchMiddleware'
    ],
];

$dataSource = new HardcodedVendorDataSource();

$app = new App($middleware, $dataSource);

$output = $app->run($argv[1], array_slice($argv, 2));

echo $output;
