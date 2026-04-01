-- =========================================================
-- MIGRACAO 20260331_005
-- Pos-envio: registro de resultado oficial das propostas
-- =========================================================

CREATE TABLE IF NOT EXISTS proposta_resultados (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    proposta_id BIGINT UNSIGNED NOT NULL,
    empresa_id BIGINT UNSIGNED NOT NULL,
    usuario_id BIGINT UNSIGNED NULL,
    situacao ENUM('EM_JULGAMENTO', 'VENCEDORA', 'NAO_VENCEDORA', 'DESCLASSIFICADA', 'ANULADA') NOT NULL DEFAULT 'EM_JULGAMENTO',
    data_resultado DATETIME NOT NULL,
    valor_homologado DECIMAL(15,2) NULL,
    colocacao SMALLINT UNSIGNED NULL,
    motivo TEXT NULL,
    link_ata VARCHAR(500) NULL,
    observacao TEXT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_proposta_resultados_proposta (proposta_id),
    KEY idx_proposta_resultados_empresa (empresa_id),
    KEY idx_proposta_resultados_situacao (situacao),
    KEY idx_proposta_resultados_data (data_resultado),
    CONSTRAINT fk_proposta_resultados_proposta
        FOREIGN KEY (proposta_id) REFERENCES propostas_execucao(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_resultados_empresa
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_proposta_resultados_usuario
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
