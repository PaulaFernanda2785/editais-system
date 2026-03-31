<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\ColetaService;

class ColetarPncpJob
{
    private ColetaService $coletaService;

    public function __construct(?ColetaService $coletaService = null)
    {
        $this->coletaService = $coletaService ?? new ColetaService();
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(int $limit = 100): array
    {
        return $this->coletaService->executarPncp(null, null, $limit);
    }
}

