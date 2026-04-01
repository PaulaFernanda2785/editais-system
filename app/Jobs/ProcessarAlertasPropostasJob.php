<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\PropostaAlertaNotificacaoService;

class ProcessarAlertasPropostasJob
{
    private PropostaAlertaNotificacaoService $service;

    public function __construct(?PropostaAlertaNotificacaoService $service = null)
    {
        $this->service = $service ?? new PropostaAlertaNotificacaoService();
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(int $empresaId, bool $enviarEmail = true): array
    {
        return $this->service->processarEmpresa($empresaId, $enviarEmail);
    }
}
