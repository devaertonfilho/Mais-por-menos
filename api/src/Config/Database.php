<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use RuntimeException;

final class Database
{
    public static function createConnection(): PDO
    {
        $host = self::environment('DB_HOST');
        $database = self::environment('DB_NAME');
        $user = self::environment('DB_USER');
        $password = $_ENV['DB_PASSWORD'] ?? '';

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $host,
            $database
        );

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private static function environment(string $key): string
    {
        $value = $_ENV[$key] ?? '';

        if (!is_string($value) || $value === '') {
            throw new RuntimeException(sprintf('A variável de ambiente %s é obrigatória.', $key));
        }

        return $value;
    }
}
