<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PropostaSubmissao;
use PDO;

class PropostaSubmissaoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, PropostaSubmissao>
     */
    public function listByProposta(int $propostaId, int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                ps.*,
                u.nome AS usuario_nome
            FROM proposta_submissoes ps
            LEFT JOIN usuarios u ON u.id = ps.usuario_id
            WHERE ps.proposta_id = :proposta_id
              AND ps.empresa_id = :empresa_id
            ORDER BY ps.data_submissao DESC, ps.id DESC'
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
            static fn(array $row): PropostaSubmissao => PropostaSubmissao::fromArray($row),
            $rows
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO proposta_submissoes (
                proposta_id,
                empresa_id,
                usuario_id,
                canal,
                protocolo,
                data_submissao,
                valor_enviado,
                link_comprovante,
                observacao,
                criado_em
            ) VALUES (
                :proposta_id,
                :empresa_id,
                :usuario_id,
                :canal,
                :protocolo,
                :data_submissao,
                :valor_enviado,
                :link_comprovante,
                :observacao,
                NOW()
            )'
        );
        $stmt->execute([
            'proposta_id' => (int) ($data['proposta_id'] ?? 0),
            'empresa_id' => (int) ($data['empresa_id'] ?? 0),
            'usuario_id' => isset($data['usuario_id']) ? (int) $data['usuario_id'] : null,
            'canal' => $this->normalizeCanal((string) ($data['canal'] ?? 'PORTAL')),
            'protocolo' => $this->normalizeText($data['protocolo'] ?? null, 150),
            'data_submissao' => (string) ($data['data_submissao'] ?? ''),
            'valor_enviado' => $this->normalizeDecimal($data['valor_enviado'] ?? null),
            'link_comprovante' => $this->normalizeText($data['link_comprovante'] ?? null, 500),
            'observacao' => $this->normalizeText($data['observacao'] ?? null, 9000),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    private function normalizeCanal(string $value): string
    {
        $canal = strtoupper(trim($value));
        if (!in_array($canal, ['PORTAL', 'EMAIL', 'PRESENCIAL', 'OUTRO'], true)) {
            return 'PORTAL';
        }

        return $canal;
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
}
