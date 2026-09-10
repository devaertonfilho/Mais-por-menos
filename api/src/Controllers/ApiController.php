<?php

declare(strict_types=1);

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class ApiController
{
    /** @return array<string, mixed> */
    protected function body(ServerRequestInterface $request): array
    {
        $body = $request->getParsedBody();

        return is_array($body) ? $body : [];
    }

    /** @param array<string, mixed> $payload */
    protected function json(ResponseInterface $response, array $payload, int $statusCode = 200): ResponseInterface
    {
        $response->getBody()->write(json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE));

        return $response
            ->withHeader('Content-Type', 'application/json; charset=utf-8')
            ->withStatus($statusCode);
    }

    protected function error(ResponseInterface $response, string $message, int $statusCode = 400): ResponseInterface
    {
        return $this->json($response, [
            'status' => 'erro',
            'mensagem' => $message,
        ], $statusCode);
    }

    /** @param array<string, mixed> $body */
    protected function requiredString(array $body, string $field): ?string
    {
        $value = $body[$field] ?? null;

        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
