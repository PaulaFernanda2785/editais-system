-- =========================================================
-- MIGRACAO 20260331_004
-- Envio controlado: workflow de aprovacao e registro de submissao
-- =========================================================

CREATE TABLE IF NOT EXISTS proposta_aprovacoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    proposta_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    status_decisao ENUM('PENDENTE', 'APROVADA', 'REPROVADA') NOT NULL DEFAULT 'PENDENTE',
    observacao_solicitacao TEXT NULL,
    solicitado_por_usuario_id BIGINT UNSIGNED NULL,
    solicitado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decidido_por_usuario_id BIGINT UNSIGNED NULL,
    decidido_em DATETIME NULL,
    parecer TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_proposta_aprovacoes_proposta (proposta_id),
    KEY idx_proposta_aprovacoes_empresa (empresa_id),
    KEY idx_proposta_aprovacoes_status (status_decisao),
    KEY idx_proposta_aprovacoes_solicitado_em (solicitado_em),
    CONSTRAINT fk_proposta_aprovacoes_proposta
        FOREIGN KEY (proposta_id) REFERENCES propostas_execucao(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_aprovacoes_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_aprovacoes_solicitado_por
        FOREIGN KEY (solicitado_por_usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_aprovacoes_decidido_por
        FOREIGN KEY (decidido_por_usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposta_submissoes (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    proposta_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    canal ENUM('PORTAL', 'EMAIL', 'PRESENCIAL', 'OUTRO') NOT NULL DEFAULT 'PORTAL',
    protocolo VARCHAR(150) NULL,
    data_submissao DATETIME NOT NULL,
    valor_enviado DECIMAL(15,2) NULL,
    link_comprovante VARCHAR(500) NULL,
    observacao TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_proposta_submissoes_proposta (proposta_id),
    KEY idx_proposta_submissoes_empresa (empresa_id),
    KEY idx_proposta_submissoes_canal (canal),
    KEY idx_proposta_submissoes_data (data_submissao),
    CONSTRAINT fk_proposta_submissoes_proposta
        FOREIGN KEY (proposta_id) REFERENCES propostas_execucao(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_submissoes_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_submissoes_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
