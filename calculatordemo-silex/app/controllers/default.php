<?php

// default route
$app->get('/', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    return  $app['twig']->render('default.twig');
});

$app->get('/1.0', function () {

    return "List of avaiable methods:
  - /1.0/plus - plus for testing purposes;\n
  - /1.0/minus - minus for testing purposes;\n
  - /1.0/multiplication - multiplication for testing purposes;\n
  - /1.0/division - division for testing purposes;";
});
