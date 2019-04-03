<?php

$app->post('/1.0/plus', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $argument1 = $request->request->get('argument1');
    $argument2 = $request->request->get('argument2');
    
    $result = Mrandmrssmith\Calculator::plus(array($argument1,$argument2));
 
    return  $app['twig']->render('plus.twig', array('plus' => 
        array('result' => $result)));
});

$app->post('/1.0/minus', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $argument1 = $request->request->get('argument1');
    $argument2 = $request->request->get('argument2');
    
    $result = Mrandmrssmith\Calculator::minus(array($argument1,$argument2));
 
    return  $app['twig']->render('minus.twig', array('minus' => 
        array('result' => $result)));
});

$app->post('/1.0/multiplication', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $argument1 = $request->request->get('argument1');
    $argument2 = $request->request->get('argument2');
    
    $result = Mrandmrssmith\Calculator::multiplication(array($argument1,$argument2));
 
    return  $app['twig']->render('multiplication.twig', array('multiplication' => 
        array('result' => $result)));
});

$app->post('/1.0/division', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {

    $argument1 = $request->request->get('argument1');
    $argument2 = $request->request->get('argument2');
    
    $result = Mrandmrssmith\Calculator::division(array($argument1,$argument2));
 
    return  $app['twig']->render('division.twig', array('division' => 
        array('result' => $result)));
});