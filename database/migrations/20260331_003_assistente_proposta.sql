-- =========================================================
-- MIGRACAO 20260331_003
-- Assistente de Proposta (rascunho, edicao e acompanhamento)
-- =========================================================

CREATE TABLE IF NOT EXISTS propostas_execucao (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    favorito_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    status ENUM('RASCUNHO', 'EM_REVISAO', 'APROVADA', 'ENVIADA') NOT NULL DEFAULT 'RASCUNHO',
    titulo VARCHAR(220) NOT NULL,
    resumo_executivo TEXT NULL,
    estrategia_proposta TEXT NULL,
    escopo_entrega TEXT NULL,
    diferenciais TEXT NULL,
    cronograma_macro TEXT NULL,
    risco_principal TEXT NULL,
    valor_proposta DECIMAL(15,2) NULL,
    observacoes TEXT NULL,
    gerada_automatica TINYINT(1) NOT NULL DEFAULT 1,
    criado_por_usuario_id BIGINT UNSIGNED NULL,
    atualizado_por_usuario_id BIGINT UNSIGNED NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_propostas_execucao_favorito (favorito_id),
    KEY idx_propostas_execucao_empresa (empresa_id),
    KEY idx_propostas_execucao_status (status),
    KEY idx_propostas_execucao_valor (valor_proposta),
    KEY idx_propostas_execucao_criado_em (criado_em),
    CONSTRAINT fk_propostas_execucao_favorito
        FOREIGN KEY (favorito_id) REFERENCES favoritos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_propostas_execucao_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_propostas_execucao_criado_por
        FOREIGN KEY (criado_por_usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_propostas_execucao_atualizado_por
        FOREIGN KEY (atualizado_por_usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
