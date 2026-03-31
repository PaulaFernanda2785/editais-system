<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Services\AssinaturaService;

class AssinaturaMiddleware
{
    private AssinaturaService $assinaturaService;

    public function __construct(?AssinaturaService $assinaturaService = null)
    {
        $this->assinaturaService = $assinaturaService ?? new AssinaturaService();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        if ($empresaId <= 0) {
            $response->redirect('/login');
            return;
        }

        $status = $this->assinaturaService->verificarAcessoPorEmpresa($empresaId);
        if ($status['permitido'] !== true) {
            $request->setSession('billing.message', $status['mensagem']);
            $response->redirect('/assinatura/status');
            return;
        }

        $assinatura = $status['assinatura'];
        if ($assinatura !== null) {
            $request->setSession('billing.current', $assinatura->toSessionArray());
        }

        $next($request, $response);
    }
}
