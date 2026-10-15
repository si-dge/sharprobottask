<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;
use Tuupola\Middleware\JwtAuthentication;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = require dirname(__DIR__) . '/config/container.php';
AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$settings = require dirname(__DIR__) . '/config/settings.php';
$app->add(new JwtAuthentication([
    'secret' => $settings['auth']['jwt_secret'],
    'secure' => false,
    'path' => ['/api'],
    'ignore' => ['/api/health', '/api/login', '/api/logout'],
    'attribute' => 'token',
]));
$app->addErrorMiddleware(true, true, true);
require dirname(__DIR__) . '/config/routes.php';
$app->run();
