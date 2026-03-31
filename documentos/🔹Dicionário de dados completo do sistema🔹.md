# 1. Tabela `empresas`

Finalidade: armazena as contas clientes do SaaS.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador único da empresa|
|razao_social|VARCHAR(200)|Não|||Razão social da empresa|
|nome_fantasia|VARCHAR(200)|Sim|||Nome fantasia|
|cnpj|VARCHAR(18)|Sim|UK||CNPJ da empresa|
|segmento|VARCHAR(150)|Sim|IDX||Segmento de atuação|
|email_contato|VARCHAR(150)|Sim|||E-mail principal de contato|
|telefone|VARCHAR(30)|Sim|||Telefone principal|
|status|ENUM('ATIVA','INATIVA','SUSPENSA')|Não|IDX|ATIVA|Situação da empresa no sistema|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 2. Tabela `usuarios`

Finalidade: armazena os usuários vinculados a cada empresa.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do usuário|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa à qual o usuário pertence|
|nome|VARCHAR(150)|Não|||Nome completo|
|email|VARCHAR(150)|Não|UK||E-mail de login|
|senha_hash|VARCHAR(255)|Não|||Hash da senha|
|perfil|ENUM('SUPER_ADMIN','ADMIN','GESTOR','USUARIO')|Não|IDX|USUARIO|Perfil de acesso|
|status|ENUM('ATIVO','INATIVO','BLOQUEADO')|Não|IDX|ATIVO|Situação do usuário|
|ultimo_login_em|DATETIME|Sim|||Data/hora do último login|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 3. Tabela `planos`

Finalidade: define os planos comerciais do SaaS.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do plano|
|nome|VARCHAR(100)|Não|UK||Nome do plano|
|descricao|VARCHAR(255)|Sim|||Descrição resumida|
|limite_usuarios|INT|Não||1|Quantidade máxima de usuários|
|limite_palavras_chave|INT|Não||10|Limite de palavras-chave|
|limite_perfis_monitoramento|INT|Não||1|Limite de perfis por empresa|
|limite_alertas_dia|INT|Não||1|Limite diário de alertas|
|limite_exportacoes_mes|INT|Não||5|Limite mensal de exportações|
|valor_mensal|DECIMAL(10,2)|Não||0.00|Valor mensal do plano|
|status|ENUM('ATIVO','INATIVO')|Não|IDX|ATIVO|Situação do plano|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 4. Tabela `assinaturas`

Finalidade: relaciona empresa e plano contratado.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador da assinatura|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa assinante|
|plano_id|BIGINT UNSIGNED|Não|FK/IDX||Plano contratado|
|status|ENUM('ATIVA','PENDENTE','SUSPENSA','CANCELADA','TESTE')|Não|IDX|TESTE|Estado da assinatura|
|data_inicio|DATE|Não|IDX||Data de início|
|data_fim|DATE|Sim|IDX||Data de término|
|gateway_referencia|VARCHAR(150)|Sim|||Identificador no gateway de pagamento|
|observacao|VARCHAR(255)|Sim|||Observação administrativa|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 5. Tabela `tokens_recuperacao_senha`

Finalidade: controla redefinição de senha.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do token|
|usuario_id|BIGINT UNSIGNED|Não|FK/IDX||Usuário dono do token|
|token|VARCHAR(255)|Não|UK||Token de recuperação|
|expira_em|DATETIME|Não|IDX||Data/hora de expiração|
|usado_em|DATETIME|Sim|||Data/hora de uso|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|

# 6. Tabela `fontes_coleta`

Finalidade: cadastra as fontes externas de editais.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador da fonte|
|nome|VARCHAR(120)|Não|||Nome da fonte|
|codigo|VARCHAR(50)|Não|UK||Código interno da fonte|
|tipo|ENUM('API','SCRAPING','MANUAL')|Não|IDX||Tipo de integração|
|url_base|VARCHAR(255)|Sim|||URL principal da fonte|
|ativa|TINYINT(1)|Não|IDX|1|Fonte ativa ou não|
|periodicidade_minutos|INT|Não||60|Intervalo recomendado de execução|
|configuracao_json|JSON|Sim|||Configurações específicas|
|ultima_execucao_em|DATETIME|Sim|||Última execução conhecida|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 7. Tabela `coletas_execucao`

Finalidade: registra cada execução de coleta.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador da execução|
|fonte_id|BIGINT UNSIGNED|Não|FK/IDX||Fonte executada|
|iniciado_em|DATETIME|Não|IDX||Início da execução|
|finalizado_em|DATETIME|Sim|||Fim da execução|
|status|ENUM('PROCESSANDO','SUCESSO','ERRO','PARCIAL')|Não|IDX|PROCESSANDO|Resultado da execução|
|total_lidos|INT|Não||0|Registros lidos|
|total_inseridos|INT|Não||0|Registros novos inseridos|
|total_atualizados|INT|Não||0|Registros atualizados|
|total_duplicados|INT|Não||0|Registros descartados por duplicidade|
|total_erros|INT|Não||0|Quantidade de erros|
|mensagem_resumo|VARCHAR(255)|Sim|||Resumo curto da execução|
|log_detalhado|LONGTEXT|Sim|||Log detalhado|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação do registro|

