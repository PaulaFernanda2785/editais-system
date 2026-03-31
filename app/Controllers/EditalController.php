<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\BuscaEditalService;
use App\Services\EditalService;

class EditalController extends Controller
{
    private BuscaEditalService $buscaService;
    private EditalService $editalService;

    public function __construct(
        ?BuscaEditalService $buscaService = null,
        ?EditalService $editalService = null
    ) {
        $this->buscaService = $buscaService ?? new BuscaEditalService();
        $this->editalService = $editalService ?? new EditalService();
    }

    public function index(Request $request, Response $response): void
    {
        $resultado = $this->buscaService->buscar($request->input());

        $response->view('editais/index', [
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
            'message' => $request->pullSession('editais.message', null),
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $editalId = (int) $request->routeParam('id', 0);
        $detalhe = $this->editalService->detalhar($editalId);

        if ($detalhe === null) {
            $request->setSession('editais.message', 'Edital nao encontrado.');
            $response->redirect('/editais');
            return;
        }

        $response->view('editais/show', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'edital' => $detalhe['edital'],
            'fonte' => $detalhe['fonte'],
            'documentos' => $detalhe['documentos'],
            'message' => $request->pullSession('editais.message', null),
        ]);
    }
}

