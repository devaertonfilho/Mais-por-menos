<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class CorsMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly ResponseFactoryInterface $responseFactory)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $request->getMethod() === 'OPTIONS'
            ? $this->responseFactory->createResponse(204)
            : $handler->handle($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization');
    }
}
