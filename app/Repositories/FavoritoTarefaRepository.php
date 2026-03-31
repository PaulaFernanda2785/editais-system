<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\FavoritoTarefa;
use PDO;
use RuntimeException;

class FavoritoTarefaRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, FavoritoTarefa>
     */
    public function listByFavorito(int $favoritoId, int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ft.*
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            WHERE ft.favorito_id = :favorito_id
              AND ft.empresa_id = :empresa_id_tarefa
              AND f.empresa_id = :empresa_id_favorito
            ORDER BY ft.ordem ASC, ft.id ASC'
        );
        $stmt->execute([
            'favorito_id' => $favoritoId,
            'empresa_id_tarefa' => $empresaId,
            'empresa_id_favorito' => $empresaId,
        ]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): FavoritoTarefa => FavoritoTarefa::fromArray($row),
            $rows
        );
    }

    public function findByIdAndFavorito(int $tarefaId, int $favoritoId, int $empresaId): ?FavoritoTarefa
    {
        $stmt = $this->pdo->prepare(
            'SELECT ft.*
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            WHERE ft.id = :id
              AND ft.favorito_id = :favorito_id
              AND ft.empresa_id = :empresa_id_tarefa
              AND f.empresa_id = :empresa_id_favorito
            LIMIT 1'
        );
        $stmt->execute([
            'id' => $tarefaId,
            'favorito_id' => $favoritoId,
            'empresa_id_tarefa' => $empresaId,
            'empresa_id_favorito' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return FavoritoTarefa::fromArray($row);
    }

    public function create(array $data): FavoritoTarefa
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO favorito_tarefas (
                favorito_id,
                empresa_id,
                titulo,
                descricao,
                responsavel,
                data_limite,
                status,
                ordem,
                concluida_em,
                criado_em
            ) VALUES (
                :favorito_id,
                :empresa_id,
                :titulo,
                :descricao,
                :responsavel,
                :data_limite,
                :status,
                :ordem,
                :concluida_em,
                NOW()
            )'
        );
        $stmt->execute([
            'favorito_id' => (int) ($data['favorito_id'] ?? 0),
            'empresa_id' => (int) ($data['empresa_id'] ?? 0),
            'titulo' => (string) ($data['titulo'] ?? ''),
            'descricao' => $this->normalizeText($data['descricao'] ?? null, 3000),
            'responsavel' => $this->normalizeText($data['responsavel'] ?? null, 120),
            'data_limite' => $data['data_limite'] ?? null,
            'status' => (string) ($data['status'] ?? 'PENDENTE'),
            'ordem' => (int) ($data['ordem'] ?? 1),
            'concluida_em' => isset($data['concluida_em']) ? (string) $data['concluida_em'] : null,
        ]);

        $tarefaId = (int) $this->pdo->lastInsertId();
        $tarefa = $this->findByIdAndFavorito(
            $tarefaId,
            (int) ($data['favorito_id'] ?? 0),
            (int) ($data['empresa_id'] ?? 0)
        );

        if ($tarefa === null) {
            throw new RuntimeException('Falha ao recuperar tarefa criada.');
        }

        return $tarefa;
    }

    public function updateStatus(int $tarefaId, int $favoritoId, int $empresaId, string $status): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE favorito_tarefas
            SET
                status = :status_set,
                concluida_em = CASE WHEN :status_check = \'CONCLUIDA\' THEN NOW() ELSE NULL END,
                atualizado_em = NOW()
            WHERE id = :id
              AND favorito_id = :favorito_id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'status_set' => $status,
            'status_check' => $status,
            'id' => $tarefaId,
            'favorito_id' => $favoritoId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $tarefaId, int $favoritoId, int $empresaId): bool
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM favorito_tarefas
            WHERE id = :id
              AND favorito_id = :favorito_id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'id' => $tarefaId,
            'favorito_id' => $favoritoId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function countByFavorito(int $favoritoId, int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM favorito_tarefas
            WHERE favorito_id = :favorito_id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'favorito_id' => $favoritoId,
            'empresa_id' => $empresaId,
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function nextOrdem(int $favoritoId, int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT MAX(ordem)
            FROM favorito_tarefas
            WHERE favorito_id = :favorito_id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'favorito_id' => $favoritoId,
            'empresa_id' => $empresaId,
        ]);

        $max = $stmt->fetchColumn();
        if (!is_numeric($max)) {
            return 1;
        }

        return ((int) $max) + 1;
    }

    private function normalizeText(mixed $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if (strlen($text) > $limit) {
            $text = substr($text, 0, $limit);
        }

        return $text;
    }
}
