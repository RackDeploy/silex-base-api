<?php

namespace App;

use Silex\WebTestCase;
use Mocks\classes\RateLimitMock;

class SetupApp extends WebTestCase
{
    protected $app;

    public function createApplication()
    {
        // app.php must return an Application instance
        require __DIR__.'/../../src/app.php';

        $app['debug'] = true;
        unset($app['exception_handler']);

        $this->app = $app;

        $rateMock = new RateLimitMock();
        $this->mockProvider('ratelimit', $rateMock->setRateLimited(false));

        require __DIR__.'/../../config/prod.php';
        require __DIR__.'/../../src/routes.php';
        require __DIR__.'/../../src/controllers.php';

        return $app;
    }

    public function mockProvider($name, $mock)
    {
        $this->app[$name] = $mock;
    }

    public function successfulResponse($data, $code, $pagination = '{}')
    {
        return '{"data":' . $data . ',"meta":{"code":' . $code . ',"success":true},"pagination":' . $pagination . '}';
    }

    public function failedResponse($data, $code, $pagination = '{}')
    {
        return '{"data":' . $data . ',"meta":{"code":' . $code . ',"success":false},"pagination":' . $pagination . '}';
    }
}
