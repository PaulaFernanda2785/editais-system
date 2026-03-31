<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PalavraChave;
use App\Repositories\PalavraChaveRepository;
use App\Repositories\PerfilMonitoramentoRepository;

class PalavraChaveService
{
    private PalavraChaveRepository $palavraRepository;
    private PerfilMonitoramentoRepository $perfilRepository;
    private AssinaturaService $assinaturaService;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?PalavraChaveRepository $palavraRepository = null,
        ?PerfilMonitoramentoRepository $perfilRepository = null,
        ?AssinaturaService $assinaturaService = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->palavraRepository = $palavraRepository ?? new PalavraChaveRepository();
        $this->perfilRepository = $perfilRepository ?? new PerfilMonitoramentoRepository();
        $this->assinaturaService = $assinaturaService ?? new AssinaturaService();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @return array<int, PalavraChave>
     */
    public function listarPorPerfil(int $empresaId, int $perfilId): array
    {
        return $this->palavraRepository->listByEmpresaAndPerfil($empresaId, $perfilId);
    }

    public function criar(int $empresaId, ?int $usuarioId, int $perfilId, array $payload): array
    {
        if (!$this->perfilExisteNaEmpresa($empresaId, $perfilId)) {
            return [
                'sucesso' => false,
                'mensagem' => 'Perfil de monitoramento nao encontrado para incluir palavra-chave.',
                'palavra' => null,
            ];
        }

        $limite = $this->validarLimitePalavras($empresaId);
        if ($limite['permitido'] !== true) {
            return [
                'sucesso' => false,
                'mensagem' => $limite['mensagem'],
                'palavra' => null,
            ];
        }

        $termo = (string) $payload['termo'];
        $existente = $this->palavraRepository->findByTermoEmpresaAndPerfil($termo, $empresaId, $perfilId);
        if ($existente !== null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Ja existe palavra-chave com esse termo para o perfil.',
                'palavra' => null,
            ];
        }

        $palavra = $this->palavraRepository->create([
            'empresa_id' => $empresaId,
            'perfil_monitoramento_id' => $perfilId,
            'termo' => $termo,
            'peso' => (int) $payload['peso'],
            'categoria' => $payload['categoria'] ?? null,
            'ativo' => 1,
        ]);

