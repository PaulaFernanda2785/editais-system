<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Usuario;
use PDO;

class UsuarioRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findByEmail(string $email): ?Usuario
    {
        $stmt = $this->pdo->prepare('SELECT * FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        $data = $stmt->fetch();
        if ($data === false) {
            return null;
        }

        return Usuario::fromArray($data);
    }

    public function findByIdAndEmpresa(int $usuarioId, int $empresaId): ?Usuario
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM usuarios WHERE id = :id AND empresa_id = :empresa_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $usuarioId,
            'empresa_id' => $empresaId,
        ]);

        $data = $stmt->fetch();
        if ($data === false) {
            return null;
        }

        return Usuario::fromArray($data);
    }

    public function updateUltimoLogin(int $usuarioId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE usuarios SET ultimo_login_em = NOW(), atualizado_em = NOW() WHERE id = :id'
        );
        $stmt->execute(['id' => $usuarioId]);
    }
}
