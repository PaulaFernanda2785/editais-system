<?php

declare(strict_types=1);

namespace App\Services;

use App\Integrations\PncpIntegration;
use App\Models\ColetaExecucao;
use App\Repositories\ColetaExecucaoRepository;
use App\Repositories\FonteColetaRepository;
use Throwable;

class ColetaService
{
    private FonteColetaRepository $fonteRepository;
    private ColetaExecucaoRepository $coletaRepository;
    private ImportacaoEditalService $importacaoService;
    private PncpIntegration $pncpIntegration;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?FonteColetaRepository $fonteRepository = null,
        ?ColetaExecucaoRepository $coletaRepository = null,
        ?ImportacaoEditalService $importacaoService = null,
        ?PncpIntegration $pncpIntegration = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->fonteRepository = $fonteRepository ?? new FonteColetaRepository();
        $this->coletaRepository = $coletaRepository ?? new ColetaExecucaoRepository();
        $this->importacaoService = $importacaoService ?? new ImportacaoEditalService();
        $this->pncpIntegration = $pncpIntegration ?? new PncpIntegration();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @return array{
     *     fontes: array<int, \App\Models\FonteColeta>,
     *     execucoes: array<int, ColetaExecucao>
     * }
     */
    public function painel(): array
    {
        return [
            'fontes' => $this->fonteRepository->listAllWithResumo(),
            'execucoes' => $this->coletaRepository->listRecentesComFonte(80),
        ];
    }

    public function buscarExecucao(int $execucaoId): ?ColetaExecucao
    {
        return $this->coletaRepository->findByIdComFonte($execucaoId);
    }

    /**
     * @return array<string, mixed>
     */
    public function executarPncp(?int $usuarioId = null, ?int $empresaId = null, int $limit = 50): array
    {
        return $this->executarPorCodigo('PNCP', $limit, $usuarioId, $empresaId);
    }

    /**
     * @return array<string, mixed>
     */
    public function executarComprasGov(?int $usuarioId = null, ?int $empresaId = null, int $limit = 40): array
    {
        return $this->executarFonteSimulada('COMPRAS_GOV', $limit, $usuarioId, $empresaId);
    }

    /**
     * @return array<string, mixed>
     */
    public function executarLicitacoesE(?int $usuarioId = null, ?int $empresaId = null, int $limit = 40): array
    {
        return $this->executarFonteSimulada('LICITACOES_E', $limit, $usuarioId, $empresaId);
    }

    /**
     * @return array<string, mixed>
     */
    private function executarPorCodigo(
        string $codigoFonte,
        int $limit,
        ?int $usuarioId,
        ?int $empresaId
    ): array {
        $fonte = $this->fonteRepository->findByCodigo($codigoFonte);
        if ($fonte === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Fonte ' . $codigoFonte . ' nao cadastrada.',
            ];
        }

        if (!$fonte->ativa) {
            return [
                'sucesso' => false,
                'mensagem' => 'Fonte ' . $codigoFonte . ' esta inativa.',
            ];
        }

