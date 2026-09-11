<?php
require 'vendor/autoload.php';

// Carrega as variáveis de ambiente do arquivo .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

try {
    $pdo = \App\Config\Database::createConnection();
    echo "✅ Conexão bem sucedida!\n";
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) echo "✅ Consulta básica funcionando!\n";
} catch (\Throwable $e) {
    echo "❌ Erro de conexão: " . $e->getMessage() . "\n";
}
