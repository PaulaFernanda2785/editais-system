<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Favorito;
use PDO;

class FavoritoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findByEmpresaAndEdital(int $empresaId, int $editalId): ?Favorito
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM favoritos
            WHERE empresa_id = :empresa_id
              AND edital_id = :edital_id
            LIMIT 1'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'edital_id' => $editalId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return Favorito::fromArray($row);
    }

    public function findByIdAndEmpresa(int $id, int $empresaId): ?Favorito
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                f.*,
                e.numero_edital AS edital_numero,
                e.orgao_nome AS edital_orgao_nome,
                e.uf AS edital_uf,
                e.modalidade AS edital_modalidade,
                e.data_publicacao AS edital_data_publicacao,
                e.data_encerramento AS edital_data_encerramento,
                e.valor_estimado AS edital_valor_estimado,
                e.link_detalhe AS edital_link_detalhe,
                e.link_edital AS edital_link_edital,
                (
                    SELECT c.id
                    FROM correspondencias c
                    WHERE c.empresa_id = f.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_id,
                (
                    SELECT c.score
                    FROM correspondencias c
                    WHERE c.empresa_id = f.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_score,
                (
                    SELECT c.nivel_relevancia
                    FROM correspondencias c
                    WHERE c.empresa_id = f.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_nivel_relevancia,
                (
                    SELECT COUNT(*)
                    FROM favorito_tarefas ft
                    WHERE ft.favorito_id = f.id
                ) AS total_tarefas,
                (
                    SELECT COUNT(*)
                    FROM favorito_tarefas ft
                    WHERE ft.favorito_id = f.id
                      AND ft.status <> \'CONCLUIDA\'
                ) AS tarefas_abertas
            FROM favoritos f
            INNER JOIN editais e ON e.id = f.edital_id
            WHERE f.id = :id
              AND f.empresa_id = :empresa_id
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

        return Favorito::fromArray($row);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     items: array<int, Favorito>,
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
        string $sort = 'atualizado_desc'
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
            FROM favoritos f
            INNER JOIN editais e ON e.id = f.edital_id
            ' . $whereSql
        );
        $this->bindParams($countStmt, $params);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare(
            'SELECT
                f.*,
                e.numero_edital AS edital_numero,
                e.orgao_nome AS edital_orgao_nome,
                e.uf AS edital_uf,
                e.modalidade AS edital_modalidade,
                e.data_publicacao AS edital_data_publicacao,
                e.data_encerramento AS edital_data_encerramento,
                e.valor_estimado AS edital_valor_estimado,
                e.link_detalhe AS edital_link_detalhe,
                e.link_edital AS edital_link_edital,
                (
                    SELECT c.id
                    FROM correspondencias c
                    WHERE c.empresa_id = f.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_id,
                (
                    SELECT c.score
                    FROM correspondencias c
                    WHERE c.empresa_id = f.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_score,
                (
                    SELECT c.nivel_relevancia
                    FROM correspondencias c
                    WHERE c.empresa_id = f.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_nivel_relevancia,
                (
                    SELECT COUNT(*)
                    FROM favorito_tarefas ft
                    WHERE ft.favorito_id = f.id
                ) AS total_tarefas,
                (
                    SELECT COUNT(*)
                    FROM favorito_tarefas ft
                    WHERE ft.favorito_id = f.id
                      AND ft.status <> \'CONCLUIDA\'
                ) AS tarefas_abertas
            FROM favoritos f
            INNER JOIN editais e ON e.id = f.edital_id
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
            ? array_map(static fn(array $row): Favorito => Favorito::fromArray($row), $rows)
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

    /**
     * @return array{acao: string, favorito_id: int|null}
     */
    public function upsertByEmpresaEdital(
        int $empresaId,
        int $editalId,
        string $statusAcompanhamento,
        ?string $observacao = null
    ): array {
        $existente = $this->findByEmpresaAndEdital($empresaId, $editalId);
        $observacao = $this->normalizeObservacao($observacao);

        if ($existente === null) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO favoritos (
                    empresa_id,
                    edital_id,
                    status_acompanhamento,
                    observacao,
                    criado_em
                ) VALUES (
                    :empresa_id,
                    :edital_id,
                    :status_acompanhamento,
                    :observacao,
                    NOW()
                )'
            );
            $stmt->execute([
                'empresa_id' => $empresaId,
                'edital_id' => $editalId,
                'status_acompanhamento' => $statusAcompanhamento,
                'observacao' => $observacao,
            ]);

            return [
                'acao' => 'CRIADO',
                'favorito_id' => (int) $this->pdo->lastInsertId(),
            ];
        }

        $novoTexto = $observacao ?? $existente->observacao;
        $stmt = $this->pdo->prepare(
            'UPDATE favoritos
            SET
                status_acompanhamento = :status_acompanhamento,
                observacao = :observacao,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'id' => $existente->id,
            'empresa_id' => $empresaId,
            'status_acompanhamento' => $statusAcompanhamento,
            'observacao' => $this->normalizeObservacao($novoTexto),
        ]);

        return [
            'acao' => 'ATUALIZADO',
            'favorito_id' => $existente->id,
        ];
    }

    public function updateStatusAndObservacao(
        int $favoritoId,
        int $empresaId,
        string $statusAcompanhamento,
        ?string $observacao = null
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE favoritos
            SET
                status_acompanhamento = :status_acompanhamento,
                observacao = :observacao,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'id' => $favoritoId,
            'empresa_id' => $empresaId,
            'status_acompanhamento' => $statusAcompanhamento,
            'observacao' => $this->normalizeObservacao($observacao),
        ]);

        return $stmt->rowCount() > 0;
    }

    public function countByEmpresa(int $empresaId): int
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM favoritos WHERE empresa_id = :empresa_id');
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * @return array<string, int>
     */
    public function countByEmpresaGroupedStatus(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT status_acompanhamento, COUNT(*) AS total
            FROM favoritos
            WHERE empresa_id = :empresa_id
            GROUP BY status_acompanhamento'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $status = (string) ($row['status_acompanhamento'] ?? '');
            if ($status === '') {
                continue;
            }

            $grouped[$status] = (int) ($row['total'] ?? 0);
        }

        return $grouped;
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(int $empresaId, array $filters): array
    {
        $conditions = ['f.empresa_id = :empresa_id'];
        $params = ['empresa_id' => $empresaId];

        $status = strtoupper(trim((string) ($filters['status_acompanhamento'] ?? '')));
        if ($status !== '' && in_array($status, ['FAVORITO', 'EM_ANALISE', 'PROPOSTA', 'DESCARTADO', 'ENCERRADO'], true)) {
            $conditions[] = 'f.status_acompanhamento = :status_acompanhamento';
            $params['status_acompanhamento'] = $status;
        }

        $termo = trim((string) ($filters['termo'] ?? ''));
        if ($termo !== '') {
            $conditions[] = '(
                e.numero_edital LIKE :termo_numero
                OR e.orgao_nome LIKE :termo_orgao
                OR e.objeto LIKE :termo_objeto
            )';
            $termoLike = '%' . $termo . '%';
            $params['termo_numero'] = $termoLike;
            $params['termo_orgao'] = $termoLike;
            $params['termo_objeto'] = $termoLike;
        }

        return [
            'WHERE ' . implode(' AND ', $conditions),
            $params,
        ];
    }

    private function resolveOrderBy(string $sort): string
    {
        return match ($sort) {
            'atualizado_asc' => 'COALESCE(f.atualizado_em, f.criado_em) ASC, f.id ASC',
            'score_desc' => 'correspondencia_score DESC, f.id DESC',
            'prazo_asc' => 'e.data_encerramento ASC, f.id ASC',
            default => 'COALESCE(f.atualizado_em, f.criado_em) DESC, f.id DESC',
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

    private function normalizeObservacao(?string $observacao): ?string
    {
        if ($observacao === null) {
            return null;
        }

        $observacao = trim($observacao);
        if ($observacao === '') {
            return null;
        }

        if (strlen($observacao) > 5000) {
            $observacao = substr($observacao, 0, 5000);
        }

        return $observacao;
    }
}
