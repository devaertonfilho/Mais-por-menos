<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class UsuarioController extends ApiController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $nome = $this->requiredString($body, 'nome');
        $email = $this->requiredString($body, 'email');
        $senha = $this->requiredString($body, 'senha');

        if ($nome === null || $email === null || $senha === null) {
            return $this->error($response, 'Nome, email e senha são obrigatórios.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->error($response, 'O email informado é inválido.');
        }

        try {
            $statement = $this->pdo->prepare(
                'INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :senha_hash)'
            );
            $statement->execute([
                'nome' => $nome,
                'email' => $email,
                'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
            ]);

            return $this->json($response, [
                'status' => 'sucesso',
                'mensagem' => 'Usuário cadastrado com sucesso.',
                'dados' => ['id' => (int) $this->pdo->lastInsertId(), 'nome' => $nome, 'email' => $email],
            ], 201);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return $this->error($response, 'Já existe um usuário com este email.', 409);
            }

            return $this->error($response, 'Não foi possível cadastrar o usuário.', 500);
        }
    }
}

    public function login(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $email = $this->requiredString($body, 'email');
        $senha = $this->requiredString($body, 'senha');

        if ($email === null || $senha === null) {
            return $this->error($response, 'Email e senha são obrigatórios.', 400);
        }

        try {
            $stmt = $this->pdo->prepare('SELECT id, email, senha_hash FROM usuarios WHERE email = :email');
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($senha, $user['senha_hash'])) {
                return $this->error($response, 'Email ou senha incorretos.', 401);
            }

            $secret = $_ENV['JWT_SECRET'] ?? '';
            if ($secret === '') {
                return $this->error($response, 'Erro interno: JWT_SECRET não configurado.', 500);
            }

            $payload = [
                'iss' => 'mais-por-menos-api',
                'sub' => (string) $user['id'],
                'id'  => (int) $user['id'],
                'email' => $user['email'],
                'iat' => time(),
                'exp' => time() + (60 * 60 * 24), // 24 horas
            ];

            $jwt = \Firebase\JWT\JWT::encode($payload, $secret, 'HS256');

            return $this->json($response, [
                'status' => 'sucesso',
                'mensagem' => 'Login realizado com sucesso.',
                'dados' => [
                    'token' => $jwt,
                    'usuario' => [
                        'id' => (int) $user['id'],
                        'email' => $user['email'],
                    ]
                ],
            ]);
        } catch (\Exception $exception) {
            return $this->error($response, 'Erro ao processar login: ' . $exception->getMessage(), 500);
        }
    }
}
