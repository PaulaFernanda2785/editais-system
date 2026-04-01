<?php

declare(strict_types=1);

namespace App\Models;

class PropostaAprovacao
{
    public int $id = 0;
    public int $propostaId = 0;
    public int $empresaId = 0;
    public string $statusDecisao = 'PENDENTE';
    public ?string $observacaoSolicitacao = null;
    public ?int $solicitadoPorUsuarioId = null;
    public ?string $solicitadoPorUsuarioNome = null;
    public ?string $solicitadoEm = null;
    public ?int $decididoPorUsuarioId = null;
    public ?string $decididoPorUsuarioNome = null;
    public ?string $decididoEm = null;
    public ?string $parecer = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = (int) ($data['id'] ?? 0);
        $item->propostaId = (int) ($data['proposta_id'] ?? 0);
        $item->empresaId = (int) ($data['empresa_id'] ?? 0);
        $item->statusDecisao = (string) ($data['status_decisao'] ?? 'PENDENTE');
        $item->observacaoSolicitacao = isset($data['observacao_solicitacao'])
            ? (string) $data['observacao_solicitacao']
            : null;
        $item->solicitadoPorUsuarioId = isset($data['solicitado_por_usuario_id'])
            ? (int) $data['solicitado_por_usuario_id']
            : null;
        $item->solicitadoPorUsuarioNome = isset($data['solicitado_por_usuario_nome'])
            ? (string) $data['solicitado_por_usuario_nome']
            : null;
        $item->solicitadoEm = isset($data['solicitado_em']) ? (string) $data['solicitado_em'] : null;
        $item->decididoPorUsuarioId = isset($data['decidido_por_usuario_id'])
            ? (int) $data['decidido_por_usuario_id']
            : null;
        $item->decididoPorUsuarioNome = isset($data['decidido_por_usuario_nome'])
            ? (string) $data['decidido_por_usuario_nome']
            : null;
        $item->decididoEm = isset($data['decidido_em']) ? (string) $data['decidido_em'] : null;
        $item->parecer = isset($data['parecer']) ? (string) $data['parecer'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $item->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $item;
    }
}
