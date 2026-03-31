<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PerfilMonitoramento;
use App\Repositories\PerfilMonitoramentoRepository;

class PerfilMonitoramentoService
{
    private PerfilMonitoramentoRepository $perfilRepository;
    private AssinaturaService $assinaturaService;
    private LogService $logService;
    private AuditService $auditService;

    public function __construct(
        ?PerfilMonitoramentoRepository $perfilRepository = null,
        ?AssinaturaService $assinaturaService = null,
        ?LogService $logService = null,
        ?AuditService $auditService = null
    ) {
        $this->perfilRepository = $perfilRepository ?? new PerfilMonitoramentoRepository();
        $this->assinaturaService = $assinaturaService ?? new AssinaturaService();
        $this->logService = $logService ?? new LogService();
        $this->auditService = $auditService ?? new AuditService($this->logService);
    }

    /**
     * @return array<int, PerfilMonitoramento>
     */
    public function listarPorEmpresa(int $empresaId): array
    {
        return $this->perfilRepository->listByEmpresa($empresaId);
    }

    public function buscarPorId(int $empresaId, int $perfilId): ?PerfilMonitoramento
    {
        return $this->perfilRepository->findByIdAndEmpresa($perfilId, $empresaId);
    }

    public function criar(int $empresaId, ?int $usuarioId, array $payload): array
    {
        $limite = $this->validarLimitePerfis($empresaId);
        if ($limite['permitido'] !== true) {
            return [
                'sucesso' => false,
                'mensagem' => $limite['mensagem'],
                'perfil' => null,
            ];
        }

        $perfil = $this->perfilRepository->create([
            'empresa_id' => $empresaId,
            'nome' => (string) $payload['nome'],
            'ufs_json' => $payload['ufs_json'] ?? [],
            'modalidades_json' => $payload['modalidades_json'] ?? [],
            'orgaos_json' => $payload['orgaos_json'] ?? [],
            'faixa_valor_min' => $payload['faixa_valor_min'] ?? null,
            'faixa_valor_max' => $payload['faixa_valor_max'] ?? null,
            'frequencia_alerta' => (string) $payload['frequencia_alerta'],
            'ativo' => isset($payload['ativo']) ? (int) $payload['ativo'] : 1,
        ]);

        $this->logService->info('monitoramento.perfil.create', 'Perfil de monitoramento criado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfil->id,
            'nome' => $perfil->nome,
        ]);

        $this->auditService->record(
            'PERFIL_MONITORAMENTO_CRIADO',
            'perfis_monitoramento',
            $perfil->id,
            [
                'nome' => $perfil->nome,
                'frequencia_alerta' => $perfil->frequenciaAlerta,
                'ativo' => $perfil->ativo ? 1 : 0,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Perfil de monitoramento criado com sucesso.',
            'perfil' => $perfil,
        ];
    }

    public function atualizar(int $empresaId, ?int $usuarioId, int $perfilId, array $payload): array
    {
        $perfilAnterior = $this->perfilRepository->findByIdAndEmpresa($perfilId, $empresaId);
        if ($perfilAnterior === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Perfil de monitoramento nao encontrado.',
                'perfil' => null,
            ];
        }

        $this->perfilRepository->update($perfilId, $empresaId, [
            'nome' => (string) $payload['nome'],
            'ufs_json' => $payload['ufs_json'] ?? [],
            'modalidades_json' => $payload['modalidades_json'] ?? [],
            'orgaos_json' => $payload['orgaos_json'] ?? [],
            'faixa_valor_min' => $payload['faixa_valor_min'] ?? null,
            'faixa_valor_max' => $payload['faixa_valor_max'] ?? null,
            'frequencia_alerta' => (string) $payload['frequencia_alerta'],
        ]);

        $perfilAtualizado = $this->perfilRepository->findByIdAndEmpresa($perfilId, $empresaId) ?? $perfilAnterior;

        $this->logService->info('monitoramento.perfil.update', 'Perfil de monitoramento atualizado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
        ]);

        $this->auditService->record(
            'PERFIL_MONITORAMENTO_ATUALIZADO',
            'perfis_monitoramento',
            $perfilId,
            [
                'nome_antigo' => $perfilAnterior->nome,
                'nome_novo' => $perfilAtualizado->nome,
                'frequencia_alerta' => $perfilAtualizado->frequenciaAlerta,
            ],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Perfil de monitoramento atualizado com sucesso.',
            'perfil' => $perfilAtualizado,
        ];
    }

    public function alternarAtivo(int $empresaId, ?int $usuarioId, int $perfilId): array
    {
        $perfil = $this->perfilRepository->findByIdAndEmpresa($perfilId, $empresaId);
        if ($perfil === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Perfil de monitoramento nao encontrado.',
                'perfil' => null,
            ];
        }

        $novoAtivo = !$perfil->ativo;
        $this->perfilRepository->setAtivo($perfilId, $empresaId, $novoAtivo);

        $perfilAtualizado = $this->perfilRepository->findByIdAndEmpresa($perfilId, $empresaId) ?? $perfil;

        $this->logService->info('monitoramento.perfil.toggle', 'Status do perfil alterado.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'ativo' => $perfilAtualizado->ativo ? 1 : 0,
        ]);

        $this->auditService->record(
            'PERFIL_MONITORAMENTO_STATUS_ALTERADO',
            'perfis_monitoramento',
            $perfilId,
            ['ativo' => $perfilAtualizado->ativo ? 1 : 0],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => $perfilAtualizado->ativo
                ? 'Perfil de monitoramento ativado.'
                : 'Perfil de monitoramento inativado.',
            'perfil' => $perfilAtualizado,
        ];
    }

    public function excluir(int $empresaId, ?int $usuarioId, int $perfilId): array
    {
        $perfil = $this->perfilRepository->findByIdAndEmpresa($perfilId, $empresaId);
        if ($perfil === null) {
            return [
                'sucesso' => false,
                'mensagem' => 'Perfil de monitoramento nao encontrado.',
            ];
        }

        $this->perfilRepository->delete($perfilId, $empresaId);

        $this->logService->warning('monitoramento.perfil.delete', 'Perfil de monitoramento removido.', [
            'empresa_id' => $empresaId,
            'usuario_id' => $usuarioId,
            'perfil_id' => $perfilId,
            'nome' => $perfil->nome,
        ]);

        $this->auditService->record(
            'PERFIL_MONITORAMENTO_EXCLUIDO',
            'perfis_monitoramento',
            $perfilId,
            ['nome' => $perfil->nome],
            $empresaId,
            $usuarioId
        );

        return [
            'sucesso' => true,
            'mensagem' => 'Perfil de monitoramento excluido com sucesso.',
        ];
    }

    private function validarLimitePerfis(int $empresaId): array
    {
        $statusAssinatura = $this->assinaturaService->verificarAcessoPorEmpresa($empresaId);
        if ($statusAssinatura['permitido'] !== true || !isset($statusAssinatura['assinatura'])) {
            return [
                'permitido' => false,
                'mensagem' => 'Nao foi possivel validar assinatura ativa para criar perfil.',
            ];
        }

        $assinatura = $statusAssinatura['assinatura'];
        $limite = (int) ($assinatura->plano?->limitePerfisMonitoramento ?? 0);
        if ($limite <= 0) {
            return [
                'permitido' => true,
                'mensagem' => null,
            ];
        }

        $atual = $this->perfilRepository->countByEmpresa($empresaId);
        if ($atual >= $limite) {
            return [
                'permitido' => false,
                'mensagem' => sprintf(
                    'Limite de perfis do plano atingido (%d).',
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