        if ($this->coletaRepository->existeExecucaoProcessandoPorFonte($fonte->id)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Ja existe uma coleta PROCESSANDO para esta fonte.',
            ];
        }

        $limit = $this->sanitizeLimit($limit);
        $execucao = $this->coletaRepository->iniciar($fonte->id);

        try {
            $registros = $this->pncpIntegration->fetch([
                'url' => (string) ($fonte->urlBase ?? ''),
                'limit' => $limit,
                'config' => $fonte->configuracao ?? [],
            ]);

            $resumo = $this->importacaoService->importar($fonte->id, $registros);
            $this->coletaRepository->finalizar($execucao->id, $resumo);
            $this->fonteRepository->touchUltimaExecucao($fonte->id);

            $this->registrarOperacao(
                'COLETA_EXECUTADA_MANUAL',
                $fonte->id,
                $execucao->id,
                $resumo,
                $usuarioId,
                $empresaId
            );

            return [
                'sucesso' => $resumo['status'] !== 'ERRO',
                'mensagem' => 'Coleta da fonte ' . $codigoFonte . ' finalizada com status ' . $resumo['status'] . '.',
                'execucao_id' => $execucao->id,
                'resumo' => $resumo,
            ];
        } catch (Throwable $exception) {
            $erroResumo = [
                'status' => 'ERRO',
                'total_lidos' => 0,
                'total_inseridos' => 0,
                'total_atualizados' => 0,
                'total_duplicados' => 0,
                'total_erros' => 1,
                'mensagem_resumo' => 'Falha inesperada na coleta: ' . $exception->getMessage(),
                'log_detalhado' => $exception->getTraceAsString(),
            ];

            $this->coletaRepository->finalizar($execucao->id, $erroResumo);
            $this->logService->error('coleta.execucao', 'Erro na coleta manual.', [
                'fonte_id' => $fonte->id,
                'execucao_id' => $execucao->id,
                'exception' => $exception->getMessage(),
            ]);

            return [
                'sucesso' => false,
                'mensagem' => 'Falha ao executar coleta: ' . $exception->getMessage(),
                'execucao_id' => $execucao->id,
            ];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function executarFonteSimulada(
        string $codigoFonte,
        int $limit,
        ?int $usuarioId,
        ?int $empresaId
    ): array {
        $fonte = $this->fonteRepository->findByCodigo($codigoFonte);
        if ($fonte === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Fonte ' . $codigoFonte . ' nao cadastrada.',
            ];
        }

        if (!$fonte->ativa) {
            return [
                'sucesso' => false,
                'mensagem' => 'Fonte ' . $codigoFonte . ' esta inativa. Ative a fonte para executar.',
            ];
        }

        if ($this->coletaRepository->existeExecucaoProcessandoPorFonte($fonte->id)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Ja existe uma coleta PROCESSANDO para esta fonte.',
            ];
        }

        $execucao = $this->coletaRepository->iniciar($fonte->id);
        $limit = $this->sanitizeLimit($limit);

        try {
            $registros = $this->gerarRegistrosSimulados($codigoFonte, $limit);
            $resumo = $this->importacaoService->importar($fonte->id, $registros);
            $this->coletaRepository->finalizar($execucao->id, $resumo);
            $this->fonteRepository->touchUltimaExecucao($fonte->id);

            $this->registrarOperacao(
                'COLETA_SIMULADA_EXECUTADA',
                $fonte->id,
                $execucao->id,
                $resumo,
                $usuarioId,
                $empresaId
            );

            return [
                'sucesso' => true,
                'mensagem' => 'Coleta simulada da fonte ' . $codigoFonte . ' concluida.',
                'execucao_id' => $execucao->id,
                'resumo' => $resumo,
            ];
        } catch (Throwable $exception) {
            $erroResumo = [
                'status' => 'ERRO',
                'total_lidos' => 0,
                'total_inseridos' => 0,
                'total_atualizados' => 0,
                'total_duplicados' => 0,
                'total_erros' => 1,
                'mensagem_resumo' => 'Falha na coleta simulada: ' . $exception->getMessage(),
                'log_detalhado' => $exception->getTraceAsString(),
            ];
            $this->coletaRepository->finalizar($execucao->id, $erroResumo);

            return [
                'sucesso' => false,
                'mensagem' => 'Falha na coleta simulada: ' . $exception->getMessage(),
                'execucao_id' => $execucao->id,
            ];
        }
    }

    private function sanitizeLimit(int $limit): int
    {
        if ($limit < 1) {
            return 30;
        }

        if ($limit > 500) {
            return 500;
        }

        return $limit;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function gerarRegistrosSimulados(string $codigoFonte, int $limit): array
    {
        $records = [];
        $today = date('Y-m-d');

        for ($i = 1; $i <= $limit; $i++) {
            $seq = str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $records[] = [
                'codigo_fonte' => $codigoFonte . '-MOCK-' . $today . '-' . $seq,
                'numero_edital' => $codigoFonte . '/' . date('Y') . '/' . $seq,
                'orgao_nome' => 'Orgao Capturado ' . $codigoFonte . ' ' . $i,
                'orgao_poder' => 'EXECUTIVO',
                'esfera' => $i % 2 === 0 ? 'MUNICIPAL' : 'ESTADUAL',
                'uf' => $i % 2 === 0 ? 'CE' : 'RJ',
                'municipio' => $i % 2 === 0 ? 'Fortaleza' : 'Rio de Janeiro',
                'modalidade' => 'PREGAO ELETRONICO',
                'modo_disputa' => 'ABERTO',
                'objeto' => 'Contratacao de servicos para ' . strtolower(str_replace('_', ' ', $codigoFonte)) . ' lote ' . $i,
                'descricao_resumida' => 'Registro simulado para validacao operacional do modulo de coleta.',
                'valor_estimado' => 15000 + ($i * 1400),
                'data_publicacao' => $today,
                'data_abertura' => date('Y-m-d 08:30:00', strtotime('+' . ($i % 4) . ' day')),
                'data_encerramento' => date('Y-m-d 18:00:00', strtotime('+' . (($i % 4) + 6) . ' day')),
                'situacao' => 'PUBLICADO',
                'link_detalhe' => 'https://www.google.com/search?q=' . rawurlencode(
                    $codigoFonte . ' ' . date('Y') . ' ' . $seq . ' detalhes edital'
                ),
                'link_edital' => 'https://www.google.com/search?q=' . rawurlencode(
                    $codigoFonte . ' ' . date('Y') . ' ' . $seq . ' arquivo edital pdf'
                ),
            ];
        }

        return $records;
    }

    /**
     * @param array<string, mixed> $resumo
     */
    private function registrarOperacao(
        string $acao,
        int $fonteId,
        int $execucaoId,
        array $resumo,
        ?int $usuarioId,
        ?int $empresaId
    ): void {
        $this->logService->info('coleta.execucao', 'Execucao de coleta finalizada.', [
            'acao' => $acao,
            'fonte_id' => $fonteId,
            'execucao_id' => $execucaoId,
            'status' => $resumo['status'] ?? 'N/A',
            'total_lidos' => $resumo['total_lidos'] ?? 0,
            'total_inseridos' => $resumo['total_inseridos'] ?? 0,
            'total_atualizados' => $resumo['total_atualizados'] ?? 0,
            'total_duplicados' => $resumo['total_duplicados'] ?? 0,
            'total_erros' => $resumo['total_erros'] ?? 0,
            'usuario_id' => $usuarioId,
        ]);

        $this->auditService->record(
            $acao,
            'coletas_execucao',
            $execucaoId,
            [
                'fonte_id' => $fonteId,
                'status' => $resumo['status'] ?? null,
                'resumo' => $resumo['mensagem_resumo'] ?? null,
            ],
            $empresaId,
            $usuarioId
        );
    }
}
