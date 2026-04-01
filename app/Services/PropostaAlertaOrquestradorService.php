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
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?PropostaAlertaPlaybookRepository $playbookRepository = null,
        ?PropostaExecucaoRepository $propostaRepository = null,
        ?PropostaResultadoRepository $resultadoRepository = null,
        ?FavoritoTarefaRepository $tarefaRepository = null,
        ?UsuarioRepository $usuarioRepository = null,
        ?EmpresaRepository $empresaRepository = null,
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
                'escalonados' => [],
                'aprendizado' => [],
            ];
        }

        return [
            'ativo' => true,
            'resumo' => $this->playbookRepository->resumoOperacionalDashboard($empresaId),
            'escalonados' => $this->playbookRepository->listarEscalonadosDashboard($empresaId, $limitEscalonados),
            'aprendizado' => $this->playbookRepository->listarAprendizadoDashboard($empresaId),
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

            $responsavel = $this->resolverResponsavel($empresaId);
            $regra = $this->playbookRepository->findAprendizadoRegra($empresaId, $tipoAlerta);
            $prioridade = $this->resolverPrioridadeInicial($tipoAlerta, $regra);
            $fatorPriorizacao = $this->resolverFatorPriorizacao($prioridade, $regra);
            $slaHoras = $this->resolverSlaHoras($tipoAlerta, $fatorPriorizacao);
            $prazoSla = (new DateTimeImmutable('now'))->modify('+' . $slaHoras . ' hours')->format('Y-m-d H:i:s');

            $payload = [
                'empresa_id' => $empresaId,
                'alerta_notificacao_id' => $alertaNotificacaoId,
                'proposta_id' => $propostaId,
                'favorito_id' => $proposta->favoritoId,
                'tipo_alerta' => $tipoAlerta,
                'status' => 'ATIVO',
                'prioridade' => $prioridade,
                'fator_priorizacao' => $fatorPriorizacao,
                'sla_horas' => $slaHoras,
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
                        'sla_horas' => $slaHoras,
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
                            'sla_horas' => $slaHoras,
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
                    'sla_horas' => $slaHoras,
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

            $prazoSla = $this->parseDateTime(isset($playbook['prazo_sla_em']) ? (string) $playbook['prazo_sla_em'] : null);
            $escalonadoEm = $this->parseDateTime(isset($playbook['escalonado_em']) ? (string) $playbook['escalonado_em'] : null);
            $deveEscalonar = $progresso <= 0.0
                && $escalonadoEm === null
                && $prazoSla instanceof DateTimeImmutable
                && $agora > $prazoSla;

            if (!$deveEscalonar) {
                continue;
            }

            $motivo = 'SLA ultrapassado sem avanco do playbook automatico.';
            $ok = $this->playbookRepository->marcarEscalonado($playbookId, $empresaId, 1, $motivo);
            if (!$ok) {
                continue;
            }

            $escalonados++;
            $this->playbookRepository->registrarEvento(
                $playbookId,
                $empresaId,
                'ESCALONADO',
                'Escalonamento automatico executado por ausencia de progresso.',
                [
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
                $this->enviarEmailEscalonamento($empresaId, $playbook, $motivo);
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
                ]
            );
            $this->playbookRepository->registrarAprendizado(
                $empresaId,
                $tipoAlerta,
                $resultadoWinLoss,
                $houveEscalonamento
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
            $destinatarios = $this->resolverDestinatariosEscalonamento($empresaId, $playbook);
            if ($destinatarios === []) {
                return;
            }

            $appName = $this->envString('APP_NAME', 'SaaS Editais');
            $subject = '[' . $appName . '] Escalonamento automatico de alerta de proposta';
            $fromAddress = trim($this->envString('ALERTA_EMAIL_FROM', 'no-reply@localhost'));
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
            $linhas[] = 'Prioridade: ' . (string) ($playbook['prioridade'] ?? 'MEDIA');
            $linhas[] = 'Responsavel: ' . (string) ($playbook['responsavel_nome'] ?? 'Nao definido');
            $linhas[] = 'Prazo SLA: ' . (string) ($playbook['prazo_sla_em'] ?? '-');
            $linhas[] = 'Motivo: ' . $motivo;
            $linhas[] = '';
            $linhas[] = 'Acompanhe no dashboard: ' . $dashboardUrl;

            $headers = [];
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/plain; charset=UTF-8';
            $headers[] = 'From: ' . $fromName . ' <' . $fromAddress . '>';

            $ok = @mail(
                implode(',', $destinatarios),
                $subject,
                implode(PHP_EOL, $linhas),
                implode("\r\n", $headers)
            );

            if (!$ok) {
                $this->logService->warning(
                    'propostas.playbook.escalonamento.email',
                    'Falha no envio de email de escalonamento.',
                    [
                        'empresa_id' => $empresaId,
                        'playbook_id' => (int) ($playbook['id'] ?? 0),
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
        $emails = [];

        $responsavelEmail = trim((string) ($playbook['responsavel_email'] ?? ''));
        if ($responsavelEmail !== '' && filter_var($responsavelEmail, FILTER_VALIDATE_EMAIL)) {
            $emails[] = strtolower($responsavelEmail);
        }

        $empresa = $this->empresaRepository->findById($empresaId);
        if ($empresa !== null) {
            $emailContato = trim((string) ($empresa->emailContato ?? ''));
            if ($emailContato !== '' && filter_var($emailContato, FILTER_VALIDATE_EMAIL)) {
                $emails[] = strtolower($emailContato);
            }
        }

        $usuarios = $this->usuarioRepository->listAtivosByEmpresa($empresaId);
        foreach ($usuarios as $usuario) {
            $perfil = strtoupper(trim((string) ($usuario->perfil ?? '')));
            $email = trim((string) ($usuario->email ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if (in_array($perfil, ['SUPER_ADMIN', 'ADMIN'], true)) {
                $emails[] = strtolower($email);
            }
        }

        $emails = array_values(array_unique($emails));
        if (count($emails) > 6) {
            $emails = array_slice($emails, 0, 6);
        }

        return $emails;
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

    private function resolverSlaHoras(string $tipoAlerta, float $fatorPriorizacao): int
    {
        $base = $tipoAlerta === 'JULGAMENTO_PARADO'
            ? $this->envInt('ALERTA_PLAYBOOK_SLA_JULGAMENTO_PARADO_HORAS', 36)
            : $this->envInt('ALERTA_PLAYBOOK_SLA_SEM_RESULTADO_HORAS', 48);

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
