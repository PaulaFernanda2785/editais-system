<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FavoritoRepository;
use App\Repositories\FavoritoStatusHistoricoRepository;
use App\Repositories\FavoritoTarefaRepository;
use App\Repositories\PropostaAprovacaoRepository;
use App\Repositories\PropostaExecucaoRepository;
use App\Repositories\PropostaResultadoRepository;
use App\Repositories\PropostaSubmissaoRepository;
use DateTimeImmutable;

class PropostaExecucaoService
{
    /**
     * @var array<int, string>
     */
    private const STATUS_PERMITIDOS = ['RASCUNHO', 'EM_REVISAO', 'APROVADA', 'ENVIADA'];

    /**
     * @var array<int, string>
     */
    private const CANAIS_SUBMISSAO = ['PORTAL', 'EMAIL', 'PRESENCIAL', 'OUTRO'];

    /**
     * @var array<int, string>
     */
    private const SITUACOES_RESULTADO = ['EM_JULGAMENTO', 'VENCEDORA', 'NAO_VENCEDORA', 'DESCLASSIFICADA', 'ANULADA'];

    private PropostaExecucaoRepository $propostaRepository;
    private PropostaAprovacaoRepository $aprovacaoRepository;
    private PropostaSubmissaoRepository $submissaoRepository;
    private PropostaResultadoRepository $resultadoRepository;
    private FavoritoRepository $favoritoRepository;
    private FavoritoStatusHistoricoRepository $favoritoHistoricoRepository;
    private FavoritoTarefaRepository $tarefaRepository;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?PropostaExecucaoRepository $propostaRepository = null,
        ?PropostaAprovacaoRepository $aprovacaoRepository = null,
        ?PropostaSubmissaoRepository $submissaoRepository = null,
        ?PropostaResultadoRepository $resultadoRepository = null,
        ?FavoritoRepository $favoritoRepository = null,
        ?FavoritoStatusHistoricoRepository $favoritoHistoricoRepository = null,
        ?FavoritoTarefaRepository $tarefaRepository = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->propostaRepository = $propostaRepository ?? new PropostaExecucaoRepository();
        $this->aprovacaoRepository = $aprovacaoRepository ?? new PropostaAprovacaoRepository();
        $this->submissaoRepository = $submissaoRepository ?? new PropostaSubmissaoRepository();
        $this->resultadoRepository = $resultadoRepository ?? new PropostaResultadoRepository();
        $this->favoritoRepository = $favoritoRepository ?? new FavoritoRepository();
        $this->favoritoHistoricoRepository = $favoritoHistoricoRepository ?? new FavoritoStatusHistoricoRepository();
        $this->tarefaRepository = $tarefaRepository ?? new FavoritoTarefaRepository();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function listar(int $empresaId, array $input = []): array
    {
        $page = $this->sanitizeInt($input['page'] ?? 1, 1, 100000, 1);
        $perPage = $this->sanitizeInt($input['per_page'] ?? 20, 5, 100, 20);
        $sort = $this->normalizarSort((string) ($input['sort'] ?? 'atualizado_desc'));
        $filters = [
            'termo' => trim((string) ($input['termo'] ?? '')),
            'status' => strtoupper(trim((string) ($input['status'] ?? ''))),
        ];

        $resultado = $this->propostaRepository->listByEmpresa(
            $empresaId,
            $filters,
            $page,
            $perPage,
            $sort
        );
        $resultado['sort'] = $sort;
        $resultado['filters'] = $filters;
        $resultado['status_permitidos'] = self::STATUS_PERMITIDOS;

        return $resultado;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detalhar(int $empresaId, int $propostaId): ?array
    {
        $proposta = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
        if ($proposta === null) {
            return null;
        }

        $favorito = $this->favoritoRepository->findByIdAndEmpresa($proposta->favoritoId, $empresaId);
        $tarefas = $this->tarefaRepository->listByFavorito($proposta->favoritoId, $empresaId);
        $aprovacoes = $this->aprovacaoRepository->listByProposta($propostaId, $empresaId);
        $submissoes = $this->submissaoRepository->listByProposta($propostaId, $empresaId);
        $resultados = $this->resultadoRepository->listByProposta($propostaId, $empresaId);

        return [
            'proposta' => $proposta,
            'favorito' => $favorito,
            'tarefas' => $tarefas,
            'aprovacoes' => $aprovacoes,
            'submissoes' => $submissoes,
            'resultados' => $resultados,
            'ultimo_resultado' => $resultados[0] ?? null,
            'aprovacao_pendente' => $this->aprovacaoRepository->findPendenteByProposta($propostaId, $empresaId),
            'status_permitidos' => self::STATUS_PERMITIDOS,
            'canais_submissao' => self::CANAIS_SUBMISSAO,
            'situacoes_resultado' => self::SITUACOES_RESULTADO,
        ];
    }

    public function gerarRascunhoPorFavorito(int $empresaId, ?int $usuarioId, int $favoritoId): array
    {
        $favorito = $this->favoritoRepository->findByIdAndEmpresa($favoritoId, $empresaId);
        if ($favorito === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Item do pipeline nao encontrado para gerar proposta.',
                'proposta_id' => null,
            ];
        }

        $tarefas = $this->tarefaRepository->listByFavorito($favoritoId, $empresaId);
        $score = (float) ($favorito->correspondenciaScore ?? 0.0);
        $titulo = $this->tituloProposta($favorito->editalNumero, $favorito->editalOrgaoNome);
        $resumo = $this->gerarResumoExecutivo($favorito, $score);
        $estrategia = $this->gerarEstrategia($score, $favorito->editalDataEncerramento);
        $escopo = $this->gerarEscopo($favorito);
        $diferenciais = $this->gerarDiferenciais($score);
        $cronograma = $this->gerarCronograma($tarefas, $favorito->editalDataEncerramento);
        $risco = $this->gerarRiscoPrincipal($tarefas, $favorito->editalDataEncerramento);

        $payload = [
            'status' => 'RASCUNHO',
            'titulo' => $titulo,
            'resumo_executivo' => $resumo,
            'estrategia_proposta' => $estrategia,
            'escopo_entrega' => $escopo,
            'diferenciais' => $diferenciais,
            'cronograma_macro' => $cronograma,
            'risco_principal' => $risco,
            'valor_proposta' => $favorito->editalValorEstimado,
            'observacoes' => 'Rascunho gerado automaticamente pelo assistente. Ajuste conforme estrategia comercial.',
            'gerada_automatica' => 1,
            'criado_por_usuario_id' => $usuarioId,
            'atualizado_por_usuario_id' => $usuarioId,
        ];

        $resultado = $this->propostaRepository->upsertByFavorito($favoritoId, $empresaId, $payload);
        $propostaId = (int) ($resultado['proposta_id'] ?? 0);

        $this->logService->info('propostas.gerar_rascunho', 'Rascunho de proposta gerado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'favorito_id' => $favoritoId,
            'proposta_id' => $propostaId,
            'acao' => $resultado['acao'] ?? null,
        ]);

