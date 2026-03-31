A aplicação será organizada em três fluxos principais:

**Fluxo web**  
Usuário acessa rota web → Router identifica a rota → Controller recebe a requisição → Service aplica regra de negócio → Repository consulta ou grava dados → Controller envia dados para a View → View renderiza HTML.

**Fluxo API**  
Frontend JS ou integração externa chama endpoint → Router API resolve → Controller API chama Service → Service processa → Response devolve JSON.

**Fluxo assíncrono / agendado**  
Cron executa arquivo CLI/Job → Job chama Integration/Service → dados são coletados, normalizados, deduplicados e persistidos → logs são gravados.

---

# 2. Estrutura completa de pastas

Estrutura recomendada:

/saas-editais  
│  
├── /app  
│   ├── /Core  
│   ├── /Controllers  
│   ├── /Services  
│   ├── /Repositories  
│   ├── /Models  
│   ├── /Integrations  
│   ├── /Jobs  
│   ├── /Middlewares  
│   ├── /Helpers  
│   └── /Validators  
│  
├── /config  
├── /database  
├── /public  
├── /resources  
│   ├── /views  
│   └── /emails  
├── /routes  
├── /storage  
├── /bootstrap  
├── /docs  
└── composer.json

Agora vou detalhar cada camada.

---

# 3. Camada Core

A pasta `/app/Core` contém a infraestrutura-base do sistema. Ela não pertence ao domínio de edital; ela pertence ao motor da aplicação.

Arquivos recomendados:

/app/Core  
├── App.php  
├── Router.php  
├── Controller.php  
├── Model.php  
├── Database.php  
├── Request.php  
├── Response.php  
├── Session.php  
├── Auth.php  
├── View.php  
├── Validator.php  
├── Csrf.php  
├── Logger.php  
└── ExceptionHandler.php

## Responsabilidade de cada arquivo

**App.php**  
Ponto central de inicialização da aplicação. Carrega configurações, dependências, conexão com banco e sistema de rotas.

**Router.php**  
Mapeia URLs para controllers e métodos. Deve suportar:

- GET
    
- POST
    
- PUT/PATCH, se necessário
    
- DELETE, se necessário
    
- grupos de rotas
    
- middlewares
    

**Controller.php**  
Classe base dos controllers. Deve fornecer utilidades padrão:

- renderização de views
    
- resposta JSON
    
- redirect
    
- acesso ao request
    

**Model.php**  
Classe base para modelos simples, com atributos comuns e utilidades compartilhadas. Não deve concentrar SQL pesado.

**Database.php**  
Responsável pela conexão PDO com o MySQL. Deve usar singleton ou fábrica controlada.

**Request.php**  
Centraliza leitura de:

- query string
    
- POST
    
- arquivos
    
- headers
    
- método HTTP
    

**Response.php**  
Padroniza resposta:

- HTML
    
- JSON
    
- redirecionamento
    
- códigos HTTP
    

**Session.php**  
Gerencia sessão com métodos padronizados:

- set
    
- get
    
- forget
    
- destroy
    

**Auth.php**  
Responsável por autenticação básica:

- login
    
- logout
    
- usuário autenticado
    
- verificação de perfil
    

**View.php**  
Responsável por renderizar templates e layouts.

**Validator.php**  
Validação genérica de entrada.

**Csrf.php**  
Geração e validação de token CSRF.

**Logger.php**  
Escrita de logs em arquivo e, quando necessário, banco de dados.

**ExceptionHandler.php**  
Tratamento centralizado de exceções e erros.

---

# 4. Camada Controllers

A pasta `/app/Controllers` recebe requisições HTTP e decide qual fluxo de negócio acionar. Controller não deve conter lógica de deduplicação, scraping, score ou regras complexas.

Estrutura:

