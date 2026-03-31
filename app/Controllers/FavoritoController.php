<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\FavoritoService;

class FavoritoController extends Controller
{
    private FavoritoService $favoritoService;

    public function __construct(?FavoritoService $favoritoService = null)
    {
        $this->favoritoService = $favoritoService ?? new FavoritoService();
    }

    public function index(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $resultado = $this->favoritoService->listarPipeline($empresaId, $request->input());
        $resumo = $this->favoritoService->resumoPorStatus($empresaId);

        $response->view('favoritos/index', [
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
            'statusPermitidos' => $resultado['status_permitidos'],
            'resumo' => $resumo,
            'message' => $request->pullSession('favoritos.message', null),
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $favoritoId = (int) $request->routeParam('id', 0);
        $detalhe = $this->favoritoService->detalhar($empresaId, $favoritoId);

        if ($detalhe === null) {
            $request->setSession('favoritos.message', 'Item do pipeline nao encontrado.');
            $response->redirect('/favoritos');
            return;
        }

        $response->view('favoritos/show', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'favorito' => $detalhe['favorito'],
            'tarefas' => $detalhe['tarefas'],
            'recomendacao' => $detalhe['recomendacao'],
            'statusPermitidos' => $detalhe['status_permitidos'],
            'statusTarefaPermitidos' => $detalhe['status_tarefa_permitidos'],
            'message' => $request->pullSession('favoritos.message', null),
        ]);
    }

    public function updateStatus(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $favoritoId = (int) $request->routeParam('id', 0);

        $resultado = $this->favoritoService->atualizarStatus(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $favoritoId,
            (string) $request->input('status_acompanhamento', ''),
            $request->input('observacao')
        );

        $request->setSession('favoritos.message', $resultado['mensagem'] ?? null);
        $response->redirect($this->sanitizeRedirect((string) $request->input('redirect_to', '/favoritos/' . $favoritoId)));
    }

    public function storeTarefa(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $favoritoId = (int) $request->routeParam('id', 0);

        $resultado = $this->favoritoService->criarTarefa(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $favoritoId,
            $request->input()
        );

        $request->setSession('favoritos.message', $resultado['mensagem'] ?? null);
        $response->redirect('/favoritos/' . $favoritoId);
    }

    public function updateTarefaStatus(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $favoritoId = (int) $request->routeParam('id', 0);
        $tarefaId = (int) $request->routeParam('tarefaId', 0);

        $resultado = $this->favoritoService->atualizarStatusTarefa(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $favoritoId,
            $tarefaId,
            (string) $request->input('status', '')
        );

        $request->setSession('favoritos.message', $resultado['mensagem'] ?? null);
        $response->redirect('/favoritos/' . $favoritoId);
    }

    public function deleteTarefa(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $favoritoId = (int) $request->routeParam('id', 0);
        $tarefaId = (int) $request->routeParam('tarefaId', 0);

        $resultado = $this->favoritoService->removerTarefa(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $favoritoId,
            $tarefaId
        );

        $request->setSession('favoritos.message', $resultado['mensagem'] ?? null);
        $response->redirect('/favoritos/' . $favoritoId);
    }

    private function sanitizeRedirect(string $redirect): string
    {
        $redirect = trim($redirect);
        if ($redirect === '' || !str_starts_with($redirect, '/')) {
            return '/favoritos';
        }

        return $redirect;
    }
}
