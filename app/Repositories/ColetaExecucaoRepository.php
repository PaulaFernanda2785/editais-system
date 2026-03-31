<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\ColetaExecucao;
use PDO;
use RuntimeException;

class ColetaExecucaoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function iniciar(int $fonteId): ColetaExecucao
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO coletas_execucao (
                fonte_id,
                iniciado_em,
                status,
                total_lidos,
                total_inseridos,
                total_atualizados,
                total_duplicados,
                total_erros,
                criado_em
            ) VALUES (
                :fonte_id,
                NOW(),
                \'PROCESSANDO\',
                0,
                0,
                0,
                0,
                0,
                NOW()
            )'
        );
        $stmt->execute(['fonte_id' => $fonteId]);

        $id = (int) $this->pdo->lastInsertId();
        $execucao = $this->findByIdComFonte($id);
        if ($execucao === null) {
            throw new RuntimeException('Falha ao recuperar execucao de coleta iniciada.');
        }

        return $execucao;
    }

    public function finalizar(int $execucaoId, array $resumo): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE coletas_execucao
            SET
                finalizado_em = NOW(),
                status = :status,
                total_lidos = :total_lidos,
                total_inseridos = :total_inseridos,
                total_atualizados = :total_atualizados,
                total_duplicados = :total_duplicados,
                total_erros = :total_erros,
                mensagem_resumo = :mensagem_resumo,
                log_detalhado = :log_detalhado
            WHERE id = :id'
        );

        $stmt->execute([
            'id' => $execucaoId,
            'status' => (string) ($resumo['status'] ?? 'ERRO'),
            'total_lidos' => (int) ($resumo['total_lidos'] ?? 0),
            'total_inseridos' => (int) ($resumo['total_inseridos'] ?? 0),
            'total_atualizados' => (int) ($resumo['total_atualizados'] ?? 0),
            'total_duplicados' => (int) ($resumo['total_duplicados'] ?? 0),
            'total_erros' => (int) ($resumo['total_erros'] ?? 0),
            'mensagem_resumo' => isset($resumo['mensagem_resumo']) ? (string) $resumo['mensagem_resumo'] : null,
            'log_detalhado' => isset($resumo['log_detalhado']) ? (string) $resumo['log_detalhado'] : null,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function existeExecucaoProcessandoPorFonte(int $fonteId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM coletas_execucao
            WHERE fonte_id = :fonte_id
              AND status = \'PROCESSANDO\''
        );
        $stmt->execute(['fonte_id' => $fonteId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function findByIdComFonte(int $id): ?ColetaExecucao
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                ce.*,
                f.nome AS fonte_nome,
                f.codigo AS fonte_codigo
            FROM coletas_execucao ce
            INNER JOIN fontes_coleta f ON f.id = ce.fonte_id
            WHERE ce.id = :id
            LIMIT 1'
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return ColetaExecucao::fromArray($row);
    }

    /**
     * @return array<int, ColetaExecucao>
     */
    public function listRecentesPorFonte(int $fonteId, int $limit = 20): array
    {
        if ($limit < 1) {
            $limit = 20;
        }

        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM coletas_execucao
            WHERE fonte_id = :fonte_id
            ORDER BY id DESC
            LIMIT :limite'
        );

        $stmt->bindValue(':fonte_id', $fonteId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): ColetaExecucao => ColetaExecucao::fromArray($row),
            $rows
        );
    }

    /**
     * @return array<int, ColetaExecucao>
     */
    public function listRecentesComFonte(int $limit = 30): array
    {
        if ($limit < 1) {
            $limit = 30;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                ce.*,
                f.nome AS fonte_nome,
                f.codigo AS fonte_codigo
            FROM coletas_execucao ce
            INNER JOIN fontes_coleta f ON f.id = ce.fonte_id
            ORDER BY ce.id DESC
            LIMIT :limite'
        );
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): ColetaExecucao => ColetaExecucao::fromArray($row),
            $rows
        );
    }
}
