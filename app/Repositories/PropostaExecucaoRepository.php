<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PropostaExecucao;
use PDO;
use RuntimeException;

class PropostaExecucaoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findByFavoritoAndEmpresa(int $favoritoId, int $empresaId): ?PropostaExecucao
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM propostas_execucao
            WHERE favorito_id = :favorito_id
              AND empresa_id = :empresa_id
            LIMIT 1'
        );
        $stmt->execute([
            'favorito_id' => $favoritoId,
            'empresa_id' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PropostaExecucao::fromArray($row);
    }

    public function findByIdAndEmpresa(int $id, int $empresaId): ?PropostaExecucao
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                p.*,
                f.status_acompanhamento AS favorito_status_acompanhamento,
                e.numero_edital AS edital_numero,
                e.orgao_nome AS edital_orgao_nome,
                e.uf AS edital_uf,
                e.modalidade AS edital_modalidade,
                e.data_encerramento AS edital_data_encerramento,
                e.valor_estimado AS edital_valor_estimado,
                (
                    SELECT c.score
                    FROM correspondencias c
                    WHERE c.empresa_id = p.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_score,
                (
                    SELECT c.nivel_relevancia
                    FROM correspondencias c
                    WHERE c.empresa_id = p.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_nivel_relevancia
            FROM propostas_execucao p
            INNER JOIN favoritos f ON f.id = p.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            WHERE p.id = :id
              AND p.empresa_id = :empresa_id
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

        return PropostaExecucao::fromArray($row);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     items: array<int, PropostaExecucao>,
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
            FROM propostas_execucao p
            INNER JOIN favoritos f ON f.id = p.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            ' . $whereSql
        );
        $this->bindParams($countStmt, $params);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare(
            'SELECT
                p.*,
                f.status_acompanhamento AS favorito_status_acompanhamento,
                e.numero_edital AS edital_numero,
                e.orgao_nome AS edital_orgao_nome,
                e.uf AS edital_uf,
                e.modalidade AS edital_modalidade,
                e.data_encerramento AS edital_data_encerramento,
                e.valor_estimado AS edital_valor_estimado,
                (
                    SELECT c.score
                    FROM correspondencias c
                    WHERE c.empresa_id = p.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_score,
                (
                    SELECT c.nivel_relevancia
                    FROM correspondencias c
                    WHERE c.empresa_id = p.empresa_id
                      AND c.edital_id = f.edital_id
                    ORDER BY c.score DESC, c.id DESC
                    LIMIT 1
                ) AS correspondencia_nivel_relevancia
            FROM propostas_execucao p
            INNER JOIN favoritos f ON f.id = p.favorito_id
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
            ? array_map(static fn(array $row): PropostaExecucao => PropostaExecucao::fromArray($row), $rows)
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
     * @param array<string, mixed> $data
     * @return array{acao: string, proposta_id: int}
     */
    public function upsertByFavorito(int $favoritoId, int $empresaId, array $data): array
    {
        $existente = $this->findByFavoritoAndEmpresa($favoritoId, $empresaId);
        $common = $this->buildCommonParams($data);
        $criadoPorUsuarioId = isset($data['criado_por_usuario_id']) ? (int) $data['criado_por_usuario_id'] : null;

        if ($existente === null) {
            $insertParams = $common;
            $insertParams['favorito_id'] = $favoritoId;
            $insertParams['empresa_id'] = $empresaId;
            $insertParams['criado_por_usuario_id'] = $criadoPorUsuarioId;

            $stmt = $this->pdo->prepare(
                'INSERT INTO propostas_execucao (
                    favorito_id,
                    empresa_id,
                    status,
                    titulo,
                    resumo_executivo,
                    estrategia_proposta,
                    escopo_entrega,
                    diferenciais,
                    cronograma_macro,
                    risco_principal,
                    valor_proposta,
                    observacoes,
                    gerada_automatica,
                    criado_por_usuario_id,
                    atualizado_por_usuario_id,
                    criado_em
                ) VALUES (
                    :favorito_id,
                    :empresa_id,
                    :status,
                    :titulo,
                    :resumo_executivo,
                    :estrategia_proposta,
                    :escopo_entrega,
                    :diferenciais,
                    :cronograma_macro,
                    :risco_principal,
                    :valor_proposta,
                    :observacoes,
                    :gerada_automatica,
                    :criado_por_usuario_id,
                    :atualizado_por_usuario_id,
                    NOW()
                )'
            );
            $stmt->execute($insertParams);

            return [
                'acao' => 'CRIADA',
                'proposta_id' => (int) $this->pdo->lastInsertId(),
            ];
        }

        $updateParams = $common;
        $updateParams['id'] = $existente->id;
        $updateParams['empresa_id'] = $empresaId;
        $stmt = $this->pdo->prepare(
            'UPDATE propostas_execucao
            SET
                status = :status,
                titulo = :titulo,
                resumo_executivo = :resumo_executivo,
                estrategia_proposta = :estrategia_proposta,
                escopo_entrega = :escopo_entrega,
                diferenciais = :diferenciais,
                cronograma_macro = :cronograma_macro,
                risco_principal = :risco_principal,
                valor_proposta = :valor_proposta,
                observacoes = :observacoes,
                gerada_automatica = :gerada_automatica,
                atualizado_por_usuario_id = :atualizado_por_usuario_id,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute($updateParams);

        return [
            'acao' => 'ATUALIZADA',
            'proposta_id' => $existente->id,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateById(int $id, int $empresaId, array $data): bool
    {
        $params = $this->buildCommonParams($data);
        $params['id'] = $id;
        $params['empresa_id'] = $empresaId;

        $stmt = $this->pdo->prepare(
            'UPDATE propostas_execucao
            SET
                status = :status,
                titulo = :titulo,
                resumo_executivo = :resumo_executivo,
                estrategia_proposta = :estrategia_proposta,
                escopo_entrega = :escopo_entrega,
                diferenciais = :diferenciais,
                cronograma_macro = :cronograma_macro,
                risco_principal = :risco_principal,
                valor_proposta = :valor_proposta,
                observacoes = :observacoes,
                gerada_automatica = :gerada_automatica,
                atualizado_por_usuario_id = :atualizado_por_usuario_id,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<string, int>
     */
    public function countByEmpresaGroupedStatus(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT status, COUNT(*) AS total
            FROM propostas_execucao
            WHERE empresa_id = :empresa_id
            GROUP BY status'
        );
        $stmt->execute(['empresa_id' => $empresaId]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $status = (string) ($row['status'] ?? '');
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
        $conditions = ['p.empresa_id = :empresa_id'];
        $params = ['empresa_id' => $empresaId];

        $status = strtoupper(trim((string) ($filters['status'] ?? '')));
        if ($status !== '' && in_array($status, ['RASCUNHO', 'EM_REVISAO', 'APROVADA', 'ENVIADA'], true)) {
            $conditions[] = 'p.status = :status';
            $params['status'] = $status;
        }

        $termo = trim((string) ($filters['termo'] ?? ''));
        if ($termo !== '') {
            $conditions[] = '(
                p.titulo LIKE :termo_titulo
                OR e.numero_edital LIKE :termo_numero
                OR e.orgao_nome LIKE :termo_orgao
            )';
            $termoLike = '%' . $termo . '%';
            $params['termo_titulo'] = $termoLike;
            $params['termo_numero'] = $termoLike;
            $params['termo_orgao'] = $termoLike;
        }

        return [
            'WHERE ' . implode(' AND ', $conditions),
            $params,
        ];
    }

    private function resolveOrderBy(string $sort): string
    {
        return match ($sort) {
            'valor_desc' => 'p.valor_proposta DESC, p.id DESC',
            'prazo_asc' => 'e.data_encerramento ASC, p.id ASC',
            'criado_desc' => 'p.criado_em DESC, p.id DESC',
            default => 'COALESCE(p.atualizado_em, p.criado_em) DESC, p.id DESC',
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

            if (is_float($value)) {
                $stmt->bindValue($paramName, (string) $value, PDO::PARAM_STR);
                continue;
            }

            $stmt->bindValue($paramName, (string) $value, PDO::PARAM_STR);
        }
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildCommonParams(array $data): array
    {
        $status = strtoupper(trim((string) ($data['status'] ?? 'RASCUNHO')));
        if (!in_array($status, ['RASCUNHO', 'EM_REVISAO', 'APROVADA', 'ENVIADA'], true)) {
            $status = 'RASCUNHO';
        }

        $titulo = trim((string) ($data['titulo'] ?? 'Proposta'));
        if ($titulo === '') {
            $titulo = 'Proposta';
        }
        if (strlen($titulo) > 220) {
            $titulo = substr($titulo, 0, 220);
        }

        return [
            'status' => $status,
            'titulo' => $titulo,
            'resumo_executivo' => $this->normalizeText($data['resumo_executivo'] ?? null, 9000),
            'estrategia_proposta' => $this->normalizeText($data['estrategia_proposta'] ?? null, 9000),
            'escopo_entrega' => $this->normalizeText($data['escopo_entrega'] ?? null, 9000),
            'diferenciais' => $this->normalizeText($data['diferenciais'] ?? null, 9000),
            'cronograma_macro' => $this->normalizeText($data['cronograma_macro'] ?? null, 9000),
            'risco_principal' => $this->normalizeText($data['risco_principal'] ?? null, 5000),
            'valor_proposta' => $this->normalizeDecimal($data['valor_proposta'] ?? null),
            'observacoes' => $this->normalizeText($data['observacoes'] ?? null, 9000),
            'gerada_automatica' => isset($data['gerada_automatica']) && (int) $data['gerada_automatica'] === 0 ? 0 : 1,
            'atualizado_por_usuario_id' => isset($data['atualizado_por_usuario_id']) ? (int) $data['atualizado_por_usuario_id'] : null,
        ];
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
