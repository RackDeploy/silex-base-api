<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Overrides to process swagger yaml as routing config
use Symfony\Component\Config\FileLocator;
use Overrides\SwaggerYamlFileLoader;

// Required for rate limiting
use bandwidthThrottle\tokenBucket\Rate;
use bandwidthThrottle\tokenBucket\TokenBucket;
use bandwidthThrottle\tokenBucket\storage\FileStorage;

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

//////
// Expose Swagger.yaml to swagger-ui
//////
$app->get('api-docs/omni-api.yaml', function () use ($app) {
    $file = file_get_contents('../config/swagger.yaml', FILE_USE_INCLUDE_PATH);
    return $file;
});

// Apply ratelimiting
// FileStorage is the type of token storage, see here:
// http://bandwidth-throttle.github.io/token-bucket/api/namespace-bandwidthThrottle.tokenBucket.storage.html
// Rate is set with Rate(number_of_requests, in_this_time_period)
// TokenBucket is set with TokenBucket(total_tokens_allowed_in_the_bucket, rate, storage)
// Bootstrap should be ran just once when deploying to pregen tokens, this needs to be moved out.
$app->before(function (Request $request, Silex\Application $app) {
    // TODO::change this to redis or Memcached
    $storage = new FileStorage(__DIR__ . "/../var/api.bucket");
    $rate    = new Rate(20, Rate::MINUTE);
    $bucket  = new TokenBucket(10, $rate, $storage);
    // TODO::move this to deploy process
    $bucket->bootstrap(10);

    if (!$bucket->consume(1, $seconds)) {
        $data = array(
            'message' => 'Too many requests, try again in ' . ceil($seconds) . ' seconds.',
            'success' => false
        );
        $response = new JsonResponse($data, 429);
        $response->headers->set('Retry-After', ceil($seconds));
        return $response;
    }
}, Silex\Application::EARLY_EVENT);

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
