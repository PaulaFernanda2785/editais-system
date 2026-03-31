<?php

declare(strict_types=1);

namespace App\Models;

class PalavraChave
{
    public int $id = 0;
    public int $empresaId = 0;
    public ?int $perfilMonitoramentoId = null;
    public string $termo = '';
    public int $peso = 1;
    public ?string $categoria = null;
    public bool $ativo = true;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $palavra = new self();
        $palavra->id = (int) ($data['id'] ?? 0);
        $palavra->empresaId = (int) ($data['empresa_id'] ?? 0);
        $palavra->perfilMonitoramentoId = isset($data['perfil_monitoramento_id'])
            ? (int) $data['perfil_monitoramento_id']
            : null;
        $palavra->termo = (string) ($data['termo'] ?? '');
        $palavra->peso = (int) ($data['peso'] ?? 1);
        $palavra->categoria = isset($data['categoria']) ? (string) $data['categoria'] : null;
        $palavra->ativo = ((int) ($data['ativo'] ?? 1)) === 1;
        $palavra->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $palavra->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $palavra;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresaId,
            'perfil_monitoramento_id' => $this->perfilMonitoramentoId,
            'termo' => $this->termo,
            'peso' => $this->peso,
            'categoria' => $this->categoria,
            'ativo' => $this->ativo ? 1 : 0,
        ];
    }
}
