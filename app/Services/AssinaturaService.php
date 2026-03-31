<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Assinatura;
use App\Repositories\AssinaturaRepository;
use App\Repositories\PlanoRepository;

class AssinaturaService
{
    private AssinaturaRepository $assinaturaRepository;
    private PlanoRepository $planoRepository;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?AssinaturaRepository $assinaturaRepository = null,
        ?PlanoRepository $planoRepository = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->assinaturaRepository = $assinaturaRepository ?? new AssinaturaRepository();
        $this->planoRepository = $planoRepository ?? new PlanoRepository();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    public function verificarAcessoPorEmpresa(int $empresaId): array
    {
        $assinatura = $this->assinaturaRepository->findParaAcessoByEmpresa($empresaId);
        if ($assinatura === null) {
            return [
                'permitido' => false,
                'mensagem' => 'Empresa sem assinatura ativa. Ative um periodo de teste ou contrate um plano.',
                'assinatura' => null,
            ];
        }

        if (!$assinatura->ativaParaAcesso()) {
            return [
                'permitido' => false,
                'mensagem' => 'Assinatura encontrada, mas fora do periodo de validade.',
                'assinatura' => $assinatura,
            ];
        }

        return [
            'permitido' => true,
            'mensagem' => null,
            'assinatura' => $assinatura,
        ];
    }

    public function assinaturaMaisRecente(int $empresaId): ?Assinatura
    {
        return $this->assinaturaRepository->findMaisRecenteByEmpresa($empresaId);
    }

    public function ativarPeriodoTeste(int $empresaId, ?int $usuarioId = null): array
    {
        $existente = $this->assinaturaRepository->findParaAcessoByEmpresa($empresaId);
        if ($existente !== null && $existente->ativaParaAcesso()) {
            return [
                'sucesso' => false,
                'mensagem' => 'Ja existe uma assinatura ativa para esta empresa.',
                'assinatura' => $existente,
            ];
        }

        $plano = $this->planoRepository->findAtivoByNome('BASICO') ?? $this->planoRepository->findPrimeiroAtivo();
        if ($plano === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Nenhum plano ativo encontrado para vincular o periodo de teste.',
                'assinatura' => null,
            ];
        }

        $dias = (int) ($_ENV['BILLING_TRIAL_DAYS'] ?? 15);
        if ($dias <= 0) {
            $dias = 15;
        }

        $assinatura = $this->assinaturaRepository->criarTeste(
            $empresaId,
            $plano->id,
            $dias,
            'MERCADO_PAGO_TESTE_LOCAL',
            'Periodo de teste inicial liberado automaticamente.'
        );

        $this->logService->info('billing.trial', 'Periodo de teste criado com sucesso.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'assinatura_id' => $assinatura->id,
            'plano_id' => $plano->id,
            'dias' => $dias,
        ]);

        $this->auditService->record(
            'ASSINATURA_TESTE_CRIADA',
            'assinaturas',
            $assinatura->id,
            [
                'plano_id' => $plano->id,
                'dias' => $dias,
                'status' => $assinatura->status,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Periodo de teste ativado com sucesso.',
            'assinatura' => $assinatura,
        ];
    }
}
