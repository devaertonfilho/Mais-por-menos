<?php

declare(strict_types=1);

use App\Controllers\ListaController;
use App\Controllers\ProdutoController;
use App\Controllers\UsuarioController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return static function (App $app): void {
    $app->get('/ping', function (
        ServerRequestInterface $request,
        ResponseInterface $response
    ): ResponseInterface {
        $response->getBody()->write(json_encode(['status' => 'ok'], JSON_THROW_ON_ERROR));

        return $response->withHeader('Content-Type', 'application/json');
    });

    $app->post('/usuarios', [UsuarioController::class, 'store']);
    $app->post('/listas', [ListaController::class, 'store']);
    $app->get('/produtos/{codigo_barras}', [ProdutoController::class, 'showByBarcode']);
    $app->post('/listas/{id}/itens', [ListaController::class, 'addItem']);
};
