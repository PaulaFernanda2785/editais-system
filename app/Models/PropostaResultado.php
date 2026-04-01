<?php

declare(strict_types=1);

namespace App\Models;

class PropostaResultado
{
    public int $id = 0;
    public int $propostaId = 0;
    public int $empresaId = 0;
    public ?int $usuarioId = null;
    public ?string $usuarioNome = null;
    public string $situacao = 'EM_JULGAMENTO';
    public ?string $dataResultado = null;
    public ?float $valorHomologado = null;
    public ?int $colocacao = null;
    public ?string $motivo = null;
    public ?string $linkAta = null;
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
        $item->situacao = (string) ($data['situacao'] ?? 'EM_JULGAMENTO');
        $item->dataResultado = isset($data['data_resultado']) ? (string) $data['data_resultado'] : null;
        $item->valorHomologado = isset($data['valor_homologado']) ? (float) $data['valor_homologado'] : null;
        $item->colocacao = isset($data['colocacao']) ? (int) $data['colocacao'] : null;
        $item->motivo = isset($data['motivo']) ? (string) $data['motivo'] : null;
        $item->linkAta = isset($data['link_ata']) ? (string) $data['link_ata'] : null;
        $item->observacao = isset($data['observacao']) ? (string) $data['observacao'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;

        return $item;
    }
}
