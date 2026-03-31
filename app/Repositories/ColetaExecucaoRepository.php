<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\ColetaExecucao;
use PDO;

class ColetaExecucaoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
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
}
