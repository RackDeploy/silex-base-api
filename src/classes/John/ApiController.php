<?php

namespace classes\John;

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
        $data = array("got_the_get" => true);
        return new JsonResponse($data);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function postAction(Request $request, Application $app)
    {
        $data = array("got_the_post" => true);
        return new JsonResponse($data);
    }
}
