<?php

declare(strict_types=1);

namespace App\Models;

class Usuario
{
    public int $id = 0;
    public int $empresaId = 0;
    public string $nome = '';
    public string $email = '';
    public string $senhaHash = '';
    public string $perfil = 'USUARIO';
    public string $status = 'ATIVO';
    public ?string $ultimoLoginEm = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $usuario = new self();
        $usuario->id = (int) ($data['id'] ?? 0);
        $usuario->empresaId = (int) ($data['empresa_id'] ?? 0);
        $usuario->nome = (string) ($data['nome'] ?? '');
        $usuario->email = (string) ($data['email'] ?? '');
        $usuario->senhaHash = (string) ($data['senha_hash'] ?? '');
        $usuario->perfil = (string) ($data['perfil'] ?? 'USUARIO');
        $usuario->status = (string) ($data['status'] ?? 'ATIVO');
        $usuario->ultimoLoginEm = isset($data['ultimo_login_em']) ? (string) $data['ultimo_login_em'] : null;
        $usuario->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $usuario->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $usuario;
    }

    public function canLogin(): bool
    {
        return $this->status === 'ATIVO';
    }

    public function toSessionArray(): array
    {
        return [
            'user_id' => $this->id,
            'empresa_id' => $this->empresaId,
            'nome' => $this->nome,
            'email' => $this->email,
            'perfil' => $this->perfil,
            'status' => $this->status,
        ];
    }
}
