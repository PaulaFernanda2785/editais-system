<?php

declare(strict_types=1);

namespace App\Models;

class FonteColeta
{
    public int $id = 0;
    public string $nome = '';
    public string $codigo = '';
    public string $tipo = 'API';
    public ?string $urlBase = null;
    public bool $ativa = true;
    public int $periodicidadeMinutos = 60;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $configuracao = null;

    public ?string $ultimaExecucaoEm = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public int $totalExecucoes = 0;
    public int $totalSucesso = 0;
    public int $totalFalhas = 0;
    public ?string $ultimaExecucaoStatus = null;

    public static function fromArray(array $data): self
    {
        $fonte = new self();
        $fonte->id = (int) ($data['id'] ?? 0);
        $fonte->nome = (string) ($data['nome'] ?? '');
        $fonte->codigo = (string) ($data['codigo'] ?? '');
        $fonte->tipo = (string) ($data['tipo'] ?? 'API');
        $fonte->urlBase = isset($data['url_base']) ? (string) $data['url_base'] : null;
        $fonte->ativa = ((int) ($data['ativa'] ?? 1)) === 1;
        $fonte->periodicidadeMinutos = (int) ($data['periodicidade_minutos'] ?? 60);
        $fonte->configuracao = self::decodeJsonObject($data['configuracao_json'] ?? null);
        $fonte->ultimaExecucaoEm = isset($data['ultima_execucao_em']) ? (string) $data['ultima_execucao_em'] : null;
        $fonte->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $fonte->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;
        $fonte->totalExecucoes = (int) ($data['total_execucoes'] ?? 0);
        $fonte->totalSucesso = (int) ($data['total_sucesso'] ?? 0);
        $fonte->totalFalhas = (int) ($data['total_falhas'] ?? 0);
        $fonte->ultimaExecucaoStatus = isset($data['ultima_execucao_status']) ? (string) $data['ultima_execucao_status'] : null;

        return $fonte;
    }

    private static function decodeJsonObject(mixed $value): ?array
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
        if (!is_array($decoded)) {
            return null;
        }

        return $decoded;
    }
}
