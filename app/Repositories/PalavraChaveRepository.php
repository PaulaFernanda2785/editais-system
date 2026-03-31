<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PalavraChave;
use PDO;
use RuntimeException;

class PalavraChaveRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, PalavraChave>
     */
    public function listByEmpresaAndPerfil(int $empresaId, int $perfilId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM palavras_chave
            WHERE empresa_id = :empresa_id
              AND perfil_monitoramento_id = :perfil_monitoramento_id
            ORDER BY ativo DESC, peso DESC, termo ASC'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
        ]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): PalavraChave => PalavraChave::fromArray($row),
            $rows
        );
    }

    public function countByEmpresa(int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM palavras_chave WHERE empresa_id = :empresa_id'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn();
    }

    public function findByIdEmpresaAndPerfil(
        int $palavraId,
        int $empresaId,
        int $perfilId
    ): ?PalavraChave {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM palavras_chave
            WHERE id = :id
              AND empresa_id = :empresa_id
              AND perfil_monitoramento_id = :perfil_monitoramento_id
            LIMIT 1'
        );
        $stmt->execute([
            'id' => $palavraId,
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PalavraChave::fromArray($row);
    }

    public function findByTermoEmpresaAndPerfil(
        string $termo,
        int $empresaId,
        int $perfilId
    ): ?PalavraChave {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM palavras_chave
            WHERE termo = :termo
              AND empresa_id = :empresa_id
              AND perfil_monitoramento_id = :perfil_monitoramento_id
            LIMIT 1'
        );
        $stmt->execute([
            'termo' => $termo,
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PalavraChave::fromArray($row);
    }

    public function create(array $data): PalavraChave
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO palavras_chave (
                empresa_id,
                perfil_monitoramento_id,
                termo,
                peso,
                categoria,
                ativo,
                criado_em
            ) VALUES (
                :empresa_id,
                :perfil_monitoramento_id,
                :termo,
                :peso,
                :categoria,
                :ativo,
                NOW()
            )'
        );
        $stmt->execute([
            'empresa_id' => (int) $data['empresa_id'],
            'perfil_monitoramento_id' => (int) $data['perfil_monitoramento_id'],
            'termo' => (string) $data['termo'],
            'peso' => (int) $data['peso'],
            'categoria' => $data['categoria'] ?? null,
            'ativo' => isset($data['ativo']) ? (int) $data['ativo'] : 1,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $palavra = $this->findByIdEmpresaAndPerfil(
            $id,
            (int) $data['empresa_id'],
            (int) $data['perfil_monitoramento_id']
        );

        if ($palavra === null) {
            throw new RuntimeException('Falha ao recuperar palavra-chave criada.');
        }

        return $palavra;
    }

    public function update(int $palavraId, int $empresaId, int $perfilId, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE palavras_chave
            SET
                termo = :termo,
                peso = :peso,
                categoria = :categoria,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id
              AND perfil_monitoramento_id = :perfil_monitoramento_id'
        );
        $stmt->execute([
            'id' => $palavraId,
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
            'termo' => (string) $data['termo'],
            'peso' => (int) $data['peso'],
            'categoria' => $data['categoria'] ?? null,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function setAtivo(int $palavraId, int $empresaId, int $perfilId, bool $ativo): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE palavras_chave
            SET ativo = :ativo, atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id
              AND perfil_monitoramento_id = :perfil_monitoramento_id'
        );
        $stmt->execute([
            'id' => $palavraId,
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
            'ativo' => $ativo ? 1 : 0,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $palavraId, int $empresaId, int $perfilId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM palavras_chave
            WHERE id = :id
              AND empresa_id = :empresa_id
              AND perfil_monitoramento_id = :perfil_monitoramento_id'
        );
        $stmt->execute([
            'id' => $palavraId,
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
        ]);

        return $stmt->rowCount() > 0;
    }
}
