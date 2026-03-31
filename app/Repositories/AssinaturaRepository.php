<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\Assinatura;
use PDO;
use RuntimeException;

class AssinaturaRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findParaAcessoByEmpresa(int $empresaId): ?Assinatura
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                a.*,
                p.nome AS plano_nome,
                p.descricao AS plano_descricao,
                p.limite_usuarios AS plano_limite_usuarios,
                p.limite_palavras_chave AS plano_limite_palavras_chave,
                p.limite_perfis_monitoramento AS plano_limite_perfis_monitoramento,
                p.limite_alertas_dia AS plano_limite_alertas_dia,
                p.limite_exportacoes_mes AS plano_limite_exportacoes_mes,
                p.valor_mensal AS plano_valor_mensal,
                p.status AS plano_status
            FROM assinaturas a
            INNER JOIN planos p ON p.id = a.plano_id
            WHERE a.empresa_id = :empresa_id
              AND a.status IN (\'ATIVA\', \'TESTE\')
              AND (a.data_fim IS NULL OR a.data_fim >= CURDATE())
            ORDER BY
                CASE a.status WHEN \'ATIVA\' THEN 0 ELSE 1 END,
                a.id DESC
            LIMIT 1'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return Assinatura::fromArray($row);
    }

    public function findMaisRecenteByEmpresa(int $empresaId): ?Assinatura
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                a.*,
                p.nome AS plano_nome,
                p.descricao AS plano_descricao,
                p.limite_usuarios AS plano_limite_usuarios,
                p.limite_palavras_chave AS plano_limite_palavras_chave,
                p.limite_perfis_monitoramento AS plano_limite_perfis_monitoramento,
                p.limite_alertas_dia AS plano_limite_alertas_dia,
                p.limite_exportacoes_mes AS plano_limite_exportacoes_mes,
                p.valor_mensal AS plano_valor_mensal,
                p.status AS plano_status
            FROM assinaturas a
            INNER JOIN planos p ON p.id = a.plano_id
            WHERE a.empresa_id = :empresa_id
            ORDER BY a.id DESC
            LIMIT 1'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        $row = $stmt->fetch();

        if ($row === false) {
            return null;
        }

        return Assinatura::fromArray($row);
    }

    public function criarTeste(
        int $empresaId,
        int $planoId,
        int $dias,
        ?string $gatewayReferencia = null,
        ?string $observacao = null
    ): Assinatura {
        $inicio = date('Y-m-d');
        $fim = (new \DateTimeImmutable('today'))->modify(sprintf('+%d days', $dias))->format('Y-m-d');

        $stmt = $this->pdo->prepare(
            'INSERT INTO assinaturas (
                empresa_id,
                plano_id,
                status,
                data_inicio,
                data_fim,
                gateway_referencia,
                observacao,
                criado_em
            ) VALUES (
                :empresa_id,
                :plano_id,
                :status,
                :data_inicio,
                :data_fim,
                :gateway_referencia,
                :observacao,
                NOW()
            )'
        );

        $stmt->execute([
            'empresa_id' => $empresaId,
            'plano_id' => $planoId,
            'status' => 'TESTE',
            'data_inicio' => $inicio,
            'data_fim' => $fim,
            'gateway_referencia' => $gatewayReferencia,
            'observacao' => $observacao,
        ]);

        $assinatura = $this->findMaisRecenteByEmpresa($empresaId);
        if ($assinatura === null) {
            throw new RuntimeException('Falha ao recuperar assinatura de teste criada.');
        }

        return $assinatura;
    }
}
