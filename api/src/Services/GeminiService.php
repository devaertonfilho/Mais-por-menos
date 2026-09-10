<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

/**
 * Serviço de integração com a API do Google Gemini para gerar sugestões inteligentes de listas.
 */
final class GeminiService
{
    private string $apiKey;
    private string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

    public function __construct(private readonly PDO $pdo)
    {
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? '';
    }

    /**
     * Gera uma sugestão textual para uma lista de compras baseada em seus itens e preços.
     */
    public function gerarSugestaoLista(int $listaId): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY não configurada no ambiente.');
        }

        $contexto = $this->montarContexto($listaId);
        if ($contexto === null) {
            throw new RuntimeException('Não foi possível obter dados suficientes da lista para gerar sugestão.');
        }

        $prompt = "Você é um assistente de economia doméstica especialista. Analise a seguinte lista de compras e o histórico de preços e forneça uma sugestão curta e útil (máximo 3 frases) para o usuário economizar. \n\nContexto:\n{$contexto}\n\nSugestão:";

        return $this->callGemini($prompt);
    }

    private function montarContexto(int $listaId): ?string
    {
        // 1. Busca itens da lista e seus preços atuais
        $sql = "
            SELECT p.id as produto_id, p.nome, il.quantidade, p.preco as preco_atual
            FROM itens_lista il
            JOIN produtos p ON il.produto_id = p.id
            JOIN precos pr ON p.id = pr.produto_id
            WHERE il.lista_id = :lista_id
            AND pr.id IN (SELECT MAX(id) FROM precos GROUP BY produto_id)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['lista_id' => $listaId]);
        $itens = $stmt->fetchAll();

        if (empty($itens)) return null;

        $texto = "Itens da Lista:\n";
        foreach ($itens as $item) {
            // Busca histórico simples (média dos últimos 5 preços)
            $histStmt = $this->pdo->prepare("
                SELECT AVG(preco) as media
                FROM (SELECT preco FROM precos WHERE produto_id = :pid ORDER BY data_consulta DESC LIMIT 5) as sub
            ");
            $histStmt->execute(['pid' => $item['produto_id'] ?? 0]); // Note: I need the product_id here, I should update the query above
            $media = $histStmt->fetchColumn();

            $texto .= "- {$item['nome']} (Qt: {$item['quantidade']}, Preço Atual: R\$ {$item['preco_atual']}, Média Histórica: R\$ " . ($media ?: $item['preco_atual']) . ")\n";
        }

        return $texto;
    }

    private function callGemini(string $prompt): string
    {
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        $url = "{$this->apiUrl}?key={$this->apiKey}";

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($response === false || $status !== 200) {
            throw new RuntimeException("Erro ao chamar API Gemini (Status {$status}): {$response}");
        }

        $dados = json_decode($response, true);

        // Estrutura de resposta do Gemini: candidates[0].content.parts[0].text
        return $dados['candidates'][0]['content']['parts'][0]['text'] ?? 'Não foi possível gerar uma sugestão no momento.';
    }
}
