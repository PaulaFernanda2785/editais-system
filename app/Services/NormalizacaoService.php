<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;
use DateTimeZone;

class NormalizacaoService
{
    /**
     * @param array<string, mixed> $raw
     * @return array<string, mixed>
     */
    public function normalizarRegistro(array $raw, int $fonteId): array
    {
        $orgaoNome = $this->pickString($raw, [
            'orgao_nome',
            'orgaoEntidade.nome',
            'orgaoEntidade.razaoSocial',
            'nome_unidade',
            'orgao',
        ]);

        $objeto = $this->pickString($raw, [
            'objeto',
            'descricao_objeto',
            'informacaoComplementar',
            'descricao',
            'detalhe',
        ]);

        $numeroEdital = $this->pickString($raw, [
            'numero_edital',
            'numeroControlePNCP',
            'numero_controle',
            'numero',
        ]);

        $codigoFonte = $this->pickString($raw, [
            'codigo_fonte',
            'numeroControlePNCP',
            'id',
            'identificador',
        ]);

        $resultado = [
            'fonte_id' => $fonteId,
            'codigo_fonte' => $this->nullable($codigoFonte),
            'numero_edital' => $this->nullable($numeroEdital),
            'orgao_nome' => $orgaoNome,
            'orgao_poder' => $this->nullable($this->uppercase($this->pickString($raw, ['orgao_poder', 'poder']))),
            'esfera' => $this->normalizarEsfera($this->pickString($raw, ['esfera', 'nivel'])),
            'uf' => $this->normalizarUf($this->pickString($raw, ['uf', 'unidadeFederativa'])),
            'municipio' => $this->nullable($this->pickString($raw, ['municipio', 'cidade', 'nomeMunicipioIbge'])),
            'modalidade' => $this->nullable($this->pickString($raw, ['modalidade', 'modalidadeNome'])),
            'modo_disputa' => $this->nullable($this->pickString($raw, ['modo_disputa', 'modoDisputaNome'])),
            'objeto' => $objeto,
            'descricao_resumida' => $this->nullable($this->pickString($raw, ['descricao_resumida', 'resumo'])),
            'valor_estimado' => $this->normalizarNumero($raw['valor_estimado'] ?? $raw['valorTotalEstimado'] ?? null),
            'data_publicacao' => $this->normalizarData($raw['data_publicacao'] ?? $raw['dataPublicacaoPncp'] ?? null, false),
            'data_abertura' => $this->normalizarData($raw['data_abertura'] ?? $raw['dataAberturaProposta'] ?? null, true),
            'data_encerramento' => $this->normalizarData($raw['data_encerramento'] ?? $raw['dataEncerramentoProposta'] ?? null, true),
            'situacao' => $this->nullable($this->uppercase($this->pickString($raw, ['situacao', 'status']))),
            'link_detalhe' => $this->normalizarUrl($raw['link_detalhe'] ?? $raw['linkSistemaOrigem'] ?? null),
            'link_edital' => $this->normalizarUrl($raw['link_edital'] ?? null),
            'score_global' => 0.0,
        ];

        if (($resultado['descricao_resumida'] === null || $resultado['descricao_resumida'] === '') && $objeto !== '') {
            $resultado['descricao_resumida'] = $this->truncate($objeto, 220);
        }

        return $resultado;
    }

    /**
     * @param array<string, mixed> $source
     * @param array<int, string> $paths
     */
    private function pickString(array $source, array $paths): string
    {
        foreach ($paths as $path) {
            $value = $this->getByPath($source, $path);
            if ($value === null) {
                continue;
            }

            $string = trim((string) $value);
            if ($string !== '') {
                return $string;
            }
        }

        return '';
    }

    /**
     * @param array<string, mixed> $source
     */
    private function getByPath(array $source, string $path): mixed
    {
        if (!str_contains($path, '.')) {
            return $source[$path] ?? null;
        }

        $segments = explode('.', $path);
        $current = $source;

        foreach ($segments as $segment) {
            if (!is_array($current) || !array_key_exists($segment, $current)) {
                return null;
            }
            $current = $current[$segment];
        }

        return $current;
    }

    private function nullable(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function uppercase(string $value): string
    {
        $value = trim($value);
        return $value === '' ? '' : strtoupper($value);
    }

    private function normalizarUf(string $uf): ?string
    {
        $uf = strtoupper(trim($uf));
        if ($uf === '' || strlen($uf) !== 2) {
            return null;
        }

        return $uf;
    }

    private function normalizarEsfera(string $value): ?string
    {
        $value = strtoupper(trim($value));
        if ($value === '') {
            return null;
        }

        return in_array($value, ['FEDERAL', 'ESTADUAL', 'MUNICIPAL', 'OUTRA'], true)
            ? $value
            : 'OUTRA';
    }

    private function normalizarNumero(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $raw = str_replace(['.', ','], ['', '.'], (string) $value);
        if (!is_numeric($raw)) {
            return null;
        }

        return (float) $raw;
    }

    private function normalizarData(mixed $value, bool $comHora): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            $date = new DateTimeImmutable((string) $value, new DateTimeZone(date_default_timezone_get()));
        } catch (\Throwable) {
            return null;
        }

        return $comHora ? $date->format('Y-m-d H:i:s') : $date->format('Y-m-d');
    }

    private function normalizarUrl(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $url = trim((string) $value);
        if ($url === '') {
            return null;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    private function truncate(string $value, int $limit): string
    {
        if ($limit < 1 || strlen($value) <= $limit) {
            return $value;
        }

        return substr($value, 0, $limit);
    }
}

