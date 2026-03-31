<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\DocumentoEditalRepository;
use App\Repositories\EditalRepository;
use App\Repositories\FonteColetaRepository;

class EditalService
{
    private EditalRepository $editalRepository;
    private DocumentoEditalRepository $documentoRepository;
    private FonteColetaRepository $fonteRepository;

    public function __construct(
        ?EditalRepository $editalRepository = null,
        ?DocumentoEditalRepository $documentoRepository = null,
        ?FonteColetaRepository $fonteRepository = null
    ) {
        $this->editalRepository = $editalRepository ?? new EditalRepository();
        $this->documentoRepository = $documentoRepository ?? new DocumentoEditalRepository();
        $this->fonteRepository = $fonteRepository ?? new FonteColetaRepository();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function detalhar(int $editalId): ?array
    {
        $edital = $this->editalRepository->findById($editalId);
        if ($edital === null) {
            return null;
        }

        $fonte = $this->fonteRepository->findById($edital->fonteId);
        $documentos = $this->documentoRepository->listByEdital($editalId);

        return [
            'edital' => $edital,
            'fonte' => $fonte,
            'documentos' => $documentos,
        ];
    }
}