# 8. Tabela `editais`

Finalidade: armazena os editais normalizados do sistema.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do edital|
|fonte_id|BIGINT UNSIGNED|Não|FK/IDX||Fonte de origem|
|codigo_fonte|VARCHAR(120)|Sim|IDX||Código original da fonte|
|numero_edital|VARCHAR(100)|Sim|IDX||Número do edital|
|orgao_nome|VARCHAR(255)|Não|IDX||Nome do órgão|
|orgao_poder|VARCHAR(100)|Sim|||Poder/estrutura do órgão|
|esfera|ENUM('FEDERAL','ESTADUAL','MUNICIPAL','OUTRA')|Sim|||Esfera administrativa|
|uf|CHAR(2)|Sim|IDX||Unidade federativa|
|municipio|VARCHAR(120)|Sim|IDX||Município relacionado|
|modalidade|VARCHAR(120)|Sim|IDX||Modalidade da contratação|
|modo_disputa|VARCHAR(120)|Sim|||Modo de disputa|
|objeto|LONGTEXT|Não|||Objeto completo do edital|
|descricao_resumida|TEXT|Sim|||Resumo do objeto|
|valor_estimado|DECIMAL(15,2)|Sim|||Valor estimado|
|data_publicacao|DATE|Sim|IDX||Data de publicação|
|data_abertura|DATETIME|Sim|||Data/hora de abertura|
|data_encerramento|DATETIME|Sim|IDX||Data/hora limite|
|situacao|VARCHAR(80)|Sim|IDX||Situação do edital|
|link_detalhe|VARCHAR(500)|Sim|||Link da página de detalhe|
|link_edital|VARCHAR(500)|Sim|||Link do edital/documento|
|hash_unico|CHAR(32)|Não|UK||Hash para anti-duplicidade|
|score_global|DECIMAL(8,2)|Não|IDX|0.00|Score geral interno|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 9. Tabela `edital_documentos`

Finalidade: relaciona documentos e anexos ao edital.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do documento|
|edital_id|BIGINT UNSIGNED|Não|FK/IDX||Edital relacionado|
|nome_documento|VARCHAR(255)|Não|||Nome do documento|
|url_documento|VARCHAR(500)|Não|||URL do documento|
|tipo_documento|VARCHAR(80)|Sim|IDX||Tipo/classificação do documento|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|

# 10. Tabela `perfis_monitoramento`

Finalidade: define filtros e interesses de cada empresa.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do perfil|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa dona do perfil|
|nome|VARCHAR(120)|Não|||Nome do perfil|
|ufs_json|JSON|Sim|||Lista de UFs de interesse|
|modalidades_json|JSON|Sim|||Modalidades de interesse|
|orgaos_json|JSON|Sim|||Órgãos preferenciais|
|faixa_valor_min|DECIMAL(15,2)|Sim|||Valor mínimo desejado|
|faixa_valor_max|DECIMAL(15,2)|Sim|||Valor máximo desejado|
|frequencia_alerta|ENUM('IMEDIATO','DIARIO','SEMANAL')|Não|IDX|DIARIO|Frequência dos alertas|
|ativo|TINYINT(1)|Não|IDX|1|Perfil ativo|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 11. Tabela `palavras_chave`

Finalidade: armazena os termos usados no motor de correspondência.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador da palavra-chave|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa proprietária|
|perfil_monitoramento_id|BIGINT UNSIGNED|Sim|FK/IDX||Perfil relacionado|
|termo|VARCHAR(150)|Não|IDX||Palavra ou expressão monitorada|
|peso|INT|Não||1|Peso da palavra no score|
|categoria|VARCHAR(80)|Sim|||Categoria opcional|
|ativo|TINYINT(1)|Não|IDX|1|Termo ativo|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 12. Tabela `correspondencias`

Finalidade: relaciona editais com empresas e perfis, com score de relevância.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador da correspondência|
|edital_id|BIGINT UNSIGNED|Não|FK/IDX||Edital relacionado|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa relacionada|
|perfil_monitoramento_id|BIGINT UNSIGNED|Sim|FK/IDX||Perfil usado no cálculo|
|score|DECIMAL(8,2)|Não|IDX|0.00|Score calculado|
|nivel_relevancia|ENUM('BAIXA','MEDIA','ALTA','PRIORITARIA')|Não|IDX|BAIXA|Faixa de relevância|
|motivo_json|JSON|Sim|||Justificativas do match|
|alertado_em|DATETIME|Sim|||Data/hora do alerta|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|

# 13. Tabela `favoritos`

Finalidade: permite acompanhar editais estratégicos.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do favorito|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa dona do favorito|
|edital_id|BIGINT UNSIGNED|Não|FK/IDX/UK||Edital favoritado|
|status_acompanhamento|ENUM('FAVORITO','EM_ANALISE','PROPOSTA','DESCARTADO','ENCERRADO')|Não|IDX|FAVORITO|Status operacional|
|observacao|TEXT|Sim|||Observações do usuário|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|
|atualizado_em|DATETIME|Sim||NULL|Última atualização|

