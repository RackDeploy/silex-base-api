<?php

// configure your app for the production environment

$app['twig.path'] = array(__DIR__.'/../templates');
$app['twig.options'] = array('cache' => __DIR__.'/../var/cache/twig');

// Expose Swagger.yaml to swagger-ui
$app->get('api-docs/omni-api.yaml', function () use ($app) {
    $file = file_get_contents('../config/swagger.yaml', FILE_USE_INCLUDE_PATH);
    return $file;
});
