<?php

declare(strict_types=1);

namespace App\Services;

use PDO;
use RuntimeException;

/**
 * Serviço responsável pela lógica de precificação e sugestões do Mais por Menos.
 */
final class PrecoService
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Sugere o preço de um produto seguindo a cascata de prioridades.
     *
     * Prioridade:
     * 1. Usuário (mais recente nos últimos 30 dias)
     * 2. Loja (mais recente naquele mercado, Comunidade > Sefaz)
     * 3. Cidade (média dos últimos 15 dias na cidade)
     * 4. Admin (preço de referência)
     *
     * @param int $produtoId
     * @param int $usuarioId
     * @param int|null $mercadoId
     * @return array{valor: float, fonte: string}|null
     */
    public function sugerirPreco(int $produtoId, int $usuarioId, ?int $mercadoId): ?array
    {
        // 1. Prioridade: Usuário (próprio histórico nos últimos 30 dias)
        $valorUsuario = $this->buscarPrecoUsuario($produtoId, $usuarioId);
        if ($valorUsuario !== null) {
            return ['valor' => $valorUsuario, 'fonte' => 'usuario'];
        }

        // 2. Prioridade: Loja (específico para o mercado informado)
        if ($mercadoId !== null) {
            $valorLoja = $this->buscarPrecoLoja($produtoId, $mercadoId);
            if ($valorLoja !== null) {
                return ['valor' => $valorLoja, 'fonte' => 'loja'];
            }
        }

        // 3. Prioridade: Cidade (média na cidade do mercado ou do usuário)
        $valorCidade = $this->buscarPrecoCidade($produtoId, $mercadoId, $usuarioId);
        if ($valorCidade !== null) {
            return ['valor' => $valorCidade, 'fonte' => 'cidade'];
        }

        // 4. Prioridade: Admin (tabela de referência)
        $valorAdmin = $this->buscarPrecoAdmin($produtoId);
        if ($valorAdmin !== null) {
            return ['valor' => $valorAdmin, 'fonte' => 'admin'];
        }

        return null;
    }

    private function buscarPrecoUsuario(int $produtoId, int $usuarioId): ?float
    {
        $sql = "SELECT valor FROM precos
                WHERE produto_id = :pid AND usuario_id = :uid
                AND data_//S lC l la última compra
                ORDER BY data_coleta DESC LIMIT 1";

        // Correção do erro de sintaxe no SQL acima: data_coleta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        $sql = "SELECT valor FROM precos
                WHERE produto_id = :pid AND usuario_id = :uid
                AND data_coleta >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ORDER BY data_coleta DESC LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pid' => $produtoId, 'uid' => $usuarioId]);
        $res = $stmt->fetch();
        return $res ? (float) $res['valor'] : null;
    }

    private function buscarPrecoLoja(int $produtoId, int $mercadoId): ?float
    {
        // Prioridade: Comunidade > Sefaz
        $sql = "SELECT valor FROM precos
                WHERE produto_id = :pid AND mercado_id = :mid
                ORDER BY (origem = 'comunidade') DESC, data_coleta DESC LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pid' => $produtoId, 'mid' => $mercadoId]);
        $res = $stmt->fetch();
        return $res ? (float) $res['valor'] : null;
    }

    private function buscarPrecoCidade(int $produtoId, ?int $mercadoId, int $usuarioId): ?float
    {
        // Tenta pegar a cidade do mercado, se não tiver, a cidade do usuário
        $cidade = null;
        if ($mercadoId !== null) {
            $stmt = $this->pdo->prepare("SELECT cidade FROM mercados WHERE id = :id");
            $stmt->execute(['id' => $mercadoId]);
            $cidade = $stmt->fetchColumn();
        }

        if (!$cidade) {
            $stmt = $this->pdo->prepare("SELECT cidade FROM usuarios WHERE id = :id");
            $stmt->execute(['id' => $usuarioId]);
            $cidade = $stmt->fetchColumn();
        }

        if (!$cidade) return null;

        // Média dos últimos 15 dias na cidade
        $sql = "SELECT AVG(p.valor) as media
                FROM precos p
                JOIN mercados m ON p.mercado_id = m.id
                WHERE p.produto_id = :pid AND m.cidade = :cidade
                AND p.data_coleta >= DATE_SUB(NOW(), INTERVAL 15 DAY)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pid' => $produtoId, 'cidade' => $cidade]);
        $res = $stmt->fetch();
        return $res['media'] !== null ? (float) $res['media'] : null;
    }

    private function buscarPrecoAdmin(int $produtoId): ?float
    {
        $stmt = $this->pdo->prepare("SELECT valor_admin FROM precos_referencia WHERE produto_id = :pid");
        $stmt->execute(['pid' => $produtoId]);
        $res = $stmt->fetch();
        return $res ? (float) $res['valor_admin'] : null;
    }
}
