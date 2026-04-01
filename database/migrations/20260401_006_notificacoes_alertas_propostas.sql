-- =========================================================
-- MIGRACAO 20260401_006
-- Notificacoes proativas para alertas de propostas
-- =========================================================

CREATE TABLE IF NOT EXISTS proposta_alerta_notificacoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    proposta_id BIGINT UNSIGNED NOT NULL,
    tipo_alerta ENUM('SEM_RESULTADO', 'JULGAMENTO_PARADO') NOT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    novo TINYINT(1) NOT NULL DEFAULT 1,
    primeiro_detectado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_detectado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolvido_em DATETIME NULL,
    email_enviado_em DATETIME NULL,
    email_tentativas SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    ultimo_erro_email VARCHAR(255) NULL,
    visualizado_em DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_proposta_alerta_notificacoes (empresa_id, proposta_id, tipo_alerta),
    KEY idx_proposta_alerta_notificacoes_ativo (empresa_id, ativo, novo),
    KEY idx_proposta_alerta_notificacoes_tipo (empresa_id, tipo_alerta, ativo),
    KEY idx_proposta_alerta_notificacoes_detectado (empresa_id, ultimo_detectado_em),
    CONSTRAINT fk_proposta_alerta_notif_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_alerta_notif_proposta
        FOREIGN KEY (proposta_id) REFERENCES propostas_execucao(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
