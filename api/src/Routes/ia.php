<?php

declare(strict_types=1);

use App\Controllers\IAController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;

return static function (RouteCollectorProxy $app): void {
    $app->post('/ia/sugestao-lista', [IAController::class, 'sugestaoLista']);
};
