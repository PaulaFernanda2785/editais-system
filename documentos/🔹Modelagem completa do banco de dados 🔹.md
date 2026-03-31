## 1. Visão geral do banco

O banco do sistema deve ser organizado em seis núcleos:

1. identidade e acesso
    
2. estrutura comercial SaaS
    
3. fontes e coleta
    
4. catálogo de editais
    
5. inteligência e acompanhamento
    
6. operação e auditoria
    

A espinha dorsal fica assim:

- empresas
    
- usuarios
    
- planos
    
- assinaturas
    
- fontes_coleta
    
- coletas_execucao
    
- editais
    
- edital_documentos
    
- perfis_monitoramento
    
- palavras_chave
    
- correspondencias
    
- favoritos
    
- alertas
    
- logs_sistema
    

## 2. Tabela `empresas`

Representa a conta cliente no SaaS.

Campos recomendados:

- `id` BIGINT UNSIGNED PK AI
    
- `razao_social` VARCHAR(200) NOT NULL
    
- `nome_fantasia` VARCHAR(200) NULL
    
- `cnpj` VARCHAR(18) NULL
    
- `segmento` VARCHAR(150) NULL
    
- `email_contato` VARCHAR(150) NULL
    
- `telefone` VARCHAR(30) NULL
    
- `status` ENUM('ATIVA','INATIVA','SUSPENSA') NOT NULL DEFAULT 'ATIVA'
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Observação: `cnpj` pode ser único quando preenchido, mas eu não prenderia o MVP a obrigatoriedade do campo, porque algumas contas de teste ou pré-venda podem não ter isso de início.

## 3. Tabela `usuarios`

Usuários vinculados a uma empresa.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `empresa_id` BIGINT UNSIGNED NOT NULL
    
- `nome` VARCHAR(150) NOT NULL
    
- `email` VARCHAR(150) NOT NULL
    
- `senha_hash` VARCHAR(255) NOT NULL
    
- `perfil` ENUM('SUPER_ADMIN','ADMIN','GESTOR','USUARIO') NOT NULL DEFAULT 'USUARIO'
    
- `status` ENUM('ATIVO','INATIVO','BLOQUEADO') NOT NULL DEFAULT 'ATIVO'
    
- `ultimo_login_em` DATETIME NULL
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Restrições:

- FK `empresa_id` → `empresas.id`
    
- UNIQUE `email`
    

Observação crítica: o e-mail deve ser único globalmente. Evita duplicidade de login entre empresas.

## 4. Tabela `tokens_recuperacao_senha`

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `usuario_id` BIGINT UNSIGNED NOT NULL
    
- `token` VARCHAR(255) NOT NULL
    
- `expira_em` DATETIME NOT NULL
    
- `usado_em` DATETIME NULL
    
- `criado_em` DATETIME NOT NULL
    

Restrições:

- FK `usuario_id` → `usuarios.id`
    
- INDEX em `token`
    

## 5. Tabela `planos`

Modelo comercial do SaaS.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `nome` VARCHAR(100) NOT NULL
    
- `descricao` VARCHAR(255) NULL
    
- `limite_usuarios` INT NOT NULL DEFAULT 1
    
- `limite_palavras_chave` INT NOT NULL DEFAULT 10
    
- `limite_perfis_monitoramento` INT NOT NULL DEFAULT 1
    
- `limite_alertas_dia` INT NOT NULL DEFAULT 1
    
- `limite_exportacoes_mes` INT NOT NULL DEFAULT 5
    
- `valor_mensal` DECIMAL(10,2) NOT NULL DEFAULT 0.00
    
- `status` ENUM('ATIVO','INATIVO') NOT NULL DEFAULT 'ATIVO'
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

## 6. Tabela `assinaturas`

Vínculo entre empresa e plano.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `empresa_id` BIGINT UNSIGNED NOT NULL
    
- `plano_id` BIGINT UNSIGNED NOT NULL
    
- `status` ENUM('ATIVA','PENDENTE','SUSPENSA','CANCELADA','TESTE') NOT NULL DEFAULT 'TESTE'
    
- `data_inicio` DATE NOT NULL
    
- `data_fim` DATE NULL
    
- `gateway_referencia` VARCHAR(150) NULL
    
- `observacao` VARCHAR(255) NULL
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Restrições:

- FK `empresa_id` → `empresas.id`
    
