<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use App\Models\PropostaAlertaNotificacao;
use PDO;
use PDOException;

class PropostaAlertaNotificacaoRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    public function findByEmpresaPropostaTipo(int $empresaId, int $propostaId, string $tipoAlerta): ?PropostaAlertaNotificacao
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM proposta_alerta_notificacoes
            WHERE empresa_id = :empresa_id
              AND proposta_id = :proposta_id
              AND tipo_alerta = :tipo_alerta
            LIMIT 1'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'proposta_id' => $propostaId,
            'tipo_alerta' => $tipoAlerta,
        ]);

        $row = $stmt->fetch();
        if ($row === false) {
            return null;
        }

        return PropostaAlertaNotificacao::fromArray($row);
    }

    /**
     * @return array{id:int, eh_novo:bool}
     */
    public function upsertAtivo(int $empresaId, int $propostaId, string $tipoAlerta): array
    {
        $existente = $this->findByEmpresaPropostaTipo($empresaId, $propostaId, $tipoAlerta);
        if ($existente === null) {
            try {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO proposta_alerta_notificacoes (
                        empresa_id,
                        proposta_id,
                        tipo_alerta,
                        ativo,
                        novo,
                        primeiro_detectado_em,
                        ultimo_detectado_em,
                        criado_em
                    ) VALUES (
                        :empresa_id,
                        :proposta_id,
                        :tipo_alerta,
                        1,
                        1,
                        NOW(),
                        NOW(),
                        NOW()
                    )'
                );
                $stmt->execute([
                    'empresa_id' => $empresaId,
                    'proposta_id' => $propostaId,
                    'tipo_alerta' => $tipoAlerta,
                ]);

                return [
                    'id' => (int) $this->pdo->lastInsertId(),
                    'eh_novo' => true,
                ];
            } catch (PDOException $exception) {
                $duplicateEntry = $exception->getCode() === '23000';
                if (!$duplicateEntry) {
                    throw $exception;
                }

                $existente = $this->findByEmpresaPropostaTipo($empresaId, $propostaId, $tipoAlerta);
                if ($existente === null) {
                    throw $exception;
                }
            }
        }

        if (!$existente->ativo) {
            $stmt = $this->pdo->prepare(
                'UPDATE proposta_alerta_notificacoes
                SET
                    ativo = 1,
                    novo = 1,
                    resolvido_em = NULL,
                    visualizado_em = NULL,
                    email_enviado_em = NULL,
                    ultimo_erro_email = NULL,
                    email_tentativas = 0,
                    ultimo_detectado_em = NOW(),
                    atualizado_em = NOW()
                WHERE id = :id'
            );
            $stmt->execute(['id' => $existente->id]);

            return [
                'id' => $existente->id,
                'eh_novo' => true,
            ];
        }

        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_notificacoes
            SET
                ativo = 1,
                ultimo_detectado_em = NOW(),
                atualizado_em = NOW()
            WHERE id = :id'
        );
        $stmt->execute(['id' => $existente->id]);

        return [
            'id' => $existente->id,
            'eh_novo' => false,
        ];
    }

    /**
     * @param array<int, int> $propostaIdsAtivas
     */
    public function resolverAusentes(int $empresaId, string $tipoAlerta, array $propostaIdsAtivas): int
    {
        if ($propostaIdsAtivas === []) {
            $stmt = $this->pdo->prepare(
                'UPDATE proposta_alerta_notificacoes
                SET
                    ativo = 0,
                    novo = 0,
                    resolvido_em = NOW(),
                    atualizado_em = NOW()
                WHERE empresa_id = :empresa_id
                  AND tipo_alerta = :tipo_alerta
                  AND ativo = 1'
            );
            $stmt->execute([
                'empresa_id' => $empresaId,
                'tipo_alerta' => $tipoAlerta,
            ]);

            return $stmt->rowCount();
        }

        $placeholders = [];
        foreach (array_values($propostaIdsAtivas) as $index => $propostaId) {
            $key = 'pid_' . $index;
            $placeholders[] = ':' . $key;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_notificacoes
            SET
                ativo = 0,
                novo = 0,
                resolvido_em = NOW(),
                atualizado_em = NOW()
            WHERE empresa_id = :empresa_id
              AND tipo_alerta = :tipo_alerta
              AND ativo = 1
              AND proposta_id NOT IN (' . implode(',', $placeholders) . ')'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':tipo_alerta', $tipoAlerta, PDO::PARAM_STR);
        foreach (array_values($propostaIdsAtivas) as $index => $propostaId) {
            $stmt->bindValue(':pid_' . $index, (int) $propostaId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return $stmt->rowCount();
    }

    /**
     * @param array<int, int> $ids
     */
    public function marcarEmailEnviado(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        $idsLimpos = array_values(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0));
        if ($idsLimpos === []) {
            return;
        }

        $placeholders = [];
        $params = [];
        foreach ($idsLimpos as $index => $id) {
            $key = 'id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_notificacoes
            SET
                email_enviado_em = NOW(),
                email_tentativas = email_tentativas + 1,
                ultimo_erro_email = NULL,
                atualizado_em = NOW()
            WHERE id IN (' . implode(',', $placeholders) . ')'
        );
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();
    }

    /**
     * @param array<int, int> $ids
     */
    public function registrarFalhaEmail(array $ids, string $erro): void
    {
        if ($ids === []) {
            return;
        }

        $idsLimpos = array_values(array_filter(array_map('intval', $ids), static fn(int $id): bool => $id > 0));
        if ($idsLimpos === []) {
            return;
        }

        $placeholders = [];
        $params = [];
        foreach ($idsLimpos as $index => $id) {
            $key = 'id_' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $id;
        }

        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_notificacoes
            SET
                email_tentativas = email_tentativas + 1,
                ultimo_erro_email = :erro,
                atualizado_em = NOW()
            WHERE id IN (' . implode(',', $placeholders) . ')'
        );
        $stmt->bindValue(':erro', $this->truncate($erro, 255), PDO::PARAM_STR);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        }
        $stmt->execute();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAtivosDashboard(int $empresaId, int $limit = 10): array
    {
        if ($limit < 1) {
            $limit = 10;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                pan.id,
                pan.proposta_id,
                pan.tipo_alerta,
                pan.novo,
                pan.primeiro_detectado_em,
                pan.ultimo_detectado_em,
                pan.email_enviado_em,
                p.titulo AS proposta_titulo,
                e.numero_edital,
                e.orgao_nome,
                e.modalidade,
                ls.ultima_submissao,
                lr.ultima_data_resultado,
                CASE
                    WHEN pan.tipo_alerta = \'SEM_RESULTADO\'
                        THEN TIMESTAMPDIFF(DAY, ls.ultima_submissao, NOW())
                    ELSE TIMESTAMPDIFF(DAY, lr.ultima_data_resultado, NOW())
                END AS dias_referencia
            FROM proposta_alerta_notificacoes pan
            INNER JOIN propostas_execucao p
                ON p.id = pan.proposta_id
               AND p.empresa_id = pan.empresa_id
            INNER JOIN favoritos f ON f.id = p.favorito_id
            INNER JOIN editais e ON e.id = f.edital_id
            LEFT JOIN (
                SELECT proposta_id, MAX(data_submissao) AS ultima_submissao
                FROM proposta_submissoes
                WHERE empresa_id = :empresa_id_submissoes
                GROUP BY proposta_id
            ) ls ON ls.proposta_id = pan.proposta_id
            LEFT JOIN (
                SELECT proposta_id, MAX(data_resultado) AS ultima_data_resultado
                FROM proposta_resultados
                WHERE empresa_id = :empresa_id_resultados
                GROUP BY proposta_id
            ) lr ON lr.proposta_id = pan.proposta_id
            WHERE pan.empresa_id = :empresa_id
              AND pan.ativo = 1
            ORDER BY pan.novo DESC, pan.ultimo_detectado_em DESC, pan.id DESC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id_submissoes', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id_resultados', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    public function countAtivosNovos(int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM proposta_alerta_notificacoes
            WHERE empresa_id = :empresa_id
              AND ativo = 1
              AND novo = 1'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn();
    }

    public function countAtivos(int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*)
            FROM proposta_alerta_notificacoes
            WHERE empresa_id = :empresa_id
              AND ativo = 1'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return (int) $stmt->fetchColumn();
    }

    public function marcarNovosComoVisualizados(int $empresaId): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_notificacoes
            SET
                novo = 0,
                visualizado_em = NOW(),
                atualizado_em = NOW()
            WHERE empresa_id = :empresa_id
              AND ativo = 1
              AND novo = 1'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        return $stmt->rowCount();
    }

    private function truncate(string $value, int $limit): string
    {
        if ($limit < 1 || strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit);
    }
}
