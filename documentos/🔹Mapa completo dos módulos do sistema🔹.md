# 1. Módulo de Autenticação e Acesso

## Objetivo

Controlar entrada no sistema, sessão do usuário, recuperação de acesso e regras básicas de autenticação.

## Telas

- Login
    
- Esqueci minha senha
    
- Redefinir senha
    
- Primeiro acesso
    
- Tela de bloqueio ou acesso negado
    

## Ações

- autenticar usuário
    
- encerrar sessão
    
- solicitar redefinição de senha
    
- redefinir senha
    
- validar sessão ativa
    
- controlar tentativas de login
    
- verificar perfil de acesso
    

## Controllers

- `AuthController`
    
- `PasswordController`
    

## Services

- `AuthService`
    
- `PasswordResetService`
    
- `SessionService`
    
- `AuditLoginService`
    

## Tabelas relacionadas

- `usuarios`
    
- `logs_sistema`
    
- `tokens_recuperacao_senha`
    

## Observações técnicas

Esse módulo deve ser entregue cedo porque todo o restante depende dele. Não precisa começar com autenticação complexa por token; sessão segura em PHP já resolve o MVP.

---

# 2. Módulo de Empresas e Conta SaaS

## Objetivo

Gerenciar a empresa cliente, seus dados institucionais, plano contratado e usuários vinculados.

## Telas

- Perfil da empresa
    
- Editar dados da empresa
    
- Lista de usuários da empresa
    
- Cadastro de usuário
    
- Editar usuário
    
- Status da assinatura/plano
    

## Ações

- cadastrar empresa
    
- editar empresa
    
- criar usuário vinculado à empresa
    
- ativar/inativar usuário
    
- definir perfil do usuário
    
- consultar plano
    
- consultar limites do plano
    

## Controllers

- `EmpresaController`
    
- `UsuarioController`
    
- `PlanoController`
    
- `AssinaturaController`
    

## Services

- `EmpresaService`
    
- `UsuarioService`
    
- `PlanoService`
    
- `AssinaturaService`
    

## Tabelas relacionadas

- `empresas`
    
- `usuarios`
    
- `planos`
    
- `assinaturas`
    

## Observações técnicas

Mesmo no MVP, isso já deve nascer com estrutura multiempresa. Se você tratar tudo como usuário solto, vai ter refatoração desnecessária depois.

---

# 3. Módulo de Perfis de Monitoramento

## Objetivo

Permitir que cada empresa configure seus critérios de interesse para que o sistema identifique oportunidades relevantes.

## Telas

- Lista de perfis de monitoramento
    
- Criar perfil de monitoramento
    
- Editar perfil
    
- Gerenciar palavras-chave
    
- Configurar preferências por UF
    
- Configurar modalidades
    
- Configurar órgãos de interesse
    
- Configurar faixa de valor
    
- Configurar frequência de alertas
    

## Ações

- criar perfil
    
- editar perfil
    
- excluir perfil
    
- adicionar palavra-chave
    
- editar peso da palavra-chave
    
- ativar/desativar palavra-chave
    
- definir filtros preferenciais
    
- configurar frequência de alerta
    

## Controllers

- `PerfilMonitoramentoController`
    
- `PalavraChaveController`
    

## Services

- `PerfilMonitoramentoService`
    
- `PalavraChaveService`
    
- `PreferenciaMonitoramentoService`
    

## Tabelas relacionadas

- `perfis_monitoramento`
    
- `palavras_chave`
    
- possivelmente campos JSON auxiliares em `perfis_monitoramento` para UFs, modalidades e órgãos
    

## Observações técnicas

Esse módulo é o que transforma o sistema de “busca de editais” em SaaS inteligente. Sem ele, o produto perde diferenciação.

---

# 4. Módulo de Fontes de Coleta

## Objetivo

Cadastrar, parametrizar e controlar as fontes públicas utilizadas para ingestão de editais.

## Telas

- Lista de fontes
    
- Detalhe da fonte
    
- Configuração da fonte
    
- Histórico operacional da fonte
    
- Status da fonte
    

## Ações

- cadastrar fonte
    
- editar parâmetros de fonte
    
- ativar/desativar fonte
    
- consultar última execução
    
- consultar falhas
    
- consultar volume coletado
    

## Controllers

- `FonteController`
    

## Services

- `FonteService`
    
- `FonteStatusService`
    

## Tabelas relacionadas

- `fontes_coleta`
    
- `coletas_execucao`
    
- `logs_sistema`
    

## Observações técnicas

No MVP, algumas fontes podem vir pré-cadastradas internamente. Ainda assim, a arquitetura deve prever administração e status operacional.

---

# 5. Módulo de Coleta e Ingestão de Editais

## Objetivo

