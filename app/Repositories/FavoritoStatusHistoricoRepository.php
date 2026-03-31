<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class FavoritoStatusHistoricoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function registrarTransicao(
        int $favoritoId,
        int $empresaId,
        ?string $statusAnterior,
        string $statusNovo,
        ?int $usuarioId = null,
        string $origem = 'manual'
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO favorito_status_historico (
                favorito_id,
                empresa_id,
                status_anterior,
                status_novo,
                usuario_id,
                origem,
                criado_em
            ) VALUES (
                :favorito_id,
                :empresa_id,
                :status_anterior,
                :status_novo,
                :usuario_id,
                :origem,
                NOW()
            )'
        );
        $stmt->execute([
            'favorito_id' => $favoritoId,
            'empresa_id' => $empresaId,
            'status_anterior' => $statusAnterior,
            'status_novo' => $statusNovo,
            'usuario_id' => $usuarioId,
            'origem' => $this->truncate($origem, 40),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function relatorioConversao(
        int $empresaId,
        ?string $dataDe = null,
        ?string $dataAte = null
    ): array {
        $dataDe = $this->normalizarData($dataDe) ?? date('Y-m-d', strtotime('-90 days'));
        $dataAte = $this->normalizarData($dataAte) ?? date('Y-m-d');

        $paramsBase = [
            'empresa_id' => $empresaId,
            'data_de' => $dataDe . ' 00:00:00',
            'data_ate' => $dataAte . ' 23:59:59',
        ];

        $totalEmAnalise = $this->countDistinctByStatus($paramsBase, 'EM_ANALISE');
        $totalProposta = $this->countConversaoAnaliseParaProposta($paramsBase);
        $totalEncerrado = $this->countConversaoPropostaParaEncerrado($paramsBase);

        $taxaAnaliseParaProposta = $totalEmAnalise > 0
            ? round(($totalProposta / $totalEmAnalise) * 100, 1)
            : 0.0;

        $taxaPropostaParaEncerrado = $totalProposta > 0
            ? round(($totalEncerrado / $totalProposta) * 100, 1)
            : 0.0;

        return [
            'periodo' => [
                'data_de' => $dataDe,
                'data_ate' => $dataAte,
            ],
            'totais' => [
                'em_analise' => $totalEmAnalise,
                'proposta' => $totalProposta,
                'encerrado' => $totalEncerrado,
            ],
            'taxas' => [
                'analise_para_proposta' => $taxaAnaliseParaProposta,
                'proposta_para_encerrado' => $taxaPropostaParaEncerrado,
            ],
            'funil' => [
                ['fase' => 'EM_ANALISE', 'quantidade' => $totalEmAnalise],
                ['fase' => 'PROPOSTA', 'quantidade' => $totalProposta],
                ['fase' => 'ENCERRADO', 'quantidade' => $totalEncerrado],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $paramsBase
     */
    private function countDistinctByStatus(array $paramsBase, string $status): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(DISTINCT hs.favorito_id)
            FROM favorito_status_historico hs
            WHERE hs.empresa_id = :empresa_id
              AND hs.status_novo = :status_novo
              AND hs.criado_em BETWEEN :data_de AND :data_ate'
        );
        $stmt->execute([
            'empresa_id' => (int) $paramsBase['empresa_id'],
            'status_novo' => $status,
            'data_de' => (string) $paramsBase['data_de'],
            'data_ate' => (string) $paramsBase['data_ate'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $paramsBase
     */
    private function countConversaoAnaliseParaProposta(array $paramsBase): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM (
                SELECT base.favorito_id
                FROM (
                    SELECT hs.favorito_id, MIN(hs.criado_em) AS em_analise_em
                    FROM favorito_status_historico hs
                    WHERE hs.empresa_id = :empresa_id
                      AND hs.status_novo = \'EM_ANALISE\'
                      AND hs.criado_em BETWEEN :data_de AND :data_ate
                    GROUP BY hs.favorito_id
                ) base
                INNER JOIN favorito_status_historico hp
                    ON hp.favorito_id = base.favorito_id
                   AND hp.empresa_id = :empresa_id_proposta
                   AND hp.status_novo = \'PROPOSTA\'
                   AND hp.criado_em >= base.em_analise_em
                GROUP BY base.favorito_id
            ) conversoes'
        );
        $stmt->execute([
            'empresa_id' => (int) $paramsBase['empresa_id'],
            'data_de' => (string) $paramsBase['data_de'],
            'data_ate' => (string) $paramsBase['data_ate'],
            'empresa_id_proposta' => (int) $paramsBase['empresa_id'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    /**
     * @param array<string, mixed> $paramsBase
     */
    private function countConversaoPropostaParaEncerrado(array $paramsBase): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM (
                SELECT proposta.favorito_id
                FROM (
                    SELECT base.favorito_id, MIN(hp.criado_em) AS proposta_em
                    FROM (
                        SELECT hs.favorito_id, MIN(hs.criado_em) AS em_analise_em
                        FROM favorito_status_historico hs
                        WHERE hs.empresa_id = :empresa_id
                          AND hs.status_novo = \'EM_ANALISE\'
                          AND hs.criado_em BETWEEN :data_de AND :data_ate
                        GROUP BY hs.favorito_id
                    ) base
                    INNER JOIN favorito_status_historico hp
                        ON hp.favorito_id = base.favorito_id
                       AND hp.empresa_id = :empresa_id_proposta
                       AND hp.status_novo = \'PROPOSTA\'
                       AND hp.criado_em >= base.em_analise_em
                    GROUP BY base.favorito_id
                ) proposta
                INNER JOIN favorito_status_historico he
                    ON he.favorito_id = proposta.favorito_id
                   AND he.empresa_id = :empresa_id_encerrado
                   AND he.status_novo = \'ENCERRADO\'
                   AND he.criado_em >= proposta.proposta_em
                GROUP BY proposta.favorito_id
            ) encerrados'
        );
        $stmt->execute([
            'empresa_id' => (int) $paramsBase['empresa_id'],
            'data_de' => (string) $paramsBase['data_de'],
            'data_ate' => (string) $paramsBase['data_ate'],
            'empresa_id_proposta' => (int) $paramsBase['empresa_id'],
            'empresa_id_encerrado' => (int) $paramsBase['empresa_id'],
        ]);

        return (int) $stmt->fetchColumn();
    }

    private function normalizarData(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $date = \DateTimeImmutable::createFromFormat('Y-m-d', trim($value));
        if (!$date instanceof \DateTimeImmutable) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function truncate(string $value, int $limit): string
    {
        if ($limit < 1 || strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit);
    }
}
