# 1. Módulo de Autenticação e Acesso

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Autenticação|Realizar login|`usuarios`, `logs_sistema`|`AuthService`|e-mail obrigatório; senha obrigatória; usuário deve existir; senha deve conferir com `senha_hash`; status do usuário deve ser `ATIVO`; empresa vinculada deve estar ativa; assinatura não pode estar suspensa quando a regra exigir|sessão iniciada com sucesso; atualização de `ultimo_login_em`; log de acesso gravado|
|Autenticação|Logout|`logs_sistema`|`AuthService`|usuário autenticado|sessão encerrada; log opcional de logout|
|Autenticação|Solicitar recuperação de senha|`tokens_recuperacao_senha`, `logs_sistema`|`PasswordResetService`|e-mail obrigatório; usuário existente; status do usuário válido|token criado; instrução de redefinição enviada|
|Autenticação|Redefinir senha|`usuarios`, `tokens_recuperacao_senha`, `logs_sistema`|`PasswordResetService`|token válido; token não expirado; token não utilizado; nova senha obrigatória; política mínima de senha|senha atualizada; token invalidado por `usado_em`; resposta de sucesso|
|Autenticação|Bloquear acesso por tentativas inválidas|`usuarios`, `logs_sistema`|`AuthService`|contagem de tentativas acima do limite|usuário marcado como `BLOQUEADO` ou bloqueio temporário registrado|

# 2. Módulo de Empresas

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Empresas|Cadastrar empresa|`empresas`, `auditorias`|`EmpresaService`|razão social obrigatória; CNPJ válido se informado; CNPJ não duplicado; status inicial permitido|empresa criada com `status=ATIVA` ou conforme regra; auditoria registrada|
|Empresas|Editar empresa|`empresas`, `auditorias`|`EmpresaService`|empresa existente; campos válidos; CNPJ único se alterado|empresa atualizada com sucesso|
|Empresas|Suspender empresa|`empresas`, `auditorias`|`EmpresaService`|empresa existente; permissão administrativa|status alterado para `SUSPENSA`; acesso operacional bloqueado|
|Empresas|Inativar empresa|`empresas`, `auditorias`|`EmpresaService`|empresa existente; ausência de impedimento interno|status alterado para `INATIVA`|
|Empresas|Consultar empresa|`empresas`|`EmpresaService`|empresa existente; escopo do usuário compatível com a empresa|dados retornados corretamente|

# 3. Módulo de Usuários

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Usuários|Criar usuário|`usuarios`, `auditorias`|`UsuarioService`|empresa existente; nome obrigatório; e-mail obrigatório; e-mail único; perfil válido; limite de usuários do plano não excedido|usuário criado com sucesso|
|Usuários|Editar usuário|`usuarios`, `auditorias`|`UsuarioService`|usuário existente; e-mail único se alterado; perfil permitido para o operador atual|usuário atualizado|
|Usuários|Alterar perfil|`usuarios`, `auditorias`|`UsuarioService`|usuário existente; perfil de destino válido; permissão suficiente do executor|perfil atualizado|
|Usuários|Inativar usuário|`usuarios`, `auditorias`|`UsuarioService`|usuário existente; não violar regra mínima de administração da empresa|status alterado para `INATIVO`|
|Usuários|Bloquear usuário|`usuarios`, `auditorias`|`UsuarioService`|usuário existente|status alterado para `BLOQUEADO`|
|Usuários|Excluir usuário|`usuarios`, `auditorias`|`UsuarioService`|usuário existente; não ser último admin da empresa; permissão adequada|usuário removido ou exclusão lógica aplicada|

# 4. Módulo de Planos e Assinaturas

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Planos|Criar plano|`planos`, `auditorias`|`PlanoService`|nome obrigatório; nome único; limites numéricos válidos; valor mensal válido|plano criado|
|Planos|Editar plano|`planos`, `auditorias`|`PlanoService`|plano existente; nome único se alterado; limites coerentes|plano atualizado|
|Planos|Inativar plano|`planos`, `auditorias`|`PlanoService`|plano existente|plano fica indisponível para novas assinaturas|
|Assinaturas|Criar assinatura|`assinaturas`, `auditorias`|`AssinaturaService`|empresa existente; plano existente; data inicial válida; não permitir mais de uma assinatura ativa por empresa|assinatura registrada|
|Assinaturas|Alterar plano da empresa|`assinaturas`, `auditorias`|`AssinaturaService`|empresa existente; plano de destino existente; regras comerciais válidas|assinatura anterior encerrada ou suspensa; nova assinatura criada/ativada|
|Assinaturas|Suspender assinatura|`assinaturas`, `auditorias`|`AssinaturaService`|assinatura existente|status alterado para `SUSPENSA`; recursos pagos bloqueados|
|Assinaturas|Encerrar assinatura|`assinaturas`, `auditorias`|`AssinaturaService`|assinatura existente|`status=CANCELADA`; vigência encerrada|

