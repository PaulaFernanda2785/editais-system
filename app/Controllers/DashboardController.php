<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\LogService;
use App\Services\PropostaAlertaNotificacaoService;
use App\Services\AssinaturaService;
use App\Services\DashboardService;
use Throwable;

class DashboardController extends Controller
{
    private AssinaturaService $assinaturaService;
    private DashboardService $dashboardService;
    private PropostaAlertaNotificacaoService $alertaNotificacaoService;
    private LogService $logService;

    public function __construct(
        ?AssinaturaService $assinaturaService = null,
        ?DashboardService $dashboardService = null,
        ?PropostaAlertaNotificacaoService $alertaNotificacaoService = null,
        ?LogService $logService = null
    )
    {
        $this->assinaturaService = $assinaturaService ?? new AssinaturaService();
        $this->dashboardService = $dashboardService ?? new DashboardService();
        $this->alertaNotificacaoService = $alertaNotificacaoService ?? new PropostaAlertaNotificacaoService();
        $this->logService = $logService ?? new LogService();
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
        $notificacoesPropostas = $empresaId > 0
            ? $this->processarCarregarNotificacoes($empresaId)
            : ['items' => [], 'novos' => 0, 'total_ativos' => 0];

        $response->view('dashboard/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'assinatura' => $assinatura,
            'resumo' => $resumo,
            'notificacoesPropostas' => $notificacoesPropostas,
            'alertasMessage' => $request->pullSession('alertas.message', null),
            'adminMessage' => $request->pullSession('admin.message', null),
        ]);
    }

    public function marcarAlertasVistos(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        if ($empresaId <= 0) {
            $request->setSession('alertas.message', 'Empresa nao identificada para atualizar alertas.');
            $response->redirect('/dashboard');
            return;
        }

        $count = $this->alertaNotificacaoService->marcarNovosComoVisualizados($empresaId);
        $request->setSession(
            'alertas.message',
            $count > 0
                ? $count . ' alerta(s) marcado(s) como visualizado(s).'
                : 'Nenhum alerta novo pendente para marcar.'
        );

        $response->redirect('/dashboard');
    }

    /**
     * @return array<string, mixed>
     */
    private function processarCarregarNotificacoes(int $empresaId): array
    {
        try {
            $this->alertaNotificacaoService->processarEmpresa($empresaId, true);
            return $this->alertaNotificacaoService->obterParaDashboard($empresaId, 8);
        } catch (Throwable $exception) {
            $this->logService->warning('dashboard.alertas', 'Falha ao processar notificacoes proativas.', [
                'empresa_id' => $empresaId,
                'exception' => $exception->getMessage(),
            ]);

            return [
                'items' => [],
                'novos' => 0,
                'total_ativos' => 0,
            ];
        }
    }
}
