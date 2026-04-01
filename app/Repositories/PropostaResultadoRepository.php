<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PropostaResultado;
use PDO;

class PropostaResultadoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, PropostaResultado>
     */
    public function listByProposta(int $propostaId, int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pr.*,
                u.nome AS usuario_nome
            FROM proposta_resultados pr
            LEFT JOIN usuarios u ON u.id = pr.usuario_id
            WHERE pr.proposta_id = :proposta_id
              AND pr.empresa_id = :empresa_id
            ORDER BY pr.data_resultado DESC, pr.id DESC'
        );
        $stmt->execute([
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
        ]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): PropostaResultado => PropostaResultado::fromArray($row),
            $rows
        );
    }

    public function findLatestByProposta(int $propostaId, int $empresaId): ?PropostaResultado
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pr.*,
                u.nome AS usuario_nome
            FROM proposta_resultados pr
            LEFT JOIN usuarios u ON u.id = pr.usuario_id
            WHERE pr.proposta_id = :proposta_id
              AND pr.empresa_id = :empresa_id
            ORDER BY pr.data_resultado DESC, pr.id DESC
            LIMIT 1'
        );
        $stmt->execute([
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PropostaResultado::fromArray($row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO proposta_resultados (
                proposta_id,
                empresa_id,
                usuario_id,
                situacao,
                data_resultado,
                valor_homologado,
                colocacao,
                motivo,
                link_ata,
                observacao,
                criado_em
            ) VALUES (
                :proposta_id,
                :empresa_id,
                :usuario_id,
                :situacao,
                :data_resultado,
                :valor_homologado,
                :colocacao,
                :motivo,
                :link_ata,
                :observacao,
                NOW()
            )'
        );
        $stmt->execute([
            'proposta_id' => (int) ($data['proposta_id'] ?? 0),
            'empresa_id' => (int) ($data['empresa_id'] ?? 0),
            'usuario_id' => isset($data['usuario_id']) ? (int) $data['usuario_id'] : null,
            'situacao' => $this->normalizeSituacao((string) ($data['situacao'] ?? 'EM_JULGAMENTO')),
            'data_resultado' => (string) ($data['data_resultado'] ?? ''),
            'valor_homologado' => $this->normalizeDecimal($data['valor_homologado'] ?? null),
            'colocacao' => $this->normalizePositiveInt($data['colocacao'] ?? null),
            'motivo' => $this->normalizeText($data['motivo'] ?? null, 9000),
            'link_ata' => $this->normalizeText($data['link_ata'] ?? null, 500),
            'observacao' => $this->normalizeText($data['observacao'] ?? null, 9000),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @return array<string, int>
     */
    public function countByEmpresaGroupedSituacao(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT situacao, COUNT(*) AS total
            FROM proposta_resultados
            WHERE empresa_id = :empresa_id
            GROUP BY situacao'
        );
        $stmt->execute(['empresa_id' => $empresaId]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $situacao = (string) ($row['situacao'] ?? '');
            if ($situacao === '') {
                continue;
            }

            $grouped[$situacao] = (int) ($row['total'] ?? 0);
        }

        return $grouped;
    }

    private function normalizeSituacao(string $situacao): string
    {
        $situacao = strtoupper(trim($situacao));
        if (!in_array($situacao, ['EM_JULGAMENTO', 'VENCEDORA', 'NAO_VENCEDORA', 'DESCLASSIFICADA', 'ANULADA'], true)) {
            return 'EM_JULGAMENTO';
        }

        return $situacao;
    }

    private function normalizeText(mixed $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (strlen($text) > $limit) {
            $text = substr($text, 0, $limit);
        }

        return $text;
    }

    private function normalizeDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = str_replace(['.', ','], ['', '.'], (string) $value);
        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function normalizePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $int = (int) $value;
        if ($int < 1) {
            return null;
        }

        return $int;
    }
}