# 5. Módulo de Fontes de Coleta

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Fontes|Cadastrar fonte|`fontes_coleta`, `auditorias`|`FonteService`|nome obrigatório; código obrigatório; código único; tipo válido|fonte criada|
|Fontes|Editar fonte|`fontes_coleta`, `auditorias`|`FonteService`|fonte existente; código único se alterado; periodicidade válida|fonte atualizada|
|Fontes|Ativar fonte|`fontes_coleta`, `auditorias`|`FonteService`|fonte existente|`ativa=1`|
|Fontes|Desativar fonte|`fontes_coleta`, `auditorias`|`FonteService`|fonte existente|`ativa=0`; jobs automáticos deixam de processar|
|Fontes|Atualizar última execução|`fontes_coleta`|`FonteService`|fonte existente; data válida|`ultima_execucao_em` atualizada|

# 6. Módulo de Coleta e Ingestão

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Coleta|Iniciar execução de coleta|`coletas_execucao`, `logs_sistema`|`ColetaService`|fonte ativa; lock de execução não existente; parâmetros mínimos válidos|execução criada com `status=PROCESSANDO`|
|Coleta|Registrar sucesso da coleta|`coletas_execucao`, `fontes_coleta`, `logs_sistema`|`ColetaService`|execução existente; dados estatísticos coerentes|execução encerrada com `SUCESSO`; `ultima_execucao_em` atualizada|
|Coleta|Registrar erro da coleta|`coletas_execucao`, `logs_sistema`|`ColetaService`|execução existente; mensagem de erro disponível|execução encerrada com `ERRO` ou `PARCIAL`; log gravado|
|Coleta|Importar registros brutos|`editais`, `edital_documentos`, `coletas_execucao`|`ImportacaoEditalService`|payload da fonte válido; campos mínimos presentes; mapeamento executado|registros processados e persistidos|
|Coleta|Atualizar edital existente|`editais`|`ImportacaoEditalService`|`hash_unico` localizado; política de atualização válida|edital atualizado sem gerar duplicidade|

# 7. Módulo de Editais

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Editais|Inserir edital novo|`editais`|`EditalService` / `ImportacaoEditalService`|`fonte_id` válido; `orgao_nome` obrigatório; `objeto` obrigatório; `hash_unico` único|edital criado|
|Editais|Atualizar edital|`editais`|`EditalService`|edital existente; dados coerentes; não quebrar integridade|edital atualizado|
|Editais|Consultar detalhes|`editais`, `edital_documentos`|`EditalService`|edital existente; acesso permitido|detalhes completos retornados|
|Editais|Listar com filtros|`editais`|`BuscaEditalService`|filtros válidos; paginação válida; ordenação válida|lista paginada e filtrada|
|Editais|Validar duplicidade|`editais`|`DeduplicacaoService`|`hash_unico` calculado; regra complementar por órgão/número/data quando necessário|decide inserir, atualizar ou ignorar|

# 8. Módulo de Documentos do Edital

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Documentos|Vincular documento ao edital|`edital_documentos`|`DocumentoEditalService`|edital existente; nome do documento obrigatório; URL válida|documento vinculado|
|Documentos|Atualizar documento|`edital_documentos`|`DocumentoEditalService`|documento existente; URL válida|documento atualizado|
|Documentos|Remover documento|`edital_documentos`|`DocumentoEditalService`|documento existente|documento removido|
|Documentos|Listar documentos|`edital_documentos`|`DocumentoEditalService`|edital existente|documentos retornados|

