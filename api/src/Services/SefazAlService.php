<?php

declare(strict_types=1);

namespace App\Services;

use App\Config\SefazAl;
use RuntimeException;

/** Cliente HTTP da fonte pública Economiza Alagoas/Sefaz-AL. */
final class SefazAlService
{
    /**
     * @return array<string, mixed>
     */
    public function consultar(string $recurso, array $query = []): array
    {
        $baseUrl = SefazAl::baseUrl();
        if ($baseUrl === '') {
            throw new RuntimeException('SEFAZ_AL_API_URL não foi configurada.');
        }

        $url = $baseUrl . '/' . ltrim($recurso, '/');
        if ($query !== []) {
            $url .= '?' . http_build_query($query);
        }

        $headers = ['Accept: application/json'];
        $token = SefazAl::token();
        if ($token !== '') {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $request = curl_init($url);
        curl_setopt_array($request, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($request);
        $status = (int) curl_getinfo($request, CURLINFO_RESPONSE_CODE);
        curl_close($request);

        if ($body === false || $status < 200 || $status >= 300) {
            throw new RuntimeException('Não foi possível consultar a API da Sefaz-AL.');
        }

        $dados = json_decode($body, true);
        if (!is_array($dados)) {
            throw new RuntimeException('A API da Sefaz-AL retornou uma resposta inválida.');
        }

        return $dados;
    }
}
