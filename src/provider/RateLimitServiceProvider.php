<?php

namespace provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use classes\RateLimit;

class RateLimitServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['ratelimit'] = $app->share(function ($app) {
            $ratelimit = new RateLimit($app);
            return $ratelimit;
        });
    }

    public function boot(Application $app)
    {
    }
}
