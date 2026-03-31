<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\FonteColeta;
use PDO;
use RuntimeException;

class FonteColetaRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, FonteColeta>
     */
    public function listAllWithResumo(): array
    {
        $stmt = $this->pdo->query(
            'SELECT
                f.*,
                (
                    SELECT COUNT(*)
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                ) AS total_execucoes,
                (
                    SELECT COUNT(*)
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                      AND c.status = \'SUCESSO\'
                ) AS total_sucesso,
                (
                    SELECT COUNT(*)
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                      AND c.status IN (\'ERRO\', \'PARCIAL\')
                ) AS total_falhas,
                (
                    SELECT c.status
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                    ORDER BY c.id DESC
                    LIMIT 1
                ) AS ultima_execucao_status
            FROM fontes_coleta f
            ORDER BY f.id DESC'
        );

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): FonteColeta => FonteColeta::fromArray($row),
            $rows
        );
    }

    public function findById(int $id): ?FonteColeta
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                f.*,
                (
                    SELECT COUNT(*)
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                ) AS total_execucoes,
                (
                    SELECT COUNT(*)
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                      AND c.status = \'SUCESSO\'
                ) AS total_sucesso,
                (
                    SELECT COUNT(*)
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                      AND c.status IN (\'ERRO\', \'PARCIAL\')
                ) AS total_falhas,
                (
                    SELECT c.status
                    FROM coletas_execucao c
                    WHERE c.fonte_id = f.id
                    ORDER BY c.id DESC
                    LIMIT 1
                ) AS ultima_execucao_status
            FROM fontes_coleta f
            WHERE f.id = :id
            LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return FonteColeta::fromArray($row);
    }

    public function findByCodigo(string $codigo): ?FonteColeta
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM fontes_coleta WHERE codigo = :codigo LIMIT 1'
        );
        $stmt->execute(['codigo' => $codigo]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return FonteColeta::fromArray($row);
    }

    public function create(array $data): FonteColeta
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO fontes_coleta (
                nome,
                codigo,
                tipo,
                url_base,
                ativa,
                periodicidade_minutos,
                configuracao_json,
                criado_em
            ) VALUES (
                :nome,
                :codigo,
                :tipo,
                :url_base,
                :ativa,
                :periodicidade_minutos,
                :configuracao_json,
                NOW()
            )'
        );

        $stmt->execute([
            'nome' => (string) $data['nome'],
            'codigo' => (string) $data['codigo'],
            'tipo' => (string) $data['tipo'],
            'url_base' => $data['url_base'] ?? null,
            'ativa' => isset($data['ativa']) ? (int) $data['ativa'] : 1,
            'periodicidade_minutos' => (int) $data['periodicidade_minutos'],
            'configuracao_json' => $this->encodeJsonOrNull($data['configuracao_json'] ?? null),
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $fonte = $this->findById($id);
        if ($fonte === null) {
            throw new RuntimeException('Falha ao recuperar fonte de coleta criada.');
        }

        return $fonte;
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE fontes_coleta
            SET
                nome = :nome,
                codigo = :codigo,
                tipo = :tipo,
                url_base = :url_base,
                periodicidade_minutos = :periodicidade_minutos,
                configuracao_json = :configuracao_json,
                atualizado_em = NOW()
            WHERE id = :id'
        );

        $stmt->execute([
            'id' => $id,
            'nome' => (string) $data['nome'],
            'codigo' => (string) $data['codigo'],
            'tipo' => (string) $data['tipo'],
            'url_base' => $data['url_base'] ?? null,
            'periodicidade_minutos' => (int) $data['periodicidade_minutos'],
            'configuracao_json' => $this->encodeJsonOrNull($data['configuracao_json'] ?? null),
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setAtiva(int $id, bool $ativa): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE fontes_coleta
            SET ativa = :ativa, atualizado_em = NOW()
            WHERE id = :id'
        );
        $stmt->execute([
            'id' => $id,
            'ativa' => $ativa ? 1 : 0,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function encodeJsonOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            return null;
        }

        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($encoded === false || $encoded === 'null') {
            return null;
        }

        return $encoded;
    }
}
