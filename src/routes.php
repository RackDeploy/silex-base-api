<?php

// Overrides to process swagger yaml as routing config
use Symfony\Component\Config\FileLocator;
use Overrides\SwaggerYamlFileLoader;

// Build routes from swagger.yaml
$app['routes'] = $app->share($app->extend('routes', function ($routes, $app) {
    $loader = new SwaggerYamlFileLoader(new FileLocator(__DIR__.'/../config'));
    $collection = $loader->load('swagger.yaml');
    $routes->addCollection($collection);

    return $routes;
}));
