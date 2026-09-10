<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\SefazAlService;
use App\Services\SincronizacaoService;

/**
 * Ponto de entrada para cron. O formato exato das respostas deve ser adaptado
 * quando a documentação/credencial da Sefaz-AL estiver disponível.
 */
final class SincronizarPrecosSefaz
{
    public function __construct(
        private SefazAlService $sefaz,
        private SincronizacaoService $sincronizacao,
    ) {
    }

    public function executar(): void
    {
        $estabelecimentos = $this->sefaz->consultar('estabelecimentos');
        $precos = $this->sefaz->consultar('precos');

        $this->sincronizacao->importar($estabelecimentos['data'] ?? [], $precos['data'] ?? []);
    }
}
