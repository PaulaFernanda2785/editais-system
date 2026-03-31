<?php

declare(strict_types=1);

namespace App\Models;

class FavoritoTarefa
{
    public int $id = 0;
    public int $favoritoId = 0;
    public int $empresaId = 0;
    public string $titulo = '';
    public ?string $descricao = null;
    public ?string $responsavel = null;
    public ?string $dataLimite = null;
    public string $status = 'PENDENTE';
    public int $ordem = 1;
    public ?string $concluidaEm = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = (int) ($data['id'] ?? 0);
        $item->favoritoId = (int) ($data['favorito_id'] ?? 0);
        $item->empresaId = (int) ($data['empresa_id'] ?? 0);
        $item->titulo = (string) ($data['titulo'] ?? '');
        $item->descricao = isset($data['descricao']) ? (string) $data['descricao'] : null;
        $item->responsavel = isset($data['responsavel']) ? (string) $data['responsavel'] : null;
        $item->dataLimite = isset($data['data_limite']) ? (string) $data['data_limite'] : null;
        $item->status = (string) ($data['status'] ?? 'PENDENTE');
        $item->ordem = (int) ($data['ordem'] ?? 1);
        $item->concluidaEm = isset($data['concluida_em']) ? (string) $data['concluida_em'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $item->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $item;
    }
}
