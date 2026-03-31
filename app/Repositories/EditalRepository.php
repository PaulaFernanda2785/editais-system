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

