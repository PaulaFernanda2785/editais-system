<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\FavoritoRepository;
use App\Repositories\FavoritoTarefaRepository;
use App\Repositories\PropostaExecucaoRepository;
use DateTimeImmutable;

class PropostaExecucaoService
{
    private PropostaExecucaoRepository $propostaRepository;
    private FavoritoRepository $favoritoRepository;
    private FavoritoTarefaRepository $tarefaRepository;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?PropostaExecucaoRepository $propostaRepository = null,
        ?FavoritoRepository $favoritoRepository = null,
        ?FavoritoTarefaRepository $tarefaRepository = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->propostaRepository = $propostaRepository ?? new PropostaExecucaoRepository();
        $this->favoritoRepository = $favoritoRepository ?? new FavoritoRepository();
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
        $resultado['status_permitidos'] = ['RASCUNHO', 'EM_REVISAO', 'APROVADA', 'ENVIADA'];

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

        return [
            'proposta' => $proposta,
            'favorito' => $favorito,
            'tarefas' => $tarefas,
            'status_permitidos' => ['RASCUNHO', 'EM_REVISAO', 'APROVADA', 'ENVIADA'],
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

        $status = strtoupper(trim((string) ($payload['status'] ?? 'RASCUNHO')));
        if (!in_array($status, ['RASCUNHO', 'EM_REVISAO', 'APROVADA', 'ENVIADA'], true)) {
            $status = 'RASCUNHO';
        }

        $this->propostaRepository->updateById($propostaId, $empresaId, [
            'status' => $status,
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
            'status' => $status,
        ]);

        $this->auditService->record(
            'PROPOSTA_ATUALIZADA',
            'propostas_execucao',
            $propostaId,
            ['status' => $status],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Proposta atualizada com sucesso.',
        ];
    }

    /**
     * @return array<string, int>
     */
    public function resumoPorStatus(int $empresaId): array
    {
        $grouped = $this->propostaRepository->countByEmpresaGroupedStatus($empresaId);
        return [
            'RASCUNHO' => $grouped['RASCUNHO'] ?? 0,
            'EM_REVISAO' => $grouped['EM_REVISAO'] ?? 0,
            'APROVADA' => $grouped['APROVADA'] ?? 0,
            'ENVIADA' => $grouped['ENVIADA'] ?? 0,
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
