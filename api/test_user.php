<?php
require __DIR__ . '/vendor/autoload.php';
use App\Config\Database;
use Dotenv\Dotenv;

Dotenv::createImmutable(__DIR__)->safeLoad();

try {
    $pdo = Database::createConnection();
    $email = 'teste@teste.com';
    $senha = '123456';
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $nome = 'Usuário de Teste';

    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (:nome, :email, :senha_hash)');
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'senha_hash' => $senhaHash
    ]);
    echo "Usuário de teste criado com sucesso!";
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
