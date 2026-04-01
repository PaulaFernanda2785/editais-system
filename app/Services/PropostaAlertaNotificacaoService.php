<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EmpresaRepository;
use App\Repositories\PropostaAlertaNotificacaoRepository;
use App\Repositories\UsuarioRepository;
use Throwable;

class PropostaAlertaNotificacaoService
{
    private PropostaExecucaoService $propostaService;
    private PropostaAlertaNotificacaoRepository $notificacaoRepository;
    private PropostaAlertaOrquestradorService $orquestradorService;
    private EmpresaRepository $empresaRepository;
    private UsuarioRepository $usuarioRepository;
    private AlertaEmailDestinatarioService $destinatarioService;
    private EmailService $emailService;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?PropostaExecucaoService $propostaService = null,
        ?PropostaAlertaNotificacaoRepository $notificacaoRepository = null,
        ?PropostaAlertaOrquestradorService $orquestradorService = null,
        ?EmpresaRepository $empresaRepository = null,
        ?UsuarioRepository $usuarioRepository = null,
        ?AlertaEmailDestinatarioService $destinatarioService = null,
        ?EmailService $emailService = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->propostaService = $propostaService ?? new PropostaExecucaoService();
        $this->notificacaoRepository = $notificacaoRepository ?? new PropostaAlertaNotificacaoRepository();
        $this->orquestradorService = $orquestradorService ?? new PropostaAlertaOrquestradorService();
        $this->empresaRepository = $empresaRepository ?? new EmpresaRepository();
        $this->usuarioRepository = $usuarioRepository ?? new UsuarioRepository();
        $this->logService = $logService ?? new LogService();
        $this->destinatarioService = $destinatarioService ?? new AlertaEmailDestinatarioService(
            $this->empresaRepository,
            $this->usuarioRepository
        );
        $this->emailService = $emailService ?? new EmailService($this->logService);
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @return array<string, mixed>
     */
    public function processarEmpresa(int $empresaId, bool $enviarEmail = true): array
    {
        $diasSemResultado = $this->envInt('ALERTA_RESULTADO_SEM_RETORNO_DIAS', 7);
        $diasJulgamentoParado = $this->envInt('ALERTA_JULGAMENTO_PARADO_DIAS', 10);
        $limite = $this->envInt('ALERTA_RESULTADO_LIMITE_ITENS', 30);
        $alertas = $this->propostaService->alertasResultadoJulgamento(
            $empresaId,
            $diasSemResultado,
            $diasJulgamentoParado,
            $limite
        );

        $novos = [];
        $idsAtivosSemResultado = [];
        $idsAtivosJulgamentoParado = [];

        $semResultado = isset($alertas['sem_resultado']) && is_array($alertas['sem_resultado'])
            ? $alertas['sem_resultado']
            : [];
        foreach ($semResultado as $item) {
            $propostaId = (int) ($item['proposta_id'] ?? 0);
            if ($propostaId <= 0) {
                continue;
            }

            $idsAtivosSemResultado[] = $propostaId;
            $upsert = $this->notificacaoRepository->upsertAtivo($empresaId, $propostaId, 'SEM_RESULTADO');
            if (($upsert['eh_novo'] ?? false) === true) {
                $novos[] = [
                    'id' => (int) ($upsert['id'] ?? 0),
                    'tipo_alerta' => 'SEM_RESULTADO',
                    'proposta_id' => $propostaId,
                    'proposta_titulo' => (string) ($item['proposta_titulo'] ?? 'Proposta'),
                    'orgao_nome' => (string) ($item['orgao_nome'] ?? '-'),
                    'dias' => (int) ($item['dias_sem_retorno'] ?? 0),
                ];
            }
        }

        $julgamentoParado = isset($alertas['julgamento_parado']) && is_array($alertas['julgamento_parado'])
            ? $alertas['julgamento_parado']
            : [];
        foreach ($julgamentoParado as $item) {
            $propostaId = (int) ($item['proposta_id'] ?? 0);
            if ($propostaId <= 0) {
                continue;
            }

            $idsAtivosJulgamentoParado[] = $propostaId;
            $upsert = $this->notificacaoRepository->upsertAtivo($empresaId, $propostaId, 'JULGAMENTO_PARADO');
            if (($upsert['eh_novo'] ?? false) === true) {
                $novos[] = [
                    'id' => (int) ($upsert['id'] ?? 0),
                    'tipo_alerta' => 'JULGAMENTO_PARADO',
                    'proposta_id' => $propostaId,
                    'proposta_titulo' => (string) ($item['proposta_titulo'] ?? 'Proposta'),
                    'orgao_nome' => (string) ($item['orgao_nome'] ?? '-'),
                    'dias' => (int) ($item['dias_em_julgamento'] ?? 0),
                ];
            }
        }

        $resolvidosSemResultado = $this->notificacaoRepository->resolverAusentes(
            $empresaId,
            'SEM_RESULTADO',
            $idsAtivosSemResultado
        );
        $resolvidosJulgamentoParado = $this->notificacaoRepository->resolverAusentes(
            $empresaId,
            'JULGAMENTO_PARADO',
            $idsAtivosJulgamentoParado
        );

        $emailsEnviados = 0;
        $emailsFalhos = 0;
        if ($enviarEmail && $novos !== []) {
            $envio = $this->enviarEmailNovosAlertas($empresaId, $novos);
            $idsNovos = array_values(array_filter(array_map(
                static fn(array $item): int => (int) ($item['id'] ?? 0),
                $novos
            ), static fn(int $id): bool => $id > 0));

            if (($envio['sucesso'] ?? false) === true) {
                $this->notificacaoRepository->marcarEmailEnviado($idsNovos);
                $emailsEnviados = count($idsNovos);
            } else {
                $this->notificacaoRepository->registrarFalhaEmail($idsNovos, (string) ($envio['erro'] ?? 'falha_desconhecida'));
                $emailsFalhos = count($idsNovos);
            }
        }

        $orquestrador = [
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
        try {
            $orquestrador = $this->orquestradorService->processarCiclo($empresaId, $novos, $enviarEmail);
        } catch (Throwable $exception) {
            $this->logService->warning('propostas.playbook.processar', 'Falha ao processar orquestrador automatico.', [
                'empresa_id' => $empresaId,
                'exception' => $exception->getMessage(),
            ]);
        }

        $this->logService->info('propostas.alertas.processar', 'Processamento de alertas de propostas executado.', [
            'empresa_id' => $empresaId,
            'novos' => count($novos),
            'resolvidos_sem_resultado' => $resolvidosSemResultado,
            'resolvidos_julgamento_parado' => $resolvidosJulgamentoParado,
            'emails_enviados' => $emailsEnviados,
            'emails_falhos' => $emailsFalhos,
            'playbooks_criados' => (int) ($orquestrador['playbooks_criados'] ?? 0),
            'playbooks_reabertos' => (int) ($orquestrador['playbooks_reabertos'] ?? 0),
            'playbooks_escalonados' => (int) ($orquestrador['playbooks_escalonados'] ?? 0),
            'playbooks_encerrados' => (int) ($orquestrador['playbooks_encerrados'] ?? 0),
        ]);

        return [
            'empresa_id' => $empresaId,
            'novos' => count($novos),
            'resolvidos' => $resolvidosSemResultado + $resolvidosJulgamentoParado,
            'emails_enviados' => $emailsEnviados,
            'emails_falhos' => $emailsFalhos,
            'orquestrador' => $orquestrador,
            'totais_alerta' => $alertas['totais'] ?? [],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function obterParaDashboard(int $empresaId, int $limit = 8): array
    {
        if ($limit < 1) {
            $limit = 8;
        }

        $items = $this->notificacaoRepository->listAtivosDashboard($empresaId, $limit);
        $novos = $this->notificacaoRepository->countAtivosNovos($empresaId);
        $totalAtivos = $this->notificacaoRepository->countAtivos($empresaId);
        $orquestrador = [
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
                ],
                'por_nivel' => [],
            ],
            'escalonados' => [],
            'aprendizado' => [],
            'evidencias' => [],
        ];
        try {
            $orquestrador = $this->orquestradorService->obterParaDashboard($empresaId, max(3, min(8, $limit)));
        } catch (Throwable $exception) {
            $this->logService->warning('propostas.playbook.dashboard', 'Falha ao carregar resumo do orquestrador.', [
                'empresa_id' => $empresaId,
                'exception' => $exception->getMessage(),
            ]);
        }

        return [
            'items' => $items,
            'novos' => $novos,
            'total_ativos' => $totalAtivos,
            'orquestrador' => $orquestrador,
        ];
    }

    public function marcarNovosComoVisualizados(int $empresaId): int
    {
        $count = $this->notificacaoRepository->marcarNovosComoVisualizados($empresaId);
        if ($count > 0) {
            $this->auditService->record(
                'ALERTAS_PROPOSTA_VISUALIZADOS',
                'proposta_alerta_notificacoes',
                null,
                ['quantidade' => $count],
                $empresaId,
                null
            );
        }

        return $count;
    }

    /**
     * @param array<int, array<string, mixed>> $novos
     * @return array{sucesso: bool, erro?: string}
     */
    private function enviarEmailNovosAlertas(int $empresaId, array $novos): array
    {
        $habilitado = $this->envBool('ALERTA_EMAIL_HABILITADO', true);
        if (!$habilitado) {
            return ['sucesso' => true];
        }

        $resolucao = $this->destinatarioService->resolverPrincipal($empresaId);
        $destinatario = isset($resolucao['email']) ? trim((string) ($resolucao['email'] ?? '')) : '';
        if ($destinatario === '') {
            return ['sucesso' => false, 'erro' => 'destinatario_nao_configurado'];
        }

        $validacao = $this->destinatarioService->validarEmail($destinatario);
        if (($validacao['valido'] ?? false) !== true) {
            $erro = (string) ($validacao['erro'] ?? 'destinatario_invalido');
            return ['sucesso' => false, 'erro' => 'destinatario_invalido:' . $erro];
        }
        $destinatario = (string) ($validacao['email'] ?? $destinatario);

        $appName = $this->envString('APP_NAME', 'SaaS Editais');
        $subject = '[' . $appName . '] ' . count($novos) . ' novo(s) alerta(s) de propostas';
        $fromAddress = trim($this->envString('ALERTA_EMAIL_FROM', 'no-reply@example.com'));
        $fromName = trim($this->envString('ALERTA_EMAIL_NOME', $appName));

        $linhas = [];
        $linhas[] = 'Foram identificados novos alertas de propostas:';
        $linhas[] = '';
        foreach ($novos as $item) {
            $tipo = (string) ($item['tipo_alerta'] ?? '-');
            $propostaId = (int) ($item['proposta_id'] ?? 0);
            $orgao = (string) ($item['orgao_nome'] ?? '-');
            $dias = (int) ($item['dias'] ?? 0);
            $textoTipo = $tipo === 'SEM_RESULTADO'
                ? 'Sem resultado apos envio'
                : 'Julgamento sem atualizacao';

            $linhas[] = '- Proposta #' . $propostaId . ' | ' . $textoTipo . ' | ' . $orgao . ' | ' . $dias . ' dia(s)';
        }
        $linhas[] = '';
        $linhas[] = 'Acesse o dashboard para detalhes.';

        $body = implode(PHP_EOL, $linhas);

        $envio = $this->emailService->sendPlainText(
            $destinatario,
            $subject,
            $body,
            $fromAddress,
            $fromName
        );
        if (($envio['sucesso'] ?? false) !== true) {
            return ['sucesso' => false, 'erro' => (string) ($envio['erro'] ?? 'falha_envio_email')];
        }

        return ['sucesso' => true];
    }

    private function resolverDestinatarioEmail(int $empresaId): ?string
    {
        $resolucao = $this->destinatarioService->resolverPrincipal($empresaId);
        $email = isset($resolucao['email']) ? trim((string) ($resolucao['email'] ?? '')) : '';
        return $email !== '' ? $email : null;
    }

    private function envInt(string $key, int $default): int
    {
        $raw = $_ENV[$key] ?? $_SERVER[$key] ?? null;
        if ($raw === null || $raw === '' || !is_numeric($raw)) {
            return $default;
        }

        $value = (int) $raw;
        return $value > 0 ? $value : $default;
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
