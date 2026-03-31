<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ExternalLinkHelper;
use App\Models\Favorito;
use App\Repositories\CorrespondenciaRepository;
use App\Repositories\FavoritoRepository;
use App\Repositories\FavoritoStatusHistoricoRepository;
use App\Repositories\FavoritoTarefaRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;
use Throwable;

class FavoritoService
{
    /**
     * @var array<int, string>
     */
    private const STATUS_ACOMPANHAMENTO = ['FAVORITO', 'EM_ANALISE', 'PROPOSTA', 'DESCARTADO', 'ENCERRADO'];

    /**
     * @var array<int, string>
     */
    private const STATUS_TAREFA = ['PENDENTE', 'EM_ANDAMENTO', 'CONCLUIDA', 'BLOQUEADA'];

    private FavoritoRepository $favoritoRepository;
    private FavoritoTarefaRepository $tarefaRepository;
    private FavoritoStatusHistoricoRepository $historicoStatusRepository;
    private UsuarioRepository $usuarioRepository;
    private CorrespondenciaRepository $correspondenciaRepository;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?FavoritoRepository $favoritoRepository = null,
        ?FavoritoTarefaRepository $tarefaRepository = null,
        ?FavoritoStatusHistoricoRepository $historicoStatusRepository = null,
        ?UsuarioRepository $usuarioRepository = null,
        ?CorrespondenciaRepository $correspondenciaRepository = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->favoritoRepository = $favoritoRepository ?? new FavoritoRepository();
        $this->tarefaRepository = $tarefaRepository ?? new FavoritoTarefaRepository();
        $this->historicoStatusRepository = $historicoStatusRepository ?? new FavoritoStatusHistoricoRepository();
        $this->usuarioRepository = $usuarioRepository ?? new UsuarioRepository();
        $this->correspondenciaRepository = $correspondenciaRepository ?? new CorrespondenciaRepository();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function listarPipeline(int $empresaId, array $input = []): array
    {
        $page = $this->sanitizeInt($input['page'] ?? 1, 1, 100000, 1);
        $perPage = $this->sanitizeInt($input['per_page'] ?? 20, 5, 100, 20);
        $sort = $this->normalizarSort((string) ($input['sort'] ?? 'atualizado_desc'));
        $filters = [
            'termo' => trim((string) ($input['termo'] ?? '')),
            'status_acompanhamento' => $this->normalizarStatus((string) ($input['status_acompanhamento'] ?? '')),
        ];

        $resultado = $this->favoritoRepository->listByEmpresa(
            $empresaId,
            $filters,
            $page,
            $perPage,
            $sort
        );

        if (isset($resultado['items']) && is_array($resultado['items'])) {
            foreach ($resultado['items'] as $item) {
                if (!$item instanceof Favorito) {
                    continue;
                }

                $this->resolverLinksFavorito($item);
            }
        }

        $resultado['sort'] = $sort;
        $resultado['filters'] = $filters;
        $resultado['status_permitidos'] = self::STATUS_ACOMPANHAMENTO;

        return $resultado;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detalhar(int $empresaId, int $favoritoId): ?array
    {
        $favorito = $this->favoritoRepository->findByIdAndEmpresa($favoritoId, $empresaId);
        if ($favorito === null) {
            return null;
        }

        $this->resolverLinksFavorito($favorito);

        return [
            'favorito' => $favorito,
            'tarefas' => $this->tarefaRepository->listByFavorito($favoritoId, $empresaId),
            'recomendacao' => $this->gerarRecomendacao($favorito->correspondenciaScore),
            'usuarios_responsaveis' => $this->usuarioRepository->listAtivosByEmpresa($empresaId),
            'status_permitidos' => self::STATUS_ACOMPANHAMENTO,
            'status_tarefa_permitidos' => self::STATUS_TAREFA,
        ];
    }

    public function decidirPorOportunidade(
        int $empresaId,
        ?int $usuarioId,
        int $correspondenciaId,
        string $statusAcompanhamento,
        ?string $observacao = null
    ): array {
        $status = $this->normalizarStatusObrigatorio($statusAcompanhamento);
        if ($status === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Status de acompanhamento invalido.',
                'favorito_id' => null,
            ];
        }

        $correspondencia = $this->correspondenciaRepository->findByIdAndEmpresa($correspondenciaId, $empresaId);
        if ($correspondencia === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Oportunidade nao encontrada para registrar decisao.',
                'favorito_id' => null,
            ];
        }

        $favoritoExistente = $this->favoritoRepository->findByEmpresaAndEdital($empresaId, $correspondencia->editalId);
        $statusAnterior = $favoritoExistente?->statusAcompanhamento;

