<?php

declare(strict_types=1);

namespace App\Models;

class PropostaSubmissao
{
    public int $id = 0;
    public int $propostaId = 0;
    public int $empresaId = 0;
    public ?int $usuarioId = null;
    public ?string $usuarioNome = null;
    public string $canal = 'PORTAL';
    public ?string $protocolo = null;
    public ?string $dataSubmissao = null;
    public ?float $valorEnviado = null;
    public ?string $linkComprovante = null;
    public ?string $observacao = null;
    public ?string $criadoEm = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = (int) ($data['id'] ?? 0);
        $item->propostaId = (int) ($data['proposta_id'] ?? 0);
        $item->empresaId = (int) ($data['empresa_id'] ?? 0);
        $item->usuarioId = isset($data['usuario_id']) ? (int) $data['usuario_id'] : null;
        $item->usuarioNome = isset($data['usuario_nome']) ? (string) $data['usuario_nome'] : null;
        $item->canal = (string) ($data['canal'] ?? 'PORTAL');
        $item->protocolo = isset($data['protocolo']) ? (string) $data['protocolo'] : null;
        $item->dataSubmissao = isset($data['data_submissao']) ? (string) $data['data_submissao'] : null;
        $item->valorEnviado = isset($data['valor_enviado']) ? (float) $data['valor_enviado'] : null;
        $item->linkComprovante = isset($data['link_comprovante']) ? (string) $data['link_comprovante'] : null;
        $item->observacao = isset($data['observacao']) ? (string) $data['observacao'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;

        return $item;
    }
}
