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

    /** @param array<string, string> $args */
    public function resumo(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $listaId = filter_var($args['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($listaId === false) {
            return $this->error($response, 'ID da lista inválido.', 400);
        }

        try {
            // 1. Busca orçamento da lista
            $stmt = $this->pdo->prepare('SELECT orcamento FROM listas WHERE id = :id');
            $stmt->execute(['id' => $listaId]);
            $lista = $stmt->fetch();

            if ($lista === false) {
                return $this->error($response, 'Lista não encontrada.', 404);
            }

            $orcamento = (float) ($lista['orcamento'] ?? 0.0);

            // 2. Soma preços dos itens marcados como comprados
            // Assume-se a tabela precos com o preco mais recente por produto
            $sql = "
                SELECT SUM(il.quantidade * p.preco) as total_gasto
                FROM itens_lista il
                JOIN (
                    SELECT produto_id, preco
                    FROM precos
                    WHERE id IN (SELECT MAX(id) FROM precos GROUP BY produto_id)
                ) p ON il.produto_id = p.produto_id
                WHERE il.lista_id = :lista_id AND il.comprado = 1
            ";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['lista_id' => $listaId]);
            $resumo = $stmt->fetch();

            $totalGasto = (float) ($resumo['total_gasto'] ?? 0.0);
            $diferenca = $orcamento - $totalGasto;

            return $this->json($response, [
                'status' => 'sucesso',
                'dados' => [
                    'orcamento' => $orcamento,
                    'total_gasto' => $totalGasto,
                    'diferenca' => $diferenca,
                    'dentro_do_orcamento' => $totalGasto <= $orcamento
                ]
            ]);
        } catch (PDOException) {
            return $this->error($response, 'Erro ao calcular resumo da lista.', 500);
        }
    }
}
