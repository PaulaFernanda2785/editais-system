<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\FonteColeta;
use App\Repositories\ColetaExecucaoRepository;
use App\Repositories\FonteColetaRepository;

class FonteColetaService
{
    private FonteColetaRepository $fonteRepository;
    private ColetaExecucaoRepository $coletaExecucaoRepository;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?FonteColetaRepository $fonteRepository = null,
        ?ColetaExecucaoRepository $coletaExecucaoRepository = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->fonteRepository = $fonteRepository ?? new FonteColetaRepository();
        $this->coletaExecucaoRepository = $coletaExecucaoRepository ?? new ColetaExecucaoRepository();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @return array<int, FonteColeta>
     */
    public function listar(): array
    {
        return $this->fonteRepository->listAllWithResumo();
    }

    public function obter(int $id): ?FonteColeta
    {
        return $this->fonteRepository->findById($id);
    }

    /**
     * @return array<int, \App\Models\ColetaExecucao>
     */
    public function listarHistoricoExecucoes(int $fonteId, int $limit = 20): array
    {
        return $this->coletaExecucaoRepository->listRecentesPorFonte($fonteId, $limit);
    }

    public function criar(array $payload, ?int $usuarioId = null, ?int $empresaId = null): array
    {
        $existente = $this->fonteRepository->findByCodigo((string) $payload['codigo']);
        if ($existente !== null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Codigo de fonte ja cadastrado.',
                'fonte' => null,
            ];
        }

        $fonte = $this->fonteRepository->create($payload);

        $this->logService->info('fonte.create', 'Fonte de coleta criada.', [
            'fonte_id' => $fonte->id,
            'codigo' => $fonte->codigo,
            'tipo' => $fonte->tipo,
            'usuario_id' => $usuarioId,
        ]);

        $this->auditService->record(
            'FONTE_COLETA_CRIADA',
            'fontes_coleta',
            $fonte->id,
            [
                'codigo' => $fonte->codigo,
                'tipo' => $fonte->tipo,
                'ativa' => $fonte->ativa ? 1 : 0,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Fonte de coleta criada com sucesso.',
            'fonte' => $fonte,
        ];
    }

    public function atualizar(int $id, array $payload, ?int $usuarioId = null, ?int $empresaId = null): array
    {
        $fonteAtual = $this->fonteRepository->findById($id);
        if ($fonteAtual === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Fonte de coleta nao encontrada.',
                'fonte' => null,
            ];
        }

        $outraFonteMesmoCodigo = $this->fonteRepository->findByCodigo((string) $payload['codigo']);
        if ($outraFonteMesmoCodigo !== null && $outraFonteMesmoCodigo->id !== $id) {
            return [
                'sucesso' => false,
                'mensagem' => 'Codigo de fonte ja em uso por outra fonte.',
                'fonte' => null,
            ];
        }

        $this->fonteRepository->update($id, $payload);
        $fonteAtualizada = $this->fonteRepository->findById($id) ?? $fonteAtual;

        $this->logService->info('fonte.update', 'Fonte de coleta atualizada.', [
            'fonte_id' => $id,
            'codigo' => $fonteAtualizada->codigo,
            'usuario_id' => $usuarioId,
        ]);

        $this->auditService->record(
            'FONTE_COLETA_ATUALIZADA',
            'fontes_coleta',
            $id,
            [
                'codigo_antigo' => $fonteAtual->codigo,
                'codigo_novo' => $fonteAtualizada->codigo,
                'tipo' => $fonteAtualizada->tipo,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Fonte de coleta atualizada com sucesso.',
            'fonte' => $fonteAtualizada,
        ];
    }

    public function alternarAtiva(int $id, ?int $usuarioId = null, ?int $empresaId = null): array
    {
        $fonte = $this->fonteRepository->findById($id);
        if ($fonte === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Fonte de coleta nao encontrada.',
                'fonte' => null,
            ];
        }

        $novoStatus = !$fonte->ativa;
        $this->fonteRepository->setAtiva($id, $novoStatus);
        $atualizada = $this->fonteRepository->findById($id) ?? $fonte;

        $this->logService->warning('fonte.toggle', 'Status da fonte alterado.', [
            'fonte_id' => $id,
            'ativa' => $atualizada->ativa ? 1 : 0,
            'usuario_id' => $usuarioId,
        ]);

        $this->auditService->record(
            'FONTE_COLETA_STATUS_ALTERADO',
            'fontes_coleta',
            $id,
            ['ativa' => $atualizada->ativa ? 1 : 0],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => $atualizada->ativa
                ? 'Fonte ativada com sucesso.'
                : 'Fonte inativada com sucesso.',
            'fonte' => $atualizada,
        ];
    }
}
