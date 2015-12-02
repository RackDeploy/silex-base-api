<?php

use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use provider\RateLimitServiceProvider;
use provider\PHPRedisServiceProvider;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app['twig'] = $app->share($app->extend('twig', function ($twig, $app) {
    // Pre-Set Pagination object
    $pagination = (object) array ();
    $twig->addGlobal('pagination', $pagination);
    return $twig;
}));
$app->register(new RateLimitServiceProvider());
$app->register(new PHPRedisServiceProvider(), array(
    'redis.host' => '127.0.0.1',
    'redis.port' => 6379,
    'redis.timeout' => 30,
    'redis.persistent' => true,
    'redis.serializer.igbinary' => false, // use igBinary serialize/unserialize
    'redis.serializer.php' => false, // use built-in serialize/unserialize
    'redis.prefix' => 'myprefix',
    'redis.database' => '0'
));

return $app;
