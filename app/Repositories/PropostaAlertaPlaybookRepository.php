<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;
use PDO;

class PropostaAlertaPlaybookRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Database::connection();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByNotificacao(int $empresaId, int $alertaNotificacaoId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT pb.*, pan.ativo AS alerta_ativo
            FROM proposta_alerta_playbooks pb
            INNER JOIN proposta_alerta_notificacoes pan
                ON pan.id = pb.alerta_notificacao_id
            WHERE pb.empresa_id = :empresa_id
              AND pb.alerta_notificacao_id = :alerta_notificacao_id
            LIMIT 1'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'alerta_notificacao_id' => $alertaNotificacaoId,
        ]);

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO proposta_alerta_playbooks (
                empresa_id,
                alerta_notificacao_id,
                proposta_id,
                favorito_id,
                tipo_alerta,
                status,
                prioridade,
                fator_priorizacao,
                sla_horas,
                prazo_sla_em,
                progresso_percentual,
                responsavel_usuario_id,
                responsavel_nome,
                responsavel_email,
                criado_em
            ) VALUES (
                :empresa_id,
                :alerta_notificacao_id,
                :proposta_id,
                :favorito_id,
                :tipo_alerta,
                :status,
                :prioridade,
                :fator_priorizacao,
                :sla_horas,
                :prazo_sla_em,
                :progresso_percentual,
                :responsavel_usuario_id,
                :responsavel_nome,
                :responsavel_email,
                NOW()
            )'
        );
        $stmt->execute([
            'empresa_id' => (int) ($data['empresa_id'] ?? 0),
            'alerta_notificacao_id' => (int) ($data['alerta_notificacao_id'] ?? 0),
            'proposta_id' => (int) ($data['proposta_id'] ?? 0),
            'favorito_id' => (int) ($data['favorito_id'] ?? 0),
            'tipo_alerta' => (string) ($data['tipo_alerta'] ?? 'SEM_RESULTADO'),
            'status' => (string) ($data['status'] ?? 'ATIVO'),
            'prioridade' => (string) ($data['prioridade'] ?? 'MEDIA'),
            'fator_priorizacao' => (float) ($data['fator_priorizacao'] ?? 1.0),
            'sla_horas' => (int) ($data['sla_horas'] ?? 48),
            'prazo_sla_em' => (string) ($data['prazo_sla_em'] ?? date('Y-m-d H:i:s')),
            'progresso_percentual' => (float) ($data['progresso_percentual'] ?? 0.0),
            'responsavel_usuario_id' => isset($data['responsavel_usuario_id'])
                ? (int) $data['responsavel_usuario_id']
                : null,
            'responsavel_nome' => $this->truncate($data['responsavel_nome'] ?? null, 120),
            'responsavel_email' => $this->truncate($data['responsavel_email'] ?? null, 180),
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function reabrir(int $id, int $empresaId, array $data): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_playbooks
            SET
                proposta_id = :proposta_id,
                favorito_id = :favorito_id,
                tipo_alerta = :tipo_alerta,
                status = :status,
                prioridade = :prioridade,
                fator_priorizacao = :fator_priorizacao,
                sla_horas = :sla_horas,
                prazo_sla_em = :prazo_sla_em,
                progresso_percentual = 0.00,
                responsavel_usuario_id = :responsavel_usuario_id,
                responsavel_nome = :responsavel_nome,
                responsavel_email = :responsavel_email,
                primeira_atividade_em = NULL,
                ultima_atividade_em = NULL,
                escalonado_em = NULL,
                escalonamento_nivel = 0,
                escalonamento_motivo = NULL,
                encerrado_em = NULL,
                resultado_win_loss = \'ABERTO\',
                aprendizado_resumo = NULL,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'proposta_id' => (int) ($data['proposta_id'] ?? 0),
            'favorito_id' => (int) ($data['favorito_id'] ?? 0),
            'tipo_alerta' => (string) ($data['tipo_alerta'] ?? 'SEM_RESULTADO'),
            'status' => (string) ($data['status'] ?? 'ATIVO'),
            'prioridade' => (string) ($data['prioridade'] ?? 'MEDIA'),
            'fator_priorizacao' => (float) ($data['fator_priorizacao'] ?? 1.0),
            'sla_horas' => (int) ($data['sla_horas'] ?? 48),
            'prazo_sla_em' => (string) ($data['prazo_sla_em'] ?? date('Y-m-d H:i:s')),
            'responsavel_usuario_id' => isset($data['responsavel_usuario_id'])
                ? (int) $data['responsavel_usuario_id']
                : null,
            'responsavel_nome' => $this->truncate($data['responsavel_nome'] ?? null, 120),
            'responsavel_email' => $this->truncate($data['responsavel_email'] ?? null, 180),
            'id' => $id,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function limparMapeamentosTarefas(int $playbookId, int $empresaId): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM proposta_alerta_playbook_tarefas
            WHERE playbook_id = :playbook_id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'playbook_id' => $playbookId,
            'empresa_id' => $empresaId,
        ]);
    }

    public function adicionarTarefaMapeada(
        int $playbookId,
        int $empresaId,
        int $favoritoTarefaId,
        string $tipoTarefa
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO proposta_alerta_playbook_tarefas (
                playbook_id,
                empresa_id,
                favorito_tarefa_id,
                tipo_tarefa,
                criado_em
            ) VALUES (
                :playbook_id,
                :empresa_id,
                :favorito_tarefa_id,
                :tipo_tarefa,
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                favorito_tarefa_id = VALUES(favorito_tarefa_id),
                tipo_tarefa = VALUES(tipo_tarefa)'
        );
        $stmt->execute([
            'playbook_id' => $playbookId,
            'empresa_id' => $empresaId,
            'favorito_tarefa_id' => $favoritoTarefaId,
            'tipo_tarefa' => $tipoTarefa,
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarAtivosParaProcessamento(int $empresaId, int $limit = 200): array
    {
        if ($limit < 1) {
            $limit = 200;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                pb.*,
                pan.ativo AS alerta_ativo,
                p.titulo AS proposta_titulo,
                e.orgao_nome,
                e.numero_edital,
                u.nome AS responsavel_usuario_nome,
                u.email AS responsavel_usuario_email
            FROM proposta_alerta_playbooks pb
            INNER JOIN proposta_alerta_notificacoes pan
                ON pan.id = pb.alerta_notificacao_id
               AND pan.empresa_id = pb.empresa_id
            INNER JOIN propostas_execucao p
                ON p.id = pb.proposta_id
               AND p.empresa_id = pb.empresa_id
            INNER JOIN favoritos f
                ON f.id = pb.favorito_id
               AND f.empresa_id = pb.empresa_id
            INNER JOIN editais e ON e.id = f.edital_id
            LEFT JOIN usuarios u
                ON u.id = pb.responsavel_usuario_id
               AND u.empresa_id = pb.empresa_id
            WHERE pb.empresa_id = :empresa_id
              AND pan.ativo = 1
              AND pb.status IN (\'ATIVO\', \'EM_PROGRESSO\', \'ESCALADO\')
            ORDER BY pb.prazo_sla_em ASC, pb.id ASC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarPendentesEncerramento(int $empresaId, int $limit = 200): array
    {
        if ($limit < 1) {
            $limit = 200;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                pb.*,
                pan.ativo AS alerta_ativo
            FROM proposta_alerta_playbooks pb
            INNER JOIN proposta_alerta_notificacoes pan
                ON pan.id = pb.alerta_notificacao_id
               AND pan.empresa_id = pb.empresa_id
            WHERE pb.empresa_id = :empresa_id
              AND pan.ativo = 0
              AND pb.status <> \'ENCERRADO\'
            ORDER BY pb.id ASC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<string, int|string|null>
     */
    public function resumoTarefas(int $playbookId, int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN ft.status = \'CONCLUIDA\' THEN 1 ELSE 0 END) AS concluidas,
                SUM(CASE WHEN ft.status = \'EM_ANDAMENTO\' THEN 1 ELSE 0 END) AS em_andamento,
                SUM(CASE WHEN ft.status = \'BLOQUEADA\' THEN 1 ELSE 0 END) AS bloqueadas,
                SUM(CASE WHEN ft.status = \'PENDENTE\' THEN 1 ELSE 0 END) AS pendentes,
                MIN(CASE
                    WHEN ft.status <> \'PENDENTE\'
                        THEN COALESCE(ft.atualizado_em, ft.criado_em)
                    ELSE NULL
                END) AS primeira_atividade_em,
                MAX(CASE
                    WHEN ft.status <> \'PENDENTE\'
                        THEN COALESCE(ft.atualizado_em, ft.criado_em)
                    ELSE NULL
                END) AS ultima_atividade_em
            FROM proposta_alerta_playbook_tarefas pbt
            INNER JOIN favorito_tarefas ft
                ON ft.id = pbt.favorito_tarefa_id
               AND ft.empresa_id = pbt.empresa_id
            WHERE pbt.playbook_id = :playbook_id
              AND pbt.empresa_id = :empresa_id'
        );
        $stmt->execute([
            'playbook_id' => $playbookId,
            'empresa_id' => $empresaId,
        ]);

        $row = $stmt->fetch();
        if (!is_array($row)) {
            return [
                'total' => 0,
                'concluidas' => 0,
                'em_andamento' => 0,
                'bloqueadas' => 0,
                'pendentes' => 0,
                'primeira_atividade_em' => null,
                'ultima_atividade_em' => null,
            ];
        }

        return [
            'total' => (int) ($row['total'] ?? 0),
            'concluidas' => (int) ($row['concluidas'] ?? 0),
            'em_andamento' => (int) ($row['em_andamento'] ?? 0),
            'bloqueadas' => (int) ($row['bloqueadas'] ?? 0),
            'pendentes' => (int) ($row['pendentes'] ?? 0),
            'primeira_atividade_em' => isset($row['primeira_atividade_em']) ? (string) $row['primeira_atividade_em'] : null,
            'ultima_atividade_em' => isset($row['ultima_atividade_em']) ? (string) $row['ultima_atividade_em'] : null,
        ];
    }

    public function atualizarProgressoStatus(
        int $playbookId,
        int $empresaId,
        float $progressoPercentual,
        string $status,
        ?string $primeiraAtividadeEm,
        ?string $ultimaAtividadeEm
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_playbooks
            SET
                progresso_percentual = :progresso_percentual,
                status = :status,
                primeira_atividade_em = :primeira_atividade_em,
                ultima_atividade_em = :ultima_atividade_em,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id'
        );
        $stmt->execute([
            'progresso_percentual' => $progressoPercentual,
            'status' => $status,
            'primeira_atividade_em' => $primeiraAtividadeEm,
            'ultima_atividade_em' => $ultimaAtividadeEm,
            'id' => $playbookId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function marcarEscalonado(
        int $playbookId,
        int $empresaId,
        int $nivel,
        string $motivo
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_playbooks
            SET
                status = \'ESCALADO\',
                escalonado_em = NOW(),
                escalonamento_nivel = :escalonamento_nivel,
                escalonamento_motivo = :escalonamento_motivo,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id
              AND escalonado_em IS NULL'
        );
        $stmt->execute([
            'escalonamento_nivel' => $nivel,
            'escalonamento_motivo' => $this->truncate($motivo, 255),
            'id' => $playbookId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    public function encerrar(
        int $playbookId,
        int $empresaId,
        string $resultadoWinLoss,
        string $aprendizadoResumo
    ): bool {
        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_playbooks
            SET
                status = \'ENCERRADO\',
                encerrado_em = NOW(),
                resultado_win_loss = :resultado_win_loss,
                aprendizado_resumo = :aprendizado_resumo,
                atualizado_em = NOW()
            WHERE id = :id
              AND empresa_id = :empresa_id
              AND status <> \'ENCERRADO\''
        );
        $stmt->execute([
            'resultado_win_loss' => $resultadoWinLoss,
            'aprendizado_resumo' => $this->truncate($aprendizadoResumo, 9000),
            'id' => $playbookId,
            'empresa_id' => $empresaId,
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * @param array<string, mixed> $detalhes
     */
    public function registrarEvento(
        int $playbookId,
        int $empresaId,
        string $tipoEvento,
        string $descricao,
        array $detalhes = []
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO proposta_alerta_playbook_eventos (
                playbook_id,
                empresa_id,
                tipo_evento,
                descricao,
                detalhes_json,
                criado_em
            ) VALUES (
                :playbook_id,
                :empresa_id,
                :tipo_evento,
                :descricao,
                :detalhes_json,
                NOW()
            )'
        );
        $stmt->execute([
            'playbook_id' => $playbookId,
            'empresa_id' => $empresaId,
            'tipo_evento' => $tipoEvento,
            'descricao' => $this->truncate($descricao, 255),
            'detalhes_json' => $detalhes !== []
                ? json_encode($detalhes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : null,
        ]);
    }

    /**
     * @return array<string, int>
     */
    public function resumoOperacionalDashboard(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                SUM(CASE WHEN status IN (\'ATIVO\', \'EM_PROGRESSO\', \'ESCALADO\') THEN 1 ELSE 0 END) AS ativos,
                SUM(CASE WHEN status IN (\'ATIVO\', \'EM_PROGRESSO\') AND progresso_percentual <= 0 THEN 1 ELSE 0 END) AS sem_progresso,
                SUM(CASE WHEN status = \'ESCALADO\' THEN 1 ELSE 0 END) AS escalados
            FROM proposta_alerta_playbooks
            WHERE empresa_id = :empresa_id'
        );
        $stmt->execute(['empresa_id' => $empresaId]);
        $row = $stmt->fetch();

        return [
            'ativos' => (int) (($row['ativos'] ?? 0)),
            'sem_progresso' => (int) (($row['sem_progresso'] ?? 0)),
            'escalados' => (int) (($row['escalados'] ?? 0)),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarEscalonadosDashboard(int $empresaId, int $limit = 5): array
    {
        if ($limit < 1) {
            $limit = 5;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                pb.id,
                pb.alerta_notificacao_id,
                pb.proposta_id,
                pb.tipo_alerta,
                pb.prioridade,
                pb.progresso_percentual,
                pb.prazo_sla_em,
                pb.escalonado_em,
                pb.escalonamento_nivel,
                pb.escalonamento_motivo,
                pb.responsavel_nome,
                pb.responsavel_email,
                p.titulo AS proposta_titulo,
                e.orgao_nome,
                e.numero_edital
            FROM proposta_alerta_playbooks pb
            INNER JOIN propostas_execucao p
                ON p.id = pb.proposta_id
               AND p.empresa_id = pb.empresa_id
            INNER JOIN favoritos f
                ON f.id = pb.favorito_id
               AND f.empresa_id = pb.empresa_id
            INNER JOIN editais e ON e.id = f.edital_id
            WHERE pb.empresa_id = :empresa_id
              AND pb.status = \'ESCALADO\'
            ORDER BY pb.escalonado_em DESC, pb.id DESC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarAprendizadoDashboard(int $empresaId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT
                tipo_alerta,
                total_casos,
                wins,
                losses,
                neutros,
                escalonamentos_total,
                win_rate,
                loss_rate,
                prioridade_sugerida,
                fator_priorizacao,
                ultima_atualizacao_em
            FROM proposta_alerta_aprendizado_regras
            WHERE empresa_id = :empresa_id
            ORDER BY tipo_alerta ASC'
        );
        $stmt->execute(['empresa_id' => $empresaId]);

        $rows = $stmt->fetchAll();
        return is_array($rows) ? $rows : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarEventosRecentesDashboard(int $empresaId, int $limit = 10): array
    {
        if ($limit < 1) {
            $limit = 10;
        }

        $stmt = $this->pdo->prepare(
            'SELECT
                pbe.id,
                pbe.playbook_id,
                pbe.tipo_evento,
                pbe.descricao,
                pbe.detalhes_json,
                pbe.criado_em,
                pb.proposta_id,
                pb.tipo_alerta,
                pb.prioridade,
                pb.status,
                pb.responsavel_nome,
                p.titulo AS proposta_titulo,
                e.orgao_nome
            FROM proposta_alerta_playbook_eventos pbe
            INNER JOIN proposta_alerta_playbooks pb
                ON pb.id = pbe.playbook_id
               AND pb.empresa_id = pbe.empresa_id
            LEFT JOIN propostas_execucao p
                ON p.id = pb.proposta_id
               AND p.empresa_id = pb.empresa_id
            LEFT JOIN favoritos f
                ON f.id = pb.favorito_id
               AND f.empresa_id = pb.empresa_id
            LEFT JOIN editais e ON e.id = f.edital_id
            WHERE pbe.empresa_id = :empresa_id
            ORDER BY pbe.criado_em DESC, pbe.id DESC
            LIMIT :limite'
        );
        $stmt->bindValue(':empresa_id', $empresaId, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        if (!is_array($rows)) {
            return [];
        }

        $resultado = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $detalhes = [];
            $detalhesRaw = isset($row['detalhes_json']) ? trim((string) $row['detalhes_json']) : '';
            if ($detalhesRaw !== '') {
                $parsed = json_decode($detalhesRaw, true);
                if (is_array($parsed)) {
                    $detalhes = $parsed;
                }
            }

            $row['detalhes'] = $detalhes;
            $resultado[] = $row;
        }

        return $resultado;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findAprendizadoRegra(int $empresaId, string $tipoAlerta): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *
            FROM proposta_alerta_aprendizado_regras
            WHERE empresa_id = :empresa_id
              AND tipo_alerta = :tipo_alerta
            LIMIT 1'
        );
        $stmt->execute([
            'empresa_id' => $empresaId,
            'tipo_alerta' => $tipoAlerta,
        ]);

        $row = $stmt->fetch();
        return is_array($row) ? $row : null;
    }

    public function registrarAprendizado(
        int $empresaId,
        string $tipoAlerta,
        string $resultadoWinLoss,
        bool $houveEscalonamento
    ): void {
        $regra = $this->findAprendizadoRegra($empresaId, $tipoAlerta);
        if ($regra === null) {
            $this->pdo->prepare(
                'INSERT INTO proposta_alerta_aprendizado_regras (
                    empresa_id,
                    tipo_alerta,
                    total_casos,
                    wins,
                    losses,
                    neutros,
                    escalonamentos_total,
                    win_rate,
                    loss_rate,
                    prioridade_sugerida,
                    fator_priorizacao,
                    ultima_atualizacao_em
                ) VALUES (
                    :empresa_id,
                    :tipo_alerta,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    \'MEDIA\',
                    1.00,
                    NOW()
                )'
            )->execute([
                'empresa_id' => $empresaId,
                'tipo_alerta' => $tipoAlerta,
            ]);
            $regra = $this->findAprendizadoRegra($empresaId, $tipoAlerta);
        }

        $total = (int) ($regra['total_casos'] ?? 0) + 1;
        $wins = (int) ($regra['wins'] ?? 0) + ($resultadoWinLoss === 'WIN' ? 1 : 0);
        $losses = (int) ($regra['losses'] ?? 0) + ($resultadoWinLoss === 'LOSS' ? 1 : 0);
        $neutros = (int) ($regra['neutros'] ?? 0) + ($resultadoWinLoss === 'NEUTRO' ? 1 : 0);
        $escalonamentosTotal = (int) ($regra['escalonamentos_total'] ?? 0) + ($houveEscalonamento ? 1 : 0);

        $casosDecisivos = $wins + $losses;
        $winRate = $casosDecisivos > 0 ? round(($wins / $casosDecisivos) * 100, 2) : 0.0;
        $lossRate = $casosDecisivos > 0 ? round(($losses / $casosDecisivos) * 100, 2) : 0.0;
        $escalonamentoRate = $total > 0 ? round(($escalonamentosTotal / $total) * 100, 2) : 0.0;

        [$prioridade, $fator] = $this->resolverPrioridadeFator($winRate, $lossRate, $escalonamentoRate, $casosDecisivos);

        $stmt = $this->pdo->prepare(
            'UPDATE proposta_alerta_aprendizado_regras
            SET
                total_casos = :total_casos,
                wins = :wins,
                losses = :losses,
                neutros = :neutros,
                escalonamentos_total = :escalonamentos_total,
                win_rate = :win_rate,
                loss_rate = :loss_rate,
                prioridade_sugerida = :prioridade_sugerida,
                fator_priorizacao = :fator_priorizacao,
                ultima_atualizacao_em = NOW()
            WHERE empresa_id = :empresa_id
              AND tipo_alerta = :tipo_alerta'
        );
        $stmt->execute([
            'total_casos' => $total,
            'wins' => $wins,
            'losses' => $losses,
            'neutros' => $neutros,
            'escalonamentos_total' => $escalonamentosTotal,
            'win_rate' => $winRate,
            'loss_rate' => $lossRate,
            'prioridade_sugerida' => $prioridade,
            'fator_priorizacao' => $fator,
            'empresa_id' => $empresaId,
            'tipo_alerta' => $tipoAlerta,
        ]);
    }

    /**
     * @return array{0: string, 1: float}
     */
    private function resolverPrioridadeFator(
        float $winRate,
        float $lossRate,
        float $escalonamentoRate,
        int $casosDecisivos
    ): array {
        if ($casosDecisivos >= 3 && $winRate >= 60.0 && $escalonamentoRate <= 40.0) {
            return ['ALTA', 1.30];
        }

        if ($casosDecisivos >= 3 && ($winRate <= 35.0 || $lossRate >= 65.0 || $escalonamentoRate >= 70.0)) {
            return ['BAIXA', 0.80];
        }

        return ['MEDIA', 1.00];
    }

    private function truncate(mixed $value, int $limit): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        if ($limit > 0 && strlen($text) > $limit) {
            return substr($text, 0, $limit);
        }

        return $text;
    }
}
