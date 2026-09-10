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

/**
 * Casos de Teste Manuais:
 * (a) Sem token ou header incorreto -> Retorna 401 {"status":"erro","mensagem":"Token não fornecido"}
 * (b) Token expirado ou assinatura inválida -> Retorna 401 {"status":"erro","mensagem":"Token inválido ou expirado"}
 * (c) Token válido -> Injeta 'usuario_id' na Request e segue para o Controller.
 */
final class AuthMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->errorResponse('Token não fornecido', 401);
        }

        $token = substr($authHeader, 7);
        $secret = $_ENV['JWT_SECRET'] ?? '';

        if ($secret === '') {
            return $this->errorResponse('Erro interno: JWT_SECRET não configurado.', 500);
        }

        try {
            // Decodifica o token
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));

            // Injeta o id do usuário no request para evitar manipulação de dados de terceiros
            $request = $request->withAttribute('usuario_id', $decoded->id);

            return $handler->handle($request);
        } catch (\Exception $e) {
            return $this->errorResponse('Token inválido ou expirado', 401);
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
