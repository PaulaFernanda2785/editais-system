<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AssinaturaService;

class DashboardController extends Controller
{
    private AssinaturaService $assinaturaService;

    public function __construct(?AssinaturaService $assinaturaService = null)
    {
        $this->assinaturaService = $assinaturaService ?? new AssinaturaService();
    }

    public function index(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $assinatura = $empresaId > 0
            ? $this->assinaturaService->assinaturaMaisRecente($empresaId)
            : null;

        $response->view('dashboard/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'assinatura' => $assinatura,
            'adminMessage' => $request->pullSession('admin.message', null),
        ]);
    }
}
