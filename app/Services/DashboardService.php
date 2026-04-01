<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CorrespondenciaRepository;
use App\Repositories\FavoritoRepository;
use App\Repositories\FavoritoStatusHistoricoRepository;
use App\Repositories\FavoritoTarefaRepository;
use App\Repositories\PropostaExecucaoRepository;
use App\Repositories\PropostaResultadoRepository;
use Throwable;

class DashboardService
{
    private CorrespondenciaRepository $correspondenciaRepository;
    private FavoritoRepository $favoritoRepository;
    private FavoritoTarefaRepository $favoritoTarefaRepository;
    private FavoritoStatusHistoricoRepository $historicoRepository;
    private PropostaExecucaoRepository $propostaRepository;
    private PropostaResultadoRepository $propostaResultadoRepository;

    public function __construct(
        ?CorrespondenciaRepository $correspondenciaRepository = null,
        ?FavoritoRepository $favoritoRepository = null,
        ?FavoritoTarefaRepository $favoritoTarefaRepository = null,
        ?FavoritoStatusHistoricoRepository $historicoRepository = null,
        ?PropostaExecucaoRepository $propostaRepository = null,
        ?PropostaResultadoRepository $propostaResultadoRepository = null
    ) {
        $this->correspondenciaRepository = $correspondenciaRepository ?? new CorrespondenciaRepository();
        $this->favoritoRepository = $favoritoRepository ?? new FavoritoRepository();
        $this->favoritoTarefaRepository = $favoritoTarefaRepository ?? new FavoritoTarefaRepository();
        $this->historicoRepository = $historicoRepository ?? new FavoritoStatusHistoricoRepository();
        $this->propostaRepository = $propostaRepository ?? new PropostaExecucaoRepository();
        $this->propostaResultadoRepository = $propostaResultadoRepository ?? new PropostaResultadoRepository();
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
            $propostas = $this->propostaRepository->countByEmpresaGroupedStatus($empresaId);
            $propostasRascunho = $propostas['RASCUNHO'] ?? 0;
            $propostasRevisao = $propostas['EM_REVISAO'] ?? 0;
            $propostasAprovadas = $propostas['APROVADA'] ?? 0;
            $propostasEnviadas = $propostas['ENVIADA'] ?? 0;
        } catch (Throwable) {
            $propostasRascunho = 0;
            $propostasRevisao = 0;
            $propostasAprovadas = 0;
            $propostasEnviadas = 0;
        }
        try {
            $resultados = $this->propostaResultadoRepository->countByEmpresaGroupedSituacao($empresaId);
            $resultadosTotal = array_sum($resultados);
            $propostasVencedoras = $resultados['VENCEDORA'] ?? 0;
            $propostasNaoVencedoras = $resultados['NAO_VENCEDORA'] ?? 0;
            $propostasDesclassificadas = $resultados['DESCLASSIFICADA'] ?? 0;
        } catch (Throwable) {
            $resultadosTotal = 0;
            $propostasVencedoras = 0;
            $propostasNaoVencedoras = 0;
            $propostasDesclassificadas = 0;
        }
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
            'propostas_rascunho' => $propostasRascunho,
            'propostas_em_revisao' => $propostasRevisao,
            'propostas_aprovadas' => $propostasAprovadas,
            'propostas_enviadas' => $propostasEnviadas,
            'propostas_total' => $propostasRascunho + $propostasRevisao + $propostasAprovadas + $propostasEnviadas,
            'propostas_resultados_total' => $resultadosTotal,
            'propostas_vencedoras' => $propostasVencedoras,
            'propostas_nao_vencedoras' => $propostasNaoVencedoras,
            'propostas_desclassificadas' => $propostasDesclassificadas,
            'taxa_sucesso_propostas' => $propostasEnviadas > 0 ? round(($propostasVencedoras / $propostasEnviadas) * 100, 1) : 0.0,
            'tarefas_vencendo_48h' => $alertasVencendo,
            'tarefas_vencidas' => $alertasVencidas,
            'taxa_analise_para_proposta' => $taxaAnaliseParaProposta,
            'taxa_proposta_para_encerrado' => $taxaPropostaParaEncerrado,
            'taxa_decisao' => $oportunidades > 0 ? round(($pipeline / $oportunidades) * 100, 1) : 0.0,
            'taxa_conclusao' => $pipeline > 0 ? round(($encerrado / $pipeline) * 100, 1) : 0.0,
        ];
    }
}
