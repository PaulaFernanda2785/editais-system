<?php

declare(strict_types=1);

namespace App\Models;

class Favorito
{
    public int $id = 0;
    public int $empresaId = 0;
    public int $editalId = 0;
    public string $statusAcompanhamento = 'FAVORITO';
    public ?string $observacao = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public ?string $editalNumero = null;
    public ?string $editalOrgaoNome = null;
    public ?string $editalUf = null;
    public ?string $editalModalidade = null;
    public ?string $editalDataPublicacao = null;
    public ?string $editalDataEncerramento = null;
    public ?float $editalValorEstimado = null;
    public ?string $editalLinkDetalhe = null;
    public ?string $editalLinkEdital = null;

    public ?int $correspondenciaId = null;
    public ?float $correspondenciaScore = null;
    public ?string $correspondenciaNivelRelevancia = null;

    public int $totalTarefas = 0;
    public int $tarefasAbertas = 0;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = (int) ($data['id'] ?? 0);
        $item->empresaId = (int) ($data['empresa_id'] ?? 0);
        $item->editalId = (int) ($data['edital_id'] ?? 0);
        $item->statusAcompanhamento = (string) ($data['status_acompanhamento'] ?? 'FAVORITO');
        $item->observacao = isset($data['observacao']) ? (string) $data['observacao'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $item->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        $item->editalNumero = isset($data['edital_numero']) ? (string) $data['edital_numero'] : null;
        $item->editalOrgaoNome = isset($data['edital_orgao_nome']) ? (string) $data['edital_orgao_nome'] : null;
        $item->editalUf = isset($data['edital_uf']) ? (string) $data['edital_uf'] : null;
        $item->editalModalidade = isset($data['edital_modalidade']) ? (string) $data['edital_modalidade'] : null;
        $item->editalDataPublicacao = isset($data['edital_data_publicacao']) ? (string) $data['edital_data_publicacao'] : null;
        $item->editalDataEncerramento = isset($data['edital_data_encerramento']) ? (string) $data['edital_data_encerramento'] : null;
        $item->editalValorEstimado = isset($data['edital_valor_estimado']) ? (float) $data['edital_valor_estimado'] : null;
        $item->editalLinkDetalhe = isset($data['edital_link_detalhe']) ? (string) $data['edital_link_detalhe'] : null;
        $item->editalLinkEdital = isset($data['edital_link_edital']) ? (string) $data['edital_link_edital'] : null;

        $item->correspondenciaId = isset($data['correspondencia_id']) ? (int) $data['correspondencia_id'] : null;
        $item->correspondenciaScore = isset($data['correspondencia_score']) ? (float) $data['correspondencia_score'] : null;
        $item->correspondenciaNivelRelevancia = isset($data['correspondencia_nivel_relevancia'])
            ? (string) $data['correspondencia_nivel_relevancia']
            : null;

        $item->totalTarefas = (int) ($data['total_tarefas'] ?? 0);
        $item->tarefasAbertas = (int) ($data['tarefas_abertas'] ?? 0);

        return $item;
    }
}
