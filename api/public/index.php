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

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(new JsonErrorHandler($app->getResponseFactory()));

try {
    $app->run();
} catch (\Throwable $e) {
    error_log("[" . date('Y-m-d H:i:s') . "] FATAL ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", 3, __DIR__ . '/../logs/error.log');
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack Trace:\n" . $e->getTraceAsString() . "\n";
}