/app/Controllers  
├── AuthController.php  
├── DashboardController.php  
├── EditalController.php  
├── EmpresaController.php  
├── PerfilMonitoramentoController.php  
├── FavoritoController.php  
├── AlertaController.php  
├── RelatorioController.php  
├── AdminController.php  
├── FonteController.php  
├── ColetaController.php  
└── Api  
    ├── EditalApiController.php  
    ├── DashboardApiController.php  
    ├── FavoritoApiController.php  
    └── PerfilApiController.php

## Responsabilidades

**AuthController.php**

- exibir login
    
- processar login
    
- logout
    
- recuperação de senha
    
- cadastro inicial
    

**DashboardController.php**

- montar métricas do painel
    
- listar editais relevantes
    
- exibir indicadores da empresa
    

**EditalController.php**

- listar editais
    
- mostrar detalhes
    
- aplicar filtros
    
- exportar resultados
    

**EmpresaController.php**

- visualizar dados da empresa
    
- editar perfil institucional
    
- gerenciar usuários da empresa
    

**PerfilMonitoramentoController.php**

- cadastrar palavras-chave
    
- definir preferências de monitoramento
    
- configurar UFs, modalidades, órgãos, valores
    

**FavoritoController.php**

- favoritar edital
    
- remover favorito
    
- atualizar status de acompanhamento
    

**AlertaController.php**

- listar alertas enviados
    
- configurar frequência de alertas
    
- registrar preferências de canal
    

**RelatorioController.php**

- gerar relatórios PDF/CSV
    
- histórico de exportações
    

**AdminController.php**

- painel operacional do sistema
    
- visão de empresas, planos, métricas globais
    

**FonteController.php**

- listar fontes de coleta
    
- ativar/desativar fonte
    
- ver status de integração
    

**ColetaController.php**

- disparo manual de coleta
    
- consulta de logs de execução
    
- reprocessamento, se autorizado
    

**Controllers de API**  
Mesma função dos web controllers, mas retornando JSON.

---

# 5. Camada Services

Essa é a camada mais importante do projeto. Toda regra de negócio deve morar aqui.

Estrutura sugerida:

/app/Services  
├── AuthService.php  
├── UsuarioService.php  
├── EmpresaService.php  
├── PlanoService.php  
├── AssinaturaService.php  
├── EditalService.php  
├── BuscaEditalService.php  
├── PerfilMonitoramentoService.php  
├── CorrespondenciaService.php  
├── ScoreService.php  
├── FavoritoService.php  
├── AlertaService.php  
├── EmailService.php  
├── RelatorioService.php  
├── DashboardService.php  
├── FonteService.php  
├── ColetaService.php  
├── NormalizacaoService.php  
├── DeduplicacaoService.php  
├── ImportacaoEditalService.php  
└── AuditoriaService.php

## Responsabilidades detalhadas

**AuthService.php**

- validar credenciais
    
- autenticar usuário
    
- controlar sessão de login
    

**UsuarioService.php**

- criar usuário
    
- atualizar usuário
    
- ativar/desativar usuário
    

**EmpresaService.php**

- cadastro e manutenção da empresa
    
- vínculo de usuários à empresa
    

**PlanoService.php / AssinaturaService.php**

- regras de limites do plano
    
- verificação de permissões comerciais
    

**EditalService.php**

- leitura principal de editais
    
- detalhamento
    
- status
    
- montagem de dados para views
    

**BuscaEditalService.php**

- filtros avançados
    
- paginação
    
- ordenação
    
- busca textual
    

**PerfilMonitoramentoService.php**

- manutenção dos critérios do cliente
    
- salvar palavras-chave e preferências
    

**CorrespondenciaService.php**

- relacionar editais com empresas/perfis
    
- gravar oportunidades relevantes
    

**ScoreService.php**

- cálculo de relevância do edital com base em regras
    

**FavoritoService.php**

- status do edital no fluxo do cliente
    

**AlertaService.php**

- decidir o que alertar
    
- montar alertas
    
- registrar envio
    

**EmailService.php**

- montar template
    
- disparar e-mail
    
- logar status de envio
    

**RelatorioService.php**

- geração de relatórios operacionais e para o cliente
    

**DashboardService.php**

- métricas do painel
    
