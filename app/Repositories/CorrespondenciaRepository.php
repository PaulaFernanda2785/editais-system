<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Correspondencia;
use PDO;

class CorrespondenciaRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findByUnica(int $editalId, int $empresaId, int $perfilId): ?Correspondencia
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM correspondencias
            WHERE edital_id = :edital_id
              AND empresa_id = :empresa_id
              AND perfil_monitoramento_id = :perfil_monitoramento_id
            LIMIT 1'
        );
        $stmt->execute([
            'edital_id' => $editalId,
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return Correspondencia::fromArray($row);
    }

    /**
     * @param array<string, mixed> $motivo
     * @return array{acao: string, correspondencia_id: int|null}
     */
    public function upsert(
        int $editalId,
        int $empresaId,
        int $perfilId,
        float $score,
        string $nivelRelevancia,
        array $motivo
    ): array {
        $existente = $this->findByUnica($editalId, $empresaId, $perfilId);
        $motivoJson = json_encode($motivo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($motivoJson === false) {
            $motivoJson = null;
        }

        if ($existente === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO correspondencias (
                    edital_id,
                    empresa_id,
                    perfil_monitoramento_id,
                    score,
                    nivel_relevancia,
                    motivo_json,
                    criado_em
                ) VALUES (
                    :edital_id,
                    :empresa_id,
                    :perfil_monitoramento_id,
                    :score,
                    :nivel_relevancia,
                    :motivo_json,
                    NOW()
                )'
            );
            $stmt->execute([
                'edital_id' => $editalId,
                'empresa_id' => $empresaId,
                'perfil_monitoramento_id' => $perfilId,
                'score' => $score,
                'nivel_relevancia' => $nivelRelevancia,
                'motivo_json' => $motivoJson,
            ]);

            return [
                'acao' => 'CRIADA',
                'correspondencia_id' => (int) $this->pdo->lastInsertId(),
            ];
        }

        $stmt = $this->pdo->prepare(
            'UPDATE correspondencias
            SET
                score = :score,
                nivel_relevancia = :nivel_relevancia,
                motivo_json = :motivo_json,
                criado_em = NOW()
            WHERE id = :id'
        );
        $stmt->execute([
            'id' => $existente->id,
            'score' => $score,
            'nivel_relevancia' => $nivelRelevancia,
            'motivo_json' => $motivoJson,
        ]);

        return [
            'acao' => 'ATUALIZADA',
            'correspondencia_id' => $existente->id,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     items: array<int, Correspondencia>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     total_pages: int
     * }
     */
    public function listByEmpresa(
        int $empresaId,
        array $filters = [],
        int $page = 1,
        int $perPage = 20,
        string $sort = 'score_desc'
    ): array {
        if ($page < 1) {
            $page = 1;
        }
        if ($perPage < 1) {
            $perPage = 20;
        }
        if ($perPage > 100) {
            $perPage = 100;
        }

        [$whereSql, $params] = $this->buildWhere($empresaId, $filters);
        $orderBy = $this->resolveOrderBy($sort);

        $countStmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM correspondencias c
            INNER JOIN editais e ON e.id = c.edital_id
            LEFT JOIN perfis_monitoramento pm ON pm.id = c.perfil_monitoramento_id
            ' . $whereSql
        );
        $this->bindParams($countStmt, $params);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare(
            'SELECT
                c.*,
                pm.nome AS perfil_nome,
                e.numero_edital AS edital_numero,
                e.orgao_nome AS edital_orgao_nome,
                e.uf AS edital_uf,
                e.modalidade AS edital_modalidade,
                e.data_publicacao AS edital_data_publicacao,
                e.data_encerramento AS edital_data_encerramento,
                e.valor_estimado AS edital_valor_estimado,
                e.link_detalhe AS edital_link_detalhe,
                e.link_edital AS edital_link_edital,
                f.id AS favorito_id,
                f.status_acompanhamento AS favorito_status_acompanhamento,
                f.observacao AS favorito_observacao,
                f.atualizado_em AS favorito_atualizado_em
            FROM correspondencias c
            INNER JOIN editais e ON e.id = c.edital_id
            LEFT JOIN perfis_monitoramento pm ON pm.id = c.perfil_monitoramento_id
            LEFT JOIN favoritos f ON f.empresa_id = c.empresa_id AND f.edital_id = c.edital_id
            ' . $whereSql . '
            ORDER BY ' . $orderBy . '
            LIMIT :limite OFFSET :offset'
        );
        $this->bindParams($stmt, $params);
        $stmt->bindValue(':limite', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        $items = is_array($rows)
            ? array_map(static fn(array $row): Correspondencia => Correspondencia::fromArray($row), $rows)
            : [];

        $totalPages = $total > 0 ? (int) ceil($total / $perPage) : 1;
        if ($page > $totalPages) {
            $page = $totalPages;
        }

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
    }

    public function findByIdAndEmpresa(int $id, int $empresaId): ?Correspondencia
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                c.*,
                pm.nome AS perfil_nome,
                e.numero_edital AS edital_numero,
                e.orgao_nome AS edital_orgao_nome,
                e.uf AS edital_uf,
                e.modalidade AS edital_modalidade,
                e.data_publicacao AS edital_data_publicacao,
                e.data_encerramento AS edital_data_encerramento,
                e.valor_estimado AS edital_valor_estimado,
                e.link_detalhe AS edital_link_detalhe,
                e.link_edital AS edital_link_edital,
                f.id AS favorito_id,
                f.status_acompanhamento AS favorito_status_acompanhamento,
                f.observacao AS favorito_observacao,
                f.atualizado_em AS favorito_atualizado_em
            FROM correspondencias c
            INNER JOIN editais e ON e.id = c.edital_id
            LEFT JOIN perfis_monitoramento pm ON pm.id = c.perfil_monitoramento_id
            LEFT JOIN favoritos f ON f.empresa_id = c.empresa_id AND f.edital_id = c.edital_id
            WHERE c.id = :id
              AND c.empresa_id = :empresa_id
            LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'empresa_id' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return Correspondencia::fromArray($row);
    }

    public function countByEmpresa(int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM correspondencias WHERE empresa_id = :empresa_id'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(int $empresaId, array $filters): array
    {
        $conditions = ['c.empresa_id = :empresa_id'];
        $params = ['empresa_id' => $empresaId];

        $nivel = strtoupper(trim((string) ($filters['nivel_relevancia'] ?? '')));
        if ($nivel !== '' && in_array($nivel, ['BAIXA', 'MEDIA', 'ALTA', 'PRIORITARIA'], true)) {
            $conditions[] = 'c.nivel_relevancia = :nivel_relevancia';
            $params['nivel_relevancia'] = $nivel;
        }

        $perfilId = isset($filters['perfil_id']) ? (int) $filters['perfil_id'] : 0;
        if ($perfilId > 0) {
            $conditions[] = 'c.perfil_monitoramento_id = :perfil_id';
            $params['perfil_id'] = $perfilId;
        }

        $termo = trim((string) ($filters['termo'] ?? ''));
        if ($termo !== '') {
            $conditions[] = '(
                e.numero_edital LIKE :termo_numero
                OR e.orgao_nome LIKE :termo_orgao
                OR e.objeto LIKE :termo_objeto
                OR pm.nome LIKE :termo_perfil
            )';
            $termoLike = '%' . $termo . '%';
            $params['termo_numero'] = $termoLike;
            $params['termo_orgao'] = $termoLike;
            $params['termo_objeto'] = $termoLike;
            $params['termo_perfil'] = $termoLike;
        }

        return [
            'WHERE ' . implode(' AND ', $conditions),
            $params,
        ];
    }

    private function resolveOrderBy(string $sort): string
    {
        return match ($sort) {
            'score_asc' => 'c.score ASC, c.id ASC',
            'criado_em_asc' => 'c.criado_em ASC, c.id ASC',
            'edital_data_desc' => 'e.data_publicacao DESC, c.id DESC',
            'edital_data_asc' => 'e.data_publicacao ASC, c.id ASC',
            default => 'c.score DESC, c.id DESC',
        };
    }

    /**
     * @param array<string, mixed> $params
     */
    private function bindParams(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $paramName = ':' . $key;
            if (is_int($value)) {
                $stmt->bindValue($paramName, $value, PDO::PARAM_INT);
                continue;
            }

            $stmt->bindValue($paramName, (string) $value, PDO::PARAM_STR);
        }
    }
}
