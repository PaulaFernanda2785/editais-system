<?php

declare(strict_types=1);

namespace App\Models;

class Edital
{
    public int $id = 0;
    public int $fonteId = 0;
    public ?string $codigoFonte = null;
    public ?string $numeroEdital = null;
    public string $orgaoNome = '';
    public ?string $orgaoPoder = null;
    public ?string $esfera = null;
    public ?string $uf = null;
    public ?string $municipio = null;
    public ?string $modalidade = null;
    public ?string $modoDisputa = null;
    public string $objeto = '';
    public ?string $descricaoResumida = null;
    public ?float $valorEstimado = null;
    public ?string $dataPublicacao = null;
    public ?string $dataAbertura = null;
    public ?string $dataEncerramento = null;
    public ?string $situacao = null;
    public ?string $linkDetalhe = null;
    public ?string $linkEdital = null;
    public string $hashUnico = '';
    public float $scoreGlobal = 0.0;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $edital = new self();
        $edital->id = (int) ($data['id'] ?? 0);
        $edital->fonteId = (int) ($data['fonte_id'] ?? 0);
        $edital->codigoFonte = isset($data['codigo_fonte']) ? (string) $data['codigo_fonte'] : null;
        $edital->numeroEdital = isset($data['numero_edital']) ? (string) $data['numero_edital'] : null;
        $edital->orgaoNome = (string) ($data['orgao_nome'] ?? '');
        $edital->orgaoPoder = isset($data['orgao_poder']) ? (string) $data['orgao_poder'] : null;
        $edital->esfera = isset($data['esfera']) ? (string) $data['esfera'] : null;
        $edital->uf = isset($data['uf']) ? (string) $data['uf'] : null;
        $edital->municipio = isset($data['municipio']) ? (string) $data['municipio'] : null;
        $edital->modalidade = isset($data['modalidade']) ? (string) $data['modalidade'] : null;
        $edital->modoDisputa = isset($data['modo_disputa']) ? (string) $data['modo_disputa'] : null;
        $edital->objeto = (string) ($data['objeto'] ?? '');
        $edital->descricaoResumida = isset($data['descricao_resumida']) ? (string) $data['descricao_resumida'] : null;
        $edital->valorEstimado = isset($data['valor_estimado']) ? (float) $data['valor_estimado'] : null;
        $edital->dataPublicacao = isset($data['data_publicacao']) ? (string) $data['data_publicacao'] : null;
        $edital->dataAbertura = isset($data['data_abertura']) ? (string) $data['data_abertura'] : null;
        $edital->dataEncerramento = isset($data['data_encerramento']) ? (string) $data['data_encerramento'] : null;
        $edital->situacao = isset($data['situacao']) ? (string) $data['situacao'] : null;
        $edital->linkDetalhe = isset($data['link_detalhe']) ? (string) $data['link_detalhe'] : null;
        $edital->linkEdital = isset($data['link_edital']) ? (string) $data['link_edital'] : null;
        $edital->hashUnico = (string) ($data['hash_unico'] ?? '');
        $edital->scoreGlobal = isset($data['score_global']) ? (float) $data['score_global'] : 0.0;
        $edital->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $edital->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $edital;
    }
}

