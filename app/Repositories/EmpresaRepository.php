<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Empresa;
use PDO;

class EmpresaRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findById(int $id): ?Empresa
    {
        $stmt = $this->pdo->prepare('SELECT * FROM empresas WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return Empresa::fromArray($row);
    }

    /**
     * @return array<int, int>
     */
    public function listIdsAtivas(?int $limit = null): array
    {
        $sql = 'SELECT id FROM empresas WHERE status = \'ATIVA\' ORDER BY id ASC';

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT :limite';
        }

        $stmt = $this->pdo->prepare($sql);
        if ($limit !== null && $limit > 0) {
            $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        }
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $ids = [];
        foreach ($rows as $row) {
            $id = (int) ($row['id'] ?? 0);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }
}
