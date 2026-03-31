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
            'SELECT
                ft.*,
                u.nome AS responsavel_usuario_nome
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            LEFT JOIN usuarios u ON u.id = ft.responsavel_usuario_id
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
            'SELECT
                ft.*,
                u.nome AS responsavel_usuario_nome
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            LEFT JOIN usuarios u ON u.id = ft.responsavel_usuario_id
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
                responsavel_usuario_id,
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
                :responsavel_usuario_id,
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
            'responsavel_usuario_id' => isset($data['responsavel_usuario_id'])
                ? (int) $data['responsavel_usuario_id']
                : null,
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

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAlertasVencendo(int $empresaId, int $dias = 2, int $limit = 20): array
    {
        if ($dias < 1) {
            $dias = 1;
        }
        if ($limit < 1) {
            $limit = 20;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                ft.id AS tarefa_id,
                ft.favorito_id,
                ft.titulo,
                ft.data_limite,
                ft.status,
                f.status_acompanhamento,
                e.numero_edital,
                e.orgao_nome,
                u.nome AS responsavel_usuario_nome
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            LEFT JOIN usuarios u ON u.id = ft.responsavel_usuario_id
            WHERE ft.empresa_id = :empresa_id
              AND f.empresa_id = :empresa_id_favorito
              AND ft.status <> \'CONCLUIDA\'
              AND ft.data_limite IS NOT NULL
              AND ft.data_limite >= CURDATE()
              AND ft.data_limite <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)
            ORDER BY ft.data_limite ASC, ft.id ASC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id_favorito', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAlertasVencidas(int $empresaId, int $limit = 20): array
    {
        if ($limit < 1) {
            $limit = 20;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                ft.id AS tarefa_id,
                ft.favorito_id,
                ft.titulo,
                ft.data_limite,
                ft.status,
                f.status_acompanhamento,
                e.numero_edital,
                e.orgao_nome,
                u.nome AS responsavel_usuario_nome
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            LEFT JOIN usuarios u ON u.id = ft.responsavel_usuario_id
            WHERE ft.empresa_id = :empresa_id
              AND f.empresa_id = :empresa_id_favorito
              AND ft.status <> \'CONCLUIDA\'
              AND ft.data_limite IS NOT NULL
              AND ft.data_limite < CURDATE()
            ORDER BY ft.data_limite ASC, ft.id ASC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id_favorito', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function countAlertasVencendo(int $empresaId, int $dias = 2): int
    {
        if ($dias < 1) {
            $dias = 1;
        }

        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            WHERE ft.empresa_id = :empresa_id
              AND f.empresa_id = :empresa_id_favorito
              AND ft.status <> \'CONCLUIDA\'
              AND ft.data_limite IS NOT NULL
              AND ft.data_limite >= CURDATE()
              AND ft.data_limite <= DATE_ADD(CURDATE(), INTERVAL :dias DAY)'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id_favorito', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countAlertasVencidas(int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM favorito_tarefas ft
            INNER JOIN favoritos f ON f.id = ft.favorito_id
            WHERE ft.empresa_id = :empresa_id
              AND f.empresa_id = :empresa_id_favorito
              AND ft.status <> \'CONCLUIDA\'
              AND ft.data_limite IS NOT NULL
              AND ft.data_limite < CURDATE()'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'empresa_id_favorito' => $empresaId,
        ]);

        return (int) $stmt->fetchColumn();
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