        $this->auditService->record(
            'PROPOSTA_RASCUNHO_GERADA',
            'propostas_execucao',
            $propostaId > 0 ? $propostaId : null,
            [
                'favorito_id' => $favoritoId,
                'acao' => $resultado['acao'] ?? null,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Rascunho de proposta gerado com sucesso.',
            'proposta_id' => $propostaId > 0 ? $propostaId : null,
        ];
    }

    public function atualizar(int $empresaId, ?int $usuarioId, int $propostaId, array $payload): array
    {
        $proposta = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
        if ($proposta === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Proposta nao encontrada.',
            ];
        }

        $this->propostaRepository->updateById($propostaId, $empresaId, [
            'status' => $proposta->status,
            'titulo' => (string) ($payload['titulo'] ?? $proposta->titulo),
            'resumo_executivo' => $payload['resumo_executivo'] ?? $proposta->resumoExecutivo,
            'estrategia_proposta' => $payload['estrategia_proposta'] ?? $proposta->estrategiaProposta,
            'escopo_entrega' => $payload['escopo_entrega'] ?? $proposta->escopoEntrega,
            'diferenciais' => $payload['diferenciais'] ?? $proposta->diferenciais,
            'cronograma_macro' => $payload['cronograma_macro'] ?? $proposta->cronogramaMacro,
            'risco_principal' => $payload['risco_principal'] ?? $proposta->riscoPrincipal,
            'valor_proposta' => $payload['valor_proposta'] ?? $proposta->valorProposta,
            'observacoes' => $payload['observacoes'] ?? $proposta->observacoes,
            'gerada_automatica' => 0,
            'atualizado_por_usuario_id' => $usuarioId,
            'criado_por_usuario_id' => $proposta->criadoPorUsuarioId,
        ]);

