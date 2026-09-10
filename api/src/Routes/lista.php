<?php

declare(strict_types=1);

use App\Controllers\ListaController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return static function (App $app): void {
    $app->get('/listas/{id}/resumo', [ListaController::class, 'resumo']);
};
