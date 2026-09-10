<?php

declare(strict_types=1);

namespace App\Controllers;

use PDO;
use PDOException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ProdutoController extends ApiController
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /** @param array<string, string> $args */
    public function showByBarcode(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $codigoBarras = trim($args['codigo_barras'] ?? '');

        if ($codigoBarras === '') {
            return $this->error($response, 'Código de barras é obrigatório.');
        }

        try {
            $statement = $this->pdo->prepare(
                'SELECT id, codigo_barras, nome, marca, categoria, peso
                 FROM produtos
                 WHERE codigo_barras = :codigo_barras
                 LIMIT 1'
            );
            $statement->execute(['codigo_barras' => $codigoBarras]);
            $produto = $statement->fetch();

            if ($produto === false) {
                return $this->error($response, 'Produto não encontrado.', 404);
            }

            return $this->json($response, [
                'status' => 'sucesso',
                'dados' => $produto,
            ]);
        } catch (PDOException) {
            return $this->error($response, 'Não foi possível consultar o produto.', 500);
        }
    }
}
