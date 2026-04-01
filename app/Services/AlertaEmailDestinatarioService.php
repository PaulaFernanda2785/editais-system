<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\EmpresaRepository;
use App\Repositories\UsuarioRepository;

class AlertaEmailDestinatarioService
{
    private EmpresaRepository $empresaRepository;
    private UsuarioRepository $usuarioRepository;

    public function __construct(
        ?EmpresaRepository $empresaRepository = null,
        ?UsuarioRepository $usuarioRepository = null
    ) {
        $this->empresaRepository = $empresaRepository ?? new EmpresaRepository();
        $this->usuarioRepository = $usuarioRepository ?? new UsuarioRepository();
    }

    /**
     * @return array{email: ?string, origem: string}
     */
    public function resolverPrincipal(int $empresaId): array
    {
        $empresa = $this->empresaRepository->findById($empresaId);
        if ($empresa !== null) {
            $emailContato = $this->normalizarEmail((string) ($empresa->emailContato ?? ''));
            if ($emailContato !== null) {
                return ['email' => $emailContato, 'origem' => 'empresa.email_contato'];
            }
        }

        $usuarios = $this->usuarioRepository->listAtivosByEmpresa($empresaId);
        foreach ($usuarios as $usuario) {
            $email = $this->normalizarEmail((string) ($usuario->email ?? ''));
            if ($email !== null) {
                return ['email' => $email, 'origem' => 'usuario.ativo'];
            }
        }

        return ['email' => null, 'origem' => 'nao_encontrado'];
    }

    /**
     * @return array<int, string>
     */
    public function resolverEscalonamento(int $empresaId, ?string $responsavelEmail = null): array
    {
        $emails = [];

        $emailResponsavel = $this->normalizarEmail((string) ($responsavelEmail ?? ''));
        if ($emailResponsavel !== null) {
            $emails[] = $emailResponsavel;
        }

        $empresa = $this->empresaRepository->findById($empresaId);
        if ($empresa !== null) {
            $emailContato = $this->normalizarEmail((string) ($empresa->emailContato ?? ''));
            if ($emailContato !== null) {
                $emails[] = $emailContato;
            }
        }

        $usuarios = $this->usuarioRepository->listAtivosByEmpresa($empresaId);
        foreach ($usuarios as $usuario) {
            $perfil = strtoupper(trim((string) ($usuario->perfil ?? '')));
            if (!in_array($perfil, ['SUPER_ADMIN', 'ADMIN'], true)) {
                continue;
            }

            $email = $this->normalizarEmail((string) ($usuario->email ?? ''));
            if ($email !== null) {
                $emails[] = $email;
            }
        }

        $emails = array_values(array_unique($emails));
        $limite = max(1, min(15, $this->envInt('ALERTA_EMAIL_ESCALONAMENTO_MAX_DESTINATARIOS', 6)));
        if (count($emails) > $limite) {
            $emails = array_slice($emails, 0, $limite);
        }

        return $emails;
    }

    /**
     * @return array{valido: bool, erro?: string, email?: string, dominio?: string, mx?: bool}
     */
    public function validarEmail(string $email): array
    {
        $normalizado = $this->normalizarEmail($email);
        if ($normalizado === null) {
            return ['valido' => false, 'erro' => 'email_invalido_formato'];
        }

        $partes = explode('@', $normalizado, 2);
        $dominio = strtolower(trim((string) ($partes[1] ?? '')));
        if ($dominio === '') {
            return ['valido' => false, 'erro' => 'email_invalido_dominio'];
        }

        if ($this->envBool('ALERTA_EMAIL_BLOQUEAR_DOMINIOS_PLACEHOLDER', true) && $this->isDominioPlaceholder($dominio)) {
            return [
                'valido' => false,
                'erro' => 'email_dominio_placeholder',
                'email' => $normalizado,
                'dominio' => $dominio,
            ];
        }

        $validarMx = $this->envBool('ALERTA_EMAIL_VALIDAR_MX', true);
        if ($validarMx) {
            $mxValido = $this->hasDnsDestinoEmail($dominio);
            if (!$mxValido) {
                return [
                    'valido' => false,
                    'erro' => 'email_dominio_sem_dns',
                    'email' => $normalizado,
                    'dominio' => $dominio,
                    'mx' => false,
                ];
            }
        }

        return [
            'valido' => true,
            'email' => $normalizado,
            'dominio' => $dominio,
            'mx' => $validarMx ? true : false,
        ];
    }

    /**
     * @param array<int, string> $emails
     * @return array{validos: array<int, string>, rejeitados: array<int, array{email: string, erro: string}>}
     */
    public function validarListaEmails(array $emails): array
    {
        $validos = [];
        $rejeitados = [];

        foreach ($emails as $email) {
            $email = trim((string) $email);
            if ($email === '') {
                continue;
            }

            $validacao = $this->validarEmail($email);
            if (($validacao['valido'] ?? false) === true) {
                $emailValido = isset($validacao['email']) ? (string) $validacao['email'] : null;
                if ($emailValido !== null && $emailValido !== '') {
                    $validos[] = $emailValido;
                }
                continue;
            }

            $rejeitados[] = [
                'email' => $email,
                'erro' => (string) ($validacao['erro'] ?? 'email_rejeitado'),
            ];
        }

        return [
            'validos' => array_values(array_unique($validos)),
            'rejeitados' => $rejeitados,
        ];
    }

    private function hasDnsDestinoEmail(string $dominio): bool
    {
        $temMx = function_exists('checkdnsrr') ? @checkdnsrr($dominio, 'MX') : false;
        if ($temMx) {
            return true;
        }

        $temA = function_exists('checkdnsrr') ? @checkdnsrr($dominio, 'A') : false;
        if ($temA) {
            return true;
        }

        $temAaaa = function_exists('checkdnsrr') ? @checkdnsrr($dominio, 'AAAA') : false;
        return $temAaaa;
    }

    private function isDominioPlaceholder(string $dominio): bool
    {
        $bloqueados = [
            'example.com',
            'example.org',
            'example.net',
            'example.edu',
            'localhost',
            'localdomain',
            'invalid',
            'test',
        ];

        if (in_array($dominio, $bloqueados, true)) {
            return true;
        }

        return str_ends_with($dominio, '.invalid')
            || str_ends_with($dominio, '.local')
            || str_ends_with($dominio, '.test');
    }

    private function normalizarEmail(string $email): ?string
    {
        $email = strtolower(trim($email));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    private function envRaw(string $key): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }

    private function envBool(string $key, bool $default): bool
    {
        $raw = $this->envRaw($key);
        if ($raw === null || $raw === '') {
            return $default;
        }

        return filter_var((string) $raw, FILTER_VALIDATE_BOOLEAN);
    }

    private function envInt(string $key, int $default): int
    {
        $raw = $this->envRaw($key);
        if ($raw === null || $raw === '' || !is_numeric($raw)) {
            return $default;
        }

        return (int) $raw;
    }
}

