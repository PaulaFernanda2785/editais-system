<?php

declare(strict_types=1);

namespace App\Models;

class PerfilMonitoramento
{
    public int $id = 0;
    public int $empresaId = 0;
    public string $nome = '';

    /**
     * @var array<int, string>
     */
    public array $ufs = [];

    /**
     * @var array<int, string>
     */
    public array $modalidades = [];

    /**
     * @var array<int, string>
     */
    public array $orgaos = [];

    public ?float $faixaValorMin = null;
    public ?float $faixaValorMax = null;
    public string $frequenciaAlerta = 'DIARIO';
    public bool $ativo = true;
    public int $totalPalavras = 0;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $perfil = new self();
        $perfil->id = (int) ($data['id'] ?? 0);
        $perfil->empresaId = (int) ($data['empresa_id'] ?? 0);
        $perfil->nome = (string) ($data['nome'] ?? '');
        $perfil->ufs = self::decodeJsonArray($data['ufs_json'] ?? null);
        $perfil->modalidades = self::decodeJsonArray($data['modalidades_json'] ?? null);
        $perfil->orgaos = self::decodeJsonArray($data['orgaos_json'] ?? null);
        $perfil->faixaValorMin = isset($data['faixa_valor_min']) ? (float) $data['faixa_valor_min'] : null;
        $perfil->faixaValorMax = isset($data['faixa_valor_max']) ? (float) $data['faixa_valor_max'] : null;
        $perfil->frequenciaAlerta = (string) ($data['frequencia_alerta'] ?? 'DIARIO');
        $perfil->ativo = ((int) ($data['ativo'] ?? 1)) === 1;
        $perfil->totalPalavras = (int) ($data['total_palavras'] ?? 0);
        $perfil->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $perfil->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $perfil;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresaId,
            'nome' => $this->nome,
            'ufs_json' => $this->ufs,
            'modalidades_json' => $this->modalidades,
            'orgaos_json' => $this->orgaos,
            'faixa_valor_min' => $this->faixaValorMin,
            'faixa_valor_max' => $this->faixaValorMax,
            'frequencia_alerta' => $this->frequenciaAlerta,
            'ativo' => $this->ativo ? 1 : 0,
            'total_palavras' => $this->totalPalavras,
        ];
    }

    private static function decodeJsonArray(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(
                array_filter(
                    array_map(static fn(mixed $item): string => trim((string) $item), $value),
                    static fn(string $item): bool => $item !== ''
                )
            );
        }

        if (!is_string($value)) {
            return [];
        }

        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(
            array_filter(
                array_map(static fn(mixed $item): string => trim((string) $item), $decoded),
                static fn(string $item): bool => $item !== ''
            )
        );
    }
}
