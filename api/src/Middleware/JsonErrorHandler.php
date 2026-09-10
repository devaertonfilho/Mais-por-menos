<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpException;
use Throwable;

final class JsonErrorHandler
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function __invoke(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $statusCode = $exception instanceof HttpException ? $exception->getCode() : 500;
        $message = $statusCode === 404
            ? 'Recurso não encontrado.'
            : 'Ocorreu um erro ao processar a solicitação.';

        $response = $this->responseFactory->createResponse($statusCode);
        $response->getBody()->write(json_encode([
            'status' => 'erro',
            'mensagem' => $message,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        return $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }
}
