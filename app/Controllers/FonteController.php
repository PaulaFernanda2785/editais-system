<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\FonteColetaService;
use App\Validators\FonteColetaValidator;

class FonteController extends Controller
{
    private FonteColetaService $fonteService;
    private FonteColetaValidator $validator;

    public function __construct(
        ?FonteColetaService $fonteService = null,
        ?FonteColetaValidator $validator = null
    ) {
        $this->fonteService = $fonteService ?? new FonteColetaService();
        $this->validator = $validator ?? new FonteColetaValidator();
    }

    public function index(Request $request, Response $response): void
    {
        $response->view('fontes/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'fontes' => $this->fonteService->listar(),
            'message' => $request->pullSession('fonte.message', null),
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $response->view('fontes/create', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'errors' => $request->pullSession('fonte.create.errors', []),
            'old' => $request->pullSession('fonte.create.old', []),
            'message' => $request->pullSession('fonte.message', null),
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $validation = $this->validator->validate($request->input());
        if ($validation['valid'] !== true) {
            $request->setSession('fonte.create.errors', $validation['errors']);
            $request->setSession('fonte.create.old', $request->input());
            $response->redirect('/fontes/novo');
            return;
        }

        $result = $this->fonteService->criar(
            $validation['data'],
            (int) $request->session('auth.user_id', 0),
            (int) $request->session('auth.empresa_id', 0)
        );

        $request->setSession('fonte.message', $result['mensagem'] ?? null);
        if ($result['sucesso'] !== true) {
            $request->setSession('fonte.create.old', $request->input());
            $response->redirect('/fontes/novo');
            return;
        }

        $response->redirect('/fontes');
    }

    public function show(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $fonte = $this->fonteService->obter($id);

        if ($fonte === null) {
            $request->setSession('fonte.message', 'Fonte de coleta nao encontrada.');
            $response->redirect('/fontes');
            return;
        }

        $response->view('fontes/show', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'fonte' => $fonte,
            'historico' => $this->fonteService->listarHistoricoExecucoes($id, 30),
            'message' => $request->pullSession('fonte.message', null),
        ]);
    }

    public function edit(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $fonte = $this->fonteService->obter($id);

        if ($fonte === null) {
            $request->setSession('fonte.message', 'Fonte de coleta nao encontrada.');
            $response->redirect('/fontes');
            return;
        }

        $response->view('fontes/edit', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'fonte' => $fonte,
            'errors' => $request->pullSession('fonte.edit.errors', []),
            'old' => $request->pullSession('fonte.edit.old', []),
            'message' => $request->pullSession('fonte.message', null),
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $validation = $this->validator->validate($request->input());

        if ($validation['valid'] !== true) {
            $request->setSession('fonte.edit.errors', $validation['errors']);
            $request->setSession('fonte.edit.old', $request->input());
            $response->redirect('/fontes/' . $id . '/editar');
            return;
        }

        $result = $this->fonteService->atualizar(
            $id,
            $validation['data'],
            (int) $request->session('auth.user_id', 0),
            (int) $request->session('auth.empresa_id', 0)
        );

        $request->setSession('fonte.message', $result['mensagem'] ?? null);
        if ($result['sucesso'] !== true) {
            $request->setSession('fonte.edit.old', $request->input());
        }

        $response->redirect('/fontes/' . $id . '/editar');
    }

    public function toggle(Request $request, Response $response): void
    {
        $id = (int) $request->routeParam('id', 0);
        $result = $this->fonteService->alternarAtiva(
            $id,
            (int) $request->session('auth.user_id', 0),
            (int) $request->session('auth.empresa_id', 0)
        );

        $request->setSession('fonte.message', $result['mensagem'] ?? null);
        $response->redirect('/fontes');
    }
}
