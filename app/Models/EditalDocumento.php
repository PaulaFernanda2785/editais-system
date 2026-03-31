<?php

declare(strict_types=1);

namespace App\Models;

class EditalDocumento
{
    public int $id = 0;
    public int $editalId = 0;
    public string $nomeDocumento = '';
    public string $urlDocumento = '';
    public ?string $tipoDocumento = null;
    public ?string $criadoEm = null;

    public static function fromArray(array $data): self
    {
        $documento = new self();
        $documento->id = (int) ($data['id'] ?? 0);
        $documento->editalId = (int) ($data['edital_id'] ?? 0);
        $documento->nomeDocumento = (string) ($data['nome_documento'] ?? '');
        $documento->urlDocumento = (string) ($data['url_documento'] ?? '');
        $documento->tipoDocumento = isset($data['tipo_documento']) ? (string) $data['tipo_documento'] : null;
        $documento->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;

        return $documento;
    }
}

