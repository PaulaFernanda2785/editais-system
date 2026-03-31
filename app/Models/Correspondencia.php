<?php

declare(strict_types=1);

namespace App\Models;

class Correspondencia
{
    public int $id = 0;
    public int $editalId = 0;
    public int $empresaId = 0;
    public ?int $perfilMonitoramentoId = null;
    public float $score = 0.0;
    public string $nivelRelevancia = 'BAIXA';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $motivo = null;

    public ?string $alertadoEm = null;
    public ?string $criadoEm = null;

    public ?string $perfilNome = null;
    public ?string $editalNumero = null;
    public ?string $editalOrgaoNome = null;
    public ?string $editalUf = null;
    public ?string $editalModalidade = null;
    public ?string $editalDataPublicacao = null;
    public ?float $editalValorEstimado = null;
    public ?string $editalLinkDetalhe = null;
    public ?string $editalLinkEdital = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = (int) ($data['id'] ?? 0);
        $item->editalId = (int) ($data['edital_id'] ?? 0);
        $item->empresaId = (int) ($data['empresa_id'] ?? 0);
        $item->perfilMonitoramentoId = isset($data['perfil_monitoramento_id'])
            ? (int) $data['perfil_monitoramento_id']
            : null;
        $item->score = isset($data['score']) ? (float) $data['score'] : 0.0;
        $item->nivelRelevancia = (string) ($data['nivel_relevancia'] ?? 'BAIXA');
        $item->motivo = self::decodeJson($data['motivo_json'] ?? null);
        $item->alertadoEm = isset($data['alertado_em']) ? (string) $data['alertado_em'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $item->perfilNome = isset($data['perfil_nome']) ? (string) $data['perfil_nome'] : null;
        $item->editalNumero = isset($data['edital_numero']) ? (string) $data['edital_numero'] : null;
        $item->editalOrgaoNome = isset($data['edital_orgao_nome']) ? (string) $data['edital_orgao_nome'] : null;
        $item->editalUf = isset($data['edital_uf']) ? (string) $data['edital_uf'] : null;
        $item->editalModalidade = isset($data['edital_modalidade']) ? (string) $data['edital_modalidade'] : null;
        $item->editalDataPublicacao = isset($data['edital_data_publicacao']) ? (string) $data['edital_data_publicacao'] : null;
        $item->editalValorEstimado = isset($data['edital_valor_estimado']) ? (float) $data['edital_valor_estimado'] : null;
        $item->editalLinkDetalhe = isset($data['edital_link_detalhe']) ? (string) $data['edital_link_detalhe'] : null;
        $item->editalLinkEdital = isset($data['edital_link_edital']) ? (string) $data['edital_link_edital'] : null;

        return $item;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function decodeJson(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : null;
    }
}

