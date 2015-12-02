<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

//////
// Testing routes
//////
$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array());
})
->bind('homepage')
;

$app->get('0.0/test/', function () use ($app) {
    $test = array('abcd' => '1234');
    return $app['twig']->render('api.json', array('test' => $test));
});

// Apply ratelimiting
// FileStorage is the type of token storage, see here:
// http://bandwidth-throttle.github.io/token-bucket/api/namespace-bandwidthThrottle.tokenBucket.storage.html
// Rate is set with Rate(number_of_requests, in_this_time_period)
// TokenBucket is set with TokenBucket(total_tokens_allowed_in_the_bucket, rate, storage)
// Bootstrap should be ran just once when deploying to pregen tokens, this needs to be moved out.
$app->before(function (Request $request, Silex\Application $app) {
    //$app['ratelimit']->connect();
    $app['ratelimit']->setUser('tom.cooper');
    if (!$app['ratelimit']->consumeAll($seconds)) {
        $data = array(
            'message' => 'Too many requests, try again in ' . ceil($seconds) . ' seconds.',
            'retry-after' => ceil($seconds)
        );
        $rendered = $app['twig']->render('api.json', array('data' => $data));
        $rendered = preg_replace('/[\t]+/', '', preg_replace('/[\r\n]+/', '', $rendered));
        $rendered = json_decode($rendered);
        $response = new JsonResponse($rendered, 429);
        $response->headers->set('Retry-After', ceil($seconds));
        return $response;
    }
}, Silex\Application::EARLY_EVENT);

$app->after(function (Request $request, Response $response, Silex\Application $app) {
    // apply the current reponse code and status to the return envelope
    $message = $response->getContent();
    $message = preg_replace('/[\t]+/', '', preg_replace('/[\r\n]+/', '', $message));
    $message = json_decode($message);
    // make sure the response is a standard envelope
    if (is_object($message) and property_exists($message, 'meta')) {
        $code = $response->getStatusCode();
        $message->meta->code = $code;
        if (substr($code, 0, 1) === '2') {
            $message->meta->success = true;
        }
        $response->setContent(json_encode($message));
    }
});

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
