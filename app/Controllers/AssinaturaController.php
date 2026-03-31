<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AssinaturaService;

class AssinaturaController extends Controller
{
    private AssinaturaService $assinaturaService;

    public function __construct(?AssinaturaService $assinaturaService = null)
    {
        $this->assinaturaService = $assinaturaService ?? new AssinaturaService();
    }

    public function status(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $assinatura = $empresaId > 0
            ? $this->assinaturaService->assinaturaMaisRecente($empresaId)
            : null;

        $response->view('assinatura/status', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'assinatura' => $assinatura,
            'message' => $request->pullSession('billing.message', null),
        ]);
    }

    public function ativarTeste(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);

        if ($empresaId <= 0) {
            $request->invalidateSession();
            $response->redirect('/login');
            return;
        }

        $resultado = $this->assinaturaService->ativarPeriodoTeste(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null
        );

        $request->setSession('billing.message', $resultado['mensagem'] ?? null);
        $response->redirect('/assinatura/status');
    }
}
