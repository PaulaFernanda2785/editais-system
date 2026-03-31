<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Usuario;
use App\Repositories\UsuarioRepository;

class AuthService
{
    private UsuarioRepository $usuarioRepository;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?UsuarioRepository $usuarioRepository = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->usuarioRepository = $usuarioRepository ?? new UsuarioRepository();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    public function attempt(string $email, string $senha, string $ip = '0.0.0.0'): array
    {
        $email = strtolower(trim($email));
        $usuario = $this->usuarioRepository->findByEmail($email);

        if ($usuario === null) {
            $this->logService->warning('auth.login', 'Tentativa com e-mail inexistente.', [
                'email' => $email,
                'ip' => $ip,
            ]);

            return [
                'success' => false,
                'message' => 'Credenciais invalidas.',
            ];
        }

        if (!$usuario->canLogin()) {
            $this->logService->warning('auth.login', 'Tentativa com usuario sem status ativo.', [
                'usuario_id' => $usuario->id,
                'empresa_id' => $usuario->empresaId,
                'status' => $usuario->status,
                'ip' => $ip,
            ]);

            $this->auditService->record(
                'LOGIN_BLOQUEADO',
                'usuarios',
                $usuario->id,
                ['status' => $usuario->status, 'ip' => $ip],
                $usuario->empresaId,
                $usuario->id
            );

            return [
                'success' => false,
                'message' => 'Usuario sem permissao para acesso.',
            ];
        }

        if (!password_verify($senha, $usuario->senhaHash)) {
            $this->logService->warning('auth.login', 'Senha incorreta em tentativa de login.', [
                'usuario_id' => $usuario->id,
                'empresa_id' => $usuario->empresaId,
                'ip' => $ip,
            ]);

            $this->auditService->record(
                'LOGIN_SENHA_INVALIDA',
                'usuarios',
                $usuario->id,
                ['ip' => $ip],
                $usuario->empresaId,
                $usuario->id
            );

            return [
                'success' => false,
                'message' => 'Credenciais invalidas.',
            ];
        }

        $this->usuarioRepository->updateUltimoLogin($usuario->id);
        $this->persistSession($usuario);

        $this->logService->info('auth.login', 'Login realizado com sucesso.', [
            'usuario_id' => $usuario->id,
            'empresa_id' => $usuario->empresaId,
            'ip' => $ip,
        ]);

        $this->auditService->record(
            'LOGIN_SUCESSO',
            'usuarios',
            $usuario->id,
            ['ip' => $ip],
            $usuario->empresaId,
            $usuario->id
        );

        return [
            'success' => true,
            'message' => 'Login realizado com sucesso.',
            'user' => $usuario->toSessionArray(),
        ];
    }

    public function logout(string $ip = '0.0.0.0'): void
    {
        $usuarioId = isset($_SESSION['auth']['user_id']) ? (int) $_SESSION['auth']['user_id'] : null;
        $empresaId = isset($_SESSION['auth']['empresa_id']) ? (int) $_SESSION['auth']['empresa_id'] : null;

        if ($usuarioId !== null && $empresaId !== null) {
            $this->logService->info('auth.logout', 'Logout realizado com sucesso.', [
                'usuario_id' => $usuarioId,
                'empresa_id' => $empresaId,
                'ip' => $ip,
            ]);

            $this->auditService->record(
                'LOGOUT_SUCESSO',
                'usuarios',
                $usuarioId,
                ['ip' => $ip],
                $empresaId,
                $usuarioId
            );
        }

        $_SESSION = [];
        session_regenerate_id(true);
    }

    public function check(): bool
    {
        return isset($_SESSION['auth']['user_id'], $_SESSION['auth']['empresa_id']);
    }

    public function user(): ?array
    {
        return $_SESSION['auth'] ?? null;
    }

    private function persistSession(Usuario $usuario): void
    {
        session_regenerate_id(true);

        $_SESSION['auth'] = array_merge($usuario->toSessionArray(), [
            'authenticated_at' => date('Y-m-d H:i:s'),
        ]);
        $_SESSION['tenant_id'] = $usuario->empresaId;
    }
}
