## 1. Script SQL completo

CREATE DATABASE IF NOT EXISTS saas_editais  
CHARACTER SET utf8mb4  
COLLATE utf8mb4_unicode_ci;  
  
USE saas_editais;  
  
SET NAMES utf8mb4;  
SET FOREIGN_KEY_CHECKS = 0;  
  
DROP TABLE IF EXISTS auditorias;  
DROP TABLE IF EXISTS exportacoes;  
DROP TABLE IF EXISTS logs_sistema;  
DROP TABLE IF EXISTS alertas;  
DROP TABLE IF EXISTS favoritos;  
DROP TABLE IF EXISTS correspondencias;  
DROP TABLE IF EXISTS palavras_chave;  
DROP TABLE IF EXISTS perfis_monitoramento;  
DROP TABLE IF EXISTS edital_documentos;  
DROP TABLE IF EXISTS editais;  
DROP TABLE IF EXISTS coletas_execucao;  
DROP TABLE IF EXISTS fontes_coleta;  
DROP TABLE IF EXISTS tokens_recuperacao_senha;  
DROP TABLE IF EXISTS assinaturas;  
DROP TABLE IF EXISTS planos;  
DROP TABLE IF EXISTS usuarios;  
DROP TABLE IF EXISTS empresas;  
  
SET FOREIGN_KEY_CHECKS = 1;  
  
