<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Request;
use App\Models\Empresa;
use App\Repositories\EmpresaRepository;

class TenantService
{
    private EmpresaRepository $empresaRepository;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?EmpresaRepository $empresaRepository = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->empresaRepository = $empresaRepository ?? new EmpresaRepository();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    public function resolveEmpresaAtiva(int $empresaId, ?int $usuarioId = null): ?Empresa
    {
        $empresa = $this->empresaRepository->findById($empresaId);

        if ($empresa === null) {
            $this->logService->warning('tenant.resolve', 'Empresa nao encontrada para sessao autenticada.', [
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
            ]);

            return null;
        }

        if (!$empresa->isAtiva()) {
            $this->logService->warning('tenant.resolve', 'Empresa inativa/suspensa bloqueada no acesso.', [
                'empresa_id' => $empresa->id,
                'usuario_id' => $usuarioId,
                'status_empresa' => $empresa->status,
            ]);

            $this->auditService->record(
                'EMPRESA_SEM_ACESSO',
                'empresas',
                $empresa->id,
                ['status' => $empresa->status],
                $empresa->id,
                $usuarioId
            );

            return null;
        }

        return $empresa;
    }

    public function sincronizarSessaoTenant(Request $request, Empresa $empresa): void
    {
        $request->setSession('tenant.id', $empresa->id);
        $request->setSession('tenant.empresa', $empresa->toSessionArray());
    }
}
