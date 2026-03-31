<?php

declare(strict_types=1);

namespace App\Models;

class Plano
{
    public int $id = 0;
    public string $nome = '';
    public ?string $descricao = null;
    public int $limiteUsuarios = 1;
    public int $limitePalavrasChave = 10;
    public int $limitePerfisMonitoramento = 1;
    public int $limiteAlertasDia = 1;
    public int $limiteExportacoesMes = 5;
    public float $valorMensal = 0.0;
    public string $status = 'ATIVO';

    public static function fromArray(array $data): self
    {
        $plano = new self();
        $plano->id = (int) ($data['id'] ?? 0);
        $plano->nome = (string) ($data['nome'] ?? '');
        $plano->descricao = isset($data['descricao']) ? (string) $data['descricao'] : null;
        $plano->limiteUsuarios = (int) ($data['limite_usuarios'] ?? 1);
        $plano->limitePalavrasChave = (int) ($data['limite_palavras_chave'] ?? 10);
        $plano->limitePerfisMonitoramento = (int) ($data['limite_perfis_monitoramento'] ?? 1);
        $plano->limiteAlertasDia = (int) ($data['limite_alertas_dia'] ?? 1);
        $plano->limiteExportacoesMes = (int) ($data['limite_exportacoes_mes'] ?? 5);
        $plano->valorMensal = (float) ($data['valor_mensal'] ?? 0.0);
        $plano->status = (string) ($data['status'] ?? 'ATIVO');

        return $plano;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'limite_usuarios' => $this->limiteUsuarios,
            'limite_palavras_chave' => $this->limitePalavrasChave,
            'limite_perfis_monitoramento' => $this->limitePerfisMonitoramento,
            'limite_alertas_dia' => $this->limiteAlertasDia,
            'limite_exportacoes_mes' => $this->limiteExportacoesMes,
            'valor_mensal' => $this->valorMensal,
            'status' => $this->status,
        ];
    }
}
