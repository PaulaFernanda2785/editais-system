<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CorrespondenciaService;

class CorrespondenciaController extends Controller
{
    private CorrespondenciaService $correspondenciaService;

    public function __construct(?CorrespondenciaService $correspondenciaService = null)
    {
        $this->correspondenciaService = $correspondenciaService ?? new CorrespondenciaService();
    }

    public function index(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $resultado = $this->correspondenciaService->listarOportunidades($empresaId, $request->input());

        $response->view('correspondencias/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'items' => $resultado['items'],
            'total' => $resultado['total'],
            'page' => $resultado['page'],
            'perPage' => $resultado['per_page'],
            'totalPages' => $resultado['total_pages'],
            'sort' => $resultado['sort'],
            'filters' => $resultado['filters'],
            'perfis' => $resultado['perfis'],
            'message' => $request->pullSession('correspondencias.message', null),
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $id = (int) $request->routeParam('id', 0);
        $detalhe = $this->correspondenciaService->detalhar($empresaId, $id);

        if ($detalhe === null) {
            $request->setSession('correspondencias.message', 'Oportunidade nao encontrada.');
            $response->redirect('/oportunidades');
            return;
        }

        $response->view('correspondencias/show', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'correspondencia' => $detalhe['correspondencia'],
            'message' => $request->pullSession('correspondencias.message', null),
        ]);
    }

    public function processar(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $limite = (int) $request->input('limite_editais', 800);

        $result = $this->correspondenciaService->processarEmpresa(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $limite
        );

        $request->setSession('correspondencias.message', $result['mensagem'] ?? null);
        $response->redirect('/oportunidades');
    }
}

