<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;
use App\Validators\AuthValidator;

class AuthController extends Controller
{
    private AuthService $authService;
    private AuthValidator $authValidator;

    public function __construct(?AuthService $authService = null, ?AuthValidator $authValidator = null)
    {
        $this->authService = $authService ?? new AuthService();
        $this->authValidator = $authValidator ?? new AuthValidator();
    }

    public function showLoginForm(Request $request, Response $response): void
    {
        if ($this->authService->check()) {
            $response->redirect('/dashboard');
            return;
        }

        $response->view('auth/login', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'errors' => $request->pullSession('auth_errors', []),
            'old' => $request->pullSession('auth_old', []),
            'message' => $request->pullSession('auth_message', null),
        ]);
    }

    public function login(Request $request, Response $response): void
    {
        $validation = $this->authValidator->validateLogin([
            'email' => (string) $request->input('email', ''),
            'senha' => (string) $request->input('senha', ''),
        ]);

        if ($validation['valid'] !== true) {
            $request->setSession('auth_errors', $validation['errors']);
            $request->setSession('auth_old', ['email' => $validation['data']['email'] ?? '']);
            $response->redirect('/login');
            return;
        }

        $result = $this->authService->attempt(
            (string) $validation['data']['email'],
            (string) $validation['data']['senha'],
            $request->ip()
        );

        if ($result['success'] !== true) {
            $request->setSession('auth_message', $result['message'] ?? 'Falha na autenticacao.');
            $request->setSession('auth_old', ['email' => $validation['data']['email'] ?? '']);
            $response->redirect('/login');
            return;
        }

        $response->redirect('/dashboard');
    }

    public function dashboard(Request $request, Response $response): void
    {
        $response->view('dashboard/index', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'auth' => $request->session('auth', []),
        ]);
    }

    public function showLogout(Request $request, Response $response): void
    {
        $response->view('auth/logout', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'loggedOut' => false,
            'auth' => $request->session('auth', []),
        ]);
    }

    public function logout(Request $request, Response $response): void
    {
        $this->authService->logout($request->ip());

        $response->view('auth/logout', [
            'appName' => $_ENV['APP_NAME'] ?? 'SaaS Editais',
            'loggedOut' => true,
            'auth' => [],
        ]);
    }
}