- cards
    
- séries temporais
    
- rankings
    

**FonteService.php**

- gerenciamento das fontes
    
- leitura de parâmetros
    
- status operacional
    

**ColetaService.php**

- orquestrar integrações
    
- disparar mapeamento
    
- chamar normalização
    
- persistir resultados
    

**NormalizacaoService.php**

- transformar formatos heterogêneos em schema padrão
    

**DeduplicacaoService.php**

- detectar duplicidade e decidir insert/update/ignorar
    

**ImportacaoEditalService.php**

- pipeline completo de ingestão
    

**AuditoriaService.php**

- registrar eventos críticos do sistema
    

---

# 6. Camada Repositories

Os repositories centralizam queries. Isso evita SQL espalhado em controller, service e job.

Estrutura:

/app/Repositories  
├── UsuarioRepository.php  
├── EmpresaRepository.php  
├── PlanoRepository.php  
├── AssinaturaRepository.php  
├── EditalRepository.php  
├── DocumentoEditalRepository.php  
├── FonteRepository.php  
├── ColetaExecucaoRepository.php  
├── PerfilMonitoramentoRepository.php  
├── PalavraChaveRepository.php  
├── CorrespondenciaRepository.php  
├── FavoritoRepository.php  
├── AlertaRepository.php  
├── LogSistemaRepository.php  
└── AuditoriaRepository.php

## Responsabilidades

**UsuarioRepository.php**  
Queries de usuários.

**EmpresaRepository.php**  
Queries de empresas.

**EditalRepository.php**  
Queries centrais de editais:

- insert
    
- update
    
- findById
    
- findByHash
    
- listagem paginada
    
- filtros
    
- estatísticas
    

**FonteRepository.php**  
Consulta e persistência de fontes de coleta.

**ColetaExecucaoRepository.php**  
Registro de cada execução de job.

**PerfilMonitoramentoRepository.php**  
Persistência dos perfis de interesse.

**CorrespondenciaRepository.php**  
Persistência de matches entre editais e empresas.

**FavoritoRepository.php**  
Controle de favoritos e acompanhamento.

**AlertaRepository.php**  
Histórico de alertas enviados ou pendentes.

---

# 7. Camada Models

Os models representam entidades do domínio. Em um PHP mais enxuto, podem ser objetos simples com getters/setters ou estruturas tipadas.

Estrutura:

/app/Models  
├── Usuario.php  
├── Empresa.php  
├── Plano.php  
├── Assinatura.php  
├── FonteColeta.php  
├── ColetaExecucao.php  
├── Edital.php  
├── DocumentoEdital.php  
├── PerfilMonitoramento.php  
├── PalavraChave.php  
├── Correspondencia.php  
├── Favorito.php  
├── Alerta.php  
└── LogSistema.php

## Responsabilidade dos models

- representar a entidade
    
- encapsular atributos
    
- opcionalmente validar estados simples
    
- nunca executar scraping
    
- nunca renderizar HTML
    
- nunca receber responsabilidade de controller
    

Exemplo correto:  
`Edital.php` representa um edital.

Exemplo incorreto:  
`Edital.php` consultar PNCP, montar ranking e enviar e-mail.

---

# 8. Camada Integrations

Aqui ficam as integrações externas. Isso é essencial porque o produto depende de fontes públicas. O documento já define PNCP como fonte principal e outras como fontes complementares.

Estrutura:

/app/Integrations  
├── /Contracts  
│   └── FonteColetaInterface.php  
│  
├── /Pncp  
│   ├── PncpClient.php  
│   ├── PncpMapper.php  
│   └── PncpParser.php  
│  
├── /ComprasGov  
│   ├── ComprasGovClient.php  
│   ├── ComprasGovScraper.php  
│   ├── ComprasGovMapper.php  
│   └── ComprasGovParser.php  
│  
├── /LicitacoesE  
│   ├── LicitacoesEClient.php  
│   ├── LicitacoesEScraper.php  
│   ├── LicitacoesEMapper.php  
│   └── LicitacoesEParser.php  
│  
└── /Mail  
    └── MailClient.php