Executar a captura de dados das fontes, normalizar os registros e persistir os editais no banco.

## Telas

- Painel de execuções de coleta
    
- Histórico de coletas
    
- Log detalhado de execução
    
- Disparo manual de coleta
    
- Resumo operacional por fonte
    

## Ações

- executar coleta automática
    
- executar coleta manual
    
- registrar início e fim da execução
    
- contabilizar lidos, inseridos, atualizados, duplicados e erros
    
- armazenar dados brutos quando necessário
    
- persistir dados normalizados
    

## Controllers

- `ColetaController`
    
- `AdminColetaController`
    

## Services

- `ColetaService`
    
- `ImportacaoEditalService`
    
- `NormalizacaoService`
    
- `DeduplicacaoService`
    
- `MapperService` ou mappers específicos por fonte
    

## Jobs

- `ColetarPncpJob`
    
- `ColetarComprasGovJob`
    
- `ColetarLicitacoesEJob`
    

## Tabelas relacionadas

- `fontes_coleta`
    
- `coletas_execucao`
    
- `editais`
    
- `edital_documentos`
    
- `logs_sistema`
    

## Observações técnicas

Esse é um módulo operacional, não apenas visual. A maior parte do valor do produto depende dele funcionar com estabilidade.

---

# 6. Módulo de Catálogo de Editais

## Objetivo

Exibir ao usuário os editais disponíveis de forma consultável, filtrável e ordenável.

## Telas

- Listagem de editais
    
- Detalhe do edital
    
- Filtros avançados
    
- Histórico recente
    
- Resultado de busca
    

## Ações

- listar editais
    
- filtrar por UF
    
- filtrar por órgão
    
- filtrar por modalidade
    
- filtrar por período
    
- filtrar por valor
    
- buscar por termo
    
- ordenar por data
    
- ordenar por relevância
    
- visualizar detalhes
    
- abrir link externo do edital
    

## Controllers

- `EditalController`
    
- `BuscaController`
    

## Services

- `EditalService`
    
- `BuscaEditalService`
    
- `FiltroEditalService`
    

## Tabelas relacionadas

- `editais`
    
- `edital_documentos`
    
- eventualmente `correspondencias` para ordenar por relevância ao usuário
    

## Observações técnicas

Esse módulo precisa ser rápido. A experiência do usuário cai muito se a busca for lenta ou mal filtrada.

---

# 7. Módulo de Correspondência Inteligente

## Objetivo

Cruzar editais novos com os perfis das empresas e identificar oportunidades relevantes.

## Telas

- Oportunidades recomendadas
    
- Detalhe da relevância
    
- Histórico de correspondências
    
- Painel de prioridade
    

## Ações

- calcular score por edital e empresa
    
- classificar relevância
    
- registrar motivo da correspondência
    
- listar oportunidades relevantes
    
- recalcular score
    
- marcar correspondência como alertada
    

## Controllers

- `CorrespondenciaController`
    
- `OportunidadeController`
    

## Services

- `CorrespondenciaService`
    
- `ScoreService`
    
- `RelevanciaService`
    

## Jobs

- `ProcessarCorrespondenciasJob`
    
- `RecalcularScoresJob`
    

## Tabelas relacionadas

- `correspondencias`
    
- `perfis_monitoramento`
    
- `palavras_chave`
    
- `editais`
    
- `empresas`
    

## Observações técnicas

No MVP, o score pode ser baseado em regra. Não é necessário começar com IA pesada. O mais importante é que o motor seja previsível, ajustável e auditável.

---

# 8. Módulo de Favoritos e Acompanhamento

## Objetivo

Permitir que o cliente acompanhe editais importantes dentro do sistema.

## Telas

- Lista de favoritos
    
- Detalhe do favorito
    
- Status de acompanhamento
    
- Histórico de acompanhamento
    

## Ações

- adicionar favorito
    
- remover favorito
    
- atualizar status
    
- registrar observação
    
- filtrar favoritos por status
    

## Controllers

- `FavoritoController`
    

## Services

- `FavoritoService`
    
- `AcompanhamentoEditalService`
    

## Tabelas relacionadas

- `favoritos`
    
- `editais`
    
- `empresas`
    

## Observações técnicas

Esse módulo aumenta retenção, porque insere o sistema na rotina operacional do cliente.

---

# 9. Módulo de Alertas e Notificações

## Objetivo

Distribuir oportunidades relevantes e resumos operacionais para o cliente.

## Telas

- Configuração de alertas
    
- Histórico de alertas enviados
    
- Visualização de alerta
    
- Preferências de notificação
    

## Ações

- configurar periodicidade
    
- configurar canal
    
- gerar alerta
    
- enviar e-mail
    
- marcar alerta como enviado
    
- registrar falha de envio
    
