<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ListaController extends ApiController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function store(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $nome = $this->requiredString($body, 'nome');
        $usuarioId = filter_var($body['usuario_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($nome === null || $usuarioId === false) {
            return $this->error($response, 'Nome e usuario_id válido são obrigatórios.');
        }

        try {
            $userStatement = $this->pdo->prepare('SELECT id FROM usuarios WHERE id = :id');
            $userStatement->execute(['id' => $usuarioId]);

            if ($userStatement->fetch() === false) {
                return $this->error($response, 'Usuário não encontrado.', 404);
            }

            $statement = $this->pdo->prepare('INSERT INTO listas (usuario_id, nome) VALUES (:usuario_id, :nome)');
            $statement->execute(['usuario_id' => $usuarioId, 'nome' => $nome]);

            return $this->json($response, [
                'status' => 'sucesso',
                'mensagem' => 'Lista criada com sucesso.',
                'dados' => ['id' => (int) $this->pdo->lastInsertId(), 'usuario_id' => $usuarioId, 'nome' => $nome],
            ], 201);
        } catch (PDOException) {
            return $this->error($response, 'Não foi possível criar a lista.', 500);
        }
    }

    /** @param array<string, string> $args */
    public function addItem(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $listaId = filter_var($args['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $body = $this->body($request);
        $produtoId = filter_var($body['produto_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $quantidade = $body['quantidade'] ?? 1;

        if ($listaId === false || $produtoId === false || !is_numeric($quantidade) || (float) $quantidade <= 0) {
            return $this->error($response, 'produto_id e quantidade positiva são obrigatórios.');
        }

        try {
            $listStatement = $this->pdo->prepare('SELECT id FROM listas WHERE id = :id');
            $listStatement->execute(['id' => $listaId]);

            if ($listStatement->fetch() === false) {
                return $this->error($response, 'Lista não encontrada.', 404);
            }

            $productStatement = $this->pdo->prepare('SELECT id FROM produtos WHERE id = :id');
            $productStatement->execute(['id' => $produtoId]);

            if ($productStatement->fetch() === false) {
                return $this->error($response, 'Produto não encontrado.', 404);
            }

            $statement = $this->pdo->prepare(
                'INSERT INTO itens_lista (lista_id, produto_id, quantidade) VALUES (:lista_id, :produto_id, :quantidade)'
            );
            $statement->execute([
                'lista_id' => $listaId,
                'produto_id' => $produtoId,
                'quantidade' => $quantidade,
            ]);

            return $this->json($response, [
                'status' => 'sucesso',
                'mensagem' => 'Produto adicionado à lista.',
                'dados' => [
                    'id' => (int) $this->pdo->lastInsertId(),
                    'lista_id' => $listaId,
                    'produto_id' => $produtoId,
                    'quantidade' => (float) $quantidade,
                ],
            ], 201);
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                return $this->error($response, 'Este produto já está na lista.', 409);
            }

            return $this->error($response, 'Não foi possível adicionar o produto à lista.', 500);
        }
    }
}