        $this->logService->info('monitoramento.palavra.create', 'Palavra-chave criada.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'palavra_id' => $palavra->id,
            'termo' => $palavra->termo,
        ]);

        $this->auditService->record(
            'PALAVRA_CHAVE_CRIADA',
            'palavras_chave',
            $palavra->id,
            [
                'perfil_monitoramento_id' => $perfilId,
                'termo' => $palavra->termo,
                'peso' => $palavra->peso,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Palavra-chave criada com sucesso.',
            'palavra' => $palavra,
        ];
    }

    public function atualizar(
        int $empresaId,
        ?int $usuarioId,
        int $perfilId,
        int $palavraId,
        array $payload
    ): array {
        $palavraAnterior = $this->palavraRepository->findByIdEmpresaAndPerfil(
            $palavraId,
            $empresaId,
            $perfilId
        );

        if ($palavraAnterior === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Palavra-chave nao encontrada.',
                'palavra' => null,
            ];
        }

        $termo = (string) $payload['termo'];
        $duplicada = $this->palavraRepository->findByTermoEmpresaAndPerfil($termo, $empresaId, $perfilId);
        if ($duplicada !== null && $duplicada->id !== $palavraId) {
            return [
                'sucesso' => false,
                'mensagem' => 'Ja existe outra palavra-chave com esse termo.',
                'palavra' => null,
            ];
        }

        $this->palavraRepository->update($palavraId, $empresaId, $perfilId, [
            'termo' => $termo,
            'peso' => (int) $payload['peso'],
            'categoria' => $payload['categoria'] ?? null,
        ]);

        $palavraAtualizada = $this->palavraRepository->findByIdEmpresaAndPerfil(
            $palavraId,
            $empresaId,
            $perfilId
        ) ?? $palavraAnterior;

        $this->logService->info('monitoramento.palavra.update', 'Palavra-chave atualizada.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'palavra_id' => $palavraId,
        ]);

        $this->auditService->record(
            'PALAVRA_CHAVE_ATUALIZADA',
            'palavras_chave',
            $palavraId,
            [
                'termo_antigo' => $palavraAnterior->termo,
                'termo_novo' => $palavraAtualizada->termo,
                'peso' => $palavraAtualizada->peso,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Palavra-chave atualizada com sucesso.',
            'palavra' => $palavraAtualizada,
        ];
    }

    public function alternarAtivo(
        int $empresaId,
        ?int $usuarioId,
        int $perfilId,
        int $palavraId
    ): array {
        $palavra = $this->palavraRepository->findByIdEmpresaAndPerfil($palavraId, $empresaId, $perfilId);
        if ($palavra === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Palavra-chave nao encontrada.',
                'palavra' => null,
            ];
        }

        $novoAtivo = !$palavra->ativo;
        $this->palavraRepository->setAtivo($palavraId, $empresaId, $perfilId, $novoAtivo);

        $palavraAtualizada = $this->palavraRepository->findByIdEmpresaAndPerfil(
            $palavraId,
            $empresaId,
            $perfilId
        ) ?? $palavra;

        $this->logService->info('monitoramento.palavra.toggle', 'Status da palavra-chave alterado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'palavra_id' => $palavraId,
            'ativo' => $palavraAtualizada->ativo ? 1 : 0,
        ]);

        $this->auditService->record(
            'PALAVRA_CHAVE_STATUS_ALTERADO',
            'palavras_chave',
            $palavraId,
            [
                'perfil_monitoramento_id' => $perfilId,
                'ativo' => $palavraAtualizada->ativo ? 1 : 0,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => $palavraAtualizada->ativo
                ? 'Palavra-chave ativada.'
                : 'Palavra-chave inativada.',
            'palavra' => $palavraAtualizada,
        ];
    }

    public function excluir(int $empresaId, ?int $usuarioId, int $perfilId, int $palavraId): array
    {
        $palavra = $this->palavraRepository->findByIdEmpresaAndPerfil($palavraId, $empresaId, $perfilId);
        if ($palavra === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Palavra-chave nao encontrada.',
            ];
        }

        $this->palavraRepository->delete($palavraId, $empresaId, $perfilId);

        $this->logService->warning('monitoramento.palavra.delete', 'Palavra-chave removida.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'palavra_id' => $palavraId,
            'termo' => $palavra->termo,
        ]);

        $this->auditService->record(
            'PALAVRA_CHAVE_EXCLUIDA',
            'palavras_chave',
            $palavraId,
            [
                'perfil_monitoramento_id' => $perfilId,
                'termo' => $palavra->termo,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Palavra-chave excluida com sucesso.',
        ];
    }

    private function perfilExisteNaEmpresa(int $empresaId, int $perfilId): bool
    {
        return $this->perfilRepository->findByIdAndEmpresa($perfilId, $empresaId) !== null;
    }

    private function validarLimitePalavras(int $empresaId): array
    {
        $statusAssinatura = $this->assinaturaService->verificarAcessoPorEmpresa($empresaId);
        if ($statusAssinatura['permitido'] !== true || !isset($statusAssinatura['assinatura'])) {
            return [
                'permitido' => false,
                'mensagem' => 'Nao foi possivel validar assinatura ativa para criar palavra-chave.',
            ];
        }

        $assinatura = $statusAssinatura['assinatura'];
        $limite = (int) ($assinatura->plano?->limitePalavrasChave ?? 0);
        if ($limite <= 0) {
            return [
                'permitido' => true,
                'mensagem' => null,
            ];
        }

        $atual = $this->palavraRepository->countByEmpresa($empresaId);
        if ($atual >= $limite) {
            return [
                'permitido' => false,
                'mensagem' => sprintf(
                    'Limite de palavras-chave do plano atingido (%d).',
                    $limite
                ),
            ];
        }

        return [
            'permitido' => true,
            'mensagem' => null,
        ];
    }
}
