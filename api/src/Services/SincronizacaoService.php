<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

/** Importa para o MySQL os estabelecimentos e preços recebidos da Sefaz-AL. */
final class SincronizacaoService
{
    public function __construct(private PDO $database)
    {
    }

    /**
     * O adaptador do job deve normalizar os itens para as chaves abaixo.
     *
     * @param iterable<array{cnpj:string,nome:string,endereco?:string}> $estabelecimentos
     * @param iterable<array{cnpj:string,codigo_barras?:string,produto:string,preco:float|string,data_coleta:string}> $precos
     */
    public function importar(iterable $estabelecimentos, iterable $precos): void
    {
        $this->database->beginTransaction();

        try {
            $salvarEstabelecimento = $this->database->prepare(
                'INSERT INTO estabelecimentos_sefaz (cnpj, nome, endereco) VALUES (:cnpj, :nome, :endereco)
                 ON DUPLICATE KEY UPDATE nome = VALUES(nome), endereco = VALUES(endereco), atualizado_em = CURRENT_TIMESTAMP'
            );
            foreach ($estabelecimentos as $item) {
                $salvarEstabelecimento->execute([
                    'cnpj' => $item['cnpj'], 'nome' => $item['nome'], 'endereco' => $item['endereco'] ?? null,
                ]);
            }

            $salvarPreco = $this->database->prepare(
                'INSERT INTO precos_sefaz (cnpj_estabelecimento, codigo_barras, produto, preco, data_coleta)
                 VALUES (:cnpj, :codigo_barras, :produto, :preco, :data_coleta)'
            );
            foreach ($precos as $item) {
                $salvarPreco->execute([
                    'cnpj' => $item['cnpj'], 'codigo_barras' => $item['codigo_barras'] ?? null,
                    'produto' => $item['produto'], 'preco' => $item['preco'], 'data_coleta' => $item['data_coleta'],
                ]);
            }
            $this->database->commit();
        } catch (\Throwable $erro) {
            $this->database->rollBack();
            throw $erro;
        }
    }
}