CREATE TABLE empresas (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    razao_social VARCHAR(200) NOT NULL,  
    nome_fantasia VARCHAR(200) NULL,  
    cnpj VARCHAR(18) NULL,  
    segmento VARCHAR(150) NULL,  
    email_contato VARCHAR(150) NULL,  
    telefone VARCHAR(30) NULL,  
    status ENUM('ATIVA', 'INATIVA', 'SUSPENSA') NOT NULL DEFAULT 'ATIVA',  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    UNIQUE KEY uk_empresas_cnpj (cnpj),  
    KEY idx_empresas_status (status),  
    KEY idx_empresas_segmento (segmento)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE usuarios (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    nome VARCHAR(150) NOT NULL,  
    email VARCHAR(150) NOT NULL,  
    senha_hash VARCHAR(255) NOT NULL,  
    perfil ENUM('SUPER_ADMIN', 'ADMIN', 'GESTOR', 'USUARIO') NOT NULL DEFAULT 'USUARIO',  
    status ENUM('ATIVO', 'INATIVO', 'BLOQUEADO') NOT NULL DEFAULT 'ATIVO',  
    ultimo_login_em DATETIME NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    UNIQUE KEY uk_usuarios_email (email),  
    KEY idx_usuarios_empresa (empresa_id),  
    KEY idx_usuarios_perfil (perfil),  
    KEY idx_usuarios_status (status),  
    CONSTRAINT fk_usuarios_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE planos (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    nome VARCHAR(100) NOT NULL,  
    descricao VARCHAR(255) NULL,  
    limite_usuarios INT NOT NULL DEFAULT 1,  
    limite_palavras_chave INT NOT NULL DEFAULT 10,  
    limite_perfis_monitoramento INT NOT NULL DEFAULT 1,  
    limite_alertas_dia INT NOT NULL DEFAULT 1,  
    limite_exportacoes_mes INT NOT NULL DEFAULT 5,  
    valor_mensal DECIMAL(10,2) NOT NULL DEFAULT 0.00,  
    status ENUM('ATIVO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    UNIQUE KEY uk_planos_nome (nome),  
    KEY idx_planos_status (status)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE assinaturas (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    plano_id BIGINT UNSIGNED NOT NULL,  
    status ENUM('ATIVA', 'PENDENTE', 'SUSPENSA', 'CANCELADA', 'TESTE') NOT NULL DEFAULT 'TESTE',  
    data_inicio DATE NOT NULL,  
    data_fim DATE NULL,  
    gateway_referencia VARCHAR(150) NULL,  
    observacao VARCHAR(255) NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_assinaturas_empresa (empresa_id),  
    KEY idx_assinaturas_plano (plano_id),  
    KEY idx_assinaturas_status (status),  
    KEY idx_assinaturas_periodo (data_inicio, data_fim),  
    CONSTRAINT fk_assinaturas_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_assinaturas_plano  
        FOREIGN KEY (plano_id) REFERENCES planos(id)  
        ON DELETE RESTRICT  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE tokens_recuperacao_senha (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    usuario_id BIGINT UNSIGNED NOT NULL,  
    token VARCHAR(255) NOT NULL,  
    expira_em DATETIME NOT NULL,  
    usado_em DATETIME NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    UNIQUE KEY uk_tokens_recuperacao_token (token),  
    KEY idx_tokens_recuperacao_usuario (usuario_id),  
    KEY idx_tokens_recuperacao_expira (expira_em),  
    CONSTRAINT fk_tokens_recuperacao_usuario  
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE fontes_coleta (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    nome VARCHAR(120) NOT NULL,  
    codigo VARCHAR(50) NOT NULL,  
    tipo ENUM('API', 'SCRAPING', 'MANUAL') NOT NULL,  
    url_base VARCHAR(255) NULL,  
    ativa TINYINT(1) NOT NULL DEFAULT 1,  
    periodicidade_minutos INT NOT NULL DEFAULT 60,  
    configuracao_json JSON NULL,  
    ultima_execucao_em DATETIME NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    UNIQUE KEY uk_fontes_coleta_codigo (codigo),  
    KEY idx_fontes_coleta_ativa (ativa),  
    KEY idx_fontes_coleta_tipo (tipo)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE coletas_execucao (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    fonte_id BIGINT UNSIGNED NOT NULL,  
    iniciado_em DATETIME NOT NULL,  
    finalizado_em DATETIME NULL,  
    status ENUM('PROCESSANDO', 'SUCESSO', 'ERRO', 'PARCIAL') NOT NULL DEFAULT 'PROCESSANDO',  
    total_lidos INT NOT NULL DEFAULT 0,  
    total_inseridos INT NOT NULL DEFAULT 0,  
    total_atualizados INT NOT NULL DEFAULT 0,  
    total_duplicados INT NOT NULL DEFAULT 0,  
    total_erros INT NOT NULL DEFAULT 0,  
    mensagem_resumo VARCHAR(255) NULL,  
    log_detalhado LONGTEXT NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_coletas_execucao_fonte (fonte_id),  
    KEY idx_coletas_execucao_status (status),  
    KEY idx_coletas_execucao_iniciado (iniciado_em),  
    CONSTRAINT fk_coletas_execucao_fonte  
        FOREIGN KEY (fonte_id) REFERENCES fontes_coleta(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE editais (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    fonte_id BIGINT UNSIGNED NOT NULL,  
    codigo_fonte VARCHAR(120) NULL,  
    numero_edital VARCHAR(100) NULL,  
    orgao_nome VARCHAR(255) NOT NULL,  
    orgao_poder VARCHAR(100) NULL,  
    esfera ENUM('FEDERAL', 'ESTADUAL', 'MUNICIPAL', 'OUTRA') NULL,  
    uf CHAR(2) NULL,  
    municipio VARCHAR(120) NULL,  
    modalidade VARCHAR(120) NULL,  
    modo_disputa VARCHAR(120) NULL,  
    objeto LONGTEXT NOT NULL,  
    descricao_resumida TEXT NULL,  
    valor_estimado DECIMAL(15,2) NULL,  
    data_publicacao DATE NULL,  
    data_abertura DATETIME NULL,  
    data_encerramento DATETIME NULL,  
    situacao VARCHAR(80) NULL,  
    link_detalhe VARCHAR(500) NULL,  
    link_edital VARCHAR(500) NULL,  
    hash_unico CHAR(32) NOT NULL,  
    score_global DECIMAL(8,2) NOT NULL DEFAULT 0.00,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    UNIQUE KEY uk_editais_hash_unico (hash_unico),  
    KEY idx_editais_fonte (fonte_id),  
    KEY idx_editais_codigo_fonte (codigo_fonte),  
    KEY idx_editais_numero (numero_edital),  
    KEY idx_editais_data_publicacao (data_publicacao),  
    KEY idx_editais_data_encerramento (data_encerramento),  
    KEY idx_editais_uf (uf),  
    KEY idx_editais_municipio (municipio),  
    KEY idx_editais_modalidade (modalidade),  
    KEY idx_editais_orgao_nome (orgao_nome),  
    KEY idx_editais_situacao (situacao),  
    KEY idx_editais_score_global (score_global),  
    CONSTRAINT fk_editais_fonte  
        FOREIGN KEY (fonte_id) REFERENCES fontes_coleta(id)  
        ON DELETE RESTRICT  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE edital_documentos (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    edital_id BIGINT UNSIGNED NOT NULL,  
    nome_documento VARCHAR(255) NOT NULL,  
    url_documento VARCHAR(500) NOT NULL,  
    tipo_documento VARCHAR(80) NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_edital_documentos_edital (edital_id),  
    KEY idx_edital_documentos_tipo (tipo_documento),  
    CONSTRAINT fk_edital_documentos_edital  
        FOREIGN KEY (edital_id) REFERENCES editais(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE perfis_monitoramento (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    nome VARCHAR(120) NOT NULL,  
    ufs_json JSON NULL,  
    modalidades_json JSON NULL,  
    orgaos_json JSON NULL,  
    faixa_valor_min DECIMAL(15,2) NULL,  
    faixa_valor_max DECIMAL(15,2) NULL,  
    frequencia_alerta ENUM('IMEDIATO', 'DIARIO', 'SEMANAL') NOT NULL DEFAULT 'DIARIO',  
    ativo TINYINT(1) NOT NULL DEFAULT 1,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_perfis_monitoramento_empresa (empresa_id),  
    KEY idx_perfis_monitoramento_ativo (ativo),  
    KEY idx_perfis_monitoramento_frequencia (frequencia_alerta),  
    CONSTRAINT fk_perfis_monitoramento_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT chk_perfis_faixa_valor  
        CHECK (  
            faixa_valor_min IS NULL  
            OR faixa_valor_max IS NULL  
            OR faixa_valor_min <= faixa_valor_max  
        )  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE palavras_chave (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    perfil_monitoramento_id BIGINT UNSIGNED NULL,  
    termo VARCHAR(150) NOT NULL,  
    peso INT NOT NULL DEFAULT 1,  
    categoria VARCHAR(80) NULL,  
    ativo TINYINT(1) NOT NULL DEFAULT 1,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_palavras_chave_empresa (empresa_id),  
    KEY idx_palavras_chave_perfil (perfil_monitoramento_id),  
    KEY idx_palavras_chave_ativo (ativo),  
    KEY idx_palavras_chave_termo (termo),  
    CONSTRAINT fk_palavras_chave_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_palavras_chave_perfil  
        FOREIGN KEY (perfil_monitoramento_id) REFERENCES perfis_monitoramento(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT chk_palavras_chave_peso  
        CHECK (peso >= 1)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE correspondencias (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    edital_id BIGINT UNSIGNED NOT NULL,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    perfil_monitoramento_id BIGINT UNSIGNED NULL,  
    score DECIMAL(8,2) NOT NULL DEFAULT 0.00,  
    nivel_relevancia ENUM('BAIXA', 'MEDIA', 'ALTA', 'PRIORITARIA') NOT NULL DEFAULT 'BAIXA',  
    motivo_json JSON NULL,  
    alertado_em DATETIME NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_correspondencias_edital (edital_id),  
    KEY idx_correspondencias_empresa (empresa_id),  
    KEY idx_correspondencias_perfil (perfil_monitoramento_id),  
    KEY idx_correspondencias_score (score),  
    KEY idx_correspondencias_nivel (nivel_relevancia),  
    UNIQUE KEY uk_correspondencias_unica (edital_id, empresa_id, perfil_monitoramento_id),  
    CONSTRAINT fk_correspondencias_edital  
        FOREIGN KEY (edital_id) REFERENCES editais(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_correspondencias_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_correspondencias_perfil  
        FOREIGN KEY (perfil_monitoramento_id) REFERENCES perfis_monitoramento(id)  
        ON DELETE SET NULL  
        ON UPDATE CASCADE,  
    CONSTRAINT chk_correspondencias_score  
        CHECK (score >= 0)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE favoritos (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    edital_id BIGINT UNSIGNED NOT NULL,  
    status_acompanhamento ENUM('FAVORITO', 'EM_ANALISE', 'PROPOSTA', 'DESCARTADO', 'ENCERRADO') NOT NULL DEFAULT 'FAVORITO',  
    observacao TEXT NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    atualizado_em DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    UNIQUE KEY uk_favoritos_empresa_edital (empresa_id, edital_id),  
    KEY idx_favoritos_empresa (empresa_id),  
    KEY idx_favoritos_edital (edital_id),  
    KEY idx_favoritos_status (status_acompanhamento),  
    CONSTRAINT fk_favoritos_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_favoritos_edital  
        FOREIGN KEY (edital_id) REFERENCES editais(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE alertas (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    usuario_id BIGINT UNSIGNED NULL,  
    tipo ENUM('RESUMO_DIARIO', 'OPORTUNIDADE', 'SISTEMA') NOT NULL,  
    canal ENUM('EMAIL', 'PAINEL') NOT NULL DEFAULT 'EMAIL',  
    assunto VARCHAR(200) NOT NULL,  
    conteudo_resumo TEXT NULL,  
    status_envio ENUM('PENDENTE', 'ENVIADO', 'ERRO') NOT NULL DEFAULT 'PENDENTE',  
    enviado_em DATETIME NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_alertas_empresa (empresa_id),  
    KEY idx_alertas_usuario (usuario_id),  
    KEY idx_alertas_tipo (tipo),  
    KEY idx_alertas_status (status_envio),  
    KEY idx_alertas_enviado_em (enviado_em),  
    CONSTRAINT fk_alertas_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_alertas_usuario  
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  
        ON DELETE SET NULL  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE logs_sistema (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    nivel ENUM('INFO', 'WARNING', 'ERROR', 'CRITICAL') NOT NULL,  
    contexto VARCHAR(100) NOT NULL,  
    mensagem VARCHAR(255) NOT NULL,  
    dados_json JSON NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_logs_sistema_nivel (nivel),  
    KEY idx_logs_sistema_contexto (contexto),  
    KEY idx_logs_sistema_criado_em (criado_em)  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE exportacoes (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NOT NULL,  
    usuario_id BIGINT UNSIGNED NULL,  
    tipo ENUM('CSV', 'PDF') NOT NULL,  
    filtro_json JSON NULL,  
    caminho_arquivo VARCHAR(255) NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_exportacoes_empresa (empresa_id),  
    KEY idx_exportacoes_usuario (usuario_id),  
    KEY idx_exportacoes_tipo (tipo),  
    CONSTRAINT fk_exportacoes_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE CASCADE  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_exportacoes_usuario  
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  
        ON DELETE SET NULL  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;  
  
CREATE TABLE auditorias (  
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,  
    empresa_id BIGINT UNSIGNED NULL,  
    usuario_id BIGINT UNSIGNED NULL,  
    acao VARCHAR(120) NOT NULL,  
    entidade VARCHAR(120) NOT NULL,  
    entidade_id BIGINT UNSIGNED NULL,  
    detalhes_json JSON NULL,  
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,  
    PRIMARY KEY (id),  
    KEY idx_auditorias_empresa (empresa_id),  
    KEY idx_auditorias_usuario (usuario_id),  
    KEY idx_auditorias_acao (acao),  
    KEY idx_auditorias_entidade (entidade),  
    KEY idx_auditorias_criado_em (criado_em),  
    CONSTRAINT fk_auditorias_empresa  
        FOREIGN KEY (empresa_id) REFERENCES empresas(id)  
        ON DELETE SET NULL  
        ON UPDATE CASCADE,  
    CONSTRAINT fk_auditorias_usuario  
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id)  
        ON DELETE SET NULL  
        ON UPDATE CASCADE  
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

## 2. Inserts iniciais recomendados

Esses dados seed são úteis para começar o sistema sem depender de cadastro manual imediato.

INSERT INTO planos (  
    nome, descricao, limite_usuarios, limite_palavras_chave, limite_perfis_monitoramento,  
    limite_alertas_dia, limite_exportacoes_mes, valor_mensal, status  
) VALUES  
('BASICO', 'Plano básico com monitoramento essencial', 1, 10, 1, 1, 5, 49.90, 'ATIVO'),  
('PROFISSIONAL', 'Plano intermediário com mais recursos', 5, 50, 5, 5, 20, 129.90, 'ATIVO'),  
('EMPRESARIAL', 'Plano avançado para equipes e operação ampliada', 20, 200, 20, 20, 100, 399.90, 'ATIVO');  
  
INSERT INTO fontes_coleta (  
    nome, codigo, tipo, url_base, ativa, periodicidade_minutos, configuracao_json  
) VALUES  
('Portal Nacional de Contratações Públicas', 'PNCP', 'API', 'https://pncp.gov.br/api/consulta/v1', 1, 60, JSON_OBJECT()),  
('Compras.gov', 'COMPRAS_GOV', 'SCRAPING', 'https://www.gov.br/compras/pt-br', 0, 180, JSON_OBJECT()),  
('Licitações-e', 'LICITACOES_E', 'SCRAPING', 'https://www.licitacoes-e.com.br', 0, 180, JSON_OBJECT());

## 3. Super admin inicial

Você pode inserir um super admin manualmente depois que gerar o hash da senha em PHP com `password_hash()`. Não recomendo armazenar senha em texto puro no SQL.

Estrutura do insert:

INSERT INTO empresas (  
    razao_social, nome_fantasia, cnpj, segmento, email_contato, telefone, status  
) VALUES (  
    'OpenAI Admin Interno', 'Admin Interno', NULL, 'Tecnologia', 'admin@seudominio.com', NULL, 'ATIVA'  
);  
  
INSERT INTO usuarios (  
    empresa_id, nome, email, senha_hash, perfil, status  
) VALUES (  
    1, 'Administrador Master', 'admin@seudominio.com', 'COLE_AQUI_O_HASH_GERADO_NO_PHP', 'SUPER_ADMIN', 'ATIVO'  
);  
  
INSERT INTO assinaturas (  
    empresa_id, plano_id, status, data_inicio, data_fim, observacao  
) VALUES (  
    1, 3, 'ATIVA', CURDATE(), NULL, 'Conta administrativa inicial'  
);

## 4. Ajustes estratégicos importantes

Alguns pontos técnicos precisam ficar claros.

O uso de `JSON` em `perfis_monitoramento`, `fontes_coleta`, `correspondencias`, `logs_sistema`, `exportacoes` e `auditorias` é aceitável no MVP porque reduz complexidade estrutural. Mas há um limite: se você passar a consultar esses campos de forma intensa com filtros SQL complexos, o desempenho cai. Então essa escolha é boa para fase inicial, não para exagerar.

A `UNIQUE KEY` em `hash_unico` na tabela `editais` é correta para o problema de duplicidade, mas só funciona bem se o backend gerar esse hash com normalização rígida. Se você gerar hash usando campos brutos e instáveis, vai duplicar mesmo com índice único.

A `UNIQUE KEY (edital_id, empresa_id, perfil_monitoramento_id)` em `correspondencias` é útil, mas tem um ponto técnico delicado: em MySQL, colunas `NULL` em índice único podem permitir mais de um registro aparentemente duplicado. Então, se você pretende permitir `perfil_monitoramento_id` nulo com frequência, a arquitetura de inserção deve controlar isso no service. A alternativa mais forte seria tornar `perfil_monitoramento_id` obrigatório ou criar uma chave técnica separada. Para o MVP, o controle no backend é suficiente.

## 5. Melhorias futuras já previstas

Essa estrutura já suporta expansão para:

- cobrança recorrente real com gateway
    
- múltiplos perfis por empresa
    
- relatórios exportados com histórico
    
- auditoria de ações administrativas
    
- novos coletores de fontes
    
- motor de recomendação mais inteligente
    
- rastreamento detalhado de uso por cliente
    

Ou seja: o banco nasce simples o suficiente para o MVP, mas sem bloquear crescimento.

## 6. Ordem correta de execução no sistema

Depois de criar esse banco, a sequência técnica mais segura é:

1. conectar o `Database.php` ao banco `saas_editais`
    
2. implementar autenticação e sessão
    
3. cadastrar super admin
    
4. construir CRUD de empresas e usuários
    
5. implementar CRUD de perfis de monitoramento
    
6. integrar a coleta PNCP
    
7. salvar editais com deduplicação
    
8. montar listagem e filtros
    
9. criar motor de correspondência
    
10. depois alertas e dashboard