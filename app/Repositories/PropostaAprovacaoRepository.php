<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PropostaAprovacao;
use PDO;

class PropostaAprovacaoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, PropostaAprovacao>
     */
    public function listByProposta(int $propostaId, int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pa.*,
                us.nome AS solicitado_por_usuario_nome,
                ud.nome AS decidido_por_usuario_nome
            FROM proposta_aprovacoes pa
            LEFT JOIN usuarios us ON us.id = pa.solicitado_por_usuario_id
            LEFT JOIN usuarios ud ON ud.id = pa.decidido_por_usuario_id
            WHERE pa.proposta_id = :proposta_id
              AND pa.empresa_id = :empresa_id
            ORDER BY pa.id DESC'
        );
        $stmt->execute([
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
        ]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        return array_map(
            static fn(array $row): PropostaAprovacao => PropostaAprovacao::fromArray($row),
            $rows
        );
    }

    public function findPendenteByProposta(int $propostaId, int $empresaId): ?PropostaAprovacao
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pa.*,
                us.nome AS solicitado_por_usuario_nome,
                ud.nome AS decidido_por_usuario_nome
            FROM proposta_aprovacoes pa
            LEFT JOIN usuarios us ON us.id = pa.solicitado_por_usuario_id
            LEFT JOIN usuarios ud ON ud.id = pa.decidido_por_usuario_id
            WHERE pa.proposta_id = :proposta_id
              AND pa.empresa_id = :empresa_id
              AND pa.status_decisao = \'PENDENTE\'
            ORDER BY pa.id DESC
            LIMIT 1'
        );
        $stmt->execute([
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PropostaAprovacao::fromArray($row);
    }

    public function findByIdAndProposta(int $id, int $propostaId, int $empresaId): ?PropostaAprovacao
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pa.*,
                us.nome AS solicitado_por_usuario_nome,
                ud.nome AS decidido_por_usuario_nome
            FROM proposta_aprovacoes pa
            LEFT JOIN usuarios us ON us.id = pa.solicitado_por_usuario_id
            LEFT JOIN usuarios ud ON ud.id = pa.decidido_por_usuario_id
            WHERE pa.id = :id
              AND pa.proposta_id = :proposta_id
              AND pa.empresa_id = :empresa_id
            LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PropostaAprovacao::fromArray($row);
    }

    public function createSolicitacao(
        int $propostaId,
        int $empresaId,
        ?int $solicitadoPorUsuarioId,
        ?string $observacaoSolicitacao
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO proposta_aprovacoes (
                proposta_id,
                empresa_id,
                status_decisao,
                observacao_solicitacao,
                solicitado_por_usuario_id,
                solicitado_em,
                criado_em
            ) VALUES (
                :proposta_id,
                :empresa_id,
                \'PENDENTE\',
                :observacao_solicitacao,
                :solicitado_por_usuario_id,
                NOW(),
                NOW()
            )'
        );
        $stmt->execute([
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
            'observacao_solicitacao' => $this->normalizeText($observacaoSolicitacao, 8000),
            'solicitado_por_usuario_id' => $solicitadoPorUsuarioId,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function decidir(
        int $id,
        int $propostaId,
        int $empresaId,
        string $statusDecisao,
        ?int $decididoPorUsuarioId,
        ?string $parecer
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE proposta_aprovacoes
            SET
                status_decisao = :status_decisao,
                decidido_por_usuario_id = :decidido_por_usuario_id,
                decidido_em = NOW(),
                parecer = :parecer,
                atualizado_em = NOW()
            WHERE id = :id
              AND proposta_id = :proposta_id
              AND empresa_id = :empresa_id
              AND status_decisao = \'PENDENTE\''
        );
        $stmt->execute([
            'status_decisao' => $statusDecisao,
            'decidido_por_usuario_id' => $decididoPorUsuarioId,
            'parecer' => $this->normalizeText($parecer, 8000),
            'id' => $id,
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    private function normalizeText(?string $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim($value);
        if ($text === '') {
            return null;
        }

        if (strlen($text) > $limit) {
            $text = substr($text, 0, $limit);
        }

        return $text;
    }
}
