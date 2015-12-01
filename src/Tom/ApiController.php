<?php

namespace Tom;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiController
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function initAction(Request $request, Application $app)
    {
        $data = array ("success" => true);
        return $app['twig']->render('api.json', array('data' => $data));
    }
}
