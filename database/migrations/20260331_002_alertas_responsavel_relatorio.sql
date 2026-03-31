-- =========================================================
-- MIGRACAO 20260331_002
-- Alertas de prazo + responsavel interno + relatorio de conversao
-- =========================================================

SET @col_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'favorito_tarefas'
      AND COLUMN_NAME = 'responsavel_usuario_id'
);
SET @sql := IF(
    @col_exists = 0,
    'ALTER TABLE favorito_tarefas ADD COLUMN responsavel_usuario_id BIGINT UNSIGNED NULL AFTER responsavel',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @idx_exists := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'favorito_tarefas'
      AND INDEX_NAME = 'idx_favorito_tarefas_responsavel_usuario'
);
SET @sql := IF(
    @idx_exists = 0,
    'ALTER TABLE favorito_tarefas ADD INDEX idx_favorito_tarefas_responsavel_usuario (responsavel_usuario_id)',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
    SELECT COUNT(*)
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'favorito_tarefas'
      AND CONSTRAINT_TYPE = 'FOREIGN KEY'
      AND CONSTRAINT_NAME = 'fk_favorito_tarefas_responsavel_usuario'
);
SET @sql := IF(
    @fk_exists = 0,
    'ALTER TABLE favorito_tarefas ADD CONSTRAINT fk_favorito_tarefas_responsavel_usuario FOREIGN KEY (responsavel_usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS favorito_status_historico (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    favorito_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    status_anterior ENUM('FAVORITO', 'EM_ANALISE', 'PROPOSTA', 'DESCARTADO', 'ENCERRADO') NULL,
    status_novo ENUM('FAVORITO', 'EM_ANALISE', 'PROPOSTA', 'DESCARTADO', 'ENCERRADO') NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    origem VARCHAR(40) NOT NULL DEFAULT 'manual',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_favorito_status_historico_favorito (favorito_id),
    KEY idx_favorito_status_historico_empresa (empresa_id),
    KEY idx_favorito_status_historico_status_novo (status_novo),
    KEY idx_favorito_status_historico_criado_em (criado_em),
    CONSTRAINT fk_favorito_status_historico_favorito
        FOREIGN KEY (favorito_id) REFERENCES favoritos(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_favorito_status_historico_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_favorito_status_historico_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
