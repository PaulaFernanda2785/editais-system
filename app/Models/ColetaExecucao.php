<?php

declare(strict_types=1);

namespace App\Models;

class ColetaExecucao
{
    public int $id = 0;
    public int $fonteId = 0;
    public string $iniciadoEm = '';
    public ?string $finalizadoEm = null;
    public string $status = 'PROCESSANDO';
    public int $totalLidos = 0;
    public int $totalInseridos = 0;
    public int $totalAtualizados = 0;
    public int $totalDuplicados = 0;
    public int $totalErros = 0;
    public ?string $mensagemResumo = null;
    public ?string $logDetalhado = null;
    public ?string $criadoEm = null;

    public static function fromArray(array $data): self
    {
        $execucao = new self();
        $execucao->id = (int) ($data['id'] ?? 0);
        $execucao->fonteId = (int) ($data['fonte_id'] ?? 0);
        $execucao->iniciadoEm = (string) ($data['iniciado_em'] ?? '');
        $execucao->finalizadoEm = isset($data['finalizado_em']) ? (string) $data['finalizado_em'] : null;
        $execucao->status = (string) ($data['status'] ?? 'PROCESSANDO');
        $execucao->totalLidos = (int) ($data['total_lidos'] ?? 0);
        $execucao->totalInseridos = (int) ($data['total_inseridos'] ?? 0);
        $execucao->totalAtualizados = (int) ($data['total_atualizados'] ?? 0);
        $execucao->totalDuplicados = (int) ($data['total_duplicados'] ?? 0);
        $execucao->totalErros = (int) ($data['total_erros'] ?? 0);
        $execucao->mensagemResumo = isset($data['mensagem_resumo']) ? (string) $data['mensagem_resumo'] : null;
        $execucao->logDetalhado = isset($data['log_detalhado']) ? (string) $data['log_detalhado'] : null;
        $execucao->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;

        return $execucao;
    }
}
