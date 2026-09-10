<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Config\Database;
use App\Services\SefazAlService;
use PDO;
use RuntimeException;
use Exception;

/**
 * Job para sincronizar preços de produtos cadastrados com a API do Economiza Alagoas (SEFAZ/AL).
 * Pode ser executado via Cron.
 */
final class SincronizarPrecosSefaz
{
    private string $logFile = 'api/logs/sefaz_sync.log';

    public function __construct(
        private SefazAlService $sefaz,
    ) {
    }

    /**
     * Executa a sincronização de preços.
     */
    public function executar(): void
    {
        $db = Database::createConnection();
        $this->log("Iniciando sincronização de preços SEFAZ/AL...");

        try {
            // 1. Busca produtos cadastrados que possuem código de barras
            $stmt = $db->query("SELECT id, codigo_barras FROM produtos WHERE codigo_barras IS NOT NULL AND codigo_barras != ''");
            $produtos = $stmt->fetchAll();

            if (empty($produtos)) {
                $this->log("Nenhum produto com código de barras encontrado para sincronizar.");
                return;
            }

            $sucessos = 0;
            $falhas = 0;

            foreach ($produtos as $produto) {
                $id = (int) $produto['id'];
                $barcode = (string) $produto['codigo_barras'];

                try {
                    // 2. Consulta preço na API SEFAZ/AL
                    // Assume-se que o endpoint 'precos' aceita { "codigo_barras": "..." }
                    $resultado = $this->sefaz->consultar('precos', ['codigo_barras' => $barcode]);

                    // 3. Extrai o preço (depende da estrutura exata da resposta da API)
                    // Assume-se que a API retorna um array de preços ou um objeto com 'preco'
                    $precoValor = $this->extrairPreco($resultado);

                    if ($precoValor !== null) {
                        // 4. Grava na tabela precos
                        $this->salvarPreco($db, $id, $precoValor);
                        $sucessos++;
                    } else {
                        $this->log("Produto {$id} ({$barcode}): Preço não encontrado na API.");
                        $falhas++;
                    }
                } catch (RuntimeException $e) {
                    if ($e->getCode() === 429) {
                        $this->log("Rate limit atingido. Aguardando 60 segundos...");
                        sleep(60);
                        // Tenta novamente uma vez
                        try {
                            $resultado = $this->sefaz->consultar('precos', ['codigo_barras' => $barcode]);
                            $precoValor = $this->extrairPreco($resultado);
                            if ($precoValor !== null) {
                                $this->salvarPreco($db, $id, $precoValor);
                                $sucessos++;
                            } else {
                                $falhas++;
                            }
                        } catch (Exception $inner) {
                            $this->log("Falha persistente no produto {$id}: " . $inner->getMessage());
                            $falhas++;
                        }
                    } else {
                        $this->log("Erro ao processar produto {$id} ({$barcode}): " . $e->getMessage());
                        $falhas++;
                    }
                } catch (Exception $e) {
                    $this->log("Erro inesperado no produto {$id}: " . $e->getMessage());
                    $falhas++;
                }
            }

            $this->log("Sincronização concluída. Sucessos: {$sucessos}, Falhas: {$falhas}.");

        } catch (Exception $e) {
            $this->log("Erro crítico na sincronização: " . $e->getMessage());
        }
    }

    /**
     * Extrai o valor do preço da resposta da API SEFAZ/AL.
     */
    private function extrairPreco(array $dados): ?float
    {
        // a API pode retornar uma lista de preços ou um único valor.
        // Aqui implementamos uma busca flexível baseada em campos comuns.

        // Caso 1: Array de resultados ('data' ou similar)
        $data = $dados['data'] ?? $dados;
        if (!is_array($data)) return null;

        if (isset($data[0])) {
            $item = $data[0];
            return $this->parsePreco($item);
        }

        return $this->parsePreco($data);
    }

    private function parsePreco(array $item): ?float
    {
        $valor = $item['preco'] ?? $item['valor'] ?? $item['valor_unitario'] ?? null;
        if ($valor === null) return null;

        return (float) filter_var($valor, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Insere ou atualiza o preço na tabela precos com origem 'sefaz'.
     */
    private function salvarPreco(PDO $db, int $produtoId, float $valor): void
    {
        $sql = "INSERT INTO precos (produto_id, preco, origem, data_consulta)
                VALUES (:pid, :preco, 'sefaz', NOW())
                ON DUPLICATE KEY UPDATE preco = :preco, data_consulta = NOW()";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':pid' => $produtoId,
            ':preco' => $valor
        ]);
    }

    /**
     * Grava mensagem no arquivo de log.
     */
    private function log(string $mensagem): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $linha = "[$timestamp] $mensagem" . PHP_EOL;
        file_put_contents($this->logFile, $linha, FILE_APPEND);
    }
}