- listar alertas enviados
    

## Controllers

- `AlertaController`
    
- `NotificacaoController`
    

## Services

- `AlertaService`
    
- `NotificacaoService`
    
- `EmailService`
    
- `TemplateEmailService`
    

## Jobs

- `EnviarAlertasJob`
    

## Tabelas relacionadas

- `alertas`
    
- `correspondencias`
    
- `empresas`
    
- `usuarios`
    

## Observações técnicas

O sistema não deve enviar excesso de mensagens. É melhor agrupar por relevância e frequência configurada.

---

# 10. Módulo de Dashboard do Cliente

## Objetivo

Entregar visão executiva e operacional das oportunidades e da atividade recente da conta.

## Telas

- Dashboard principal
    
- Cards de métricas
    
- Gráficos de editais por período
    
- Top áreas
    
- Top órgãos
    
- Oportunidades prioritárias
    
- Resumo de favoritos
    
- Resumo de alertas
    

## Ações

- exibir métricas principais
    
- montar rankings
    
- exibir tendências
    
- listar editais recentes
    
- listar oportunidades prioritárias
    

## Controllers

- `DashboardController`
    

## Services

- `DashboardService`
    
- `EstatisticaService`
    
- `IndicadorService`
    

## Tabelas relacionadas

- `editais`
    
- `correspondencias`
    
- `favoritos`
    
- `alertas`
    

## Observações técnicas

Esse módulo é vitrine, mas não é o coração do produto. O erro seria priorizá-lo antes da qualidade da coleta e da correspondência.

---

# 11. Módulo de Relatórios e Exportações

## Objetivo

Permitir que o cliente exporte dados relevantes do sistema para uso interno.

## Telas

- Exportar resultados
    
- Histórico de exportações
    
- Relatórios por período
    
- Relatórios de oportunidades
    
- Relatórios de favoritos
    

## Ações

- exportar CSV
    
- exportar PDF
    
- exportar resultado filtrado
    
- registrar exportação
    
- consultar histórico
    

## Controllers

- `RelatorioController`
    
- `ExportacaoController`
    

## Services

- `RelatorioService`
    
- `ExportacaoService`
    
- `PdfService`
    
- `CsvService`
    

## Tabelas relacionadas

- `editais`
    
- `favoritos`
    
- `correspondencias`
    
- opcionalmente `exportacoes` para histórico
    

## Observações técnicas

Esse módulo pode entrar após a estabilização do catálogo e dos filtros.

---

# 12. Módulo Administrativo do Sistema

## Objetivo

Dar à operação interna controle sobre fontes, empresas, coletas, falhas e saúde geral da plataforma.

## Telas

- Dashboard administrativo
    
- Lista de empresas
    
- Detalhe da empresa
    
- Lista de planos
    
- Lista de fontes
    
- Histórico de coletas
    
- Logs do sistema
    
- Painel de falhas
    
- Métricas globais
    

## Ações

- listar empresas
    
- ativar/inativar empresa
    
- consultar uso por empresa
    
- consultar saúde das fontes
    
- executar coleta manual
    
- consultar falhas
    
- consultar métricas globais
    
- gerenciar planos
    

## Controllers

- `AdminController`
    
- `AdminEmpresaController`
    
- `AdminFonteController`
    
- `AdminColetaController`
    
- `AdminPlanoController`
    
- `LogController`
    

## Services

- `AdminDashboardService`
    
- `EmpresaAdminService`
    
- `FonteService`
    
- `ColetaService`
    
- `PlanoService`
    
- `LogService`
    

## Tabelas relacionadas

- `empresas`
    
- `usuarios`
    
- `planos`
    
- `assinaturas`
    
- `fontes_coleta`
    
- `coletas_execucao`
    
- `logs_sistema`
    

## Observações técnicas

Sem esse módulo, você não consegue operar o SaaS com segurança. Ele não precisa ser bonito no começo, mas precisa ser funcional.

---

# 13. Módulo de Logs, Auditoria e Rastreabilidade

## Objetivo

Registrar eventos críticos de segurança, operação e negócio.

## Telas

- Visualização de logs
    
- Auditoria por usuário
    
- Auditoria por empresa
    
- Auditoria de coletas
    
- Falhas recentes
    

## Ações

- registrar login
    
- registrar erro
    
- registrar falha de integração
    
- registrar alteração crítica
    
- consultar auditoria
    

## Controllers

- `LogController`
    
- `AuditoriaController`
    

## Services

- `LoggerService`
    
- `AuditoriaService`
    

## Tabelas relacionadas

- `logs_sistema`
    
- possivelmente `auditorias`
    

## Observações técnicas

Se isso não existir, qualquer problema em produção vira investigação manual e lenta.

---

# 14. Módulo de Documentos de Editais

