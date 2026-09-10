<?php

declare(strict_types=1);

namespace App\Config;

use DI\Container;
use DI\ContainerBuilder;
use PDO;

final class ContainerFactory
{
    public static function create(): Container
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            PDO::class => static fn (): PDO => Database::createConnection(),
            \App\Services\GeminiService::class => \DI\autowire(),
            \App\Services\SefazAlService::class => \DI\autowire(),
            \App\Jobs\SincronizarPrecosSefaz::class => \DI\autowire(),
            \App\Controllers\IAController::class => \DI\autowire(),
            \App\Controllers\ListaController::class => \DI\autowire(),
        ]);

        return $builder->build();
    }
}
