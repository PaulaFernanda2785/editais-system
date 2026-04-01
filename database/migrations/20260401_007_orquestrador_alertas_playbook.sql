-- =========================================================
-- MIGRACAO 20260401_007
-- Orquestrador de acoes automaticas: alerta -> playbook -> escalonamento -> evidencia
-- =========================================================

CREATE TABLE IF NOT EXISTS proposta_alerta_playbooks (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    alerta_notificacao_id BIGINT UNSIGNED NOT NULL,
    proposta_id BIGINT UNSIGNED NOT NULL,
    favorito_id BIGINT UNSIGNED NOT NULL,
    tipo_alerta ENUM('SEM_RESULTADO', 'JULGAMENTO_PARADO') NOT NULL,
    status ENUM('ATIVO', 'EM_PROGRESSO', 'ESCALADO', 'ENCERRADO') NOT NULL DEFAULT 'ATIVO',
    prioridade ENUM('ALTA', 'MEDIA', 'BAIXA') NOT NULL DEFAULT 'MEDIA',
    fator_priorizacao DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    sla_horas SMALLINT UNSIGNED NOT NULL DEFAULT 48,
    prazo_sla_em DATETIME NOT NULL,
    progresso_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    responsavel_usuario_id BIGINT UNSIGNED NULL,
    responsavel_nome VARCHAR(120) NULL,
    responsavel_email VARCHAR(180) NULL,
    primeira_atividade_em DATETIME NULL,
    ultima_atividade_em DATETIME NULL,
    escalonado_em DATETIME NULL,
    escalonamento_nivel SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    escalonamento_motivo VARCHAR(255) NULL,
    encerrado_em DATETIME NULL,
    resultado_win_loss ENUM('ABERTO', 'WIN', 'LOSS', 'NEUTRO') NOT NULL DEFAULT 'ABERTO',
    aprendizado_resumo TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_playbook_alerta_notificacao (empresa_id, alerta_notificacao_id),
    KEY idx_playbook_empresa_status (empresa_id, status),
    KEY idx_playbook_empresa_prazo (empresa_id, prazo_sla_em),
    KEY idx_playbook_empresa_tipo (empresa_id, tipo_alerta),
    CONSTRAINT fk_playbook_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_playbook_alerta_notificacao
        FOREIGN KEY (alerta_notificacao_id) REFERENCES proposta_alerta_notificacoes(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_playbook_proposta
        FOREIGN KEY (proposta_id) REFERENCES propostas_execucao(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_playbook_favorito
        FOREIGN KEY (favorito_id) REFERENCES favoritos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_playbook_responsavel_usuario
        FOREIGN KEY (responsavel_usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposta_alerta_playbook_tarefas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    playbook_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    favorito_tarefa_id BIGINT UNSIGNED NOT NULL,
    tipo_tarefa ENUM('TRIAGEM', 'CONTATO', 'EVIDENCIA') NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_playbook_tarefa_unica (playbook_id, favorito_tarefa_id),
    UNIQUE KEY uk_playbook_tipo_tarefa (playbook_id, tipo_tarefa),
    KEY idx_playbook_tarefa_empresa (empresa_id),
    KEY idx_playbook_tarefa_tarefa (favorito_tarefa_id),
    CONSTRAINT fk_playbook_tarefa_playbook
        FOREIGN KEY (playbook_id) REFERENCES proposta_alerta_playbooks(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_playbook_tarefa_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_playbook_tarefa_favorito_tarefa
        FOREIGN KEY (favorito_tarefa_id) REFERENCES favorito_tarefas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposta_alerta_playbook_eventos (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    playbook_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    tipo_evento ENUM('PLAYBOOK_CRIADO', 'PROGRESSO_ATUALIZADO', 'ESCALONADO', 'ENCERRADO', 'REABERTO') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    detalhes_json JSON NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_playbook_evento_playbook (playbook_id),
    KEY idx_playbook_evento_empresa (empresa_id),
    KEY idx_playbook_evento_tipo (tipo_evento),
    KEY idx_playbook_evento_criado (criado_em),
    CONSTRAINT fk_playbook_evento_playbook
        FOREIGN KEY (playbook_id) REFERENCES proposta_alerta_playbooks(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_playbook_evento_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proposta_alerta_aprendizado_regras (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    empresa_id BIGINT UNSIGNED NOT NULL,
    tipo_alerta ENUM('SEM_RESULTADO', 'JULGAMENTO_PARADO') NOT NULL,
    total_casos INT UNSIGNED NOT NULL DEFAULT 0,
    wins INT UNSIGNED NOT NULL DEFAULT 0,
    losses INT UNSIGNED NOT NULL DEFAULT 0,
    neutros INT UNSIGNED NOT NULL DEFAULT 0,
    escalonamentos_total INT UNSIGNED NOT NULL DEFAULT 0,
    win_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    loss_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    prioridade_sugerida ENUM('ALTA', 'MEDIA', 'BAIXA') NOT NULL DEFAULT 'MEDIA',
    fator_priorizacao DECIMAL(5,2) NOT NULL DEFAULT 1.00,
    ultima_atualizacao_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_aprendizado_empresa_tipo (empresa_id, tipo_alerta),
    KEY idx_aprendizado_empresa_prioridade (empresa_id, prioridade_sugerida),
    CONSTRAINT fk_aprendizado_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
