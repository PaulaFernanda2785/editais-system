<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PerfilMonitoramento;
use PDO;
use RuntimeException;

class PerfilMonitoramentoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, PerfilMonitoramento>
     */
    public function listByEmpresa(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pm.*,
                (
                    SELECT COUNT(*)
                    FROM palavras_chave pc
                    WHERE pc.empresa_id = pm.empresa_id
                      AND pc.perfil_monitoramento_id = pm.id
                ) AS total_palavras
            FROM perfis_monitoramento pm
            WHERE pm.empresa_id = :empresa_id
            ORDER BY pm.id DESC'
        );
        $stmt->execute(['empresa_id' => $empresaId]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): PerfilMonitoramento => PerfilMonitoramento::fromArray($row),
            $rows
        );
    }

    public function countByEmpresa(int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM perfis_monitoramento WHERE empresa_id = :empresa_id'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn();
    }

    public function findByIdAndEmpresa(int $perfilId, int $empresaId): ?PerfilMonitoramento
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pm.*,
                (
                    SELECT COUNT(*)
                    FROM palavras_chave pc
                    WHERE pc.empresa_id = pm.empresa_id
                      AND pc.perfil_monitoramento_id = pm.id
                ) AS total_palavras
            FROM perfis_monitoramento pm
            WHERE pm.id = :id
              AND pm.empresa_id = :empresa_id
            LIMIT 1'
        );
        $stmt->execute([
            'id' => $perfilId,
            'empresa_id' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PerfilMonitoramento::fromArray($row);
    }

    public function create(array $data): PerfilMonitoramento
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO perfis_monitoramento (
                empresa_id,
                nome,
                ufs_json,
                modalidades_json,
                orgaos_json,
                faixa_valor_min,
                faixa_valor_max,
                frequencia_alerta,
                ativo,
                criado_em
            ) VALUES (
                :empresa_id,
                :nome,
                :ufs_json,
                :modalidades_json,
                :orgaos_json,
                :faixa_valor_min,
                :faixa_valor_max,
                :frequencia_alerta,
                :ativo,
                NOW()
            )'
        );

        $stmt->execute([
            'empresa_id' => (int) $data['empresa_id'],
            'nome' => (string) $data['nome'],
            'ufs_json' => $this->encodeJsonOrNull($data['ufs_json'] ?? []),
            'modalidades_json' => $this->encodeJsonOrNull($data['modalidades_json'] ?? []),
            'orgaos_json' => $this->encodeJsonOrNull($data['orgaos_json'] ?? []),
            'faixa_valor_min' => $data['faixa_valor_min'] ?? null,
            'faixa_valor_max' => $data['faixa_valor_max'] ?? null,
            'frequencia_alerta' => (string) $data['frequencia_alerta'],
            'ativo' => isset($data['ativo']) ? (int) $data['ativo'] : 1,
        ]);

        $perfilId = (int) $this->pdo->lastInsertId();
        $perfil = $this->findByIdAndEmpresa($perfilId, (int) $data['empresa_id']);
        if ($perfil === null) {
            throw new RuntimeException('Falha ao recuperar perfil monitoramento criado.');
        }

        return $perfil;
    }

    public function update(int $perfilId, int $empresaId, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE perfis_monitoramento
            SET
                nome = :nome,
                ufs_json = :ufs_json,
                modalidades_json = :modalidades_json,
                orgaos_json = :orgaos_json,
                faixa_valor_min = :faixa_valor_min,
                faixa_valor_max = :faixa_valor_max,
                frequencia_alerta = :frequencia_alerta,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );

        $stmt->execute([
            'id' => $perfilId,
            'empresa_id' => $empresaId,
            'nome' => (string) $data['nome'],
            'ufs_json' => $this->encodeJsonOrNull($data['ufs_json'] ?? []),
            'modalidades_json' => $this->encodeJsonOrNull($data['modalidades_json'] ?? []),
            'orgaos_json' => $this->encodeJsonOrNull($data['orgaos_json'] ?? []),
            'faixa_valor_min' => $data['faixa_valor_min'] ?? null,
            'faixa_valor_max' => $data['faixa_valor_max'] ?? null,
            'frequencia_alerta' => (string) $data['frequencia_alerta'],
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setAtivo(int $perfilId, int $empresaId, bool $ativo): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE perfis_monitoramento
            SET ativo = :ativo, atualizado_em = NOW()
            WHERE id = :id AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'ativo' => $ativo ? 1 : 0,
            'id' => $perfilId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $perfilId, int $empresaId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM perfis_monitoramento
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'id' => $perfilId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function encodeJsonOrNull(mixed $value): ?string
    {
        if (!is_array($value) || $value === []) {
            return null;
        }

        $encoded = json_encode(array_values($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || $encoded === '[]') {
            return null;
        }

        return $encoded;
    }
}
