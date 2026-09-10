<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\SefazAl;
use RuntimeException;

/**
 * Cliente HTTP para a API do Economiza Alagoas (SEFAZ/AL).
 * Implementa requisições POST conforme exigido pelo Manual do Desenvolvedor.
 */
final class SefazAlService
{
    /**
     * Realiza uma requisição POST para a API da SEFAZ/AL.
     *
     * @param string $recurso Caminho do endpoint (ex: 'precos', 'estabelecimentos').
     * @param array<string, mixed> $body Dados a serem enviados no corpo da requisição.
     * @return array<string, mixed> Resposta decodificada da API.
     * @throws RuntimeException Em caso de erro na requisição ou resposta inválida.
     */
    public function consultar(string $recurso, array $body = []): array
    {
        $baseUrl = SefazAl::baseUrl();
        if ($baseUrl === '') {
            throw new RuntimeException('SEFAZ_AL_API_URL não foi configurada.');
        }

        $url = $baseUrl . '/' . ltrim($recurso, '/');
        $token = SefazAl::token();

        if ($token === '') {
            throw new RuntimeException('O token SEFAZ_TOKEN não foi configurado no ambiente.');
        }

        $payload = json_encode($body);
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'AppToken: ' . $token,
        ];

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $response = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($response === false) {
            throw new RuntimeException("Erro de rede na API SEFAZ/AL: {$error}");
        }

        if ($status === 429) {
            throw new RuntimeException('Limite de requisições (Rate Limit) atingido na API SEFAZ/AL.', 429);
        }

        if ($status < 200 || $status >= 300) {
            throw new RuntimeException("A API SEFAZ/AL retornou erro HTTP {$status}: {$response}");
        }

        $dados = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Resposta da API SEFAZ/AL não é um JSON válido.');
        }

        return $dados;
    }
}
