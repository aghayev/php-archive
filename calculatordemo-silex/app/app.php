<?php

require_once __DIR__.'/bootstrap.php';

// init Silex app
$app = new Mrandmrssmith\Application();

// Configuration
require_once __DIR__.'/config.php';

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app['twig']->addFunction(new \Twig_SimpleFunction('path', function($url) use ($app) {
    return $app['url_generator']->generate($url);
}));

$app->before(function (Symfony\Component\HttpFoundation\Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->after(function (Symfony\Component\HttpFoundation\Request $request,
                      Symfony\Component\HttpFoundation\Response $response) {
    $response->headers->set('Access-Control-Allow-Origin', '*');
});

// if route not found, redirect home
$app->error(function (\Exception $e, $code) use ($app) {
    if (404 === $code) {
        return $app->redirect('/');
    }
});

require(__DIR__. '/controllers/default.php');
require(__DIR__ . '/controllers/calculator.php');

return $app;