<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\PalavraChaveService;
use App\Services\PerfilMonitoramentoService;
use App\Validators\PerfilMonitoramentoValidator;

class PerfilMonitoramentoController extends Controller
{
    private PerfilMonitoramentoService $perfilService;
    private PalavraChaveService $palavraService;
    private PerfilMonitoramentoValidator $validator;

    public function __construct(
        ?PerfilMonitoramentoService $perfilService = null,
        ?PalavraChaveService $palavraService = null,
        ?PerfilMonitoramentoValidator $validator = null
    ) {
        $this->perfilService = $perfilService ?? new PerfilMonitoramentoService();
        $this->palavraService = $palavraService ?? new PalavraChaveService();
        $this->validator = $validator ?? new PerfilMonitoramentoValidator();
    }

    public function index(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $perfis = $this->perfilService->listarPorEmpresa($empresaId);

        $response->view('monitoramento/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'perfis' => $perfis,
            'message' => $request->pullSession('monitoramento.message', null),
        ]);
    }

    public function create(Request $request, Response $response): void
    {
        $response->view('monitoramento/create', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'errors' => $request->pullSession('monitoramento.create.errors', []),
            'old' => $request->pullSession('monitoramento.create.old', []),
            'message' => $request->pullSession('monitoramento.message', null),
        ]);
    }

    public function store(Request $request, Response $response): void
    {
        $validation = $this->validator->validate($request->input());
        if ($validation['valid'] !== true) {
            $request->setSession('monitoramento.create.errors', $validation['errors']);
            $request->setSession('monitoramento.create.old', $request->input());
            $response->redirect('/monitoramento/novo');
            return;
        }

        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);

        $result = $this->perfilService->criar(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $validation['data']
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        if ($result['sucesso'] !== true) {
            $request->setSession('monitoramento.create.old', $request->input());
            $response->redirect('/monitoramento/novo');
            return;
        }

        $response->redirect('/monitoramento');
    }

    public function edit(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $perfilId = (int) $request->routeParam('id', 0);

        $perfil = $this->perfilService->buscarPorId($empresaId, $perfilId);
        if ($perfil === null) {
            $request->setSession('monitoramento.message', 'Perfil de monitoramento nao encontrado.');
            $response->redirect('/monitoramento');
            return;
        }

        $response->view('monitoramento/edit', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'perfil' => $perfil,
            'palavras' => $this->palavraService->listarPorPerfil($empresaId, $perfilId),
            'message' => $request->pullSession('monitoramento.message', null),
            'errors' => $request->pullSession('monitoramento.edit.errors', []),
            'old' => $request->pullSession('monitoramento.edit.old', []),
            'palavraErrors' => $request->pullSession('monitoramento.palavra.errors', []),
            'palavraOld' => $request->pullSession('monitoramento.palavra.old', []),
        ]);
    }

    public function update(Request $request, Response $response): void
    {
        $perfilId = (int) $request->routeParam('id', 0);
        $validation = $this->validator->validate($request->input());

        if ($validation['valid'] !== true) {
            $request->setSession('monitoramento.edit.errors', $validation['errors']);
            $request->setSession('monitoramento.edit.old', $request->input());
            $response->redirect('/monitoramento/' . $perfilId . '/editar');
            return;
        }

        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $result = $this->perfilService->atualizar(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $perfilId,
            $validation['data']
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        if ($result['sucesso'] !== true) {
            $request->setSession('monitoramento.edit.old', $request->input());
        }

        $response->redirect('/monitoramento/' . $perfilId . '/editar');
    }

    public function toggle(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $perfilId = (int) $request->routeParam('id', 0);

        $result = $this->perfilService->alternarAtivo(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $perfilId
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        $response->redirect('/monitoramento');
    }

    public function delete(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $perfilId = (int) $request->routeParam('id', 0);

        $result = $this->perfilService->excluir(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $perfilId
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        $response->redirect('/monitoramento');
    }
}
