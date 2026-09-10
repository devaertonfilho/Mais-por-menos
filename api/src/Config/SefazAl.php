<?php

declare(strict_types=1);

namespace App\Config;

/** Configuração da API Economiza Alagoas obtida exclusivamente do ambiente. */
final class SefazAl
{
    public static function baseUrl(): string
    {
        return rtrim((string) ($_ENV['SEFAZ_AL_API_URL'] ?? 'http://api.sefaz.al.gov.br/sfz-economiza-alagoas-api/api/public'), '/');
    }

    public static function token(): string
    {
        return (string) ($_ENV['SEFAZ_TOKEN'] ?? $_ENV['SEFAZ_AL_API_TOKEN'] ?? '');
    }
}
