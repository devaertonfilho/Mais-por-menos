<?php

declare(strict_types=1);

namespace App\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;
use RuntimeException;

final class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->errorResponse('Token de autenticação ausente ou inválido.', 401);
        }

        $token = substr($authHeader, 7);
        $secret = $_ENV['JWT_SECRET'] ?? '';

        if ($secret === '') {
            return $this->errorResponse('Erro interno: JWT_SECRET não configurado.', 500);
        }

        try {
            // Decodifica o token
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // Injeta o id do usuário no request para uso posterior nos controllers
            $request = $request->withAttribute('userId', $decoded->id);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->errorResponse('Sessão expirada ou token inválido.', 401);
        }
    }

    private function errorResponse(string $message, int $code): ResponseInterface
    {
        $response = new Response();
        $response->getBody()->write(json_encode([
            'status' => 'erro',
            'mensagem' => $message
        ], JSON_THROW_ON_ERROR));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($code);
    }
}
