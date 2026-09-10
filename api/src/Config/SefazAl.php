<?php

declare(strict_types=1);

namespace App\Config;

/** Configuração da API Economiza Alagoas obtida exclusivamente do ambiente. */
final class SefazAl
{
    public static function baseUrl(): string
    {
        return rtrim((string) ($_ENV['SEFAZ_AL_API_URL'] ?? ''), '/');
    }

    public static function token(): string
    {
        return (string) ($_ENV['SEFAZ_AL_API_TOKEN'] ?? '');
    }
}
