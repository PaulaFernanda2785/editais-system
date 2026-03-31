<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Edital;
use PDO;
use RuntimeException;

class EditalRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?Edital
    {
        $stmt = $this->pdo->prepare('SELECT * FROM editais WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return Edital::fromArray($row);
    }

    public function findByHash(string $hashUnico): ?Edital
    {
        $stmt = $this->pdo->prepare('SELECT * FROM editais WHERE hash_unico = :hash_unico LIMIT 1');
        $stmt->execute(['hash_unico' => $hashUnico]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return Edital::fromArray($row);
    }

    public function create(array $data): Edital
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO editais (
                fonte_id,
                codigo_fonte,
                numero_edital,
                orgao_nome,
                orgao_poder,
                esfera,
                uf,
                municipio,
                modalidade,
                modo_disputa,
                objeto,
                descricao_resumida,
                valor_estimado,
                data_publicacao,
                data_abertura,
                data_encerramento,
                situacao,
                link_detalhe,
                link_edital,
                hash_unico,
                score_global,
                criado_em
            ) VALUES (
                :fonte_id,
                :codigo_fonte,
                :numero_edital,
                :orgao_nome,
                :orgao_poder,
                :esfera,
                :uf,
                :municipio,
                :modalidade,
                :modo_disputa,
                :objeto,
                :descricao_resumida,
                :valor_estimado,
                :data_publicacao,
                :data_abertura,
                :data_encerramento,
                :situacao,
                :link_detalhe,
                :link_edital,
                :hash_unico,
                :score_global,
                NOW()
            )'
        );

        $stmt->execute($this->buildParams($data));

        $id = (int) $this->pdo->lastInsertId();
        $edital = $this->findById($id);
        if ($edital === null) {
            throw new RuntimeException('Falha ao recuperar edital inserido.');
        }

        return $edital;
    }

    public function updateById(int $id, array $data): bool
    {
        $params = $this->buildParams($data);
        $params['id'] = $id;

        $stmt = $this->pdo->prepare(
            'UPDATE editais
            SET
                fonte_id = :fonte_id,
                codigo_fonte = :codigo_fonte,
                numero_edital = :numero_edital,
                orgao_nome = :orgao_nome,
                orgao_poder = :orgao_poder,
                esfera = :esfera,
                uf = :uf,
                municipio = :municipio,
                modalidade = :modalidade,
                modo_disputa = :modo_disputa,
                objeto = :objeto,
                descricao_resumida = :descricao_resumida,
                valor_estimado = :valor_estimado,
                data_publicacao = :data_publicacao,
                data_abertura = :data_abertura,
                data_encerramento = :data_encerramento,
                situacao = :situacao,
                link_detalhe = :link_detalhe,
                link_edital = :link_edital,
                hash_unico = :hash_unico,
                score_global = :score_global,
                atualizado_em = NOW()
            WHERE id = :id'
        );

        $stmt->execute($params);
        return $stmt->rowCount() > 0;
    }

    /**
     * @return array<int, Edital>
     */
    public function listRecentes(int $limit = 50): array
    {
        if ($limit < 1) {
            $limit = 50;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM editais
            ORDER BY id DESC
            LIMIT :limite'
        );
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): Edital => Edital::fromArray($row),
            $rows
        );
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{
     *     items: array<int, Edital>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     total_pages: int
     * }
     */
    public function search(array $filters, int $page = 1, int $perPage = 20, string $sort = 'data_publicacao_desc'): array
    {
        if ($page < 1) {
            $page = 1;
        }

        if ($perPage < 1) {
            $perPage = 20;
        }

        if ($perPage > 100) {
            $perPage = 100;
        }

        [$whereSql, $params] = $this->buildWhere($filters);
        $orderBy = $this->resolveOrderBy($sort);

        $countStmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM editais e
            INNER JOIN fontes_coleta f ON f.id = e.fonte_id
            ' . $whereSql
        );
        $this->bindParams($countStmt, $params);
        $countStmt->execute();
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;
        $stmt = $this->pdo->prepare(
            'SELECT
                e.*,
                f.codigo AS fonte_codigo,
                f.nome AS fonte_nome
            FROM editais e
            INNER JOIN fontes_coleta f ON f.id = e.fonte_id
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
            ? array_map(static fn(array $row): Edital => Edital::fromArray($row), $rows)
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

    public function countAll(): int
    {
        $stmt = $this->pdo->query('SELECT COUNT(*) FROM editais');
        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function buildWhere(array $filters): array
    {
        $conditions = [];
        $params = [];

        $termo = trim((string) ($filters['termo'] ?? ''));
        if ($termo !== '') {
            $conditions[] = '(e.numero_edital LIKE :termo OR e.orgao_nome LIKE :termo OR e.objeto LIKE :termo OR e.codigo_fonte LIKE :termo)';
            $params['termo'] = '%' . $termo . '%';
        }

        $uf = strtoupper(trim((string) ($filters['uf'] ?? '')));
        if ($uf !== '') {
            $conditions[] = 'e.uf = :uf';
            $params['uf'] = $uf;
        }

        $orgaoNome = trim((string) ($filters['orgao_nome'] ?? ''));
        if ($orgaoNome !== '') {
            $conditions[] = 'e.orgao_nome LIKE :orgao_nome';
            $params['orgao_nome'] = '%' . $orgaoNome . '%';
        }

        $modalidade = trim((string) ($filters['modalidade'] ?? ''));
        if ($modalidade !== '') {
            $conditions[] = 'e.modalidade LIKE :modalidade';
            $params['modalidade'] = '%' . $modalidade . '%';
        }

        $fonteId = isset($filters['fonte_id']) ? (int) $filters['fonte_id'] : 0;
        if ($fonteId > 0) {
            $conditions[] = 'e.fonte_id = :fonte_id';
            $params['fonte_id'] = $fonteId;
        }

        $dataDe = trim((string) ($filters['data_publicacao_de'] ?? ''));
        if ($dataDe !== '') {
            $conditions[] = 'e.data_publicacao >= :data_publicacao_de';
            $params['data_publicacao_de'] = $dataDe;
        }

        $dataAte = trim((string) ($filters['data_publicacao_ate'] ?? ''));
        if ($dataAte !== '') {
            $conditions[] = 'e.data_publicacao <= :data_publicacao_ate';
            $params['data_publicacao_ate'] = $dataAte;
        }

        $valorMin = $filters['valor_min'] ?? null;
        if ($valorMin !== null && $valorMin !== '') {
            $conditions[] = 'e.valor_estimado >= :valor_min';
            $params['valor_min'] = (float) $valorMin;
        }

        $valorMax = $filters['valor_max'] ?? null;
        if ($valorMax !== null && $valorMax !== '') {
            $conditions[] = 'e.valor_estimado <= :valor_max';
            $params['valor_max'] = (float) $valorMax;
        }

        if ($conditions === []) {
            return ['', []];
        }

        return ['WHERE ' . implode(' AND ', $conditions), $params];
    }

    private function resolveOrderBy(string $sort): string
    {
        return match ($sort) {
            'data_publicacao_asc' => 'e.data_publicacao ASC, e.id ASC',
            'data_abertura_desc' => 'e.data_abertura DESC, e.id DESC',
            'valor_desc' => 'e.valor_estimado DESC, e.id DESC',
            'valor_asc' => 'e.valor_estimado ASC, e.id ASC',
            'relevancia_desc' => 'e.score_global DESC, e.id DESC',
            default => 'e.data_publicacao DESC, e.id DESC',
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

            $stmt->bindValue($paramName, $value, PDO::PARAM_STR);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function buildParams(array $data): array
    {
        return [
            'fonte_id' => (int) ($data['fonte_id'] ?? 0),
            'codigo_fonte' => $data['codigo_fonte'] ?? null,
            'numero_edital' => $data['numero_edital'] ?? null,
            'orgao_nome' => (string) ($data['orgao_nome'] ?? ''),
            'orgao_poder' => $data['orgao_poder'] ?? null,
            'esfera' => $data['esfera'] ?? null,
            'uf' => $data['uf'] ?? null,
            'municipio' => $data['municipio'] ?? null,
            'modalidade' => $data['modalidade'] ?? null,
            'modo_disputa' => $data['modo_disputa'] ?? null,
            'objeto' => (string) ($data['objeto'] ?? ''),
            'descricao_resumida' => $data['descricao_resumida'] ?? null,
            'valor_estimado' => $data['valor_estimado'] ?? null,
            'data_publicacao' => $data['data_publicacao'] ?? null,
            'data_abertura' => $data['data_abertura'] ?? null,
            'data_encerramento' => $data['data_encerramento'] ?? null,
            'situacao' => $data['situacao'] ?? null,
            'link_detalhe' => $data['link_detalhe'] ?? null,
            'link_edital' => $data['link_edital'] ?? null,
            'hash_unico' => (string) ($data['hash_unico'] ?? ''),
            'score_global' => isset($data['score_global']) ? (float) $data['score_global'] : 0.0,
        ];
    }
}