## Responsabilidades

**FonteColetaInterface.php**  
Contrato comum para qualquer fonte. Exemplo de métodos:

- `fetch(array $params): array`
    
- `map(array $raw): array`
    

**PncpClient.php**  
Requisição HTTP para endpoints do PNCP.

**PncpMapper.php**  
Transforma JSON bruto do PNCP em estrutura interna.

**ComprasGovScraper.php**  
Faz scraping estruturado.

**ComprasGovMapper.php**  
Mapeia HTML extraído para schema padrão.

**LicitacoesEScraper.php**  
Scraping específico do Licitações-e.

**MailClient.php**  
Envio de e-mails do sistema.

A vantagem dessa camada é clara: se o HTML de uma fonte mudar, você corrige só a integração, sem quebrar o resto.

---

# 9. Camada Jobs

Os jobs são responsáveis pela execução agendada e por processos operacionais pesados.

Estrutura:

/app/Jobs  
├── ColetarPncpJob.php  
├── ColetarComprasGovJob.php  
├── ColetarLicitacoesEJob.php  
├── ProcessarCorrespondenciasJob.php  
├── EnviarAlertasJob.php  
├── RecalcularScoresJob.php  
├── LimparLogsJob.php  
└── ExportarRelatorioJob.php

## Responsabilidades

**ColetarPncpJob.php**  
Executa coleta PNCP.

**ColetarComprasGovJob.php**  
Executa scraping Compras.gov.

**ColetarLicitacoesEJob.php**  
Executa scraping Licitações-e.

**ProcessarCorrespondenciasJob.php**  
Busca editais novos e calcula matches com perfis.

**EnviarAlertasJob.php**  
Agrupa correspondências pendentes e envia alertas.

**RecalcularScoresJob.php**  
Reprocessa score quando houver mudança de regras.

**LimparLogsJob.php**  
Remove logs antigos e arquivos temporários.

---

# 10. Camada Middlewares

Middlewares executam verificações antes do controller.

Estrutura:

/app/Middlewares  
├── AuthMiddleware.php  
├── GuestMiddleware.php  
├── AdminMiddleware.php  
├── EmpresaAtivaMiddleware.php  
├── PlanoAtivoMiddleware.php  
├── CsrfMiddleware.php  
└── RateLimitMiddleware.php

## Responsabilidades

**AuthMiddleware.php**  
Exige login.

**GuestMiddleware.php**  
Impede usuário logado de acessar login/cadastro novamente.

**AdminMiddleware.php**  
Restringe rotas administrativas.

**EmpresaAtivaMiddleware.php**  
Bloqueia empresa inativa ou suspensa.

**PlanoAtivoMiddleware.php**  
Garante acesso apenas a clientes com plano válido.

**CsrfMiddleware.php**  
Valida token CSRF em formulários.

**RateLimitMiddleware.php**  
Protege endpoints sensíveis contra abuso.

---

# 11. Camada Helpers

Helpers são utilitários genéricos. Devem ser pequenos e sem regra de negócio complexa.

Estrutura:

/app/Helpers  
├── DateHelper.php  
├── StringHelper.php  
├── HashHelper.php  
├── PaginationHelper.php  
├── CurrencyHelper.php  
└── ArrayHelper.php

## Responsabilidades

**DateHelper.php**  
Formatação e conversão de datas.

**StringHelper.php**  
Sanitização, slug, truncamento, normalização textual.

**HashHelper.php**  
Geração de hash de duplicidade.

**PaginationHelper.php**  
Montagem de paginação.

**CurrencyHelper.php**  
Formatação monetária.

---

# 12. Camada Validators

Para validações mais específicas que não devem ficar em controller.

Estrutura:

/app/Validators  
├── LoginValidator.php  
├── UsuarioValidator.php  
├── EmpresaValidator.php  
├── PerfilMonitoramentoValidator.php  
├── EditalFiltroValidator.php  
└── FonteColetaValidator.php

