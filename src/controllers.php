<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Overrides to process swagger yaml as routing config
use Symfony\Component\Config\FileLocator;
use Overrides\SwaggerYamlFileLoader;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('0.0', function () use ($app) {
    return $app['twig']->render('index.html', array());
})
->bind('homepage')
;

$app->get('0.0/test/', function () use ($app) {
    $test = array('abcd' => '1234');
    return $app['twig']->render('api.json', array('test' => $test));
});

// Build routes from swagger.yaml
$app['routes'] = $app->share($app->extend('routes', function ($routes, $app) {
    $loader = new SwaggerYamlFileLoader(new FileLocator(__DIR__.'/../config'));
    $collection = $loader->load('swagger.yaml');
    $routes->addCollection($collection);

    return $routes;
}));

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html',
        'errors/'.substr($code, 0, 2).'x.html',
        'errors/'.substr($code, 0, 1).'xx.html',
        'errors/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