## Objetivo

Armazenar e relacionar links e metadados de documentos associados aos editais.

## Telas

- Documentos do edital
    
- Download/abertura do documento
    
- Lista de anexos
    

## Ações

- registrar documento vinculado ao edital
    
- listar documentos
    
- abrir link do documento
    
- classificar tipo do documento
    

## Controllers

- `DocumentoEditalController`
    

## Services

- `DocumentoEditalService`
    

## Tabelas relacionadas

- `edital_documentos`
    
- `editais`
    

## Observações técnicas

No MVP, pode ser apenas o vínculo com URL externa. Não há necessidade de armazenar arquivo localmente no início.

---

# 15. Módulo Comercial e Assinaturas

## Objetivo

Sustentar o modelo SaaS baseado em plano e recorrência.

## Telas

- Status do plano
    
- Upgrade/downgrade
    
- Recursos disponíveis
    
- Histórico da assinatura
    
- Pagamentos, se for integrado depois
    

## Ações

- consultar plano
    
- validar limite do plano
    
- restringir recurso por plano
    
- atualizar assinatura
    
- suspender conta
    
- liberar acesso
    

## Controllers

- `PlanoController`
    
- `AssinaturaController`
    
- futuramente `PagamentoController`
    

## Services

- `PlanoService`
    
- `AssinaturaService`
    
- futuramente `PagamentoService`
    

## Tabelas relacionadas

- `planos`
    
- `assinaturas`
    
- futuramente `pagamentos`
    

## Observações técnicas

Mesmo que o MVP ainda não cobre automaticamente, a arquitetura deve prever limites por plano.

---

# 16. Relação consolidada entre módulos e tabelas

Para visualizar a espinha dorsal do sistema:

- **usuarios** → autenticação, empresas, alertas, auditoria
    
- **empresas** → conta SaaS, assinatura, perfis, favoritos, correspondências
    
- **planos** → comercial e restrição de recursos
    
- **assinaturas** → status da conta
    
- **fontes_coleta** → integração e operação
    
- **coletas_execucao** → rastreabilidade operacional
    
- **editais** → núcleo de dados do produto
    
- **edital_documentos** → anexos e links relacionados
    
- **perfis_monitoramento** → preferências da empresa
    
- **palavras_chave** → motor de relevância
    
- **correspondencias** → oportunidades recomendadas
    
- **favoritos** → acompanhamento do cliente
    
- **alertas** → notificações e histórico
    
- **logs_sistema** → operação e segurança
    
- **tokens_recuperacao_senha** → recuperação de acesso
    
- opcionalmente **exportacoes** e **auditorias** → rastreabilidade ampliada
    

---

# 17. Ordem correta de implementação dos módulos

Aqui vale um contraponto estratégico: não faz sentido implementar tudo na ordem visual. A ordem correta é a que reduz risco técnico e coloca o sistema em funcionamento progressivamente.

Sequência recomendada:

1. Autenticação e acesso
    
2. Empresas e usuários
    
3. Perfis de monitoramento
    
4. Fontes de coleta
    
5. Coleta e ingestão PNCP
    
6. Catálogo de editais
    
7. Correspondência inteligente
    
8. Dashboard do cliente
    
9. Favoritos
    
10. Alertas
    
11. Administração operacional
    
12. Relatórios/exportações
    
13. Assinaturas e comercial
    
14. Expansão para novas fontes
    

Essa ordem é mais sólida do que começar por telas bonitas.

---

# 18. Mapa resumido em formato de referência rápida

## Núcleo do produto

- Coleta e ingestão
    
- Normalização
    
- Deduplicação
    
- Catálogo de editais
    
- Correspondência inteligente
    

## Camada de uso do cliente

- Autenticação
    
- Empresa e usuários
    
- Perfis de monitoramento
    
- Dashboard
    
- Favoritos
    
- Alertas
    
- Relatórios
    

## Camada operacional

- Fontes de coleta
    
- Coletas
    
- Administração
    
- Logs e auditoria
    

## Camada comercial

- Planos
    
- Assinaturas
    

---

# 19. Conclusão arquitetural

O mapa completo mostra que o sistema não é apenas um portal de consulta. Ele é uma plataforma com três blocos centrais: **operação de dados**, **inteligência de correspondência** e **uso comercial recorrente pelo cliente**. Essa divisão é coerente com o software que você definiu no documento, incluindo monitoramento automatizado, filtros personalizados, distribuição por alertas, painel web e monetização por assinatura.

O próximo passo mais lógico não é ir direto para código solto. O correto agora é estruturar o **banco de dados completo com todas as tabelas, campos, tipos, chaves primárias, chaves estrangeiras e índices**. Isso vai amarrar tecnicamente tudo o que foi definido aqui.