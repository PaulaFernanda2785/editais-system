<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\EditalDocumento;
use PDO;

class DocumentoEditalRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, EditalDocumento>
     */
    public function listByEdital(int $editalId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM edital_documentos
            WHERE edital_id = :edital_id
            ORDER BY id DESC'
        );
        $stmt->execute(['edital_id' => $editalId]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): EditalDocumento => EditalDocumento::fromArray($row),
            $rows
        );
    }
}

