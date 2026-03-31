<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Services\TenantService;

class TenantMiddleware
{
    private TenantService $tenantService;

    public function __construct(?TenantService $tenantService = null)
    {
        $this->tenantService = $tenantService ?? new TenantService();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);

        if ($empresaId <= 0) {
            $request->invalidateSession();
            $response->redirect('/login');
            return;
        }

        $empresa = $this->tenantService->resolveEmpresaAtiva($empresaId, $usuarioId > 0 ? $usuarioId : null);
        if ($empresa === null) {
            $request->invalidateSession();
            $response->redirect('/login');
            return;
        }

        $this->tenantService->sincronizarSessaoTenant($request, $empresa);
        $next($request, $response);
    }
}