- FK `plano_id` → `planos.id`
    

Observação: no MVP você pode manter uma assinatura ativa por empresa. Depois, se quiser histórico completo, basta não sobrescrever registros.

## 7. Tabela `fontes_coleta`

Cadastro das fontes externas.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `nome` VARCHAR(120) NOT NULL
    
- `codigo` VARCHAR(50) NOT NULL
    
- `tipo` ENUM('API','SCRAPING','MANUAL') NOT NULL
    
- `url_base` VARCHAR(255) NULL
    
- `ativa` TINYINT(1) NOT NULL DEFAULT 1
    
- `periodicidade_minutos` INT NOT NULL DEFAULT 60
    
- `configuracao_json` JSON NULL
    
- `ultima_execucao_em` DATETIME NULL
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Restrições:

- UNIQUE `codigo`
    

Exemplo de `codigo`: `PNCP`, `COMPRAS_GOV`, `LICITACOES_E`.

## 8. Tabela `coletas_execucao`

Rastreia cada execução de job.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `fonte_id` BIGINT UNSIGNED NOT NULL
    
- `iniciado_em` DATETIME NOT NULL
    
- `finalizado_em` DATETIME NULL
    
- `status` ENUM('PROCESSANDO','SUCESSO','ERRO','PARCIAL') NOT NULL DEFAULT 'PROCESSANDO'
    
- `total_lidos` INT NOT NULL DEFAULT 0
    
- `total_inseridos` INT NOT NULL DEFAULT 0
    
- `total_atualizados` INT NOT NULL DEFAULT 0
    
- `total_duplicados` INT NOT NULL DEFAULT 0
    
- `total_erros` INT NOT NULL DEFAULT 0
    
- `mensagem_resumo` VARCHAR(255) NULL
    
- `log_detalhado` LONGTEXT NULL
    
- `criado_em` DATETIME NOT NULL
    

Restrições:

- FK `fonte_id` → `fontes_coleta.id`
    

Essa tabela é obrigatória. Sem ela, você fica cega para saber se a coleta está funcionando.

## 9. Tabela `editais`

Núcleo do sistema.

Campos recomendados:

- `id` BIGINT UNSIGNED PK AI
    
- `fonte_id` BIGINT UNSIGNED NOT NULL
    
- `codigo_fonte` VARCHAR(120) NULL
    
- `numero_edital` VARCHAR(100) NULL
    
- `orgao_nome` VARCHAR(255) NOT NULL
    
- `orgao_poder` VARCHAR(100) NULL
    
- `esfera` ENUM('FEDERAL','ESTADUAL','MUNICIPAL','OUTRA') NULL
    
- `uf` CHAR(2) NULL
    
- `municipio` VARCHAR(120) NULL
    
- `modalidade` VARCHAR(120) NULL
    
- `modo_disputa` VARCHAR(120) NULL
    
- `objeto` LONGTEXT NOT NULL
    
- `descricao_resumida` TEXT NULL
    
- `valor_estimado` DECIMAL(15,2) NULL
    
- `data_publicacao` DATE NULL
    
- `data_abertura` DATETIME NULL
    
- `data_encerramento` DATETIME NULL
    
- `situacao` VARCHAR(80) NULL
    
- `link_detalhe` VARCHAR(500) NULL
    
- `link_edital` VARCHAR(500) NULL
    
- `hash_unico` CHAR(32) NOT NULL
    
- `score_global` DECIMAL(8,2) NOT NULL DEFAULT 0.00
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Restrições e índices:

- FK `fonte_id` → `fontes_coleta.id`
    
- UNIQUE `hash_unico`
    
- INDEX `idx_editais_data_publicacao`
    
- INDEX `idx_editais_data_encerramento`
    
- INDEX `idx_editais_uf`
    
- INDEX `idx_editais_modalidade`
    
- INDEX `idx_editais_orgao_nome`
    
- INDEX `idx_editais_situacao`
    

Observação crítica: `hash_unico` é melhor que tentar impor unicidade por vários campos frágeis. Mas ele deve ser gerado de forma consistente no backend.

## 10. Estratégia do `hash_unico`

Recomendação:

Gerar MD5 com concatenação normalizada de:

- órgão sanitizado
    
- número do edital sanitizado
    
- objeto resumido sanitizado
    
- data_publicacao
    
- código da fonte
    

