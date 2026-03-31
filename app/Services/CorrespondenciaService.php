<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\ExternalLinkHelper;
use App\Models\PalavraChave;
use App\Models\PerfilMonitoramento;
use App\Repositories\CorrespondenciaRepository;
use App\Repositories\EditalRepository;
use App\Repositories\PalavraChaveRepository;
use App\Repositories\PerfilMonitoramentoRepository;

class CorrespondenciaService
{
    private CorrespondenciaRepository $correspondenciaRepository;
    private PerfilMonitoramentoRepository $perfilRepository;
    private PalavraChaveRepository $palavraRepository;
    private EditalRepository $editalRepository;
    private ScoreService $scoreService;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?CorrespondenciaRepository $correspondenciaRepository = null,
        ?PerfilMonitoramentoRepository $perfilRepository = null,
        ?PalavraChaveRepository $palavraRepository = null,
        ?EditalRepository $editalRepository = null,
        ?ScoreService $scoreService = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->correspondenciaRepository = $correspondenciaRepository ?? new CorrespondenciaRepository();
        $this->perfilRepository = $perfilRepository ?? new PerfilMonitoramentoRepository();
        $this->palavraRepository = $palavraRepository ?? new PalavraChaveRepository();
        $this->editalRepository = $editalRepository ?? new EditalRepository();
        $this->scoreService = $scoreService ?? new ScoreService();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, mixed>
     */
    public function listarOportunidades(int $empresaId, array $input = []): array
    {
        $page = $this->sanitizeInt($input['page'] ?? 1, 1, 100000, 1);
        $perPage = $this->sanitizeInt($input['per_page'] ?? 20, 5, 100, 20);
        $sort = $this->normalizarSort((string) ($input['sort'] ?? 'score_desc'));
        $filters = [
            'termo' => trim((string) ($input['termo'] ?? '')),
            'nivel_relevancia' => strtoupper(trim((string) ($input['nivel_relevancia'] ?? ''))),
            'perfil_id' => $this->sanitizeInt($input['perfil_id'] ?? 0, 0, 10000000, 0),
        ];

        $resultado = $this->correspondenciaRepository->listByEmpresa($empresaId, $filters, $page, $perPage, $sort);
        if (isset($resultado['items']) && is_array($resultado['items'])) {
            foreach ($resultado['items'] as $item) {
                $this->resolverLinksCorrespondencia($item);
            }
        }
        $resultado['sort'] = $sort;
        $resultado['filters'] = $filters;
        $resultado['perfis'] = $this->perfilRepository->listByEmpresa($empresaId);

        return $resultado;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detalhar(int $empresaId, int $correspondenciaId): ?array
    {
        $item = $this->correspondenciaRepository->findByIdAndEmpresa($correspondenciaId, $empresaId);
        if ($item === null) {
            return null;
        }

        $this->resolverLinksCorrespondencia($item);

        return [
            'correspondencia' => $item,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function processarEmpresa(
        int $empresaId,
        ?int $usuarioId = null,
        ?int $limiteEditais = null
    ): array {
        $limite = $limiteEditais ?? 800;
        if ($limite < 50) {
            $limite = 50;
        }
        if ($limite > 5000) {
            $limite = 5000;
        }

        $perfisAtivos = $this->buscarPerfisAtivos($empresaId);
        if ($perfisAtivos === []) {
            return [
                'sucesso' => false,
                'mensagem' => 'Nenhum perfil ativo disponivel para processar correspondencias.',
                'resumo' => null,
            ];
        }

        $editais = $this->editalRepository->listRecentes($limite);
        if ($editais === []) {
            return [
                'sucesso' => false,
                'mensagem' => 'Nenhum edital disponivel para processar correspondencias.',
                'resumo' => null,
            ];
        }

        $totalPerfis = count($perfisAtivos);
        $totalEditais = count($editais);
        $analisados = 0;
        $compativeis = 0;
        $geradas = 0;
        $atualizadas = 0;

        foreach ($perfisAtivos as $perfil) {
            $palavras = $this->buscarPalavrasAtivas($empresaId, $perfil->id);
            if ($palavras === []) {
                continue;
            }

            foreach ($editais as $edital) {
                $analisados++;
                $scoreData = $this->scoreService->calcular($edital, $perfil, $palavras);
                if (($scoreData['motivo']['filtros'] ?? '') === 'compativel') {
                    $compativeis++;
                }
                $score = (float) ($scoreData['score'] ?? 0.0);

                if ($score <= 0) {
                    continue;
                }

                $resultado = $this->correspondenciaRepository->upsert(
                    $edital->id,
                    $empresaId,
                    $perfil->id,
                    $score,
                    (string) ($scoreData['nivel_relevancia'] ?? 'BAIXA'),
                    isset($scoreData['motivo']) && is_array($scoreData['motivo']) ? $scoreData['motivo'] : []
                );

                if (($resultado['acao'] ?? '') === 'CRIADA') {
                    $geradas++;
                } else {
                    $atualizadas++;
                }
            }
        }

        $resumo = [
            'total_perfis' => $totalPerfis,
            'total_editais' => $totalEditais,
            'total_analisados' => $analisados,
            'total_compativeis' => $compativeis,
            'total_geradas' => $geradas,
            'total_atualizadas' => $atualizadas,
        ];

        $this->logService->info('correspondencia.processar', 'Processamento de correspondencias concluido.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'resumo' => $resumo,
        ]);

        $this->auditService->record(
            'CORRESPONDENCIAS_PROCESSADAS',
            'correspondencias',
            null,
            $resumo,
            $empresaId,
            $usuarioId
        );

        $mensagem = 'Processamento concluido. Geradas: ' . $geradas . ' | Atualizadas: ' . $atualizadas . '.';
        if (($geradas + $atualizadas) === 0) {
            if ($compativeis === 0) {
                $mensagem = 'Nenhuma oportunidade gerada. Nao houve edital compativel com os filtros de UF/modalidade/orgao/faixa de valor dos perfis.';
            } else {
                $mensagem = 'Nenhuma oportunidade gerada. Editais compativeis foram encontrados, mas sem ocorrencia de palavras-chave ativas.';
            }
        }

        return [
            'sucesso' => true,
            'mensagem' => $mensagem,
            'resumo' => $resumo,
        ];
    }

    /**
     * @return array<int, PerfilMonitoramento>
     */
    private function buscarPerfisAtivos(int $empresaId): array
    {
        $perfis = $this->perfilRepository->listByEmpresa($empresaId);
        return array_values(
            array_filter(
                $perfis,
                static fn(PerfilMonitoramento $perfil): bool => $perfil->ativo
            )
        );
    }

    /**
     * @return array<int, PalavraChave>
     */
    private function buscarPalavrasAtivas(int $empresaId, int $perfilId): array
    {
        $palavras = $this->palavraRepository->listByEmpresaAndPerfil($empresaId, $perfilId);
        return array_values(
            array_filter(
                $palavras,
                static fn(PalavraChave $palavra): bool => $palavra->ativo && trim($palavra->termo) !== ''
            )
        );
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
        $permitidos = ['score_desc', 'score_asc', 'criado_em_asc', 'edital_data_desc', 'edital_data_asc'];
        return in_array($sort, $permitidos, true) ? $sort : 'score_desc';
    }

    private function resolverLinksCorrespondencia(object $item): void
    {
        $item->editalLinkDetalhe = ExternalLinkHelper::resolveForDetail(
            isset($item->editalLinkDetalhe) ? (string) $item->editalLinkDetalhe : null,
            isset($item->editalNumero) ? (string) $item->editalNumero : null,
            isset($item->editalOrgaoNome) ? (string) $item->editalOrgaoNome : null,
            null,
            'detalhe'
        );

        $item->editalLinkEdital = ExternalLinkHelper::resolveForDetail(
            isset($item->editalLinkEdital) ? (string) $item->editalLinkEdital : null,
            isset($item->editalNumero) ? (string) $item->editalNumero : null,
            isset($item->editalOrgaoNome) ? (string) $item->editalOrgaoNome : null,
            null,
            'edital'
        );
    }
}
