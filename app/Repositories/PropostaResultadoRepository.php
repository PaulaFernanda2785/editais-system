<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PropostaResultado;
use PDO;

class PropostaResultadoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<int, PropostaResultado>
     */
    public function listByProposta(int $propostaId, int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pr.*,
                u.nome AS usuario_nome
            FROM proposta_resultados pr
            LEFT JOIN usuarios u ON u.id = pr.usuario_id
            WHERE pr.proposta_id = :proposta_id
              AND pr.empresa_id = :empresa_id
            ORDER BY pr.data_resultado DESC, pr.id DESC'
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
            static fn(array $row): PropostaResultado => PropostaResultado::fromArray($row),
            $rows
        );
    }

    public function findLatestByProposta(int $propostaId, int $empresaId): ?PropostaResultado
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                pr.*,
                u.nome AS usuario_nome
            FROM proposta_resultados pr
            LEFT JOIN usuarios u ON u.id = pr.usuario_id
            WHERE pr.proposta_id = :proposta_id
              AND pr.empresa_id = :empresa_id
            ORDER BY pr.data_resultado DESC, pr.id DESC
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

        return PropostaResultado::fromArray($row);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO proposta_resultados (
                proposta_id,
                empresa_id,
                usuario_id,
                situacao,
                data_resultado,
                valor_homologado,
                colocacao,
                motivo,
                link_ata,
                observacao,
                criado_em
            ) VALUES (
                :proposta_id,
                :empresa_id,
                :usuario_id,
                :situacao,
                :data_resultado,
                :valor_homologado,
                :colocacao,
                :motivo,
                :link_ata,
                :observacao,
                NOW()
            )'
        );
        $stmt->execute([
            'proposta_id' => (int) ($data['proposta_id'] ?? 0),
            'empresa_id' => (int) ($data['empresa_id'] ?? 0),
            'usuario_id' => isset($data['usuario_id']) ? (int) $data['usuario_id'] : null,
            'situacao' => $this->normalizeSituacao((string) ($data['situacao'] ?? 'EM_JULGAMENTO')),
            'data_resultado' => (string) ($data['data_resultado'] ?? ''),
            'valor_homologado' => $this->normalizeDecimal($data['valor_homologado'] ?? null),
            'colocacao' => $this->normalizePositiveInt($data['colocacao'] ?? null),
            'motivo' => $this->normalizeText($data['motivo'] ?? null, 9000),
            'link_ata' => $this->normalizeText($data['link_ata'] ?? null, 500),
            'observacao' => $this->normalizeText($data['observacao'] ?? null, 9000),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @return array<string, int>
     */
    public function countByEmpresaGroupedSituacao(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT situacao, COUNT(*) AS total
            FROM proposta_resultados
            WHERE empresa_id = :empresa_id
            GROUP BY situacao'
        );
        $stmt->execute(['empresa_id' => $empresaId]);

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $situacao = (string) ($row['situacao'] ?? '');
            if ($situacao === '') {
                continue;
            }

            $grouped[$situacao] = (int) ($row['total'] ?? 0);
        }

        return $grouped;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAlertasSemResultado(int $empresaId, string $cutoffDateTime, int $limit = 10): array
    {
        if ($limit < 1) {
            $limit = 10;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                p.id AS proposta_id,
                p.titulo AS proposta_titulo,
                e.numero_edital,
                e.orgao_nome,
                e.modalidade,
                ls.ultima_submissao,
                TIMESTAMPDIFF(DAY, ls.ultima_submissao, NOW()) AS dias_sem_retorno
            FROM propostas_execucao p
            INNER JOIN favoritos f ON f.id = p.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            INNER JOIN (
                SELECT
                    ps.proposta_id,
                    MAX(ps.data_submissao) AS ultima_submissao
                FROM proposta_submissoes ps
                WHERE ps.empresa_id = :empresa_id_sub
                GROUP BY ps.proposta_id
            ) ls ON ls.proposta_id = p.id
            LEFT JOIN proposta_resultados pr
                ON pr.proposta_id = p.id
               AND pr.empresa_id = p.empresa_id
            WHERE p.empresa_id = :empresa_id_main
              AND p.status = \'ENVIADA\'
              AND ls.ultima_submissao <= :cutoff_data
            GROUP BY
                p.id,
                p.titulo,
                e.numero_edital,
                e.orgao_nome,
                e.modalidade,
                ls.ultima_submissao
            HAVING COUNT(pr.id) = 0
            ORDER BY ls.ultima_submissao ASC, p.id ASC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id_sub', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id_main', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':cutoff_data', $cutoffDateTime, PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAlertasJulgamentoParado(int $empresaId, string $cutoffDateTime, int $limit = 10): array
    {
        if ($limit < 1) {
            $limit = 10;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                p.id AS proposta_id,
                p.titulo AS proposta_titulo,
                e.numero_edital,
                e.orgao_nome,
                e.modalidade,
                r.data_resultado AS ultima_atualizacao_resultado,
                TIMESTAMPDIFF(DAY, r.data_resultado, NOW()) AS dias_em_julgamento
            FROM propostas_execucao p
            INNER JOIN favoritos f ON f.id = p.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            INNER JOIN (
                SELECT
                    pr1.id,
                    pr1.proposta_id,
                    pr1.data_resultado,
                    pr1.situacao
                FROM proposta_resultados pr1
                INNER JOIN (
                    SELECT
                        proposta_id,
                        MAX(id) AS max_id
                    FROM proposta_resultados
                    WHERE empresa_id = :empresa_id_sub_latest
                    GROUP BY proposta_id
                ) latest ON latest.max_id = pr1.id
                WHERE pr1.empresa_id = :empresa_id_sub_rows
            ) r ON r.proposta_id = p.id
            WHERE p.empresa_id = :empresa_id_main
              AND p.status = \'ENVIADA\'
              AND r.situacao = \'EM_JULGAMENTO\'
              AND r.data_resultado <= :cutoff_data
            ORDER BY r.data_resultado ASC, p.id ASC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id_sub_latest', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id_sub_rows', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id_main', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':cutoff_data', $cutoffDateTime, PDO::PARAM_STR);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function painelWinLossPorOrgao(int $empresaId, int $limit = 10): array
    {
        return $this->painelWinLossByDimensao($empresaId, 'orgao', $limit);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function painelWinLossPorModalidade(int $empresaId, int $limit = 10): array
    {
        return $this->painelWinLossByDimensao($empresaId, 'modalidade', $limit);
    }

    private function normalizeSituacao(string $situacao): string
    {
        $situacao = strtoupper(trim($situacao));
        if (!in_array($situacao, ['EM_JULGAMENTO', 'VENCEDORA', 'NAO_VENCEDORA', 'DESCLASSIFICADA', 'ANULADA'], true)) {
            return 'EM_JULGAMENTO';
        }

        return $situacao;
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

    private function normalizeDecimal(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = str_replace(['.', ','], ['', '.'], (string) $value);
        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function normalizePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $int = (int) $value;
        if ($int < 1) {
            return null;
        }

        return $int;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function painelWinLossByDimensao(int $empresaId, string $dimensao, int $limit): array
    {
        if ($limit < 1) {
            $limit = 10;
        }

        $coluna = $dimensao === 'modalidade' ? 'e.modalidade' : 'e.orgao_nome';
        $stmt = $this->pdo->prepare(
            'SELECT
                ' . $coluna . ' AS dimensao,
                COUNT(*) AS total,
                SUM(CASE WHEN pr.situacao = \'VENCEDORA\' THEN 1 ELSE 0 END) AS vitorias,
                SUM(CASE WHEN pr.situacao IN (\'NAO_VENCEDORA\', \'DESCLASSIFICADA\', \'ANULADA\') THEN 1 ELSE 0 END) AS derrotas,
                SUM(CASE WHEN pr.situacao = \'EM_JULGAMENTO\' THEN 1 ELSE 0 END) AS em_julgamento
            FROM proposta_resultados pr
            INNER JOIN propostas_execucao p
                ON p.id = pr.proposta_id
               AND p.empresa_id = pr.empresa_id
            INNER JOIN favoritos f ON f.id = p.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            WHERE pr.empresa_id = :empresa_id
            GROUP BY ' . $coluna . '
            ORDER BY total DESC, vitorias DESC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $result = [];
        foreach ($rows as $row) {
            $nomeDimensao = trim((string) ($row['dimensao'] ?? ''));
            if ($nomeDimensao === '') {
                $nomeDimensao = '-';
            }

            $vitorias = (int) ($row['vitorias'] ?? 0);
            $derrotas = (int) ($row['derrotas'] ?? 0);
            $emJulgamento = (int) ($row['em_julgamento'] ?? 0);
            $finalizados = $vitorias + $derrotas;

            $result[] = [
                'dimensao' => $nomeDimensao,
                'total' => (int) ($row['total'] ?? 0),
                'vitorias' => $vitorias,
                'derrotas' => $derrotas,
                'em_julgamento' => $emJulgamento,
                'taxa_sucesso' => $finalizados > 0 ? round(($vitorias / $finalizados) * 100, 1) : 0.0,
            ];
        }

        return $result;
    }
}
