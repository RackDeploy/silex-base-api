<?php

namespace John;

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
        $data = array("gay" => true, "likes_men" => true, "dumb_dog" => true, "pink_slip" => "priceless");
        return new JsonResponse($data);
    }
}