# 9. Módulo de Perfis de Monitoramento

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Perfis|Criar perfil|`perfis_monitoramento`, `auditorias`|`PerfilMonitoramentoService`|empresa existente; nome obrigatório; limite do plano não excedido; faixa mínima menor ou igual à máxima|perfil criado|
|Perfis|Editar perfil|`perfis_monitoramento`, `auditorias`|`PerfilMonitoramentoService`|perfil existente; empresa dona do perfil; dados válidos|perfil atualizado|
|Perfis|Ativar perfil|`perfis_monitoramento`|`PerfilMonitoramentoService`|perfil existente|`ativo=1`|
|Perfis|Inativar perfil|`perfis_monitoramento`|`PerfilMonitoramentoService`|perfil existente|`ativo=0`; perfil sai do matching|
|Perfis|Excluir perfil|`perfis_monitoramento`, `palavras_chave`, `auditorias`|`PerfilMonitoramentoService`|perfil existente; permissão adequada|perfil excluído; palavras-chave associadas removidas|

# 10. Módulo de Palavras-chave

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Palavras-chave|Criar palavra-chave|`palavras_chave`, `auditorias`|`PalavraChaveService`|empresa existente; termo obrigatório; peso >= 1; limite do plano não excedido|palavra-chave criada|
|Palavras-chave|Editar palavra-chave|`palavras_chave`, `auditorias`|`PalavraChaveService`|palavra existente; termo válido; peso válido|palavra atualizada|
|Palavras-chave|Ativar/Inativar palavra|`palavras_chave`|`PalavraChaveService`|palavra existente|status ajustado|
|Palavras-chave|Excluir palavra|`palavras_chave`, `auditorias`|`PalavraChaveService`|palavra existente|palavra removida|

# 11. Módulo de Correspondência Inteligente

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Correspondência|Calcular score por edital e perfil|nenhuma gravação inicial ou `correspondencias`|`ScoreService` / `CorrespondenciaService`|edital válido; perfil ativo; palavras-chave ativas; filtros do perfil válidos|score calculado|
|Correspondência|Registrar correspondência|`correspondencias`|`CorrespondenciaService`|edital existente; empresa existente; evitar duplicidade lógica; score >= 0|correspondência criada|
|Correspondência|Atualizar correspondência|`correspondencias`|`CorrespondenciaService`|correspondência existente; nova pontuação válida|score e nível atualizados|
|Correspondência|Marcar como alertada|`correspondencias`|`CorrespondenciaService`|correspondência existente; alerta efetivamente enviado|`alertado_em` preenchido|
|Correspondência|Reprocessar correspondências|`correspondencias`|`CorrespondenciaService`|regras novas disponíveis; contexto consistente|correspondências recalculadas|

# 12. Módulo de Favoritos

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Favoritos|Adicionar favorito|`favoritos`|`FavoritoService`|empresa existente; edital existente; não duplicar `empresa_id + edital_id`|favorito criado|
|Favoritos|Alterar status de acompanhamento|`favoritos`, `auditorias`|`FavoritoService`|favorito existente; status de destino válido|status atualizado|
|Favoritos|Registrar observação|`favoritos`|`FavoritoService`|favorito existente|observação salva|
|Favoritos|Remover favorito|`favoritos`, `auditorias`|`FavoritoService`|favorito existente|favorito removido|
|Favoritos|Listar favoritos|`favoritos`, `editais`|`FavoritoService`|empresa existente; filtros válidos|favoritos listados|

# 13. Módulo de Alertas e Notificações

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Alertas|Gerar alerta|`alertas`|`AlertaService`|empresa existente; tipo válido; canal válido; conteúdo mínimo definido|alerta criado como `PENDENTE`|
|Alertas|Enviar alerta por e-mail|`alertas`, `logs_sistema`|`EmailService` / `AlertaService`|e-mail de destino válido; canal `EMAIL`; template montado|`status_envio=ENVIADO` ou `ERRO`|
|Alertas|Registrar envio em painel|`alertas`|`AlertaService`|empresa ou usuário válido; conteúdo válido|alerta persistido para exibição|
|Alertas|Configurar frequência|`perfis_monitoramento` ou tabela futura de preferências|`AlertaService` / `PerfilMonitoramentoService`|valor permitido: `IMEDIATO`, `DIARIO`, `SEMANAL`|configuração atualizada|
|Alertas|Evitar duplicidade de alerta|`alertas`, `correspondencias`|`AlertaService`|verificar `alertado_em`; verificar agrupamento por janela|alerta único por regra de envio|