Exemplo lógico:

`md5(orgao + numero_edital + objeto_base + data_publicacao + fonte)`

Isso conversa diretamente com a necessidade de anti-duplicidade que já estava prevista no documento.

## 11. Tabela `edital_documentos`

Documentos vinculados ao edital.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `edital_id` BIGINT UNSIGNED NOT NULL
    
- `nome_documento` VARCHAR(255) NOT NULL
    
- `url_documento` VARCHAR(500) NOT NULL
    
- `tipo_documento` VARCHAR(80) NULL
    
- `criado_em` DATETIME NOT NULL
    

Restrições:

- FK `edital_id` → `editais.id`
    
- INDEX `idx_edital_documentos_edital`
    

No MVP, basta guardar URL e metadados. Não precisa armazenar binário local.

## 12. Tabela `perfis_monitoramento`

Configuração de interesse por empresa.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `empresa_id` BIGINT UNSIGNED NOT NULL
    
- `nome` VARCHAR(120) NOT NULL
    
- `ufs_json` JSON NULL
    
- `modalidades_json` JSON NULL
    
- `orgaos_json` JSON NULL
    
- `faixa_valor_min` DECIMAL(15,2) NULL
    
- `faixa_valor_max` DECIMAL(15,2) NULL
    
- `frequencia_alerta` ENUM('IMEDIATO','DIARIO','SEMANAL') NOT NULL DEFAULT 'DIARIO'
    
- `ativo` TINYINT(1) NOT NULL DEFAULT 1
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Restrições:

- FK `empresa_id` → `empresas.id`
    

Observação: usar JSON aqui é aceitável para MVP, porque simplifica bastante a configuração de preferências múltiplas sem gerar muitas tabelas auxiliares cedo demais.

## 13. Tabela `palavras_chave`

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `empresa_id` BIGINT UNSIGNED NOT NULL
    
- `perfil_monitoramento_id` BIGINT UNSIGNED NULL
    
- `termo` VARCHAR(150) NOT NULL
    
- `peso` INT NOT NULL DEFAULT 1
    
- `categoria` VARCHAR(80) NULL
    
- `ativo` TINYINT(1) NOT NULL DEFAULT 1
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Restrições:

- FK `empresa_id` → `empresas.id`
    
- FK `perfil_monitoramento_id` → `perfis_monitoramento.id`
    
- INDEX `idx_palavras_empresa`
    

Observação: permitir `perfil_monitoramento_id` nulo dá flexibilidade para palavra-chave global da empresa.

## 14. Tabela `correspondencias`

Liga edital a empresa/perfil com score de relevância.

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `edital_id` BIGINT UNSIGNED NOT NULL
    
- `empresa_id` BIGINT UNSIGNED NOT NULL
    
- `perfil_monitoramento_id` BIGINT UNSIGNED NULL
    
- `score` DECIMAL(8,2) NOT NULL DEFAULT 0.00
    
- `nivel_relevancia` ENUM('BAIXA','MEDIA','ALTA','PRIORITARIA') NOT NULL DEFAULT 'BAIXA'
    
- `motivo_json` JSON NULL
    
- `alertado_em` DATETIME NULL
    
- `criado_em` DATETIME NOT NULL
    

Restrições:

- FK `edital_id` → `editais.id`
    
- FK `empresa_id` → `empresas.id`
    
- FK `perfil_monitoramento_id` → `perfis_monitoramento.id`
    
- UNIQUE (`edital_id`, `empresa_id`, `perfil_monitoramento_id`)
    
- INDEX `idx_correspondencias_empresa_score`
    

Essa tabela é o coração da inteligência do SaaS.

## 15. Tabela `favoritos`

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `empresa_id` BIGINT UNSIGNED NOT NULL
    
- `edital_id` BIGINT UNSIGNED NOT NULL
    
- `status_acompanhamento` ENUM('FAVORITO','EM_ANALISE','PROPOSTA','DESCARTADO','ENCERRADO') NOT NULL DEFAULT 'FAVORITO'
    
- `observacao` TEXT NULL
    
- `criado_em` DATETIME NOT NULL
    
- `atualizado_em` DATETIME NULL
    

Restrições:

- FK `empresa_id` → `empresas.id`
    
- FK `edital_id` → `editais.id`
    
- UNIQUE (`empresa_id`, `edital_id`)
    

