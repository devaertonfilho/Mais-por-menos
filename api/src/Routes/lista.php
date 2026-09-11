<?php

declare(strict_types=1);

use App\Controllers\ListaController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;

return static function (RouteCollectorProxy $app): void {
    $app->get('/listas/{id}/resumo', [ListaController::class, 'resumo']);
};