# 14. Tabela `alertas`

Finalidade: registra alertas enviados ou pendentes.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do alerta|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa destinatária|
|usuario_id|BIGINT UNSIGNED|Sim|FK/IDX||Usuário destinatário|
|tipo|ENUM('RESUMO_DIARIO','OPORTUNIDADE','SISTEMA')|Não|IDX||Tipo do alerta|
|canal|ENUM('EMAIL','PAINEL')|Não||EMAIL|Canal utilizado|
|assunto|VARCHAR(200)|Não|||Assunto do alerta|
|conteudo_resumo|TEXT|Sim|||Conteúdo resumido|
|status_envio|ENUM('PENDENTE','ENVIADO','ERRO')|Não|IDX|PENDENTE|Situação do envio|
|enviado_em|DATETIME|Sim|IDX||Data/hora do envio|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|

# 15. Tabela `logs_sistema`

Finalidade: registra eventos operacionais e erros.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador do log|
|nivel|ENUM('INFO','WARNING','ERROR','CRITICAL')|Não|IDX||Nível do log|
|contexto|VARCHAR(100)|Não|IDX||Contexto do evento|
|mensagem|VARCHAR(255)|Não|||Mensagem principal|
|dados_json|JSON|Sim|||Dados complementares|
|criado_em|DATETIME|Não|IDX|CURRENT_TIMESTAMP|Data de criação|

# 16. Tabela `exportacoes`

Finalidade: histórico de arquivos exportados.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador da exportação|
|empresa_id|BIGINT UNSIGNED|Não|FK/IDX||Empresa dona da exportação|
|usuario_id|BIGINT UNSIGNED|Sim|FK/IDX||Usuário que exportou|
|tipo|ENUM('CSV','PDF')|Não|IDX||Tipo de arquivo|
|filtro_json|JSON|Sim|||Filtros usados na exportação|
|caminho_arquivo|VARCHAR(255)|Sim|||Caminho do arquivo gerado|
|criado_em|DATETIME|Não||CURRENT_TIMESTAMP|Data de criação|

# 17. Tabela `auditorias`

Finalidade: rastrear ações críticas do sistema e dos usuários.

|Campo|Tipo|Nulo|Chave|Padrão|Descrição|
|---|---|---|---|---|---|
|id|BIGINT UNSIGNED|Não|PK|AUTO_INCREMENT|Identificador da auditoria|
|empresa_id|BIGINT UNSIGNED|Sim|FK/IDX||Empresa relacionada|
|usuario_id|BIGINT UNSIGNED|Sim|FK/IDX||Usuário responsável|
|acao|VARCHAR(120)|Não|IDX||Ação executada|
|entidade|VARCHAR(120)|Não|IDX||Entidade afetada|
|entidade_id|BIGINT UNSIGNED|Sim|||ID da entidade afetada|
|detalhes_json|JSON|Sim|||Detalhes adicionais|
|criado_em|DATETIME|Não|IDX|CURRENT_TIMESTAMP|Data de criação|

# 18. Resumo das chaves estrangeiras

- `usuarios.empresa_id` → `empresas.id`
    
- `assinaturas.empresa_id` → `empresas.id`
    
- `assinaturas.plano_id` → `planos.id`
    
- `tokens_recuperacao_senha.usuario_id` → `usuarios.id`
    
- `coletas_execucao.fonte_id` → `fontes_coleta.id`
    
- `editais.fonte_id` → `fontes_coleta.id`
    
- `edital_documentos.edital_id` → `editais.id`
    
- `perfis_monitoramento.empresa_id` → `empresas.id`
    
- `palavras_chave.empresa_id` → `empresas.id`
    
- `palavras_chave.perfil_monitoramento_id` → `perfis_monitoramento.id`
    
- `correspondencias.edital_id` → `editais.id`
    
- `correspondencias.empresa_id` → `empresas.id`
    
- `correspondencias.perfil_monitoramento_id` → `perfis_monitoramento.id`
    
- `favoritos.empresa_id` → `empresas.id`
    
- `favoritos.edital_id` → `editais.id`
    
- `alertas.empresa_id` → `empresas.id`
    
- `alertas.usuario_id` → `usuarios.id`
    
- `exportacoes.empresa_id` → `empresas.id`
    
- `exportacoes.usuario_id` → `usuarios.id`
    
- `auditorias.empresa_id` → `empresas.id`
    
- `auditorias.usuario_id` → `usuarios.id`
    

# 19. Observações críticas

Há três pontos que merecem sua atenção.

O primeiro é `correspondencias`. Como `perfil_monitoramento_id` aceita `NULL`, a unicidade composta pode não bloquear todas as duplicidades em alguns cenários de MySQL. Então a prevenção real deve continuar no backend, não apenas no banco.

O segundo é o uso de campos `JSON`. Eles são úteis para acelerar o MVP e manter flexibilidade, mas não devem virar depósito genérico de regra de negócio. Use-os apenas onde a variabilidade compensa.

O terceiro é `hash_unico` em `editais`. Ele é a defesa principal contra duplicidade, então precisa ser gerado sempre pela mesma lógica no PHP.