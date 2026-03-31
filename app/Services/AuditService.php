<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

class AuditService
{
    private LogService $logService;

    public function __construct(?LogService $logService = null)
    {
        $this->logService = $logService ?? new LogService();
    }

    public function record(
        string $acao,
        string $entidade,
        ?int $entidadeId = null,
        array $detalhes = [],
        ?int $empresaId = null,
        ?int $usuarioId = null
    ): void {
        $acao = substr($acao, 0, 120);
        $entidade = substr($entidade, 0, 120);

        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO auditorias (
                    empresa_id,
                    usuario_id,
                    acao,
                    entidade,
                    entidade_id,
                    detalhes_json,
                    criado_em
                ) VALUES (
                    :empresa_id,
                    :usuario_id,
                    :acao,
                    :entidade,
                    :entidade_id,
                    :detalhes_json,
                    NOW()
                )'
            );

            $stmt->execute([
                'empresa_id' => $empresaId,
                'usuario_id' => $usuarioId,
                'acao' => $acao,
                'entidade' => $entidade,
                'entidade_id' => $entidadeId,
                'detalhes_json' => $detalhes !== []
                    ? json_encode($detalhes, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : null,
            ]);
        } catch (Throwable $exception) {
            $this->logService->error('audit.record', 'Falha ao gravar auditoria.', [
                'exception' => $exception->getMessage(),
                'acao' => $acao,
                'entidade' => $entidade,
            ]);
        }
    }
}
