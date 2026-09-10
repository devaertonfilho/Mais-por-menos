<?php

declare(strict_types=1);

use App\Controllers\IAController;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

return static function (App $app): void {
    $app->post('/ia/sugestao-lista', [IAController::class, 'sugestaoLista']);
};
