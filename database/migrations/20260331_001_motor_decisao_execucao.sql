-- =========================================================
-- MIGRACAO 20260331_001
-- Etapa evolutiva: Motor de Decisao e Execucao
-- =========================================================

CREATE TABLE IF NOT EXISTS favorito_tarefas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    favorito_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    descricao TEXT NULL,
    responsavel VARCHAR(120) NULL,
    data_limite DATE NULL,
    status ENUM('PENDENTE', 'EM_ANDAMENTO', 'CONCLUIDA', 'BLOQUEADA') NOT NULL DEFAULT 'PENDENTE',
    ordem INT NOT NULL DEFAULT 1,
    concluida_em DATETIME NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_favorito_tarefas_favorito (favorito_id),
    KEY idx_favorito_tarefas_empresa (empresa_id),
    KEY idx_favorito_tarefas_status (status),
    KEY idx_favorito_tarefas_data_limite (data_limite),
    CONSTRAINT fk_favorito_tarefas_favorito
        FOREIGN KEY (favorito_id) REFERENCES favoritos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_favorito_tarefas_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
