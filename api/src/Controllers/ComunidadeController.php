<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ComunidadeController extends ApiController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Recebe os preços de uma compra e os salva na base comunitária.
     * Espera-se um body: { "mercado_id": 1, "itens": [ { "produto_id": 10, "valor": 5.50 }, ... ] }
     */
    public function compartilharPrecos(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $mercadoId = filter_var($body['mercado_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $itens = $body['itens'] ?? null;
        $usuarioId = $request->getAttribute('usuario_id');

        if ($mercadoId === false || !is_array($itens) || empty($itens) || $usuarioId === null) {
            return $this->error($response, 'mercado_id e a lista de itens são obrigatórios.', 400);
        }

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO precos (produto_id, mercado_id, valor, data_coleta, origem, usuario_id)
                 VALUES (:pid, :mid, :valor, NOW(), "comunidade", :uid)'
            );

            foreach ($itens as $index => $item) {
                $produtoId = filter_var($item['produto_id'] ?? null, FILTER_VALIDATE_INT);
                $valor = filter_var($item['valor'] ?? null, FILTER_VALIDATE_FLOAT);

                if ($produtoId === false || $valor === false) {
                    throw new \RuntimeException("Item inválido no índice {$index}: produto_id e valor são obrigatórios.");
                }

                $stmt->execute([
                    'pid' => $produtoId,
                    'mid' => $mercadoId,
                    'valor' => $valor,
                    'uid' => $usuarioId
                ]);
            }

            $this->pdo->commit();

            return $this->json($//S lC l $response, [
                'status' => 'sucesso',
                'mensagem' => 'Preços compartilhados com a comunidade com sucesso!',
                'dados' => [
                    'itens_processados' => count($itens)
                ]
            ]);
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return $this->error($response, 'Erro ao compartilhar preços: ' . $e->getMessage(), 500);
        }
    }
}
