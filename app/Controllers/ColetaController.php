<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\ColetaService;

class ColetaController extends Controller
{
    private ColetaService $coletaService;

    public function __construct(?ColetaService $coletaService = null)
    {
        $this->coletaService = $coletaService ?? new ColetaService();
    }

    public function index(Request $request, Response $response): void
    {
        $painel = $this->coletaService->painel();

        $response->view('coletas/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'fontes' => $painel['fontes'],
            'execucoes' => $painel['execucoes'],
            'message' => $request->pullSession('coleta.message', null),
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $execucaoId = (int) $request->routeParam('id', 0);
        $execucao = $this->coletaService->buscarExecucao($execucaoId);

        if ($execucao === null) {
            $request->setSession('coleta.message', 'Execucao de coleta nao encontrada.');
            $response->redirect('/admin/coletas');
            return;
        }

        $response->view('coletas/show', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'execucao' => $execucao,
            'message' => $request->pullSession('coleta.message', null),
        ]);
    }

    public function executarPncp(Request $request, Response $response): void
    {
        $limit = (int) $request->input('limite', 50);
        $result = $this->coletaService->executarPncp(
            (int) $request->session('auth.user_id', 0),
            (int) $request->session('auth.empresa_id', 0),
            $limit
        );

        $request->setSession('coleta.message', $result['mensagem'] ?? null);
        if (isset($result['execucao_id'])) {
            $response->redirect('/admin/coletas/' . (int) $result['execucao_id']);
            return;
        }

        $response->redirect('/admin/coletas');
    }

    public function executarComprasGov(Request $request, Response $response): void
    {
        $limit = (int) $request->input('limite', 40);
        $result = $this->coletaService->executarComprasGov(
            (int) $request->session('auth.user_id', 0),
            (int) $request->session('auth.empresa_id', 0),
            $limit
        );

        $request->setSession('coleta.message', $result['mensagem'] ?? null);
        if (isset($result['execucao_id'])) {
            $response->redirect('/admin/coletas/' . (int) $result['execucao_id']);
            return;
        }

        $response->redirect('/admin/coletas');
    }

    public function executarLicitacoesE(Request $request, Response $response): void
    {
        $limit = (int) $request->input('limite', 40);
        $result = $this->coletaService->executarLicitacoesE(
            (int) $request->session('auth.user_id', 0),
            (int) $request->session('auth.empresa_id', 0),
            $limit
        );

        $request->setSession('coleta.message', $result['mensagem'] ?? null);
        if (isset($result['execucao_id'])) {
            $response->redirect('/admin/coletas/' . (int) $result['execucao_id']);
            return;
        }

        $response->redirect('/admin/coletas');
    }
}

