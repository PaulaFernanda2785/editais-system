<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CorrespondenciaRepository;
use App\Repositories\FavoritoRepository;
use App\Repositories\FavoritoStatusHistoricoRepository;
use App\Repositories\FavoritoTarefaRepository;
use Throwable;

class DashboardService
{
    private CorrespondenciaRepository $correspondenciaRepository;
    private FavoritoRepository $favoritoRepository;
    private FavoritoTarefaRepository $favoritoTarefaRepository;
    private FavoritoStatusHistoricoRepository $historicoRepository;

    public function __construct(
        ?CorrespondenciaRepository $correspondenciaRepository = null,
        ?FavoritoRepository $favoritoRepository = null,
        ?FavoritoTarefaRepository $favoritoTarefaRepository = null,
        ?FavoritoStatusHistoricoRepository $historicoRepository = null
    ) {
        $this->correspondenciaRepository = $correspondenciaRepository ?? new CorrespondenciaRepository();
        $this->favoritoRepository = $favoritoRepository ?? new FavoritoRepository();
        $this->favoritoTarefaRepository = $favoritoTarefaRepository ?? new FavoritoTarefaRepository();
        $this->historicoRepository = $historicoRepository ?? new FavoritoStatusHistoricoRepository();
    }

    /**
     * @return array<string, int|float>
     */
    public function resumoEmpresa(int $empresaId): array
    {
        $oportunidades = $this->correspondenciaRepository->countByEmpresa($empresaId);
        $pipeline = $this->favoritoRepository->countByEmpresa($empresaId);
        $grouped = $this->favoritoRepository->countByEmpresaGroupedStatus($empresaId);

        $emAnalise = $grouped['EM_ANALISE'] ?? 0;
        $proposta = $grouped['PROPOSTA'] ?? 0;
        $descartado = $grouped['DESCARTADO'] ?? 0;
        $encerrado = $grouped['ENCERRADO'] ?? 0;
        $favorito = $grouped['FAVORITO'] ?? 0;
        try {
            $alertasVencendo = $this->favoritoTarefaRepository->countAlertasVencendo($empresaId, 2);
            $alertasVencidas = $this->favoritoTarefaRepository->countAlertasVencidas($empresaId);
        } catch (Throwable) {
            $alertasVencendo = 0;
            $alertasVencidas = 0;
        }

        try {
            $conversao = $this->historicoRepository->relatorioConversao($empresaId, null, null);
            $taxaAnaliseParaProposta = isset($conversao['taxas']['analise_para_proposta'])
                ? (float) $conversao['taxas']['analise_para_proposta']
                : 0.0;
            $taxaPropostaParaEncerrado = isset($conversao['taxas']['proposta_para_encerrado'])
                ? (float) $conversao['taxas']['proposta_para_encerrado']
                : 0.0;
        } catch (Throwable) {
            $taxaAnaliseParaProposta = 0.0;
            $taxaPropostaParaEncerrado = 0.0;
        }

        return [
            'oportunidades_total' => $oportunidades,
            'pipeline_total' => $pipeline,
            'favorito' => $favorito,
            'em_analise' => $emAnalise,
            'proposta' => $proposta,
            'descartado' => $descartado,
            'encerrado' => $encerrado,
            'tarefas_vencendo_48h' => $alertasVencendo,
            'tarefas_vencidas' => $alertasVencidas,
            'taxa_analise_para_proposta' => $taxaAnaliseParaProposta,
            'taxa_proposta_para_encerrado' => $taxaPropostaParaEncerrado,
            'taxa_decisao' => $oportunidades > 0 ? round(($pipeline / $oportunidades) * 100, 1) : 0.0,
            'taxa_conclusao' => $pipeline > 0 ? round(($encerrado / $pipeline) * 100, 1) : 0.0,
        ];
    }
}
