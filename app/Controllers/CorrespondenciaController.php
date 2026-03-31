<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CorrespondenciaService;
use App\Services\FavoritoService;

class CorrespondenciaController extends Controller
{
    private CorrespondenciaService $correspondenciaService;
    private FavoritoService $favoritoService;

    public function __construct(
        ?CorrespondenciaService $correspondenciaService = null,
        ?FavoritoService $favoritoService = null
    )
    {
        $this->correspondenciaService = $correspondenciaService ?? new CorrespondenciaService();
        $this->favoritoService = $favoritoService ?? new FavoritoService();
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

    public function decidir(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $correspondenciaId = (int) $request->routeParam('id', 0);

        $resultado = $this->favoritoService->decidirPorOportunidade(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $correspondenciaId,
            (string) $request->input('status_acompanhamento', ''),
            $request->input('observacao')
        );

        $request->setSession('correspondencias.message', $resultado['mensagem'] ?? null);

        $abrirPipeline = filter_var($request->input('abrir_pipeline', false), FILTER_VALIDATE_BOOLEAN);
        $favoritoId = isset($resultado['favorito_id']) ? (int) $resultado['favorito_id'] : 0;
        if ($abrirPipeline && $favoritoId > 0) {
            $response->redirect('/favoritos/' . $favoritoId);
            return;
        }

        $redirectTo = trim((string) $request->input('redirect_to', '/oportunidades'));
        if ($redirectTo === '' || !str_starts_with($redirectTo, '/')) {
            $redirectTo = '/oportunidades';
        }

        $response->redirect($redirectTo);
    }
}
