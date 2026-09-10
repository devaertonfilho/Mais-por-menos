<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Services\GeminiService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class IAController extends ApiController
{
    public function __construct(private readonly GeminiService $gemini)
    {
    }

    public function sugestaoLista(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $body = $this->body($request);
        $listaId = filter_var($body['lista_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if ($listaId === false) {
            return $this->error($response, 'lista_id válido é obrigatório.', 400);
        }

        try {
            $sugestao = $this->gemini->gerarSugestaoLista($listaId);

            return $this->json($response, [
                'status' => 'sucesso',
                'dados' => [
                    'sugestao' => $sugestao
                ]
            ]);
        } catch (\Exception $e) {
            return $this->error($response, 'Erro ao gerar sugestão com IA: ' . $e->getMessage(), 500);
        }
    }
}