# 14. Módulo de Dashboard

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Dashboard|Obter métricas principais|`editais`, `correspondencias`, `favoritos`, `alertas`|`DashboardService`|empresa existente; escopo do usuário válido|cards e métricas retornados|
|Dashboard|Obter rankings|`editais`, `correspondencias`|`DashboardService` / `EstatisticaService`|filtros válidos|ranking por órgão, modalidade, UF ou relevância|
|Dashboard|Obter tendências|`editais`|`EstatisticaService`|período válido|séries temporais retornadas|

# 15. Módulo de Exportações

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Exportações|Exportar CSV|`exportacoes`|`ExportacaoService` / `CsvService`|empresa existente; usuário válido; limite mensal do plano não excedido; filtros válidos|arquivo CSV gerado; histórico registrado|
|Exportações|Exportar PDF|`exportacoes`|`ExportacaoService` / `PdfService`|empresa existente; usuário válido; limite mensal do plano não excedido|arquivo PDF gerado; histórico registrado|
|Exportações|Consultar histórico|`exportacoes`|`ExportacaoService`|empresa existente|histórico listado|

# 16. Módulo de Logs do Sistema

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Logs|Registrar evento informativo|`logs_sistema`|`LoggerService`|contexto obrigatório; mensagem obrigatória|log gravado|
|Logs|Registrar erro|`logs_sistema`|`LoggerService`|contexto obrigatório; mensagem obrigatória; detalhes quando existirem|erro persistido|
|Logs|Consultar logs|`logs_sistema`|`LogService`|filtros válidos; permissão administrativa|logs retornados|

# 17. Módulo de Auditoria

|Módulo|Ação|Tabela impactada|Service responsável|Validações|Resposta esperada|
|---|---|---|---|---|---|
|Auditoria|Registrar criação de entidade|`auditorias`|`AuditoriaService`|usuário executor identificado quando aplicável; entidade e ação obrigatórias|auditoria registrada|
|Auditoria|Registrar alteração crítica|`auditorias`|`AuditoriaService`|entidade alvo definida; dados mínimos no `detalhes_json`|auditoria registrada|
|Auditoria|Registrar exclusão|`auditorias`|`AuditoriaService`|entidade e ID informados|auditoria registrada|
|Auditoria|Consultar histórico|`auditorias`|`AuditoriaService`|filtros válidos; permissão adequada|histórico retornado|

# 18. Regras transversais obrigatórias

|Regra transversal|Tabela impactada|Service principal|Validação|Resposta esperada|
|---|---|---|---|---|
|Isolamento multiempresa|várias tabelas com `empresa_id`|todos os services de domínio|usuário só pode operar dados da própria empresa, salvo `SUPER_ADMIN`|acesso permitido ou negado|
|Limite por plano|`usuarios`, `perfis_monitoramento`, `palavras_chave`, `exportacoes`, `alertas`|`PlanoService` / `AssinaturaService`|verificar limites antes da ação|operação aceita ou bloqueada com mensagem clara|
|Integridade temporal de edital|`editais`|`EditalService`|datas coerentes|registro aceito ou rejeitado|
|Anti-duplicidade de edital|`editais`|`DeduplicacaoService`|`hash_unico` e regra complementar|inserir, atualizar ou ignorar|
|Anti-execução concorrente de coleta|`coletas_execucao`|`ColetaService`|lock ativo por fonte|nova execução bloqueada|
|Controle de status de empresa/usuário/assinatura|`empresas`, `usuarios`, `assinaturas`|`AuthService` / `AssinaturaService`|status compatíveis com a ação|acesso autorizado ou recusado|

# 19. Observação estratégica

A matriz está sólida para implementação, mas há um ponto que não deve ser negligenciado: algumas regras **não podem depender apenas do banco**. Em especial:

- unicidade lógica em `correspondencias` quando `perfil_monitoramento_id` for `NULL`
    
- limite de uma assinatura ativa por empresa
    
- lock de execução por fonte
    
- deduplicação inteligente de editais
    
- validação do escopo multiempresa
    

Essas regras precisam estar centralizadas nos `Services`, não espalhadas pelos controllers.