---

# 13. Camada Views

As views ficam em `/resources/views`. Devem ser fragmentadas e organizadas por módulo.

Estrutura:

/resources/views  
├── /layouts  
│   ├── app.php  
│   ├── auth.php  
│   └── admin.php  
│  
├── /components  
│   ├── header.php  
│   ├── sidebar.php  
│   ├── footer.php  
│   ├── alerts.php  
│   ├── pagination.php  
│   ├── filters.php  
│   └── cards.php  
│  
├── /auth  
│   ├── login.php  
│   ├── forgot-password.php  
│   └── reset-password.php  
│  
├── /dashboard  
│   └── index.php  
│  
├── /editais  
│   ├── index.php  
│   ├── show.php  
│   └── partials  
│       ├── table.php  
│       ├── filters.php  
│       └── details-card.php  
│  
├── /empresa  
│   ├── profile.php  
│   └── users.php  
│  
├── /perfil-monitoramento  
│   ├── index.php  
│   └── form.php  
│  
├── /favoritos  
│   └── index.php  
│  
├── /alertas  
│   └── index.php  
│  
├── /admin  
│   ├── dashboard.php  
│   ├── fontes.php  
│   ├── coletas.php  
│   └── logs.php  
│  
└── /errors  
    ├── 403.php  
    ├── 404.php  
    └── 500.php

## Regra arquitetural para views

A view deve:

- exibir dados
    
- chamar componentes
    
- conter loops e condicionais simples
    

A view não deve:

- executar query
    
- chamar integração externa
    
- calcular score
    
- montar regra de filtro complexa
    

---

# 14. Assets frontend

Como o documento exige separação de CSS e JS por página/módulo, a organização deve refletir isso.

Estrutura:

/public/assets  
├── /css  
│   ├── global.css  
│   ├── variables.css  
│   ├── components.css  
│   ├── auth.css  
│   ├── dashboard.css  
│   ├── editais.css  
│   ├── empresa.css  
│   ├── perfil-monitoramento.css  
│   ├── favoritos.css  
│   └── admin.css  
│  
├── /js  
│   ├── global.js  
│   ├── auth.js  
│   ├── dashboard.js  
│   ├── editais.js  
│   ├── perfil-monitoramento.js  
│   ├── favoritos.js  
│   └── admin.js  
│  
├── /img  
└── /icons

---

# 15. Arquivos de configuração

Estrutura:

/config  
├── app.php  
├── database.php  
├── mail.php  
├── auth.php  
├── routes.php  
├── logging.php  
├── plans.php  
├── sources.php  
└── cron.php

## Responsabilidades

**app.php**  
Nome do sistema, URL base, timezone, ambiente.

**database.php**  
Conexão MySQL.

**mail.php**  
Configurações SMTP.

**auth.php**  
Tempo de sessão, políticas de login.

**plans.php**  
Limites dos planos.

**sources.php**  
Configuração das fontes de coleta.

**cron.php**  
Frequências recomendadas.

---

# 16. Estrutura de rotas do projeto

Agora a parte crítica: organização de rotas.

A separação correta é:

/routes  
├── web.php  
├── api.php  
└── cli.php

## 16.1 Rotas web

As rotas web servem páginas HTML e processam formulários.

### Exemplo de estrutura lógica

GET    /                        -> DashboardController@index  
GET    /login                   -> AuthController@loginForm  
POST   /login                   -> AuthController@login  
POST   /logout                  -> AuthController@logout  
  
GET    /dashboard               -> DashboardController@index  
  
GET    /editais                 -> EditalController@index  
GET    /editais/{id}            -> EditalController@show  
GET    /editais/exportar/csv    -> EditalController@exportCsv  
GET    /editais/exportar/pdf    -> EditalController@exportPdf  
  
GET    /empresa/perfil          -> EmpresaController@profile  
POST   /empresa/perfil          -> EmpresaController@updateProfile  
GET    /empresa/usuarios        -> EmpresaController@users  
POST   /empresa/usuarios        -> EmpresaController@storeUser  
  
