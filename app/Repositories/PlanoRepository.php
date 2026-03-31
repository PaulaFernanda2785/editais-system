<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Plano;
use PDO;

class PlanoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findAtivoByNome(string $nome): ?Plano
    {
        $stmt = $this->pdo->prepare('SELECT * FROM planos WHERE nome = :nome AND status = :status LIMIT 1');
        $stmt->execute([
            'nome' => strtoupper(trim($nome)),
            'status' => 'ATIVO',
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return Plano::fromArray($row);
    }

    public function findPrimeiroAtivo(): ?Plano
    {
        $stmt = $this->pdo->prepare('SELECT * FROM planos WHERE status = :status ORDER BY valor_mensal ASC, id ASC LIMIT 1');
        $stmt->execute(['status' => 'ATIVO']);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return Plano::fromArray($row);
    }
}
