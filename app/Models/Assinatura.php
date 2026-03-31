<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

class Assinatura
{
    public int $id = 0;
    public int $empresaId = 0;
    public int $planoId = 0;
    public string $status = 'PENDENTE';
    public string $dataInicio = '';
    public ?string $dataFim = null;
    public ?string $gatewayReferencia = null;
    public ?string $observacao = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public ?Plano $plano = null;

    public static function fromArray(array $data): self
    {
        $assinatura = new self();
        $assinatura->id = (int) ($data['id'] ?? 0);
        $assinatura->empresaId = (int) ($data['empresa_id'] ?? 0);
        $assinatura->planoId = (int) ($data['plano_id'] ?? 0);
        $assinatura->status = (string) ($data['status'] ?? 'PENDENTE');
        $assinatura->dataInicio = (string) ($data['data_inicio'] ?? '');
        $assinatura->dataFim = isset($data['data_fim']) ? (string) $data['data_fim'] : null;
        $assinatura->gatewayReferencia = isset($data['gateway_referencia']) ? (string) $data['gateway_referencia'] : null;
        $assinatura->observacao = isset($data['observacao']) ? (string) $data['observacao'] : null;
        $assinatura->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $assinatura->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        if (isset($data['plano_nome'])) {
            $assinatura->plano = Plano::fromArray([
                'id' => $assinatura->planoId,
                'nome' => $data['plano_nome'],
                'descricao' => $data['plano_descricao'] ?? null,
                'limite_usuarios' => $data['plano_limite_usuarios'] ?? null,
                'limite_palavras_chave' => $data['plano_limite_palavras_chave'] ?? null,
                'limite_perfis_monitoramento' => $data['plano_limite_perfis_monitoramento'] ?? null,
                'limite_alertas_dia' => $data['plano_limite_alertas_dia'] ?? null,
                'limite_exportacoes_mes' => $data['plano_limite_exportacoes_mes'] ?? null,
                'valor_mensal' => $data['plano_valor_mensal'] ?? null,
                'status' => $data['plano_status'] ?? 'ATIVO',
            ]);
        }

        return $assinatura;
    }

    public function ativaParaAcesso(): bool
    {
        if (!in_array($this->status, ['ATIVA', 'TESTE'], true)) {
            return false;
        }

        if ($this->dataFim === null || $this->dataFim === '') {
            return true;
        }

        $fim = DateTimeImmutable::createFromFormat('Y-m-d', substr($this->dataFim, 0, 10));
        if (!$fim) {
            return false;
        }

        $hoje = new DateTimeImmutable('today');
        return $fim >= $hoje;
    }

    public function toSessionArray(): array
    {
        return [
            'id' => $this->id,
            'empresa_id' => $this->empresaId,
            'plano_id' => $this->planoId,
            'plano_nome' => $this->plano?->nome,
            'status' => $this->status,
            'data_inicio' => $this->dataInicio,
            'data_fim' => $this->dataFim,
            'gateway_referencia' => $this->gatewayReferencia,
            'valor_mensal' => $this->plano?->valorMensal,
        ];
    }
}