GET    /monitoramento           -> PerfilMonitoramentoController@index  
GET    /monitoramento/novo      -> PerfilMonitoramentoController@create  
POST   /monitoramento           -> PerfilMonitoramentoController@store  
GET    /monitoramento/{id}/editar -> PerfilMonitoramentoController@edit  
POST   /monitoramento/{id}      -> PerfilMonitoramentoController@update  
POST   /monitoramento/{id}/delete -> PerfilMonitoramentoController@delete  
  
GET    /favoritos               -> FavoritoController@index  
POST   /favoritos/adicionar     -> FavoritoController@store  
POST   /favoritos/remover       -> FavoritoController@delete  
POST   /favoritos/status        -> FavoritoController@updateStatus  
  
GET    /alertas                 -> AlertaController@index  
POST   /alertas/configuracoes   -> AlertaController@saveSettings

## 16.2 Rotas administrativas

GET    /admin                   -> AdminController@index  
GET    /admin/fontes            -> FonteController@index  
POST   /admin/fontes/{id}/ativar -> FonteController@ativar  
POST   /admin/fontes/{id}/desativar -> FonteController@desativar  
  
GET    /admin/coletas           -> ColetaController@index  
POST   /admin/coletas/pncp      -> ColetaController@executarPncp  
POST   /admin/coletas/comprasgov -> ColetaController@executarComprasGov  
POST   /admin/coletas/licitacoese -> ColetaController@executarLicitacoesE  
  
GET    /admin/logs              -> AdminController@logs  
GET    /admin/empresas          -> AdminController@empresas  
GET    /admin/planos            -> AdminController@planos

---

# 17. Rotas API

As rotas API devolvem JSON para frontend dinâmico e futuras integrações.

### Exemplo

GET    /api/dashboard/metricas              -> DashboardApiController@metricas  
GET    /api/editais                         -> EditalApiController@index  
GET    /api/editais/{id}                    -> EditalApiController@show  
POST   /api/favoritos/toggle                -> FavoritoApiController@toggle  
GET    /api/monitoramento/perfis            -> PerfilApiController@index  
POST   /api/monitoramento/perfis            -> PerfilApiController@store  
PUT    /api/monitoramento/perfis/{id}       -> PerfilApiController@update  
DELETE /api/monitoramento/perfis/{id}       -> PerfilApiController@delete

## Regras para API

- resposta padronizada
    
- status HTTP consistente
    
- autenticação por sessão ou token interno
    
- validação obrigatória
    
- nunca expor dados de outra empresa
    

---

# 18. Rotas CLI / Jobs

As rotas CLI ou comandos internos servem para cron e automação.

Exemplo conceitual:

php public/index.php coleta:pncp  
php public/index.php coleta:comprasgov  
php public/index.php coleta:licitacoese  
php public/index.php processa:correspondencias  
php public/index.php envia:alertas

Ou, se preferir, arquivos dedicados:

/public/cron/coleta_pncp.php  
/public/cron/coleta_comprasgov.php  
/public/cron/coleta_licitacoese.php  
/public/cron/processa_correspondencias.php  
/public/cron/envia_alertas.php

Arquiteturalmente, prefiro **um entry point CLI central**, porque padroniza execução e logging.

---

# 19. Fluxo interno de uma rota MVC

Exemplo prático da rota:

`GET /editais`

Fluxo correto:

1. `Router` identifica a rota
    
2. `AuthMiddleware` valida login
    
3. `PlanoAtivoMiddleware` valida assinatura
    
4. `EditalController@index()` recebe filtros
    
5. `EditalFiltroValidator` valida parâmetros
    
6. `BuscaEditalService` executa a lógica
    
7. `EditalRepository` consulta o banco
    
8. `Controller` chama a `View`
    
9. `resources/views/editais/index.php` renderiza
    

Esse fluxo é o padrão que deve ser replicado.

---

# 20. Responsabilidade por arquivo em um módulo real

Vou exemplificar com o módulo **Editais**.

