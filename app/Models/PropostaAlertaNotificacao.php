<?php

declare(strict_types=1);

namespace App\Models;

class PropostaAlertaNotificacao
{
    public int $id = 0;
    public int $empresaId = 0;
    public int $propostaId = 0;
    public string $tipoAlerta = 'SEM_RESULTADO';
    public bool $ativo = true;
    public bool $novo = true;
    public ?string $primeiroDetectadoEm = null;
    public ?string $ultimoDetectadoEm = null;
    public ?string $resolvidoEm = null;
    public ?string $emailEnviadoEm = null;
    public int $emailTentativas = 0;
    public ?string $ultimoErroEmail = null;
    public ?string $visualizadoEm = null;
    public ?string $criadoEm = null;
    public ?string $atualizadoEm = null;

    public static function fromArray(array $data): self
    {
        $item = new self();
        $item->id = (int) ($data['id'] ?? 0);
        $item->empresaId = (int) ($data['empresa_id'] ?? 0);
        $item->propostaId = (int) ($data['proposta_id'] ?? 0);
        $item->tipoAlerta = (string) ($data['tipo_alerta'] ?? 'SEM_RESULTADO');
        $item->ativo = ((int) ($data['ativo'] ?? 1)) === 1;
        $item->novo = ((int) ($data['novo'] ?? 1)) === 1;
        $item->primeiroDetectadoEm = isset($data['primeiro_detectado_em']) ? (string) $data['primeiro_detectado_em'] : null;
        $item->ultimoDetectadoEm = isset($data['ultimo_detectado_em']) ? (string) $data['ultimo_detectado_em'] : null;
        $item->resolvidoEm = isset($data['resolvido_em']) ? (string) $data['resolvido_em'] : null;
        $item->emailEnviadoEm = isset($data['email_enviado_em']) ? (string) $data['email_enviado_em'] : null;
        $item->emailTentativas = (int) ($data['email_tentativas'] ?? 0);
        $item->ultimoErroEmail = isset($data['ultimo_erro_email']) ? (string) $data['ultimo_erro_email'] : null;
        $item->visualizadoEm = isset($data['visualizado_em']) ? (string) $data['visualizado_em'] : null;
        $item->criadoEm = isset($data['criado_em']) ? (string) $data['criado_em'] : null;
        $item->atualizadoEm = isset($data['atualizado_em']) ? (string) $data['atualizado_em'] : null;

        return $item;
    }
}
