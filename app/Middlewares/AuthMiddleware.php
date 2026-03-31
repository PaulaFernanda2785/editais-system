<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;
use App\Repositories\UsuarioRepository;

class AuthMiddleware
{
    private UsuarioRepository $usuarioRepository;

    public function __construct(?UsuarioRepository $usuarioRepository = null)
    {
        $this->usuarioRepository = $usuarioRepository ?? new UsuarioRepository();
    }

    public function handle(Request $request, Response $response, callable $next): void
    {
        $usuarioId = (int) $request->session('auth.user_id', 0);
        $empresaId = (int) $request->session('auth.empresa_id', 0);

        if ($usuarioId <= 0 || $empresaId <= 0) {
            $response->redirect('/login');
            return;
        }

        $usuario = $this->usuarioRepository->findByIdAndEmpresa($usuarioId, $empresaId);
        if ($usuario === null || $usuario->status !== 'ATIVO') {
            $request->invalidateSession();
            $response->redirect('/login');
            return;
        }

        $next($request, $response);
    }
}
