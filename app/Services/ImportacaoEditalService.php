<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EditalRepository;
use Throwable;

class ImportacaoEditalService
{
    private NormalizacaoService $normalizacaoService;
    private DeduplicacaoService $deduplicacaoService;
    private EditalRepository $editalRepository;
    private LogService $logService;

    public function __construct(
        ?NormalizacaoService $normalizacaoService = null,
        ?DeduplicacaoService $deduplicacaoService = null,
        ?EditalRepository $editalRepository = null,
        ?LogService $logService = null
    ) {
        $this->normalizacaoService = $normalizacaoService ?? new NormalizacaoService();
        $this->deduplicacaoService = $deduplicacaoService ?? new DeduplicacaoService();
        $this->editalRepository = $editalRepository ?? new EditalRepository();
        $this->logService = $logService ?? new LogService();
    }

    /**
     * @param array<int, array<string, mixed>> $registrosBrutos
     * @return array<string, mixed>
     */
    public function importar(int $fonteId, array $registrosBrutos): array
    {
        $totalLidos = 0;
        $totalInseridos = 0;
        $totalAtualizados = 0;
        $totalDuplicados = 0;
        $totalErros = 0;
        $erros = [];

        foreach ($registrosBrutos as $index => $raw) {
            $totalLidos++;

            try {
                $normalizado = $this->normalizacaoService->normalizarRegistro($raw, $fonteId);
                if (!$this->validoParaPersistir($normalizado)) {
                    $totalErros++;
                    $erros[] = [
                        'indice' => $index,
                        'erro' => 'Registro invalido para persistencia (campos minimos ausentes).',
                    ];
                    continue;
                }

                $normalizado['hash_unico'] = $this->deduplicacaoService->gerarHash($normalizado);
                $decisao = $this->deduplicacaoService->decidirAcao($normalizado);

                if ($decisao['acao'] === 'INSERIR') {
                    $this->editalRepository->create($normalizado);
                    $totalInseridos++;
                    continue;
                }

                if ($decisao['acao'] === 'ATUALIZAR' && $decisao['edital'] !== null) {
                    $this->editalRepository->updateById($decisao['edital']->id, $normalizado);
                    $totalAtualizados++;
                    continue;
                }

                $totalDuplicados++;
            } catch (Throwable $exception) {
                $totalErros++;
                $erros[] = [
                    'indice' => $index,
                    'erro' => $exception->getMessage(),
                ];
            }
        }

        $status = $this->calcularStatus($totalInseridos, $totalAtualizados, $totalErros);
        $mensagemResumo = sprintf(
            'Lidos: %d | Inseridos: %d | Atualizados: %d | Duplicados: %d | Erros: %d',
            $totalLidos,
            $totalInseridos,
            $totalAtualizados,
            $totalDuplicados,
            $totalErros
        );

        $logDetalhado = [
            'status' => $status,
            'resumo' => $mensagemResumo,
            'erros' => array_slice($erros, 0, 50),
        ];

        $this->logService->info('coleta.importacao', 'Importacao de editais processada.', [
            'fonte_id' => $fonteId,
            'status' => $status,
            'total_lidos' => $totalLidos,
            'total_inseridos' => $totalInseridos,
            'total_atualizados' => $totalAtualizados,
            'total_duplicados' => $totalDuplicados,
            'total_erros' => $totalErros,
        ]);

        return [
            'status' => $status,
            'total_lidos' => $totalLidos,
            'total_inseridos' => $totalInseridos,
            'total_atualizados' => $totalAtualizados,
            'total_duplicados' => $totalDuplicados,
            'total_erros' => $totalErros,
            'mensagem_resumo' => $mensagemResumo,
            'log_detalhado' => json_encode($logDetalhado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];
    }

    /**
     * @param array<string, mixed> $registro
     */
    private function validoParaPersistir(array $registro): bool
    {
        $orgao = trim((string) ($registro['orgao_nome'] ?? ''));
        $objeto = trim((string) ($registro['objeto'] ?? ''));
        return $orgao !== '' && $objeto !== '';
    }

    private function calcularStatus(int $inseridos, int $atualizados, int $erros): string
    {
        if ($erros === 0) {
            return 'SUCESSO';
        }

        if (($inseridos + $atualizados) > 0) {
            return 'PARCIAL';
        }

        return 'ERRO';
    }
}