## Arquivos envolvidos

/app/Controllers/EditalController.php  
/app/Services/EditalService.php  
/app/Services/BuscaEditalService.php  
/app/Repositories/EditalRepository.php  
/app/Models/Edital.php  
/app/Validators/EditalFiltroValidator.php  
/resources/views/editais/index.php  
/resources/views/editais/show.php  
/public/assets/css/editais.css  
/public/assets/js/editais.js

## Função de cada um

**EditalController.php**  
Recebe a requisição web e delega.

**EditalService.php**  
Regras de leitura e detalhe.

**BuscaEditalService.php**  
Filtros, paginação, ordenação.

**EditalRepository.php**  
SQL de busca.

**Edital.php**  
Entidade edital.

**EditalFiltroValidator.php**  
Validação de filtros.

**index.php**  
Tela de listagem.

**show.php**  
Tela de detalhe.

**editais.css / editais.js**  
Comportamento e estilo da interface.

---

# 21. Convenções de nomenclatura

Seu documento já define padrão de nomes claros, camelCase para métodos/atributos e SNAKE_CASE para constantes. Isso deve ser mantido rigidamente.

Recomendo a convenção abaixo:

**Classes**

- PascalCase  
    Ex.: `EditalController`
    

**Métodos**

- camelCase  
    Ex.: `listarEditaisRecentes()`
    

**Variáveis**

- camelCase  
    Ex.: `$dataPublicacao`
    

**Constantes**

- SNAKE_CASE  
    Ex.: `MAX_TENTATIVAS_LOGIN`
    

**Arquivos de classe**

- mesmo nome da classe  
    Ex.: `EditalRepository.php`
    

**Views**

- kebab-case ou nomes curtos padronizados  
    Ex.: `index.php`, `show.php`, `form.php`
    

---

# 22. Erros arquiteturais que você deve evitar

Aqui vai a parte crítica.

**Erro 1 — Controller gordo**  
Se controller faz query, validação, regra e renderização, o projeto degrada rápido.

**Erro 2 — Repository com regra de negócio**  
Repository consulta dados. Ele não decide relevância nem plano de assinatura.

**Erro 3 — Service acessando superglobal diretamente**  
Service não deve depender de `$_POST` ou `$_SESSION`. Isso acopla a regra ao HTTP.

**Erro 4 — View com SQL ou lógica forte**  
Interface deve exibir, não decidir negócio.

**Erro 5 — Integração externa misturada no módulo web**  
PNCP, scraping e parsing devem ficar em `/Integrations`, não dentro de controller.

**Erro 6 — Jobs replicando lógica do service**  
Job deve orquestrar. A lógica deve continuar nos services reaproveitáveis.

---

# 23. Estrutura mínima de bootstrap

Sugestão:

/bootstrap  
├── app.php  
├── routes.php  
├── providers.php  
└── error_handler.php

**bootstrap/app.php**  
Inicializa o sistema.

**bootstrap/routes.php**  
Carrega arquivos de rota.

**bootstrap/error_handler.php**  
Configura tratamento global de erros.

---

# 24. Arquitetura final recomendada

A arquitetura MVC completa do seu SaaS deve seguir este princípio:

**Controllers finos, Services inteligentes, Repositories responsáveis por persistência, Integrations isoladas por fonte, Jobs para automação, Views limpas e rotas separadas por contexto.**

Essa organização é a mais adequada para o tipo de produto descrito no seu documento, porque o sistema não é apenas um CRUD. Ele é um **SaaS operacional orientado a coleta, processamento, correspondência e distribuição de dados públicos**, e isso exige camadas bem separadas para manter estabilidade e evolução.

A sequência técnica mais correta, depois desta etapa, é:

1. definir o **mapa de módulos**
    
2. definir o **banco de dados completo**
    
3. criar a **estrutura de rotas web/api/cli**
    
4. implementar o **core MVC**
    
5. desenvolver primeiro o módulo **Auth + Empresa + Perfil**
    
6. depois integrar o **PNCP**