<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use App\Config\ContainerFactory;
use App\Jobs\SincronizarPrecosSefaz;

try {
    Dotenv::createImmutable(__DIR__)->safeLoad();
    $container = ContainerFactory::create();
    $job = $container->get(SincronizarPrecosSefaz::class);

    $job->executar();

    echo "Sincronização SEFAZ concluída com sucesso." . PHP_EOL;
} catch (Exception $e) {
    fwrite(STDERR, "Erro fatal na sincronização SEFAZ: " . $e->getMessage() . PHP_EOL);
    exit(1);
}
