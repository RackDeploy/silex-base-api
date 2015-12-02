<?php

namespace App;

use Silex\WebTestCase;
use Mocks\classes\RateLimitMock;

class SetupApp extends WebTestCase
{
    public function createApplication()
    {
        $rateMock = new RateLimitMock();

        // app.php must return an Application instance
        require __DIR__.'/../../src/app.php';
        $app['debug'] = true;
        unset($app['exception_handler']);

        $app['ratelimit'] = $rateMock->getBase();
        require __DIR__.'/../../config/prod.php';
        require __DIR__.'/../../src/routes.php';
        require __DIR__.'/../../src/controllers.php';

        return $app;
    }

    public function successfulResponse($data, $code)
    {
        return '{"data":' . $data . ',"meta":{"code":' . $code . ',"success":true},"pagination":{}}';
    }

    public function failedResponse($data, $code)
    {
        return '{"data":' . $data . ',"meta":{"code":' . $code . ',"success":false},"pagination":{}}';
    }
}
