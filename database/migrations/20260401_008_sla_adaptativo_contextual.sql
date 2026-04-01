-- =========================================================
-- MIGRACAO 20260401_008
-- SLA adaptativo + priorizacao contextual por tipo + orgao + modalidade
-- =========================================================

ALTER TABLE proposta_alerta_playbook_eventos
    MODIFY COLUMN tipo_evento
        ENUM('PLAYBOOK_CRIADO', 'PROGRESSO_ATUALIZADO', 'ESCALONADO', 'ENCERRADO', 'REABERTO', 'APRENDIZADO_ATUALIZADO')
        NOT NULL;

ALTER TABLE proposta_alerta_playbooks
    ADD COLUMN contexto_orgao_nome VARCHAR(255) NULL AFTER tipo_alerta,
    ADD COLUMN contexto_modalidade VARCHAR(120) NULL AFTER contexto_orgao_nome,
    ADD COLUMN risco_atraso_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER fator_priorizacao,
    ADD COLUMN sla_sugerido_horas SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER sla_horas,
    ADD KEY idx_playbook_empresa_contexto (empresa_id, tipo_alerta, contexto_orgao_nome, contexto_modalidade);

UPDATE proposta_alerta_playbooks pb
INNER JOIN favoritos f
    ON f.id = pb.favorito_id
   AND f.empresa_id = pb.empresa_id
INNER JOIN editais e
    ON e.id = f.edital_id
SET
    pb.contexto_orgao_nome = UPPER(TRIM(COALESCE(e.orgao_nome, 'GERAL'))),
    pb.contexto_modalidade = UPPER(TRIM(COALESCE(e.modalidade, 'GERAL')))
WHERE pb.contexto_orgao_nome IS NULL
   OR pb.contexto_orgao_nome = ''
   OR pb.contexto_modalidade IS NULL
   OR pb.contexto_modalidade = '';

UPDATE proposta_alerta_playbooks
SET
    contexto_orgao_nome = COALESCE(NULLIF(TRIM(contexto_orgao_nome), ''), 'GERAL'),
    contexto_modalidade = COALESCE(NULLIF(TRIM(contexto_modalidade), ''), 'GERAL'),
    sla_sugerido_horas = CASE
        WHEN sla_sugerido_horas IS NULL OR sla_sugerido_horas <= 0 THEN sla_horas
        ELSE sla_sugerido_horas
    END
WHERE contexto_orgao_nome IS NULL
   OR contexto_orgao_nome = ''
   OR contexto_modalidade IS NULL
   OR contexto_modalidade = ''
   OR sla_sugerido_horas IS NULL
   OR sla_sugerido_horas <= 0;

ALTER TABLE proposta_alerta_aprendizado_regras
    ADD COLUMN orgao_nome_contexto VARCHAR(255) NOT NULL DEFAULT 'GERAL' AFTER tipo_alerta,
    ADD COLUMN modalidade_contexto VARCHAR(120) NOT NULL DEFAULT 'GERAL' AFTER orgao_nome_contexto,
    ADD COLUMN casos_com_primeira_acao INT UNSIGNED NOT NULL DEFAULT 0 AFTER escalonamentos_total,
    ADD COLUMN soma_horas_primeira_acao DECIMAL(10,2) NOT NULL DEFAULT 0.00 AFTER casos_com_primeira_acao,
    ADD COLUMN tempo_medio_primeira_acao_horas DECIMAL(8,2) NOT NULL DEFAULT 0.00 AFTER loss_rate,
    ADD COLUMN taxa_escalonamento_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER tempo_medio_primeira_acao_horas,
    ADD COLUMN risco_atraso_percentual DECIMAL(5,2) NOT NULL DEFAULT 0.00 AFTER taxa_escalonamento_percentual,
    ADD COLUMN sla_sugerido_horas SMALLINT UNSIGNED NOT NULL DEFAULT 48 AFTER risco_atraso_percentual,
    DROP INDEX uk_aprendizado_empresa_tipo,
    ADD UNIQUE KEY uk_aprendizado_empresa_contexto (
        empresa_id,
        tipo_alerta,
        orgao_nome_contexto,
        modalidade_contexto
    ),
    ADD KEY idx_aprendizado_empresa_risco (empresa_id, risco_atraso_percentual),
    ADD KEY idx_aprendizado_empresa_sla (empresa_id, sla_sugerido_horas);

UPDATE proposta_alerta_aprendizado_regras
SET
    orgao_nome_contexto = COALESCE(NULLIF(UPPER(TRIM(orgao_nome_contexto)), ''), 'GERAL'),
    modalidade_contexto = COALESCE(NULLIF(UPPER(TRIM(modalidade_contexto)), ''), 'GERAL'),
    taxa_escalonamento_percentual = CASE
        WHEN total_casos > 0 THEN ROUND((escalonamentos_total / total_casos) * 100, 2)
        ELSE 0.00
    END,
    risco_atraso_percentual = CASE
        WHEN total_casos > 0 THEN LEAST(
            95.00,
            ROUND(
                (loss_rate * 0.55)
                + ((escalonamentos_total / total_casos) * 100 * 0.45),
                2
            )
        )
        ELSE 40.00
    END,
    sla_sugerido_horas = CASE
        WHEN tipo_alerta = 'JULGAMENTO_PARADO' THEN 36
        ELSE 48
    END;
