<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CorrespondenciaRepository;
use App\Repositories\FavoritoRepository;

class DashboardService
{
    private CorrespondenciaRepository $correspondenciaRepository;
    private FavoritoRepository $favoritoRepository;

    public function __construct(
        ?CorrespondenciaRepository $correspondenciaRepository = null,
        ?FavoritoRepository $favoritoRepository = null
    ) {
        $this->correspondenciaRepository = $correspondenciaRepository ?? new CorrespondenciaRepository();
        $this->favoritoRepository = $favoritoRepository ?? new FavoritoRepository();
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

        return [
            'oportunidades_total' => $oportunidades,
            'pipeline_total' => $pipeline,
            'favorito' => $favorito,
            'em_analise' => $emAnalise,
            'proposta' => $proposta,
            'descartado' => $descartado,
            'encerrado' => $encerrado,
            'taxa_decisao' => $oportunidades > 0 ? round(($pipeline / $oportunidades) * 100, 1) : 0.0,
            'taxa_conclusao' => $pipeline > 0 ? round(($encerrado / $pipeline) * 100, 1) : 0.0,
        ];
    }
}
