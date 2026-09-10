<?php

declare(strict_types=1);

use App\Config\ContainerFactory;
use App\Middleware\CorsMiddleware;
use App\Middleware\JsonErrorHandler;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

require dirname(__DIR__) . '/vendor/autoload.php';

Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

$container = ContainerFactory::create();
AppFactory::setContainer($container);
$app = AppFactory::create();

(require dirname(__DIR__) . '/src/Routes/api.php')($app);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new CorsMiddleware($app->getResponseFactory()));

$errorMiddleware = $app->addErrorMiddleware(false, false, false);
$errorMiddleware->setDefaultErrorHandler(new JsonErrorHandler($app->getResponseFactory()));

$app->run();