        $resultado = $this->favoritoRepository->upsertByEmpresaEdital(
            $empresaId,
            $correspondencia->editalId,
            $status,
            $this->normalizarObservacao($observacao)
        );

        $favoritoId = isset($resultado['favorito_id']) ? (int) $resultado['favorito_id'] : null;
        if ($favoritoId === null && $favoritoExistente !== null) {
            $favoritoId = $favoritoExistente->id;
        }

        $this->registrarHistoricoStatus(
            $favoritoId,
            $empresaId,
            $usuarioId,
            $statusAnterior,
            $status,
            'decisao_oportunidade'
        );

        if ($favoritoId !== null && in_array($status, ['EM_ANALISE', 'PROPOSTA'], true)) {
            $this->garantirChecklistInicial(
                $favoritoId,
                $empresaId,
                isset($correspondencia->editalDataEncerramento) ? (string) $correspondencia->editalDataEncerramento : null
            );
        }

        $this->logService->info('favoritos.decisao', 'Decisao operacional registrada a partir de oportunidade.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'correspondencia_id' => $correspondenciaId,
            'edital_id' => $correspondencia->editalId,
            'status_acompanhamento' => $status,
            'favorito_id' => $favoritoId,
            'acao' => $resultado['acao'] ?? null,
        ]);

        $this->auditService->record(
            'DECISAO_OPORTUNIDADE_REGISTRADA',
            'favoritos',
            $favoritoId,
            [
                'correspondencia_id' => $correspondenciaId,
                'status_acompanhamento' => $status,
                'acao' => $resultado['acao'] ?? null,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Decisao registrada no pipeline de execucao.',
            'favorito_id' => $favoritoId,
        ];
    }

    public function atualizarStatus(
        int $empresaId,
        ?int $usuarioId,
        int $favoritoId,
        string $statusAcompanhamento,
        ?string $observacao = null
    ): array {
        $status = $this->normalizarStatusObrigatorio($statusAcompanhamento);
        if ($status === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Status de acompanhamento invalido.',
            ];
        }

        $favorito = $this->favoritoRepository->findByIdAndEmpresa($favoritoId, $empresaId);
        if ($favorito === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Item do pipeline nao encontrado.',
            ];
        }

        $textoObservacao = $this->normalizarObservacao($observacao);
        if ($textoObservacao === null) {
            $textoObservacao = $favorito->observacao;
        }

        $this->favoritoRepository->updateStatusAndObservacao(
            $favoritoId,
            $empresaId,
            $status,
            $textoObservacao
        );

        $this->registrarHistoricoStatus(
            $favoritoId,
            $empresaId,
            $usuarioId,
            $favorito->statusAcompanhamento,
            $status,
            'pipeline_status'
        );

        if (in_array($status, ['EM_ANALISE', 'PROPOSTA'], true)) {
            $this->garantirChecklistInicial(
                $favoritoId,
                $empresaId,
                $favorito->editalDataEncerramento
            );
        }

        $this->logService->info('favoritos.status.update', 'Status do pipeline atualizado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'favorito_id' => $favoritoId,
            'status_anterior' => $favorito->statusAcompanhamento,
            'status_novo' => $status,
        ]);

        $this->auditService->record(
            'PIPELINE_STATUS_ATUALIZADO',
            'favoritos',
            $favoritoId,
            [
                'status_anterior' => $favorito->statusAcompanhamento,
                'status_novo' => $status,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Status do pipeline atualizado com sucesso.',
        ];
    }

    public function criarTarefa(
        int $empresaId,
        ?int $usuarioId,
        int $favoritoId,
        array $payload
    ): array {
        $favorito = $this->favoritoRepository->findByIdAndEmpresa($favoritoId, $empresaId);
        if ($favorito === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Item do pipeline nao encontrado.',
            ];
        }

        $titulo = trim((string) ($payload['titulo'] ?? ''));
        if ($titulo === '') {
            return [
                'sucesso' => false,
                'mensagem' => 'Informe o titulo da tarefa.',
            ];
        }

        if (strlen($titulo) > 180) {
            $titulo = substr($titulo, 0, 180);
        }

        $dataLimite = $this->normalizarData($payload['data_limite'] ?? null);
        $responsavelUsuarioId = $this->sanitizeInt($payload['responsavel_usuario_id'] ?? 0, 0, 100000000, 0);
        $responsavelUsuario = null;
        if ($responsavelUsuarioId > 0) {
            $responsavelUsuario = $this->usuarioRepository->findByIdAndEmpresa($responsavelUsuarioId, $empresaId);
            if ($responsavelUsuario === null || !$responsavelUsuario->canLogin()) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Responsavel selecionado invalido ou inativo.',
                ];
            }
        }

        $responsavelTexto = $this->normalizarTextoLimite($payload['responsavel'] ?? null, 120);
        if ($responsavelUsuario !== null) {
            $responsavelTexto = $responsavelUsuario->nome;
        }

        $ordem = $this->tarefaRepository->nextOrdem($favoritoId, $empresaId);
        $tarefa = $this->tarefaRepository->create([
            'favorito_id' => $favoritoId,
            'empresa_id' => $empresaId,
            'titulo' => $titulo,
            'descricao' => $this->normalizarTextoLimite($payload['descricao'] ?? null, 3000),
            'responsavel' => $responsavelTexto,
            'responsavel_usuario_id' => $responsavelUsuario?->id,
            'data_limite' => $dataLimite,
            'status' => 'PENDENTE',
            'ordem' => $ordem,
        ]);

        $this->logService->info('favoritos.tarefa.create', 'Tarefa de execucao criada.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'favorito_id' => $favoritoId,
            'tarefa_id' => $tarefa->id,
        ]);

        $this->auditService->record(
            'PIPELINE_TAREFA_CRIADA',
            'favorito_tarefas',
            $tarefa->id,
            [
                'favorito_id' => $favoritoId,
                'titulo' => $tarefa->titulo,
                'responsavel_usuario_id' => $responsavelUsuario?->id,
                'data_limite' => $tarefa->dataLimite,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Tarefa adicionada com sucesso.',
        ];
    }

    public function atualizarStatusTarefa(
        int $empresaId,
        ?int $usuarioId,
        int $favoritoId,
        int $tarefaId,
        string $status
    ): array {
        $statusNormalizado = $this->normalizarStatusTarefaObrigatorio($status);
        if ($statusNormalizado === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Status de tarefa invalido.',
            ];
        }

        $tarefa = $this->tarefaRepository->findByIdAndFavorito($tarefaId, $favoritoId, $empresaId);
        if ($tarefa === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Tarefa nao encontrada.',
            ];
        }

        $this->tarefaRepository->updateStatus($tarefaId, $favoritoId, $empresaId, $statusNormalizado);

        $this->logService->info('favoritos.tarefa.status', 'Status da tarefa atualizado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'favorito_id' => $favoritoId,
            'tarefa_id' => $tarefaId,
            'status_anterior' => $tarefa->status,
            'status_novo' => $statusNormalizado,
        ]);

        $this->auditService->record(
            'PIPELINE_TAREFA_STATUS_ATUALIZADO',
            'favorito_tarefas',
            $tarefaId,
            [
                'favorito_id' => $favoritoId,
                'status_anterior' => $tarefa->status,
                'status_novo' => $statusNormalizado,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Status da tarefa atualizado.',
        ];
    }

    public function removerTarefa(
        int $empresaId,
        ?int $usuarioId,
        int $favoritoId,
        int $tarefaId
    ): array {
        $tarefa = $this->tarefaRepository->findByIdAndFavorito($tarefaId, $favoritoId, $empresaId);
        if ($tarefa === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Tarefa nao encontrada.',
            ];
        }

        $this->tarefaRepository->delete($tarefaId, $favoritoId, $empresaId);

        $this->logService->warning('favoritos.tarefa.delete', 'Tarefa removida do pipeline.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'favorito_id' => $favoritoId,
            'tarefa_id' => $tarefaId,
        ]);

        $this->auditService->record(
            'PIPELINE_TAREFA_REMOVIDA',
            'favorito_tarefas',
            $tarefaId,
            ['favorito_id' => $favoritoId],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Tarefa removida do pipeline.',
        ];
    }

    /**
     * @return array<string, int>
     */
    public function resumoPorStatus(int $empresaId): array
    {
        $grouped = $this->favoritoRepository->countByEmpresaGroupedStatus($empresaId);
        return [
            'FAVORITO' => $grouped['FAVORITO'] ?? 0,
            'EM_ANALISE' => $grouped['EM_ANALISE'] ?? 0,
            'PROPOSTA' => $grouped['PROPOSTA'] ?? 0,
            'DESCARTADO' => $grouped['DESCARTADO'] ?? 0,
            'ENCERRADO' => $grouped['ENCERRADO'] ?? 0,
            'TOTAL' => array_sum($grouped),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function alertasPrazo(int $empresaId, int $horas = 48, int $limite = 20): array
    {
        if ($horas < 1) {
            $horas = 48;
        }
        if ($limite < 1) {
            $limite = 20;
        }

        $dias = max(1, (int) ceil($horas / 24));
        try {
            $vencendo = $this->tarefaRepository->listAlertasVencendo($empresaId, $dias, $limite);
            $vencidas = $this->tarefaRepository->listAlertasVencidas($empresaId, $limite);
        } catch (Throwable $exception) {
            $this->logService->error('favoritos.alertas_prazo', 'Falha ao carregar alertas de prazo.', [
                'empresa_id' => $empresaId,
                'erro' => $exception->getMessage(),
            ]);

            $vencendo = [];
            $vencidas = [];
        }

        return [
            'janela_horas' => $horas,
            'vencendo' => $vencendo,
            'vencidas' => $vencidas,
            'totais' => [
                'vencendo' => count($vencendo),
                'vencidas' => count($vencidas),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function relatorioConversao(int $empresaId, array $input = []): array
    {
        $dataDe = $this->normalizarData($input['data_de'] ?? null);
        $dataAte = $this->normalizarData($input['data_ate'] ?? null);
        try {
            return $this->historicoStatusRepository->relatorioConversao($empresaId, $dataDe, $dataAte);
        } catch (Throwable $exception) {
            $this->logService->error('favoritos.relatorio_conversao', 'Falha ao gerar relatorio de conversao.', [
                'empresa_id' => $empresaId,
                'erro' => $exception->getMessage(),
            ]);

            return [
                'periodo' => [
                    'data_de' => $dataDe ?? date('Y-m-d', strtotime('-90 days')),
                    'data_ate' => $dataAte ?? date('Y-m-d'),
                ],
                'totais' => [
                    'em_analise' => 0,
                    'proposta' => 0,
                    'encerrado' => 0,
                ],
                'taxas' => [
                    'analise_para_proposta' => 0.0,
                    'proposta_para_encerrado' => 0.0,
                ],
                'funil' => [
                    ['fase' => 'EM_ANALISE', 'quantidade' => 0],
                    ['fase' => 'PROPOSTA', 'quantidade' => 0],
                    ['fase' => 'ENCERRADO', 'quantidade' => 0],
                ],
            ];
        }
    }

    /**
     * @return array<string, string>
     */
    private function gerarRecomendacao(?float $score): array
    {
        $score = $score ?? 0.0;
        if ($score >= 80.0) {
            return [
                'nivel' => 'FORTEMENTE_RECOMENDADO',
                'descricao' => 'Alta aderencia. Priorize analise e avance para proposta.',
                'status_sugerido' => 'EM_ANALISE',
            ];
        }

        if ($score >= 55.0) {
            return [
                'nivel' => 'RECOMENDADO',
                'descricao' => 'Boa aderencia. Valide documentacao e viabilidade economica.',
                'status_sugerido' => 'EM_ANALISE',
            ];
        }

        if ($score >= 30.0) {
            return [
                'nivel' => 'ANALISE_CAUTELA',
                'descricao' => 'Aderencia moderada. Decida com base em capacidade e margem.',
                'status_sugerido' => 'FAVORITO',
            ];
        }

        return [
            'nivel' => 'BAIXA_PRIORIDADE',
            'descricao' => 'Baixa aderencia. Considere descartar para preservar capacidade operacional.',
            'status_sugerido' => 'DESCARTADO',
        ];
    }

    private function resolverLinksFavorito(Favorito $item): void
    {
        $item->editalLinkDetalhe = ExternalLinkHelper::resolveForDetail(
            $item->editalLinkDetalhe,
            $item->editalNumero,
            $item->editalOrgaoNome,
            null,
            'detalhe'
        );

        $item->editalLinkEdital = ExternalLinkHelper::resolveForDetail(
            $item->editalLinkEdital,
            $item->editalNumero,
            $item->editalOrgaoNome,
            null,
            'edital'
        );
    }

    private function registrarHistoricoStatus(
        ?int $favoritoId,
        int $empresaId,
        ?int $usuarioId,
        ?string $statusAnterior,
        string $statusNovo,
        string $origem
    ): void {
        if ($favoritoId === null || $favoritoId <= 0) {
            return;
        }

        $anterior = $statusAnterior !== null ? strtoupper(trim($statusAnterior)) : null;
        $novo = strtoupper(trim($statusNovo));

        if ($novo === '') {
            return;
        }

        if ($anterior === $novo) {
            return;
        }

        try {
            $this->historicoStatusRepository->registrarTransicao(
                $favoritoId,
                $empresaId,
                $anterior,
                $novo,
                $usuarioId,
                $origem
            );
        } catch (Throwable $exception) {
            $this->logService->error('favoritos.historico_status', 'Falha ao registrar historico de status.', [
                'empresa_id' => $empresaId,
                'favorito_id' => $favoritoId,
                'erro' => $exception->getMessage(),
            ]);
        }
    }

    private function garantirChecklistInicial(int $favoritoId, int $empresaId, ?string $dataEncerramento): void
    {
        $tarefasExistentes = $this->tarefaRepository->listByFavorito($favoritoId, $empresaId);
        $titulosExistentes = [];
        foreach ($tarefasExistentes as $tarefa) {
            $titulo = mb_strtolower(trim((string) ($tarefa->titulo ?? '')));
            if ($titulo === '') {
                continue;
            }

            $titulosExistentes[] = $titulo;
        }

        $prazos = $this->buildPrazos($dataEncerramento);
        $tarefas = [
            'Conferir aderencia tecnica e documental do edital',
            'Definir estrategia comercial e margem da proposta',
            'Produzir proposta e anexar documentos obrigatorios',
            'Revisar e protocolar submissao final',
        ];

        foreach ($tarefas as $indice => $titulo) {
            $chaveTitulo = mb_strtolower($titulo);
            if (in_array($chaveTitulo, $titulosExistentes, true)) {
                continue;
            }

            $this->tarefaRepository->create([
                'favorito_id' => $favoritoId,
                'empresa_id' => $empresaId,
                'titulo' => $titulo,
                'descricao' => null,
                'responsavel' => null,
                'data_limite' => $prazos[$indice] ?? null,
                'status' => 'PENDENTE',
                'ordem' => $this->tarefaRepository->nextOrdem($favoritoId, $empresaId),
            ]);
        }
    }

    /**
     * @return array<int, string|null>
     */
    private function buildPrazos(?string $dataEncerramento): array
    {
        $hoje = new DateTimeImmutable('today');
        $encerramento = $this->parseDate($dataEncerramento);

        if ($encerramento !== null && $encerramento >= $hoje) {
            $dias = (int) $hoje->diff($encerramento)->format('%a');
            $dias = max(2, $dias);
            $proporcoes = [0.2, 0.45, 0.7, 0.95];
            $prazos = [];

            foreach ($proporcoes as $proporcao) {
                $delta = max(1, (int) ceil($dias * $proporcao));
                $data = $hoje->modify('+' . $delta . ' days');
                if ($data > $encerramento) {
                    $data = $encerramento;
                }

                $prazos[] = $data->format('Y-m-d');
            }

            return $prazos;
        }

        return [
            $hoje->modify('+2 days')->format('Y-m-d'),
            $hoje->modify('+5 days')->format('Y-m-d'),
            $hoje->modify('+9 days')->format('Y-m-d'),
            $hoje->modify('+13 days')->format('Y-m-d'),
        ];
    }

    private function parseDate(?string $value): ?DateTimeImmutable
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $raw = trim($value);
        $formatos = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
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

    private function normalizarStatus(string $status): string
    {
        $status = strtoupper(trim($status));
        if (in_array($status, self::STATUS_ACOMPANHAMENTO, true)) {
            return $status;
        }

        return '';
    }

    private function normalizarStatusObrigatorio(string $status): ?string
    {
        $status = $this->normalizarStatus($status);
        if ($status === '') {
            return null;
        }

        return $status;
    }

    private function normalizarStatusTarefaObrigatorio(string $status): ?string
    {
        $status = strtoupper(trim($status));
        if (!in_array($status, self::STATUS_TAREFA, true)) {
            return null;
        }

        return $status;
    }

    private function normalizarObservacao(?string $observacao): ?string
    {
        if ($observacao === null) {
            return null;
        }

        $observacao = trim($observacao);
        if ($observacao === '') {
            return null;
        }

        if (strlen($observacao) > 5000) {
            $observacao = substr($observacao, 0, 5000);
        }

        return $observacao;
    }

    private function normalizarTextoLimite(mixed $value, int $limit): ?string
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

    private function normalizarData(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        $date = DateTimeImmutable::createFromFormat('Y-m-d', $raw);
        if (!$date instanceof DateTimeImmutable) {
            return null;
        }

        return $date->format('Y-m-d');
    }

    private function sanitizeInt(mixed $value, int $min, int $max, int $default): int
    {
        if (!is_numeric($value)) {
            return $default;
        }

        $int = (int) $value;
        if ($int < $min) {
            return $min;
        }

        if ($int > $max) {
            return $max;
        }

        return $int;
    }

    private function normalizarSort(string $sort): string
    {
        $permitidos = ['atualizado_desc', 'atualizado_asc', 'score_desc', 'prazo_asc'];
        return in_array($sort, $permitidos, true) ? $sort : 'atualizado_desc';
    }
}
