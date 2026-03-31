<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\PalavraChaveService;
use App\Validators\PalavraChaveValidator;

class PalavraChaveController extends Controller
{
    private PalavraChaveService $palavraService;
    private PalavraChaveValidator $validator;

    public function __construct(
        ?PalavraChaveService $palavraService = null,
        ?PalavraChaveValidator $validator = null
    ) {
        $this->palavraService = $palavraService ?? new PalavraChaveService();
        $this->validator = $validator ?? new PalavraChaveValidator();
    }

    public function store(Request $request, Response $response): void
    {
        $perfilId = (int) $request->routeParam('id', 0);
        $validation = $this->validator->validate($request->input());

        if ($validation['valid'] !== true) {
            $request->setSession('monitoramento.palavra.errors', $validation['errors']);
            $request->setSession('monitoramento.palavra.old', $request->input());
            $response->redirect('/monitoramento/' . $perfilId . '/editar');
            return;
        }

        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);

        $result = $this->palavraService->criar(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $perfilId,
            $validation['data']
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        if ($result['sucesso'] !== true) {
            $request->setSession('monitoramento.palavra.old', $request->input());
        }

        $response->redirect('/monitoramento/' . $perfilId . '/editar');
    }

    public function update(Request $request, Response $response): void
    {
        $perfilId = (int) $request->routeParam('id', 0);
        $palavraId = (int) $request->routeParam('palavraId', 0);
        $validation = $this->validator->validate($request->input());

        if ($validation['valid'] !== true) {
            $request->setSession(
                'monitoramento.message',
                implode(' ', array_values($validation['errors']))
            );
            $response->redirect('/monitoramento/' . $perfilId . '/editar');
            return;
        }

        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);

        $result = $this->palavraService->atualizar(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $perfilId,
            $palavraId,
            $validation['data']
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        $response->redirect('/monitoramento/' . $perfilId . '/editar');
    }

    public function toggle(Request $request, Response $response): void
    {
        $perfilId = (int) $request->routeParam('id', 0);
        $palavraId = (int) $request->routeParam('palavraId', 0);
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);

        $result = $this->palavraService->alternarAtivo(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $perfilId,
            $palavraId
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        $response->redirect('/monitoramento/' . $perfilId . '/editar');
    }

    public function delete(Request $request, Response $response): void
    {
        $perfilId = (int) $request->routeParam('id', 0);
        $palavraId = (int) $request->routeParam('palavraId', 0);
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);

        $result = $this->palavraService->excluir(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $perfilId,
            $palavraId
        );

        $request->setSession('monitoramento.message', $result['mensagem'] ?? null);
        $response->redirect('/monitoramento/' . $perfilId . '/editar');
    }
}
