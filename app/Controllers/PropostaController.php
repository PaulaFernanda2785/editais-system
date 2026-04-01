<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\PropostaExecucaoService;

class PropostaController extends Controller
{
    private PropostaExecucaoService $propostaService;

    public function __construct(?PropostaExecucaoService $propostaService = null)
    {
        $this->propostaService = $propostaService ?? new PropostaExecucaoService();
    }

    public function index(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $resultado = $this->propostaService->listar($empresaId, $request->input());
        $resumo = $this->propostaService->resumoPorStatus($empresaId);

        $response->view('propostas/index', [
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
            'message' => $request->pullSession('propostas.message', null),
        ]);
    }

    public function show(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $propostaId = (int) $request->routeParam('id', 0);
        $detalhe = $this->propostaService->detalhar($empresaId, $propostaId);

        if ($detalhe === null) {
            $request->setSession('propostas.message', 'Proposta nao encontrada.');
            $response->redirect('/propostas');
            return;
        }

        $response->view('propostas/show', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
            'tenant' => $request->session('tenant.empresa', []),
            'proposta' => $detalhe['proposta'],
            'favorito' => $detalhe['favorito'],
            'tarefas' => $detalhe['tarefas'],
            'aprovacoes' => $detalhe['aprovacoes'],
            'submissoes' => $detalhe['submissoes'],
            'resultados' => $detalhe['resultados'],
            'ultimoResultado' => $detalhe['ultimo_resultado'],
            'aprovacaoPendente' => $detalhe['aprovacao_pendente'],
            'statusPermitidos' => $detalhe['status_permitidos'],
            'canaisSubmissao' => $detalhe['canais_submissao'],
            'situacoesResultado' => $detalhe['situacoes_resultado'],
            'message' => $request->pullSession('propostas.message', null),
        ]);
    }

    public function gerarPorFavorito(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $favoritoId = (int) $request->routeParam('id', 0);

        $resultado = $this->propostaService->gerarRascunhoPorFavorito(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $favoritoId
        );

        $request->setSession('propostas.message', $resultado['mensagem'] ?? null);
        $request->setSession('favoritos.message', $resultado['mensagem'] ?? null);

        $abrir = filter_var($request->input('abrir_detalhe', true), FILTER_VALIDATE_BOOLEAN);
        $propostaId = isset($resultado['proposta_id']) ? (int) $resultado['proposta_id'] : 0;
        if ($abrir && $propostaId > 0) {
            $response->redirect('/propostas/' . $propostaId);
            return;
        }

        $redirectTo = trim((string) $request->input('redirect_to', '/favoritos/' . $favoritoId));
        if ($redirectTo === '' || !str_starts_with($redirectTo, '/')) {
            $redirectTo = '/favoritos/' . $favoritoId;
        }

        $response->redirect($redirectTo);
    }

    public function update(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $propostaId = (int) $request->routeParam('id', 0);

        $resultado = $this->propostaService->atualizar(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $propostaId,
            $request->input()
        );

        $request->setSession('propostas.message', $resultado['mensagem'] ?? null);
        $response->redirect('/propostas/' . $propostaId);
    }

    public function solicitarAprovacao(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $propostaId = (int) $request->routeParam('id', 0);

        $resultado = $this->propostaService->solicitarAprovacao(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $propostaId,
            $request->input('observacao_solicitacao')
        );

        $request->setSession('propostas.message', $resultado['mensagem'] ?? null);
        $response->redirect('/propostas/' . $propostaId);
    }

    public function decidirAprovacao(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $propostaId = (int) $request->routeParam('id', 0);

        $resultado = $this->propostaService->decidirAprovacao(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $propostaId,
            $request->input()
        );

        $request->setSession('propostas.message', $resultado['mensagem'] ?? null);
        $response->redirect('/propostas/' . $propostaId);
    }

    public function registrarSubmissao(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $propostaId = (int) $request->routeParam('id', 0);

        $resultado = $this->propostaService->registrarSubmissao(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $propostaId,
            $request->input()
        );

        $request->setSession('propostas.message', $resultado['mensagem'] ?? null);
        $response->redirect('/propostas/' . $propostaId);
    }

    public function registrarResultado(Request $request, Response $response): void
    {
        $empresaId = (int) $request->session('auth.empresa_id', 0);
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $propostaId = (int) $request->routeParam('id', 0);

        $resultado = $this->propostaService->registrarResultado(
            $empresaId,
            $usuarioId > 0 ? $usuarioId : null,
            $propostaId,
            $request->input()
        );

        $request->setSession('propostas.message', $resultado['mensagem'] ?? null);
        $response->redirect('/propostas/' . $propostaId);
    }
}
