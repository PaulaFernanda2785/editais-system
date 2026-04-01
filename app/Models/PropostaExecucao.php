<?php

declare(strict_types=1);

namespace App\Models;

class PropostaExecucao
{
    public int $id = 0;
    public int $favoritoId = 0;
    public int $empresaId = 0;
    public string $status = 'RASCUNHO';
    public string $titulo = '';
    public ?string $resumoExecutivo = null;
    public ?string $estrategiaProposta = null;
    public ?string $escopoEntrega = null;
    public ?string $diferenciais = null;
    public ?string $cronogramaMacro = null;
    public ?string $riscoPrincipal = null;
    public ?float $valorProposta = null;
    public ?string $observacoes = null;
    public bool $geradaAutomatica = true;
    public ?int $criadoPorUsuarioId = null;
    public ?int $atualizadoPorUsuarioId = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public ?string $favoritoStatusAcompanhamento = null;
    public ?string $editalNumero = null;
    public ?string $editalOrgaoNome = null;
    public ?string $editalUf = null;
    public ?string $editalModalidade = null;
    public ?string $editalDataEncerramento = null;
    public ?float $editalValorEstimado = null;
    public ?float $correspondenciaScore = null;
    public ?string $correspondenciaNivelRelevancia = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = (int) ($data['id'] ?? 0);
        $item->favoritoId = (int) ($data['favorito_id'] ?? 0);
        $item->empresaId = (int) ($data['empresa_id'] ?? 0);
        $item->status = (string) ($data['status'] ?? 'RASCUNHO');
        $item->titulo = (string) ($data['titulo'] ?? '');
        $item->resumoExecutivo = isset($data['resumo_executivo']) ? (string) $data['resumo_executivo'] : null;
        $item->estrategiaProposta = isset($data['estrategia_proposta']) ? (string) $data['estrategia_proposta'] : null;
        $item->escopoEntrega = isset($data['escopo_entrega']) ? (string) $data['escopo_entrega'] : null;
        $item->diferenciais = isset($data['diferenciais']) ? (string) $data['diferenciais'] : null;
        $item->cronogramaMacro = isset($data['cronograma_macro']) ? (string) $data['cronograma_macro'] : null;
        $item->riscoPrincipal = isset($data['risco_principal']) ? (string) $data['risco_principal'] : null;
        $item->valorProposta = isset($data['valor_proposta']) ? (float) $data['valor_proposta'] : null;
        $item->observacoes = isset($data['observacoes']) ? (string) $data['observacoes'] : null;
        $item->geradaAutomatica = ((int) ($data['gerada_automatica'] ?? 1)) === 1;
        $item->criadoPorUsuarioId = isset($data['criado_por_usuario_id']) ? (int) $data['criado_por_usuario_id'] : null;
        $item->atualizadoPorUsuarioId = isset($data['atualizado_por_usuario_id']) ? (int) $data['atualizado_por_usuario_id'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $item->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        $item->favoritoStatusAcompanhamento = isset($data['favorito_status_acompanhamento'])
            ? (string) $data['favorito_status_acompanhamento']
            : null;
        $item->editalNumero = isset($data['edital_numero']) ? (string) $data['edital_numero'] : null;
        $item->editalOrgaoNome = isset($data['edital_orgao_nome']) ? (string) $data['edital_orgao_nome'] : null;
        $item->editalUf = isset($data['edital_uf']) ? (string) $data['edital_uf'] : null;
        $item->editalModalidade = isset($data['edital_modalidade']) ? (string) $data['edital_modalidade'] : null;
        $item->editalDataEncerramento = isset($data['edital_data_encerramento'])
            ? (string) $data['edital_data_encerramento']
            : null;
        $item->editalValorEstimado = isset($data['edital_valor_estimado']) ? (float) $data['edital_valor_estimado'] : null;
        $item->correspondenciaScore = isset($data['correspondencia_score']) ? (float) $data['correspondencia_score'] : null;
        $item->correspondenciaNivelRelevancia = isset($data['correspondencia_nivel_relevancia'])
            ? (string) $data['correspondencia_nivel_relevancia']
            : null;

        return $item;
    }
}
