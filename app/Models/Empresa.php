<?php

declare(strict_types=1);

namespace App\Models;

class Empresa
{
    public int $id = 0;
    public string $razaoSocial = '';
    public ?string $nomeFantasia = null;
    public ?string $cnpj = null;
    public ?string $segmento = null;
    public ?string $emailContato = null;
    public ?string $telefone = null;
    public string $status = 'ATIVA';
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $empresa = new self();
        $empresa->id = (int) ($data['id'] ?? 0);
        $empresa->razaoSocial = (string) ($data['razao_social'] ?? '');
        $empresa->nomeFantasia = isset($data['nome_fantasia']) ? (string) $data['nome_fantasia'] : null;
        $empresa->cnpj = isset($data['cnpj']) ? (string) $data['cnpj'] : null;
        $empresa->segmento = isset($data['segmento']) ? (string) $data['segmento'] : null;
        $empresa->emailContato = isset($data['email_contato']) ? (string) $data['email_contato'] : null;
        $empresa->telefone = isset($data['telefone']) ? (string) $data['telefone'] : null;
        $empresa->status = (string) ($data['status'] ?? 'ATIVA');
        $empresa->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $empresa->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $empresa;
    }

    public function isAtiva(): bool
    {
        return $this->status === 'ATIVA';
    }

    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'razao_social' => $this->razaoSocial,
            'nome_fantasia' => $this->nomeFantasia,
            'cnpj' => $this->cnpj,
            'status' => $this->status,
        ];
    }
}
