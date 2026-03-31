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
}
