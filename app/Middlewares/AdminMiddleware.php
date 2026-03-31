<?php

declare(strict_types=1);

namespace App\Middlewares;

use App\Core\Request;
use App\Core\Response;

class AdminMiddleware
{
    /**
     * @var array<int, string>
     */
    private array $perfisPermitidos = ['SUPER_ADMIN', 'ADMIN'];

    public function handle(Request $request, Response $response, callable $next): void
    {
        $perfil = strtoupper((string) $request->session('auth.perfil', ''));
        if (!in_array($perfil, $this->perfisPermitidos, true)) {
            $request->setSession('admin.message', 'Acesso restrito a administradores.');
            $response->redirect('/dashboard');
            return;
        }

        $next($request, $response);
    }
}
