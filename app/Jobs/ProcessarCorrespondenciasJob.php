<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\CorrespondenciaService;

class ProcessarCorrespondenciasJob
{
    private CorrespondenciaService $correspondenciaService;

    public function __construct(?CorrespondenciaService $correspondenciaService = null)
    {
        $this->correspondenciaService = $correspondenciaService ?? new CorrespondenciaService();
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(int $empresaId, ?int $usuarioId = null, int $limiteEditais = 800): array
    {
        return $this->correspondenciaService->processarEmpresa($empresaId, $usuarioId, $limiteEditais);
    }
}
