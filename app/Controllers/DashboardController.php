<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AssinaturaService;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    private AssinaturaService $assinaturaService;
    private DashboardService $dashboardService;

    public function __construct(
        ?AssinaturaService $assinaturaService = null,
        ?DashboardService $dashboardService = null
    )
    {
        $this->assinaturaService = $assinaturaService ?? new AssinaturaService();
        $this->dashboardService = $dashboardService ?? new DashboardService();
    }

    public function index(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $assinatura = $empresaId > 0
            ? $this->assinaturaService->assinaturaMaisRecente($empresaId)
            : null;
        $resumo = $empresaId > 0
            ? $this->dashboardService->resumoEmpresa($empresaId)
            : null;

        $response->view('dashboard/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'assinatura' => $assinatura,
            'resumo' => $resumo,
            'adminMessage' => $request->pullSession('admin.message', null),
        ]);
    }
}