        $this->logService->info('propostas.atualizar', 'Proposta atualizada manualmente.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'proposta_id' => $propostaId,
            'status' => $proposta->status,
        ]);

        $this->auditService->record(
            'PROPOSTA_ATUALIZADA',
            'propostas_execucao',
            $propostaId,
            ['status' => $proposta->status],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Proposta atualizada com sucesso.',
        ];
    }

    public function solicitarAprovacao(int $empresaId, ?int $usuarioId, int $propostaId, ?string $observacao): array
    {
        $proposta = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
        if ($proposta === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Proposta nao encontrada.',
            ];
        }

        if (strtoupper($proposta->status) !== 'RASCUNHO') {
            return [
                'sucesso' => false,
                'mensagem' => 'Somente propostas em RASCUNHO podem ir para aprovacao.',
            ];
        }

        $aprovacaoPendente = $this->aprovacaoRepository->findPendenteByProposta($propostaId, $empresaId);
        if ($aprovacaoPendente !== null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Ja existe uma solicitacao de aprovacao pendente para esta proposta.',
            ];
        }

        $aprovacaoId = $this->aprovacaoRepository->createSolicitacao(
            $propostaId,
            $empresaId,
            $usuarioId,
            $this->normalizarTextoLimite($observacao, 8000)
        );
        $this->propostaRepository->updateStatus($propostaId, $empresaId, 'EM_REVISAO', $usuarioId);

        $this->logService->info('propostas.aprovacao.solicitar', 'Aprovacao solicitada para proposta.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'proposta_id' => $propostaId,
            'aprovacao_id' => $aprovacaoId,
        ]);

        $this->auditService->record(
            'PROPOSTA_APROVACAO_SOLICITADA',
            'proposta_aprovacoes',
            $aprovacaoId,
            [
                'proposta_id' => $propostaId,
                'status_proposta_novo' => 'EM_REVISAO',
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Solicitacao de aprovacao enviada. Proposta movida para EM_REVISAO.',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function decidirAprovacao(int $empresaId, ?int $usuarioId, int $propostaId, array $payload): array
    {
        $proposta = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
        if ($proposta === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Proposta nao encontrada.',
            ];
        }

        if (strtoupper($proposta->status) !== 'EM_REVISAO') {
            return [
                'sucesso' => false,
                'mensagem' => 'A proposta precisa estar EM_REVISAO para receber decisao.',
            ];
        }

        $decisao = strtoupper(trim((string) ($payload['decisao'] ?? '')));
        if (!in_array($decisao, ['APROVADA', 'REPROVADA'], true)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Decisao invalida. Use APROVADA ou REPROVADA.',
            ];
        }

        $aprovacaoId = $this->sanitizeInt($payload['aprovacao_id'] ?? 0, 0, 100000000, 0);
        $aprovacao = $aprovacaoId > 0
            ? $this->aprovacaoRepository->findByIdAndProposta($aprovacaoId, $propostaId, $empresaId)
            : $this->aprovacaoRepository->findPendenteByProposta($propostaId, $empresaId);

        if ($aprovacao === null || strtoupper($aprovacao->statusDecisao) !== 'PENDENTE') {
            return [
                'sucesso' => false,
                'mensagem' => 'Nao existe solicitacao pendente para decidir.',
            ];
        }

        $parecer = $this->normalizarTextoLimite($payload['parecer'] ?? null, 8000);
        $ok = $this->aprovacaoRepository->decidir(
            $aprovacao->id,
            $propostaId,
            $empresaId,
            $decisao,
            $usuarioId,
            $parecer
        );

        if (!$ok) {
            return [
                'sucesso' => false,
                'mensagem' => 'Nao foi possivel registrar a decisao de aprovacao.',
            ];
        }

        $statusNovo = $decisao === 'APROVADA' ? 'APROVADA' : 'RASCUNHO';
        $this->propostaRepository->updateStatus($propostaId, $empresaId, $statusNovo, $usuarioId);

        $this->logService->info('propostas.aprovacao.decidir', 'Decisao de aprovacao registrada.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'proposta_id' => $propostaId,
            'aprovacao_id' => $aprovacao->id,
            'decisao' => $decisao,
            'status_proposta_novo' => $statusNovo,
        ]);

        $this->auditService->record(
            'PROPOSTA_APROVACAO_DECIDIDA',
            'proposta_aprovacoes',
            $aprovacao->id,
            [
                'proposta_id' => $propostaId,
                'decisao' => $decisao,
                'status_proposta_novo' => $statusNovo,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => $decisao === 'APROVADA'
                ? 'Proposta aprovada e pronta para envio.'
                : 'Proposta reprovada e retornada para RASCUNHO.',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function registrarSubmissao(int $empresaId, ?int $usuarioId, int $propostaId, array $payload): array
    {
        $proposta = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
        if ($proposta === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Proposta nao encontrada.',
            ];
        }

        if (strtoupper($proposta->status) !== 'APROVADA') {
            return [
                'sucesso' => false,
                'mensagem' => 'Somente propostas APROVADAS podem ser enviadas.',
            ];
        }

        $dataSubmissao = $this->normalizarDataHora($payload['data_submissao'] ?? null);
        if ($dataSubmissao === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Data de submissao invalida.',
            ];
        }

        $valorEnviado = $payload['valor_enviado'] ?? null;
        if ($valorEnviado === null || $valorEnviado === '') {
            $valorEnviado = $proposta->valorProposta;
        }

        $submissaoId = $this->submissaoRepository->create([
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'canal' => $this->normalizarCanal((string) ($payload['canal'] ?? 'PORTAL')),
            'protocolo' => $payload['protocolo'] ?? null,
            'data_submissao' => $dataSubmissao,
            'valor_enviado' => $valorEnviado,
            'link_comprovante' => $payload['link_comprovante'] ?? null,
            'observacao' => $payload['observacao'] ?? null,
        ]);

        $this->propostaRepository->updateStatus($propostaId, $empresaId, 'ENVIADA', $usuarioId);

        $this->logService->info('propostas.submissao.registrar', 'Submissao registrada.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'proposta_id' => $propostaId,
            'submissao_id' => $submissaoId,
        ]);

        $this->auditService->record(
            'PROPOSTA_SUBMISSAO_REGISTRADA',
            'proposta_submissoes',
            $submissaoId,
            [
                'proposta_id' => $propostaId,
                'status_proposta_novo' => 'ENVIADA',
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Submissao registrada com sucesso. Proposta movida para ENVIADA.',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function registrarResultado(int $empresaId, ?int $usuarioId, int $propostaId, array $payload): array
    {
        $proposta = $this->propostaRepository->findByIdAndEmpresa($propostaId, $empresaId);
        if ($proposta === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Proposta nao encontrada.',
            ];
        }

        if (strtoupper($proposta->status) !== 'ENVIADA') {
            return [
                'sucesso' => false,
                'mensagem' => 'Resultado so pode ser registrado para proposta ENVIADA.',
            ];
        }

        $situacao = $this->normalizarSituacaoResultado((string) ($payload['situacao'] ?? ''));
        if ($situacao === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Situacao de resultado invalida.',
            ];
        }

        $dataResultado = $this->normalizarDataHora($payload['data_resultado'] ?? null);
        if ($dataResultado === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Data do resultado invalida.',
            ];
        }

        $resultadoId = $this->resultadoRepository->create([
            'proposta_id' => $propostaId,
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'situacao' => $situacao,
            'data_resultado' => $dataResultado,
            'valor_homologado' => $payload['valor_homologado'] ?? null,
            'colocacao' => $payload['colocacao'] ?? null,
            'motivo' => $payload['motivo'] ?? null,
            'link_ata' => $payload['link_ata'] ?? null,
            'observacao' => $payload['observacao'] ?? null,
        ]);

        $pipelineEncerrado = false;
        $favorito = $this->favoritoRepository->findByIdAndEmpresa($proposta->favoritoId, $empresaId);
        if (
            $situacao !== 'EM_JULGAMENTO'
            && $favorito !== null
            && strtoupper((string) $favorito->statusAcompanhamento) !== 'ENCERRADO'
        ) {
            $observacao = trim((string) ($favorito->observacao ?? ''));
            $resumoResultado = '[Resultado proposta] ' . $situacao . ' em ' . $this->normalizarDataHumana($dataResultado) . '.';
            $novaObservacao = $observacao !== '' ? ($observacao . PHP_EOL . $resumoResultado) : $resumoResultado;

            $this->favoritoRepository->updateStatusAndObservacao(
                $proposta->favoritoId,
                $empresaId,
                'ENCERRADO',
                $novaObservacao
            );
            $pipelineEncerrado = true;

            try {
                $this->favoritoHistoricoRepository->registrarTransicao(
                    $proposta->favoritoId,
                    $empresaId,
                    $favorito->statusAcompanhamento,
                    'ENCERRADO',
                    $usuarioId,
                    'proposta_resultado'
                );
            } catch (\Throwable) {
                // Nao bloqueia o fluxo de resultado caso o historico de pipeline falhe.
            }
        }

        $this->logService->info('propostas.resultado.registrar', 'Resultado de proposta registrado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'proposta_id' => $propostaId,
            'resultado_id' => $resultadoId,
            'situacao' => $situacao,
        ]);

        $this->auditService->record(
            'PROPOSTA_RESULTADO_REGISTRADO',
            'proposta_resultados',
            $resultadoId,
            [
                'proposta_id' => $propostaId,
                'situacao' => $situacao,
                'favorito_id' => $proposta->favoritoId,
            ],
            $empresaId,
            $usuarioId
        );

        $mensagem = $situacao === 'EM_JULGAMENTO'
            ? 'Resultado parcial registrado. Pipeline mantido para acompanhamento.'
            : ($pipelineEncerrado
                ? 'Resultado final registrado e item do pipeline movido para ENCERRADO.'
                : 'Resultado final registrado com sucesso.');

        return [
            'sucesso' => true,
            'mensagem' => $mensagem,
        ];
    }

    /**
     * @return array<string, int|float>
     */
    public function resumoPorStatus(int $empresaId): array
    {
        $grouped = $this->propostaRepository->countByEmpresaGroupedStatus($empresaId);
        $resultados = $this->resultadoRepository->countByEmpresaGroupedSituacao($empresaId);
        $enviadas = $grouped['ENVIADA'] ?? 0;
        $vencedoras = $resultados['VENCEDORA'] ?? 0;

        return [
            'RASCUNHO' => $grouped['RASCUNHO'] ?? 0,
            'EM_REVISAO' => $grouped['EM_REVISAO'] ?? 0,
            'APROVADA' => $grouped['APROVADA'] ?? 0,
            'ENVIADA' => $grouped['ENVIADA'] ?? 0,
            'RESULTADOS_TOTAL' => array_sum($resultados),
            'RESULTADO_EM_JULGAMENTO' => $resultados['EM_JULGAMENTO'] ?? 0,
            'RESULTADO_VENCEDORA' => $vencedoras,
            'RESULTADO_NAO_VENCEDORA' => $resultados['NAO_VENCEDORA'] ?? 0,
            'RESULTADO_DESCLASSIFICADA' => $resultados['DESCLASSIFICADA'] ?? 0,
            'RESULTADO_ANULADA' => $resultados['ANULADA'] ?? 0,
            'TAXA_SUCESSO_ENVIADAS' => $enviadas > 0 ? round(($vencedoras / $enviadas) * 100, 1) : 0.0,
            'TOTAL' => array_sum($grouped),
        ];
    }

    private function tituloProposta(?string $numeroEdital, ?string $orgaoNome): string
    {
        $numero = trim((string) ($numeroEdital ?? ''));
        $orgao = trim((string) ($orgaoNome ?? ''));

        if ($numero === '' && $orgao === '') {
            return 'Proposta Tecnica e Comercial';
        }

        if ($numero !== '' && $orgao !== '') {
            return 'Proposta Tecnica e Comercial - Edital ' . $numero . ' - ' . $orgao;
        }

        return 'Proposta Tecnica e Comercial - ' . ($numero !== '' ? $numero : $orgao);
    }

    private function gerarResumoExecutivo(object $favorito, float $score): string
    {
        $orgao = trim((string) ($favorito->editalOrgaoNome ?? 'orgao contratante'));
        $numero = trim((string) ($favorito->editalNumero ?? 'sem numero'));
        $modalidade = trim((string) ($favorito->editalModalidade ?? 'licitacao'));
        $encerramento = trim((string) ($favorito->editalDataEncerramento ?? ''));
        $prazo = $encerramento !== '' ? $encerramento : 'data de encerramento nao informada';

        return 'Esta proposta foi preparada para o edital ' . $numero
            . ' do ' . $orgao
            . ', modalidade ' . $modalidade
            . '. O score atual de aderencia e ' . number_format($score, 2, ',', '.')
            . ', indicando potencial competitivo para submissao. Prazo de referencia: ' . $prazo . '.';
    }

    private function gerarEstrategia(float $score, ?string $dataEncerramento): string
    {
        $foco = 'foco em aderencia documental, planejamento de escopo e validacao comercial.';
        if ($score >= 80) {
            $foco = 'foco em velocidade de execucao, robustez tecnica e prova de capacidade imediata.';
        } elseif ($score >= 55) {
            $foco = 'foco em consolidacao de evidencias tecnicas e margem de proposta competitiva.';
        } elseif ($score < 30) {
            $foco = 'foco em mitigar riscos de baixa aderencia e decidir go/no-go com criterio financeiro.';
        }

        $data = $this->normalizarDataHumana($dataEncerramento);

        return 'Estrategia sugerida: ' . $foco
            . ' Estruturar revisoes internas em marcos curtos ate ' . $data
            . ' e definir responsaveis por cada bloco da submissao.';
    }

    private function gerarEscopo(object $favorito): string
    {
        $numero = trim((string) ($favorito->editalNumero ?? ''));
        $orgao = trim((string) ($favorito->editalOrgaoNome ?? ''));
        $uf = trim((string) ($favorito->editalUf ?? ''));

        return 'Escopo preliminar da proposta:' . PHP_EOL
            . '- Atendimento integral aos requisitos do edital ' . ($numero !== '' ? $numero : 'referencia nao informada') . '.' . PHP_EOL
            . '- Estrutura de execucao adequada ao orgao ' . ($orgao !== '' ? $orgao : 'contratante') . '.' . PHP_EOL
            . '- Planejamento de entrega e suporte para a UF ' . ($uf !== '' ? $uf : 'nao informada') . '.';
    }

    private function gerarDiferenciais(float $score): string
    {
        $base = 'Diferenciais propostos:' . PHP_EOL
            . '- Governanca de projeto com checkpoints formais.' . PHP_EOL
            . '- Rastreabilidade documental e plano de conformidade.' . PHP_EOL
            . '- Equipe alocada com responsaveis definidos por etapa.';

        if ($score >= 55) {
            $base .= PHP_EOL . '- Estrategia comercial alinhada ao nivel de aderencia identificado pelo motor.';
        }

        return $base;
    }

    /**
     * @param array<int, object> $tarefas
     */
    private function gerarCronograma(array $tarefas, ?string $dataEncerramento): string
    {
        if ($tarefas === []) {
            return 'Cronograma macro sugerido:' . PHP_EOL
                . '- D+1: alinhamento tecnico e documental.' . PHP_EOL
                . '- D+3: consolidacao de proposta tecnica.' . PHP_EOL
                . '- D+5: revisao final e submissao ate ' . $this->normalizarDataHumana($dataEncerramento) . '.';
        }

        $linhas = ['Cronograma macro baseado no checklist:'];
        foreach ($tarefas as $tarefa) {
            $titulo = trim((string) ($tarefa->titulo ?? 'Etapa'));
            $data = trim((string) ($tarefa->dataLimite ?? 'sem data'));
            $linhas[] = '- ' . $titulo . ' (prazo: ' . $data . ')';
        }

        return implode(PHP_EOL, $linhas);
    }

    /**
     * @param array<int, object> $tarefas
     */
    private function gerarRiscoPrincipal(array $tarefas, ?string $dataEncerramento): string
    {
        foreach ($tarefas as $tarefa) {
            $status = strtoupper(trim((string) ($tarefa->status ?? '')));
            if ($status === 'BLOQUEADA') {
                return 'Risco principal identificado: existem tarefas bloqueadas no checklist. Priorizar desbloqueio imediato para manter a data alvo de ' . $this->normalizarDataHumana($dataEncerramento) . '.';
            }
        }

        return 'Risco principal monitorado: compressao de prazo ate ' . $this->normalizarDataHumana($dataEncerramento) . '. Mitigar com revisoes antecipadas e controle de pendencias documentais.';
    }

    private function normalizarDataHumana(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return 'data nao informada';
        }

        $raw = trim($value);
        $formatos = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
        foreach ($formatos as $formato) {
            $date = DateTimeImmutable::createFromFormat($formato, $raw);
            if ($date instanceof DateTimeImmutable) {
                return $date->format('d/m/Y');
            }
        }

        return $raw;
    }

    private function normalizarDataHora(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return (new DateTimeImmutable('now'))->format('Y-m-d H:i:s');
        }

        $raw = trim((string) $value);
        $raw = str_replace('T', ' ', $raw);
        $formatos = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];

        foreach ($formatos as $formato) {
            $date = DateTimeImmutable::createFromFormat($formato, $raw);
            if ($date instanceof DateTimeImmutable) {
                if ($formato === 'Y-m-d') {
                    return $date->format('Y-m-d 00:00:00');
                }

                return $date->format('Y-m-d H:i:s');
            }
        }

        $timestamp = strtotime($raw);
        if ($timestamp === false) {
            return null;
        }

        return (new DateTimeImmutable())->setTimestamp($timestamp)->format('Y-m-d H:i:s');
    }

    private function normalizarCanal(string $canal): string
    {
        $canal = strtoupper(trim($canal));
        if (!in_array($canal, self::CANAIS_SUBMISSAO, true)) {
            return 'PORTAL';
        }

        return $canal;
    }

    private function normalizarSituacaoResultado(string $situacao): ?string
    {
        $situacao = strtoupper(trim($situacao));
        if (!in_array($situacao, self::SITUACOES_RESULTADO, true)) {
            return null;
        }

        return $situacao;
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
        $permitidos = ['atualizado_desc', 'criado_desc', 'valor_desc', 'prazo_asc'];
        return in_array($sort, $permitidos, true) ? $sort : 'atualizado_desc';
    }
}