## 16. Tabela `alertas`

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `empresa_id` BIGINT UNSIGNED NOT NULL
    
- `usuario_id` BIGINT UNSIGNED NULL
    
- `tipo` ENUM('RESUMO_DIARIO','OPORTUNIDADE','SISTEMA') NOT NULL
    
- `canal` ENUM('EMAIL','PAINEL') NOT NULL DEFAULT 'EMAIL'
    
- `assunto` VARCHAR(200) NOT NULL
    
- `conteudo_resumo` TEXT NULL
    
- `status_envio` ENUM('PENDENTE','ENVIADO','ERRO') NOT NULL DEFAULT 'PENDENTE'
    
- `enviado_em` DATETIME NULL
    
- `criado_em` DATETIME NOT NULL
    

Restrições:

- FK `empresa_id` → `empresas.id`
    
- FK `usuario_id` → `usuarios.id`
    
- INDEX `idx_alertas_empresa_status`
    

## 17. Tabela `logs_sistema`

Campos:

- `id` BIGINT UNSIGNED PK AI
    
- `nivel` ENUM('INFO','WARNING','ERROR','CRITICAL') NOT NULL
    
- `contexto` VARCHAR(100) NOT NULL
    
- `mensagem` VARCHAR(255) NOT NULL
    
- `dados_json` JSON NULL
    
- `criado_em` DATETIME NOT NULL
    

Índices:

- INDEX `idx_logs_contexto`
    
- INDEX `idx_logs_nivel`
    
- INDEX `idx_logs_criado_em`
    

## 18. Tabelas opcionais, mas recomendáveis

Para não inflar demais o MVP, eu trataria estas como fase 2:

**`auditorias`**

- para rastrear ações de usuário em nível fino
    

**`exportacoes`**

- para registrar PDFs e CSVs gerados
    

**`pagamentos`**

- quando houver integração com gateway
    

## 19. Relacionamentos principais

Resumo técnico:

- uma `empresa` possui muitos `usuarios`
    
- uma `empresa` possui muitas `assinaturas` ao longo do tempo
    
- uma `empresa` possui muitos `perfis_monitoramento`
    
- um `perfil_monitoramento` possui muitas `palavras_chave`
    
- uma `fonte_coleta` possui muitas `coletas_execucao`
    
- uma `fonte_coleta` possui muitos `editais`
    
- um `edital` possui muitos `edital_documentos`
    
- um `edital` possui muitas `correspondencias`
    
- uma `empresa` possui muitas `correspondencias`
    
- uma `empresa` possui muitos `favoritos`
    
- uma `empresa` possui muitos `alertas`
    

## 20. Índices estratégicos

Para performance, estes índices são os mais importantes no começo:

Na tabela `editais`:

- `data_publicacao`
    
- `data_encerramento`
    
- `uf`
    
- `modalidade`
    
- `orgao_nome`
    
- `hash_unico`
    

Na tabela `correspondencias`:

- `empresa_id`
    
- `score`
    
- `nivel_relevancia`
    

Na tabela `favoritos`:

- `empresa_id`
    
- `status_acompanhamento`
    

Na tabela `coletas_execucao`:

- `fonte_id`
    
- `status`
    
- `iniciado_em`
    

## 21. Decisões arquiteturais que você não deve errar

Alguns contrapontos importantes.

Primeiro, não criar tudo ultra-normalizado desde o início. Para o seu cenário, usar alguns campos JSON em `perfis_monitoramento` é melhor do que multiplicar tabelas auxiliares sem necessidade.

Segundo, não misturar dado operacional com dado bruto de scraping sem critério. O banco principal deve guardar o dado já normalizado. Se quiser guardar bruto, crie tabela separada depois.

Terceiro, não depender apenas de `numero_edital` para unicidade. Em várias fontes esse campo vem vazio, inconsistente ou repetido.

Quarto, não deixar `empresa_id` de fora das tabelas que representam uso do cliente. Sem isso, o sistema perde isolamento multiempresa.

## 22. Conclusão

A modelagem acima sustenta com segurança os módulos que já definimos: autenticação, empresas, monitoramento, coleta, catálogo, inteligência, favoritos, alertas, relatórios e administração. Ela também permanece coerente com o desenho funcional do seu documento, especialmente quanto ao uso de MySQL, cron jobs, múltiplas fontes e evolução gradual do SaaS.