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
        ]);

        return $builder->build();
    }
}
