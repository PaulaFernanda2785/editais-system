<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PropostaResultado;
use App\Repositories\EmpresaRepository;
use App\Repositories\FavoritoTarefaRepository;
use App\Repositories\PropostaAlertaPlaybookRepository;
use App\Repositories\PropostaExecucaoRepository;
use App\Repositories\PropostaResultadoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;
use Throwable;

class PropostaAlertaOrquestradorService
{
    private PropostaAlertaPlaybookRepository $playbookRepository;
    private PropostaExecucaoRepository $propostaRepository;
    private PropostaResultadoRepository $resultadoRepository;
    private FavoritoTarefaRepository $tarefaRepository;
    private UsuarioRepository $usuarioRepository;
    private EmpresaRepository $empresaRepository;
    private AlertaEmailDestinatarioService $destinatarioService;
    private EmailService $emailService;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?PropostaAlertaPlaybookRepository $playbookRepository = null,
        ?PropostaExecucaoRepository $propostaRepository = null,
        ?PropostaResultadoRepository $resultadoRepository = null,
        ?FavoritoTarefaRepository $tarefaRepository = null,
        ?UsuarioRepository $usuarioRepository = null,
        ?EmpresaRepository $empresaRepository = null,
        ?AlertaEmailDestinatarioService $destinatarioService = null,
        ?EmailService $emailService = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->playbookRepository = $playbookRepository ?? new PropostaAlertaPlaybookRepository();
        $this->propostaRepository = $propostaRepository ?? new PropostaExecucaoRepository();
        $this->resultadoRepository = $resultadoRepository ?? new PropostaResultadoRepository();
        $this->tarefaRepository = $tarefaRepository ?? new FavoritoTarefaRepository();
        $this->usuarioRepository = $usuarioRepository ?? new UsuarioRepository();
        $this->empresaRepository = $empresaRepository ?? new EmpresaRepository();
        $this->logService = $logService ?? new LogService();
        $this->destinatarioService = $destinatarioService ?? new AlertaEmailDestinatarioService(
            $this->empresaRepository,
            $this->usuarioRepository
        );
        $this->emailService = $emailService ?? new EmailService($this->logService);
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @param array<int, array<string, mixed>> $novosAlertas
     * @return array<string, int|array<string, int>>
     */
    public function processarCiclo(int $empresaId, array $novosAlertas, bool $enviarEmailEscalonamento = true): array
    {
        if (!$this->envBool('ALERTA_PLAYBOOK_ATIVADO', true)) {
            return [
                'playbooks_criados' => 0,
                'playbooks_reabertos' => 0,
                'playbooks_escalonados' => 0,
                'playbooks_encerrados' => 0,
                'aprendizado' => [
                    'wins' => 0,
                    'losses' => 0,
                    'neutros' => 0,
                ],
            ];
        }

        $criacao = $this->garantirPlaybooksParaNovosAlertas($empresaId, $novosAlertas);
        $monitoramento = $this->monitorarProgressoEscalonar($empresaId, $enviarEmailEscalonamento);
        $encerramento = $this->encerrarPlaybooksResolvidos($empresaId);

        return [
            'playbooks_criados' => $criacao['criados'],
            'playbooks_reabertos' => $criacao['reabertos'],
            'playbooks_escalonados' => $monitoramento['escalonados'],
            'playbooks_encerrados' => $encerramento['encerrados'],
            'aprendizado' => [
                'wins' => $encerramento['wins'],
                'losses' => $encerramento['losses'],
                'neutros' => $encerramento['neutros'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function obterParaDashboard(int $empresaId, int $limitEscalonados = 5): array
    {
        if ($limitEscalonados < 1) {
            $limitEscalonados = 5;
        }

        if (!$this->envBool('ALERTA_PLAYBOOK_ATIVADO', true)) {
            return [
                'ativo' => false,
                'resumo' => [
                    'ativos' => 0,
                    'sem_progresso' => 0,
                    'escalados' => 0,
                ],
                'executivo' => [
                    'resumo' => [
                        'total_playbooks' => 0,
                        'encerrados_total' => 0,
                        'encerrados_no_prazo' => 0,
                        'com_escalonamento' => 0,
                        'taxa_sla_percentual' => 0.0,
                        'taxa_escalonamento_percentual' => 0.0,
                        'tempo_medio_primeira_atividade_horas' => 0.0,
                        'tempo_medio_encerramento_horas' => 0.0,
                        'risco_atraso_percentual' => 0.0,
                        'sla_sugerido_horas' => 0.0,
                    ],
                    'por_nivel' => [],
                ],
                'escalonados' => [],
                'aprendizado' => [],
                'top_contextos_criticos' => [],
                'evidencias' => [],
            ];
        }

        $limitEscalonados = max(1, $limitEscalonados);
        $limitEvidencias = max(5, min(20, $limitEscalonados * 2));

        return [
            'ativo' => true,
            'resumo' => $this->playbookRepository->resumoOperacionalDashboard($empresaId),
            'executivo' => [
                'resumo' => $this->playbookRepository->resumoExecutivoDashboard($empresaId),
                'por_nivel' => $this->playbookRepository->listarEscalonamentoPorNivelDashboard($empresaId),
            ],
            'escalonados' => $this->playbookRepository->listarEscalonadosDashboard($empresaId, $limitEscalonados),
            'aprendizado' => $this->playbookRepository->listarAprendizadoDashboard($empresaId),
            'top_contextos_criticos' => $this->playbookRepository->listarTopContextosCriticosDashboard($empresaId, 5),
            'evidencias' => $this->playbookRepository->listarEventosRecentesDashboard($empresaId, $limitEvidencias),
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $novosAlertas
     * @return array{criados: int, reabertos: int}
     */
    private function garantirPlaybooksParaNovosAlertas(int $empresaId, array $novosAlertas): array
    {
        $criados = 0;
        $reabertos = 0;

        foreach ($novosAlertas as $alerta) {
            $alertaNotificacaoId = (int) ($alerta['id'] ?? 0);
            $propostaId = (int) ($alerta['proposta_id'] ?? 0);
            $tipoAlerta = strtoupper(trim((string) ($alerta['tipo_alerta'] ?? 'SEM_RESULTADO')));

            if ($alertaNotificacaoId <= 0 || $propostaId <= 0 || !$this->tipoAlertaValido($tipoAlerta)) {
                continue;
            }

            $proposta = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
            if ($proposta === null) {
                continue;
            }

            $contextoAprendizado = $this->resolverContextoAprendizado(
                $proposta->editalOrgaoNome,
                $proposta->editalModalidade
            );
            $responsavel = $this->resolverResponsavel($empresaId);
            $regra = $this->playbookRepository->findAprendizadoRegra(
                $empresaId,
                $tipoAlerta,
                $contextoAprendizado['orgao_nome_contexto'],
                $contextoAprendizado['modalidade_contexto']
            );
            if ($regra === null) {
                $regra = $this->playbookRepository->findAprendizadoRegraFallback($empresaId, $tipoAlerta);
            }
            $prioridade = $this->resolverPrioridadeInicial($tipoAlerta, $regra);
            $fatorPriorizacao = $this->resolverFatorPriorizacao($prioridade, $regra);
            $slaHoras = $this->resolverSlaHoras($tipoAlerta, $fatorPriorizacao, $regra);
            $slaSugeridoHoras = $this->resolverSlaSugeridoHoras($slaHoras, $regra);
            $riscoAtrasoPercentual = $this->resolverRiscoAtrasoPercentual($prioridade, $regra);
            $prazoSla = (new DateTimeImmutable('now'))->modify('+' . $slaHoras . ' hours')->format('Y-m-d H:i:s');

            $payload = [
                'empresa_id' => $empresaId,
                'alerta_notificacao_id' => $alertaNotificacaoId,
                'proposta_id' => $propostaId,
                'favorito_id' => $proposta->favoritoId,
                'tipo_alerta' => $tipoAlerta,
                'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                'status' => 'ATIVO',
                'prioridade' => $prioridade,
                'fator_priorizacao' => $fatorPriorizacao,
                'risco_atraso_percentual' => $riscoAtrasoPercentual,
                'sla_horas' => $slaHoras,
                'sla_sugerido_horas' => $slaSugeridoHoras,
                'prazo_sla_em' => $prazoSla,
                'progresso_percentual' => 0.0,
                'responsavel_usuario_id' => $responsavel['id'] ?? null,
                'responsavel_nome' => $responsavel['nome'] ?? 'Equipe operacional',
                'responsavel_email' => $responsavel['email'] ?? null,
            ];

            $existente = $this->playbookRepository->findByNotificacao($empresaId, $alertaNotificacaoId);
            if ($existente === null) {
                $playbookId = $this->playbookRepository->create($payload);
                $criados++;
                $this->playbookRepository->registrarEvento(
                    $playbookId,
                    $empresaId,
                    'PLAYBOOK_CRIADO',
                    'Playbook automatico criado a partir de novo alerta.',
                    [
                        'alerta_notificacao_id' => $alertaNotificacaoId,
                        'proposta_id' => $propostaId,
                        'tipo_alerta' => $tipoAlerta,
                        'prioridade' => $prioridade,
                        'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                        'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                        'risco_atraso_percentual' => $riscoAtrasoPercentual,
                        'sla_horas' => $slaHoras,
                        'sla_sugerido_horas' => $slaSugeridoHoras,
                    ]
                );
            } else {
                $playbookId = (int) ($existente['id'] ?? 0);
                if ($playbookId <= 0) {
                    continue;
                }

                if ((string) ($existente['status'] ?? '') === 'ENCERRADO') {
                    $this->playbookRepository->reabrir($playbookId, $empresaId, $payload);
                    $this->playbookRepository->limparMapeamentosTarefas($playbookId, $empresaId);
                    $reabertos++;
                    $this->playbookRepository->registrarEvento(
                        $playbookId,
                        $empresaId,
                        'REABERTO',
                        'Playbook reaberto por reativacao de alerta.',
                        [
                            'alerta_notificacao_id' => $alertaNotificacaoId,
                            'proposta_id' => $propostaId,
                            'tipo_alerta' => $tipoAlerta,
                            'prioridade' => $prioridade,
                            'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                            'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                            'risco_atraso_percentual' => $riscoAtrasoPercentual,
                            'sla_horas' => $slaHoras,
                            'sla_sugerido_horas' => $slaSugeridoHoras,
                        ]
                    );
                } else {
                    continue;
                }
            }

            $this->criarTarefasPlaybook(
                $playbookId,
                $empresaId,
                $proposta->favoritoId,
                $tipoAlerta,
                $slaHoras,
                $responsavel
            );

            $this->auditService->record(
                'ALERTA_PLAYBOOK_CRIADO',
                'proposta_alerta_playbooks',
                $playbookId,
                [
                    'alerta_notificacao_id' => $alertaNotificacaoId,
                    'proposta_id' => $propostaId,
                    'tipo_alerta' => $tipoAlerta,
                    'prioridade' => $prioridade,
                    'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                    'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                    'risco_atraso_percentual' => $riscoAtrasoPercentual,
                    'sla_horas' => $slaHoras,
                    'sla_sugerido_horas' => $slaSugeridoHoras,
                    'fator_priorizacao' => $fatorPriorizacao,
                ],
                $empresaId,
                isset($responsavel['id']) ? (int) $responsavel['id'] : null
            );
        }

        return [
            'criados' => $criados,
            'reabertos' => $reabertos,
        ];
    }

    /**
     * @return array{escalonados: int}
     */
    private function monitorarProgressoEscalonar(int $empresaId, bool $enviarEmailEscalonamento): array
    {
        $escalonados = 0;
        $limite = $this->envInt('ALERTA_PLAYBOOK_PROCESSAMENTO_LIMITE', 300);
        $maxNivelEscalonamento = max(1, min(5, $this->envInt('ALERTA_PLAYBOOK_ESCALONAMENTO_MAX_NIVEL', 3)));
        $intervaloReescalaHoras = max(1, min(168, $this->envInt('ALERTA_PLAYBOOK_ESCALONAMENTO_INTERVALO_HORAS', 12)));
        $agora = new DateTimeImmutable('now');
        $playbooks = $this->playbookRepository->listarAtivosParaProcessamento($empresaId, $limite);

        foreach ($playbooks as $playbook) {
            $playbookId = (int) ($playbook['id'] ?? 0);
            if ($playbookId <= 0) {
                continue;
            }

            $resumo = $this->playbookRepository->resumoTarefas($playbookId, $empresaId);
            $progresso = $this->calcularProgressoPercentual($resumo);
            $statusAtual = (string) ($playbook['status'] ?? 'ATIVO');
            $progressoAtual = isset($playbook['progresso_percentual']) ? (float) $playbook['progresso_percentual'] : 0.0;
            $novoStatus = $statusAtual === 'ESCALADO'
                ? 'ESCALADO'
                : ($progresso > 0.0 ? 'EM_PROGRESSO' : 'ATIVO');
            $primeiraAtividade = $resumo['primeira_atividade_em'] ?? null;
            $ultimaAtividade = $resumo['ultima_atividade_em'] ?? null;

            if ($primeiraAtividade === null && isset($playbook['primeira_atividade_em'])) {
                $primeiraAtividade = (string) $playbook['primeira_atividade_em'];
            }
            if ($ultimaAtividade === null && isset($playbook['ultima_atividade_em'])) {
                $ultimaAtividade = (string) $playbook['ultima_atividade_em'];
            }

            $this->playbookRepository->atualizarProgressoStatus(
                $playbookId,
                $empresaId,
                $progresso,
                $novoStatus,
                $primeiraAtividade !== null ? (string) $primeiraAtividade : null,
                $ultimaAtividade !== null ? (string) $ultimaAtividade : null
            );

            $mudouProgresso = abs($progresso - $progressoAtual) >= 0.01;
            $mudouStatus = $novoStatus !== $statusAtual;
            if ($mudouProgresso || $mudouStatus) {
                $this->playbookRepository->registrarEvento(
                    $playbookId,
                    $empresaId,
                    'PROGRESSO_ATUALIZADO',
                    'Progresso e status recalculados automaticamente pelas tarefas do playbook.',
                    [
                        'progresso_anterior' => round($progressoAtual, 2),
                        'progresso_atual' => round($progresso, 2),
                        'status_anterior' => $statusAtual,
                        'status_atual' => $novoStatus,
                    ]
                );
            }

            $prazoSla = $this->parseDateTime(isset($playbook['prazo_sla_em']) ? (string) $playbook['prazo_sla_em'] : null);
            $escalonadoEm = $this->parseDateTime(isset($playbook['escalonado_em']) ? (string) $playbook['escalonado_em'] : null);
            $nivelAtual = isset($playbook['escalonamento_nivel']) ? (int) $playbook['escalonamento_nivel'] : 0;
            if ($nivelAtual < 0) {
                $nivelAtual = 0;
            }

            $slaExpirado = $prazoSla instanceof DateTimeImmutable && $agora > $prazoSla;
            $semProgresso = $progresso <= 0.0;
            if (!$slaExpirado || !$semProgresso) {
                continue;
            }

            $escalaInicial = $escalonadoEm === null && $nivelAtual <= 0;
            if ($escalaInicial) {
                $nivelDestino = 1;
                $motivo = 'SLA ultrapassado sem avanco do playbook automatico.';
                $ok = $this->playbookRepository->marcarEscalonado($playbookId, $empresaId, $nivelDestino, $motivo);
            } else {
                if (!$escalonadoEm instanceof DateTimeImmutable) {
                    continue;
                }

                if ($nivelAtual >= $maxNivelEscalonamento) {
                    continue;
                }

                $proximaEscalada = $escalonadoEm->modify('+' . $intervaloReescalaHoras . ' hours');
                if ($proximaEscalada instanceof DateTimeImmutable && $agora <= $proximaEscalada) {
                    continue;
                }

                $nivelDestino = $nivelAtual + 1;
                $motivo = 'Sem avanco apos escalonamento anterior; reescalonamento automatico para L' . $nivelDestino . '.';
                $ok = $this->playbookRepository->atualizarEscalonamentoNivel(
                    $playbookId,
                    $empresaId,
                    $nivelDestino,
                    $motivo
                );
            }

            if (!$ok) {
                continue;
            }

            $escalonados++;
            $playbookEscalonado = $playbook;
            $playbookEscalonado['escalonamento_nivel'] = $nivelDestino;
            $this->playbookRepository->registrarEvento(
                $playbookId,
                $empresaId,
                'ESCALONADO',
                $escalaInicial
                    ? 'Escalonamento automatico inicial executado por ausencia de progresso.'
                    : 'Reescalonamento automatico executado por ausencia de progresso.',
                [
                    'escalonamento_nivel' => $nivelDestino,
                    'escalonamento_anterior' => $nivelAtual,
                    'motivo' => $motivo,
                    'prazo_sla_em' => isset($playbook['prazo_sla_em']) ? (string) $playbook['prazo_sla_em'] : null,
                    'progresso_percentual' => $progresso,
                    'proposta_id' => (int) ($playbook['proposta_id'] ?? 0),
                    'tipo_alerta' => (string) ($playbook['tipo_alerta'] ?? ''),
                ]
            );

            $this->auditService->record(
                'ALERTA_PLAYBOOK_ESCALONADO',
                'proposta_alerta_playbooks',
                $playbookId,
                [
                    'escalonamento_nivel' => $nivelDestino,
                    'escalonamento_anterior' => $nivelAtual,
                    'motivo' => $motivo,
                    'progresso_percentual' => $progresso,
                    'proposta_id' => (int) ($playbook['proposta_id'] ?? 0),
                    'tipo_alerta' => (string) ($playbook['tipo_alerta'] ?? ''),
                    'prazo_sla_em' => isset($playbook['prazo_sla_em']) ? (string) $playbook['prazo_sla_em'] : null,
                ],
                $empresaId,
                isset($playbook['responsavel_usuario_id']) ? (int) $playbook['responsavel_usuario_id'] : null
            );

            if ($enviarEmailEscalonamento) {
                $this->enviarEmailEscalonamento($empresaId, $playbookEscalonado, $motivo);
            }
        }

        return ['escalonados' => $escalonados];
    }

    /**
     * @return array{encerrados: int, wins: int, losses: int, neutros: int}
     */
    private function encerrarPlaybooksResolvidos(int $empresaId): array
    {
        $encerrados = 0;
        $wins = 0;
        $losses = 0;
        $neutros = 0;
        $limite = $this->envInt('ALERTA_PLAYBOOK_PROCESSAMENTO_LIMITE', 300);
        $pendentes = $this->playbookRepository->listarPendentesEncerramento($empresaId, $limite);

        foreach ($pendentes as $playbook) {
            $playbookId = (int) ($playbook['id'] ?? 0);
            $propostaId = (int) ($playbook['proposta_id'] ?? 0);
            $tipoAlerta = strtoupper(trim((string) ($playbook['tipo_alerta'] ?? 'SEM_RESULTADO')));
            if ($playbookId <= 0 || $propostaId <= 0 || !$this->tipoAlertaValido($tipoAlerta)) {
                continue;
            }

            $resultadoWinLoss = $this->resolverResultadoWinLoss($propostaId, $empresaId);
            $houveEscalonamento = isset($playbook['escalonado_em']) && $playbook['escalonado_em'] !== null;
            $contextoAprendizado = $this->resolverContextoAprendizado(
                isset($playbook['contexto_orgao_nome']) ? (string) $playbook['contexto_orgao_nome'] : null,
                isset($playbook['contexto_modalidade']) ? (string) $playbook['contexto_modalidade'] : null
            );
            if (
                $contextoAprendizado['orgao_nome_contexto'] === PropostaAlertaPlaybookRepository::CONTEXTO_GERAL
                || $contextoAprendizado['modalidade_contexto'] === PropostaAlertaPlaybookRepository::CONTEXTO_GERAL
            ) {
                $propostaContexto = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
                if ($propostaContexto !== null) {
                    $contextoAprendizado = $this->resolverContextoAprendizado(
                        $propostaContexto->editalOrgaoNome,
                        $propostaContexto->editalModalidade
                    );
                }
            }
            $tempoPrimeiraAcaoHoras = $this->calcularTempoPrimeiraAcaoHoras(
                isset($playbook['criado_em']) ? (string) $playbook['criado_em'] : null,
                isset($playbook['primeira_atividade_em']) ? (string) $playbook['primeira_atividade_em'] : null
            );
            $slaBaseHoras = $this->resolverSlaBaseHoras($tipoAlerta);
            $aprendizadoResumo = $this->gerarAprendizadoResumo(
                $tipoAlerta,
                $resultadoWinLoss,
                $houveEscalonamento,
                (float) ($playbook['progresso_percentual'] ?? 0.0)
            );

            $ok = $this->playbookRepository->encerrar(
                $playbookId,
                $empresaId,
                $resultadoWinLoss,
                $aprendizadoResumo
            );
            if (!$ok) {
                continue;
            }

            $encerrados++;
            if ($resultadoWinLoss === 'WIN') {
                $wins++;
            } elseif ($resultadoWinLoss === 'LOSS') {
                $losses++;
            } else {
                $neutros++;
            }

            $this->playbookRepository->registrarEvento(
                $playbookId,
                $empresaId,
                'ENCERRADO',
                'Playbook encerrado com aprendizado win/loss.',
                [
                    'resultado_win_loss' => $resultadoWinLoss,
                    'tipo_alerta' => $tipoAlerta,
                    'proposta_id' => $propostaId,
                    'houve_escalonamento' => $houveEscalonamento ? 1 : 0,
                    'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                    'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                    'tempo_primeira_acao_horas' => $tempoPrimeiraAcaoHoras,
                ]
            );
            $aprendizadoAtualizado = $this->playbookRepository->registrarAprendizado(
                $empresaId,
                $tipoAlerta,
                $contextoAprendizado['orgao_nome_contexto'],
                $contextoAprendizado['modalidade_contexto'],
                $resultadoWinLoss,
                $houveEscalonamento,
                $tempoPrimeiraAcaoHoras,
                $slaBaseHoras
            );
            $this->playbookRepository->registrarEvento(
                $playbookId,
                $empresaId,
                'APRENDIZADO_ATUALIZADO',
                'Aprendizado contextual consolidado para o tipo de alerta.',
                [
                    'tipo_alerta' => $tipoAlerta,
                    'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                    'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                    'total_casos' => (int) ($aprendizadoAtualizado['total_casos'] ?? 0),
                    'win_rate' => (float) ($aprendizadoAtualizado['win_rate'] ?? 0.0),
                    'loss_rate' => (float) ($aprendizadoAtualizado['loss_rate'] ?? 0.0),
                    'taxa_escalonamento_percentual' => (float) ($aprendizadoAtualizado['taxa_escalonamento_percentual'] ?? 0.0),
                    'tempo_medio_primeira_acao_horas' => (float) ($aprendizadoAtualizado['tempo_medio_primeira_acao_horas'] ?? 0.0),
                    'risco_atraso_percentual' => (float) ($aprendizadoAtualizado['risco_atraso_percentual'] ?? 0.0),
                    'sla_sugerido_horas' => (float) ($aprendizadoAtualizado['sla_sugerido_horas'] ?? 0.0),
                    'prioridade_sugerida' => (string) ($aprendizadoAtualizado['prioridade_sugerida'] ?? 'MEDIA'),
                ]
            );

            $this->auditService->record(
                'ALERTA_PLAYBOOK_ENCERRADO',
                'proposta_alerta_playbooks',
                $playbookId,
                [
                    'resultado_win_loss' => $resultadoWinLoss,
                    'tipo_alerta' => $tipoAlerta,
                    'proposta_id' => $propostaId,
                    'aprendizado' => $aprendizadoResumo,
                    'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                    'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                    'tempo_primeira_acao_horas' => $tempoPrimeiraAcaoHoras,
                ],
                $empresaId,
                isset($playbook['responsavel_usuario_id']) ? (int) $playbook['responsavel_usuario_id'] : null
            );
            $this->auditService->record(
                'ALERTA_PLAYBOOK_APRENDIZADO_ATUALIZADO',
                'proposta_alerta_aprendizado_regras',
                isset($aprendizadoAtualizado['id']) ? (int) $aprendizadoAtualizado['id'] : null,
                [
                    'tipo_alerta' => $tipoAlerta,
                    'contexto_orgao_nome' => $contextoAprendizado['orgao_nome_contexto'],
                    'contexto_modalidade' => $contextoAprendizado['modalidade_contexto'],
                    'total_casos' => (int) ($aprendizadoAtualizado['total_casos'] ?? 0),
                    'win_rate' => (float) ($aprendizadoAtualizado['win_rate'] ?? 0.0),
                    'loss_rate' => (float) ($aprendizadoAtualizado['loss_rate'] ?? 0.0),
                    'taxa_escalonamento_percentual' => (float) ($aprendizadoAtualizado['taxa_escalonamento_percentual'] ?? 0.0),
                    'tempo_medio_primeira_acao_horas' => (float) ($aprendizadoAtualizado['tempo_medio_primeira_acao_horas'] ?? 0.0),
                    'risco_atraso_percentual' => (float) ($aprendizadoAtualizado['risco_atraso_percentual'] ?? 0.0),
                    'sla_sugerido_horas' => (float) ($aprendizadoAtualizado['sla_sugerido_horas'] ?? 0.0),
                    'prioridade_sugerida' => (string) ($aprendizadoAtualizado['prioridade_sugerida'] ?? 'MEDIA'),
                    'fator_priorizacao' => (float) ($aprendizadoAtualizado['fator_priorizacao'] ?? 1.0),
                ],
                $empresaId,
                isset($playbook['responsavel_usuario_id']) ? (int) $playbook['responsavel_usuario_id'] : null
            );
        }

        return [
            'encerrados' => $encerrados,
            'wins' => $wins,
            'losses' => $losses,
            'neutros' => $neutros,
        ];
    }

    /**
     * @param array<string, mixed> $playbook
     */
    private function enviarEmailEscalonamento(int $empresaId, array $playbook, string $motivo): void
    {
        if (!$this->envBool('ALERTA_ESCALONAMENTO_EMAIL_HABILITADO', true)) {
            return;
        }

        try {
            $destinatarios = $this->destinatarioService->resolverEscalonamento(
                $empresaId,
                isset($playbook['responsavel_email']) ? (string) $playbook['responsavel_email'] : null
            );
            if ($destinatarios === []) {
                return;
            }

            $validacaoDestinos = $this->destinatarioService->validarListaEmails($destinatarios);
            $destinosValidos = isset($validacaoDestinos['validos']) && is_array($validacaoDestinos['validos'])
                ? $validacaoDestinos['validos']
                : [];
            if ($destinosValidos === []) {
                $this->logService->warning(
                    'propostas.playbook.escalonamento.email',
                    'Nenhum destinatario valido para email de escalonamento.',
                    [
                        'empresa_id' => $empresaId,
                        'playbook_id' => (int) ($playbook['id'] ?? 0),
                        'rejeitados' => $validacaoDestinos['rejeitados'] ?? [],
                    ]
                );
                return;
            }

            $appName = $this->envString('APP_NAME', 'SaaS Editais');
            $nivelEscalonamento = max(1, (int) ($playbook['escalonamento_nivel'] ?? 1));
            $subject = '[' . $appName . '] Escalonamento automatico L' . $nivelEscalonamento . ' de alerta de proposta';
            $fromAddress = trim($this->envString('ALERTA_EMAIL_FROM', 'no-reply@example.com'));
            $fromName = trim($this->envString('ALERTA_EMAIL_NOME', $appName));
            $dashboardUrl = rtrim($this->envString('APP_URL', ''), '/') . '/dashboard';

            $tipoAlerta = strtoupper(trim((string) ($playbook['tipo_alerta'] ?? 'SEM_RESULTADO')));
            $rotuloTipo = $tipoAlerta === 'JULGAMENTO_PARADO'
                ? 'Julgamento parado'
                : 'Sem resultado apos submissao';

            $linhas = [];
            $linhas[] = 'Escalonamento automatico acionado pelo orquestrador.';
            $linhas[] = '';
            $linhas[] = 'Playbook: #' . (int) ($playbook['id'] ?? 0);
            $linhas[] = 'Proposta: #' . (int) ($playbook['proposta_id'] ?? 0);
            $linhas[] = 'Tipo de alerta: ' . $rotuloTipo;
            $linhas[] = 'Nivel de escalonamento: L' . $nivelEscalonamento;
            $linhas[] = 'Prioridade: ' . (string) ($playbook['prioridade'] ?? 'MEDIA');
            $linhas[] = 'Responsavel: ' . (string) ($playbook['responsavel_nome'] ?? 'Nao definido');
            $linhas[] = 'Prazo SLA: ' . (string) ($playbook['prazo_sla_em'] ?? '-');
            $linhas[] = 'Motivo: ' . $motivo;
            $linhas[] = '';
            $linhas[] = 'Acompanhe no dashboard: ' . $dashboardUrl;

            $envio = $this->emailService->sendPlainText(
                $destinosValidos,
                $subject,
                implode(PHP_EOL, $linhas),
                $fromAddress,
                $fromName
            );

            if (($envio['sucesso'] ?? false) !== true) {
                $this->logService->warning(
                    'propostas.playbook.escalonamento.email',
                    'Falha no envio de email de escalonamento.',
                    [
                        'empresa_id' => $empresaId,
                        'playbook_id' => (int) ($playbook['id'] ?? 0),
                        'erro' => $envio['erro'] ?? 'falha_envio_email',
                    ]
                );
            }
        } catch (Throwable $exception) {
            $this->logService->warning(
                'propostas.playbook.escalonamento.email',
                'Erro ao montar envio de email de escalonamento.',
                [
                    'empresa_id' => $empresaId,
                    'playbook_id' => (int) ($playbook['id'] ?? 0),
                    'exception' => $exception->getMessage(),
                ]
            );
        }
    }

    /**
     * @param array<string, mixed> $playbook
     * @return array<int, string>
     */
    private function resolverDestinatariosEscalonamento(int $empresaId, array $playbook): array
    {
        return $this->destinatarioService->resolverEscalonamento(
            $empresaId,
            isset($playbook['responsavel_email']) ? (string) $playbook['responsavel_email'] : null
        );
    }

    /**
     * @param array<string, mixed>|null $regra
     */
    private function resolverPrioridadeInicial(string $tipoAlerta, ?array $regra): string
    {
        if ($regra !== null) {
            $prioridade = strtoupper(trim((string) ($regra['prioridade_sugerida'] ?? '')));
            if (in_array($prioridade, ['ALTA', 'MEDIA', 'BAIXA'], true)) {
                return $prioridade;
            }
        }

        return $tipoAlerta === 'JULGAMENTO_PARADO' ? 'ALTA' : 'MEDIA';
    }

    /**
     * @param array<string, mixed>|null $regra
     */
    private function resolverFatorPriorizacao(string $prioridade, ?array $regra): float
    {
        if ($regra !== null && isset($regra['fator_priorizacao']) && is_numeric($regra['fator_priorizacao'])) {
            $fator = (float) $regra['fator_priorizacao'];
            if ($fator > 0.0) {
                return max(0.5, min(2.0, $fator));
            }
        }

        return match ($prioridade) {
            'ALTA' => 1.20,
            'BAIXA' => 0.85,
            default => 1.00,
        };
    }

    /**
     * @param array<string, mixed>|null $regra
     */
    private function resolverSlaHoras(string $tipoAlerta, float $fatorPriorizacao, ?array $regra = null): int
    {
        if ($regra !== null && isset($regra['sla_sugerido_horas']) && is_numeric($regra['sla_sugerido_horas'])) {
            $slaSugerido = (int) $regra['sla_sugerido_horas'];
            if ($slaSugerido > 0) {
                return max(6, min(240, $slaSugerido));
            }
        }

        $base = $this->resolverSlaBaseHoras($tipoAlerta);

        if ($base < 1) {
            $base = 24;
        }

        $sla = (int) round($base / max(0.5, min(2.0, $fatorPriorizacao)));
        if ($sla < 6) {
            $sla = 6;
        }
        if ($sla > 240) {
            $sla = 240;
        }

        return $sla;
    }

    /**
     * @param array<string, mixed>|null $regra
     */
    private function resolverSlaSugeridoHoras(int $slaHoras, ?array $regra): int
    {
        if ($regra !== null && isset($regra['sla_sugerido_horas']) && is_numeric($regra['sla_sugerido_horas'])) {
            $sla = (int) $regra['sla_sugerido_horas'];
            if ($sla > 0) {
                return max(6, min(240, $sla));
            }
        }

        return max(6, min(240, $slaHoras));
    }

    /**
     * @param array<string, mixed>|null $regra
     */
    private function resolverRiscoAtrasoPercentual(string $prioridade, ?array $regra): float
    {
        if ($regra !== null && isset($regra['risco_atraso_percentual']) && is_numeric($regra['risco_atraso_percentual'])) {
            $risco = (float) $regra['risco_atraso_percentual'];
            if ($risco > 0.0) {
                return round(max(0.0, min(100.0, $risco)), 2);
            }
        }

        return match ($prioridade) {
            'ALTA' => 70.0,
            'BAIXA' => 25.0,
            default => 45.0,
        };
    }

    private function resolverSlaBaseHoras(string $tipoAlerta): int
    {
        $base = $tipoAlerta === 'JULGAMENTO_PARADO'
            ? $this->envInt('ALERTA_PLAYBOOK_SLA_JULGAMENTO_PARADO_HORAS', 36)
            : $this->envInt('ALERTA_PLAYBOOK_SLA_SEM_RESULTADO_HORAS', 48);

        return $base > 0 ? $base : 24;
    }

    /**
     * @return array{orgao_nome_contexto: string, modalidade_contexto: string}
     */
    private function resolverContextoAprendizado(?string $orgaoNome, ?string $modalidade): array
    {
        return [
            'orgao_nome_contexto' => $this->normalizarContextoValor($orgaoNome, 255),
            'modalidade_contexto' => $this->normalizarContextoValor($modalidade, 120),
        ];
    }

    private function normalizarContextoValor(?string $value, int $limit): string
    {
        $text = trim((string) $value);
        if ($text !== '') {
            $text = function_exists('mb_strtoupper')
                ? (string) mb_strtoupper($text, 'UTF-8')
                : strtoupper($text);
        }
        if ($text === '') {
            return PropostaAlertaPlaybookRepository::CONTEXTO_GERAL;
        }

        $text = preg_replace('/\s+/', ' ', $text) ?? $text;
        if ($limit <= 0) {
            return $text;
        }

        if (function_exists('mb_substr')) {
            return (string) mb_substr($text, 0, $limit, 'UTF-8');
        }

        return substr($text, 0, $limit);
    }

    private function calcularTempoPrimeiraAcaoHoras(?string $criadoEm, ?string $primeiraAtividadeEm): ?float
    {
        $criado = $this->parseDateTime($criadoEm);
        $primeiraAtividade = $this->parseDateTime($primeiraAtividadeEm);
        if (!$criado instanceof DateTimeImmutable || !$primeiraAtividade instanceof DateTimeImmutable) {
            return null;
        }

        $diffSegundos = $primeiraAtividade->getTimestamp() - $criado->getTimestamp();
        if ($diffSegundos <= 0) {
            return null;
        }

        return round($diffSegundos / 3600, 2);
    }

    /**
     * @param array<string, int|string|null> $resumo
     */
    private function calcularProgressoPercentual(array $resumo): float
    {
        $total = (int) ($resumo['total'] ?? 0);
        if ($total <= 0) {
            return 0.0;
        }

        $concluidas = (int) ($resumo['concluidas'] ?? 0);
        $emAndamento = (int) ($resumo['em_andamento'] ?? 0);
        $progresso = (($concluidas + ($emAndamento * 0.5)) / $total) * 100;

        return round(max(0.0, min(100.0, $progresso)), 2);
    }

    /**
     * @param array<string, mixed> $responsavel
     */
    private function criarTarefasPlaybook(
        int $playbookId,
        int $empresaId,
        int $favoritoId,
        string $tipoAlerta,
        int $slaHoras,
        array $responsavel
    ): void {
        $templates = $this->resolverTemplateTarefas($tipoAlerta);
        $agora = new DateTimeImmutable('today');
        $baseDias = max(1, (int) ceil($slaHoras / 24));

        foreach ($templates as $index => $template) {
            $peso = (float) ($template['peso_prazo'] ?? 0.33);
            $diasOffset = max(0, (int) floor($baseDias * $peso));
            $dataLimite = $agora->modify('+' . $diasOffset . ' days')->format('Y-m-d');
            $ordem = $this->tarefaRepository->nextOrdem($favoritoId, $empresaId);

            $tarefa = $this->tarefaRepository->create([
                'favorito_id' => $favoritoId,
                'empresa_id' => $empresaId,
                'titulo' => (string) ($template['titulo'] ?? 'Acao automatica'),
                'descricao' => (string) ($template['descricao'] ?? ''),
                'responsavel' => $responsavel['nome'] ?? null,
                'responsavel_usuario_id' => $responsavel['id'] ?? null,
                'data_limite' => $dataLimite,
                'status' => 'PENDENTE',
                'ordem' => $ordem,
            ]);

            $this->playbookRepository->adicionarTarefaMapeada(
                $playbookId,
                $empresaId,
                $tarefa->id,
                (string) ($template['tipo'] ?? ('EVIDENCIA_' . $index))
            );
        }
    }

    /**
     * @return array<int, array{tipo: string, titulo: string, descricao: string, peso_prazo: float}>
     */
    private function resolverTemplateTarefas(string $tipoAlerta): array
    {
        if ($tipoAlerta === 'JULGAMENTO_PARADO') {
            return [
                [
                    'tipo' => 'TRIAGEM',
                    'titulo' => '[Playbook] Revisar historico do julgamento',
                    'descricao' => 'Conferir ultima atualizacao oficial e riscos de estagnacao do processo.',
                    'peso_prazo' => 0.25,
                ],
                [
                    'tipo' => 'CONTATO',
                    'titulo' => '[Playbook] Acionar orgao para status formal',
                    'descricao' => 'Registrar contato com o orgao para destravar informacoes do julgamento.',
                    'peso_prazo' => 0.60,
                ],
                [
                    'tipo' => 'EVIDENCIA',
                    'titulo' => '[Playbook] Consolidar evidencias e proximo passo',
                    'descricao' => 'Atualizar proposta com evidencias coletadas e recomendacao operacional.',
                    'peso_prazo' => 1.00,
                ],
            ];
        }

        return [
            [
                'tipo' => 'TRIAGEM',
                'titulo' => '[Playbook] Validar ausencia de resultado',
                'descricao' => 'Confirmar status da proposta em portais oficiais e canais de publicacao.',
                'peso_prazo' => 0.25,
            ],
            [
                'tipo' => 'CONTATO',
                'titulo' => '[Playbook] Solicitar retorno formal do orgao',
                'descricao' => 'Executar contato operacional para obter previsao de resultado.',
                'peso_prazo' => 0.60,
            ],
            [
                'tipo' => 'EVIDENCIA',
                'titulo' => '[Playbook] Registrar evidencia e decisao de continuidade',
                'descricao' => 'Documentar resposta, risco e recomendacao final para o time comercial.',
                'peso_prazo' => 1.00,
            ],
        ];
    }

    /**
     * @return array{id?: int, nome: string, email?: string}
     */
    private function resolverResponsavel(int $empresaId): array
    {
        $usuarios = $this->usuarioRepository->listAtivosByEmpresa($empresaId);
        foreach ($usuarios as $usuario) {
            $perfil = strtoupper(trim((string) ($usuario->perfil ?? '')));
            if (!in_array($perfil, ['SUPER_ADMIN', 'ADMIN'], true)) {
                continue;
            }

            return [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
            ];
        }

        if ($usuarios !== []) {
            $usuario = $usuarios[0];
            return [
                'id' => $usuario->id,
                'nome' => $usuario->nome,
                'email' => $usuario->email,
            ];
        }

        return ['nome' => 'Equipe operacional'];
    }

    private function resolverResultadoWinLoss(int $propostaId, int $empresaId): string
    {
        $resultado = $this->resultadoRepository->findLatestByProposta($propostaId, $empresaId);
        if (!$resultado instanceof PropostaResultado) {
            return 'NEUTRO';
        }

        $situacao = strtoupper(trim((string) ($resultado->situacao ?? '')));
        if ($situacao === 'VENCEDORA') {
            return 'WIN';
        }

        if (in_array($situacao, ['NAO_VENCEDORA', 'DESCLASSIFICADA', 'ANULADA'], true)) {
            return 'LOSS';
        }

        return 'NEUTRO';
    }

    private function gerarAprendizadoResumo(
        string $tipoAlerta,
        string $resultadoWinLoss,
        bool $houveEscalonamento,
        float $progresso
    ): string {
        $tipoTexto = $tipoAlerta === 'JULGAMENTO_PARADO'
            ? 'julgamento parado'
            : 'sem resultado apos submissao';
        $escalonamentoTexto = $houveEscalonamento
            ? 'houve escalonamento automatico'
            : 'nao houve escalonamento';

        if ($resultadoWinLoss === 'WIN') {
            return 'Caso de ' . $tipoTexto . ': resultado WIN, ' . $escalonamentoTexto
                . ', progresso final de ' . number_format($progresso, 1, ',', '.') . '%.';
        }

        if ($resultadoWinLoss === 'LOSS') {
            return 'Caso de ' . $tipoTexto . ': resultado LOSS, ' . $escalonamentoTexto
                . ', revisar gatilhos de prioridade e antecipacao de resposta.';
        }

        return 'Caso de ' . $tipoTexto . ': encerrado como NEUTRO, ' . $escalonamentoTexto
            . ', manter monitoramento e coleta de evidencias.';
    }

    private function tipoAlertaValido(string $tipoAlerta): bool
    {
        return in_array($tipoAlerta, ['SEM_RESULTADO', 'JULGAMENTO_PARADO'], true);
    }

    private function parseDateTime(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $raw = trim($value);
        $formatos = ['Y-m-d H:i:s', 'Y-m-d H:i'];
        foreach ($formatos as $formato) {
            $date = DateTimeImmutable::createFromFormat($formato, $raw);
            if ($date instanceof DateTimeImmutable) {
                return $date;
            }
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return (new DateTimeImmutable())->setTimestamp($timestamp);
    }

    private function envInt(string $key, int $default): int
    {
        $raw = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        if ($raw === null || $raw === '' || !is_numeric($raw)) {
            return $default;
        }

        return (int) $raw;
    }

    private function envBool(string $key, bool $default): bool
    {
        $raw = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        if ($raw === null || $raw === '') {
            return $default;
        }

        return filter_var((string) $raw, FILTER_VALIDATE_BOOLEAN);
    }

    private function envString(string $key, string $default): string
    {
        $raw = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        if ($raw === null) {
            return $default;
        }

        $value = trim((string) $raw);
        return $value !== '' ? $value : $default;
    }
}
