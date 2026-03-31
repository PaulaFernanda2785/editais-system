A arquitetura recomendada é em camadas, com separação clara entre:

- apresentação
    
- aplicação
    
- domínio
    
- infraestrutura
    
- dados
    
- automações
    

A estrutura macro fica assim:

Usuário  
  ↓  
Frontend Web (HTML + CSS + JS + Bootstrap)  
  ↓  
Controllers HTTP (PHP)  
  ↓  
Services / Use Cases  
  ↓  
Repositories / Models  
  ↓  
MySQL  
  
Paralelamente:  
Cron Jobs / Workers de Coleta  
  ↓  
Coletadores por fonte  
  ↓  
Normalizador  
  ↓  
Validador / Anti-duplicidade  
  ↓  
Persistência  
  ↓  
Motor de correspondência com perfis de clientes  
  ↓  
Sistema de alertas

Essa separação é importante porque o sistema tem, na prática, **dois mundos diferentes**:

1. o mundo transacional do usuário, onde ele acessa painel, busca editais, salva favoritos, configura palavras-chave;
    
2. o mundo operacional do backend, onde robôs coletam, limpam e classificam dados.
    

Se você misturar tudo em páginas PHP sem isolamento, o sistema vai nascer rápido, mas ficará frágil. Em contrapartida, se tentar construir microserviços desde o início, você vai atrasar desnecessariamente. Para o seu contexto, o melhor caminho é um **monólito modular bem organizado**.

## 2. Arquitetura recomendada: monólito modular

Para MVP e fase inicial de crescimento, a melhor arquitetura é:

**Monólito modular em PHP 8**, com responsabilidades isoladas por domínio.

Isso significa um único sistema, um único deploy e um único banco, mas dividido internamente em módulos independentes.

Módulos principais:

1. Autenticação e contas
    
2. Empresas e perfis de monitoramento
    
3. Fontes de coleta
    
4. Coleta e ingestão de editais
    
5. Normalização e deduplicação
    
6. Catálogo de editais
    
7. Motor de correspondência
    
8. Alertas e notificações
    
9. Favoritos e acompanhamento
    
10. Relatórios e analytics
    
11. Administração do sistema
    
12. Auditoria e logs
    

Essa é a arquitetura certa para startup porque permite:

- lançar rápido
    
- manter simplicidade operacional
    
- crescer por módulos
    
- migrar partes críticas depois, se necessário
    

## 3. Objetivos técnicos da arquitetura

A arquitetura deve perseguir cinco objetivos principais.

Primeiro, **confiabilidade na coleta**. O sistema precisa continuar útil mesmo quando uma fonte falhar.

Segundo, **consistência dos dados**. Edital duplicado destrói a confiança do usuário.

Terceiro, **performance de consulta**. O cliente precisa encontrar oportunidades rapidamente.

Quarto, **facilidade de manutenção**. Novas fontes de coleta devem entrar sem reescrever o sistema inteiro.

Quinto, **capacidade de evolução para IA**. Mesmo que o MVP comece com palavras-chave, a arquitetura já deve aceitar classificação mais inteligente depois. O próprio documento aponta a evolução futura para análise automática, recomendação e tendências.

## 4. Estrutura de pastas profissional

A estrutura abaixo é adequada para startup e compatível com seu stack:

/saas-editais  
│  
├── /app  
│   ├── /Core  
│   │   ├── App.php  
│   │   ├── Router.php  
│   │   ├── Controller.php  
│   │   ├── Model.php  
│   │   ├── Database.php  
│   │   ├── Auth.php  
│   │   ├── Validator.php  
│   │   ├── Response.php  
│   │   └── Logger.php  
│   │  
│   ├── /Controllers  
│   │   ├── AuthController.php  
│   │   ├── DashboardController.php  
│   │   ├── EditalController.php  
│   │   ├── FavoritoController.php  
│   │   ├── AlertaController.php  
│   │   ├── EmpresaController.php  
│   │   ├── PerfilMonitoramentoController.php  
│   │   ├── AdminController.php  
│   │   └── ApiController.php  
│   │  
│   ├── /Services  
│   │   ├── AuthService.php  
│   │   ├── EditalService.php  
│   │   ├── ColetaService.php  
│   │   ├── NormalizacaoService.php  
│   │   ├── DeduplicacaoService.php  
│   │   ├── CorrespondenciaService.php  
│   │   ├── AlertaService.php  
│   │   ├── EmailService.php  
│   │   ├── RelatorioService.php  
│   │   └── EstatisticaService.php  
│   │  
│   ├── /Repositories  
│   │   ├── UsuarioRepository.php  
│   │   ├── EmpresaRepository.php  
│   │   ├── EditalRepository.php  
│   │   ├── FonteRepository.php  
│   │   ├── AlertaRepository.php  
│   │   ├── FavoritoRepository.php  
│   │   └── ColetaExecucaoRepository.php  
│   │  
│   ├── /Models  
│   │   ├── Usuario.php  
│   │   ├── Empresa.php  
│   │   ├── Edital.php  
│   │   ├── FonteColeta.php  
│   │   ├── PalavraChave.php  
│   │   ├── Favorito.php  
│   │   ├── Alerta.php  
│   │   └── ColetaExecucao.php  
│   │  
│   ├── /Jobs  
│   │   ├── ColetorPncpJob.php  
│   │   ├── ColetorComprasGovJob.php  
│   │   ├── ColetorLicitacoesEJob.php  
│   │   ├── ProcessarCorrespondenciasJob.php  
│   │   └── EnviarAlertasJob.php  
│   │  
│   ├── /Integrations  
│   │   ├── /Pncp  
│   │   │   ├── PncpClient.php  
│   │   │   └── PncpMapper.php  
│   │   ├── /ComprasGov  
│   │   │   ├── ComprasGovScraper.php  
│   │   │   └── ComprasGovMapper.php  
│   │   ├── /LicitacoesE  
│   │   │   ├── LicitacoesEScraper.php  
│   │   │   └── LicitacoesEMapper.php  
│   │   └── /Email  
│   │       └── Mailer.php  
│   │  
│   ├── /Middlewares  
│   │   ├── AuthMiddleware.php  
│   │   ├── AdminMiddleware.php  
│   │   └── CsrfMiddleware.php  
│   │  
│   └── /Helpers  
│       ├── DateHelper.php  
│       ├── StringHelper.php  
│       ├── HashHelper.php  
│       └── PaginationHelper.php  
│  
├── /config  
│   ├── app.php  
│   ├── database.php  
│   ├── mail.php  
│   ├── cron.php  
│   └── sources.php  
│  
├── /public  
│   ├── index.php  
│   ├── /assets  
│   │   ├── /css  
│   │   ├── /js  
│   │   ├── /img  
│   │   └── /icons  
│   └── /.htaccess  
│  
├── /resources  
│   ├── /views  
│   │   ├── /layouts  
│   │   ├── /components  
│   │   ├── /auth  
│   │   ├── /dashboard  
│   │   ├── /editais  
│   │   ├── /perfil  
│   │   ├── /favoritos  
│   │   ├── /alertas  
│   │   └── /admin  
│   └── /emails  
│  
├── /storage  
│   ├── /logs  
│   ├── /cache  
│   ├── /exports  
│   └── /temp  
│  
├── /database  
│   ├── schema.sql  
│   ├── seeds.sql  
│   └── migrations  
│  
├── /routes  
│   ├── web.php  
│   ├── api.php  
│   └── cli.php  
│  
├── /docs  
│   └── arquitetura.md  
│  
└── composer.json

Essa organização resolve um problema comum em projetos PHP: o código começa organizado e, após alguns meses, controllers viram depósitos de regra de negócio. Aqui, os controllers ficam finos; a inteligência vai para services e integrations.

## 5. Camadas da aplicação

### 5.1 Camada de apresentação

Responsável por:

- renderizar páginas
    
- receber formulários
    
- exibir tabelas, cards, filtros e gráficos
    
- enviar requisições assíncronas
    

Tecnologias:

- HTML5
    
- CSS3
    
- JavaScript ES6+
    
- Bootstrap
    
- Chart.js
    
- Axios, se necessário
    

Essa camada não deve conter regra de negócio crítica. Ela apenas consome dados preparados pelo backend.

### 5.2 Camada de controle

Os controllers recebem a requisição e delegam ao service correto.

Exemplos:

- `EditalController@index()` lista editais
    
- `EditalController@show()` exibe detalhes
    
- `PerfilMonitoramentoController@save()` salva palavras-chave
    
- `AdminController@executarColeta()` dispara rotina manual
    

Regra: controller não deve decidir como classificar, normalizar ou deduplicar edital. Isso pertence à camada de serviços.

### 5.3 Camada de serviços

Aqui está o coração do sistema.

Exemplos:

- `ColetaService` orquestra a captura de fontes
    
- `NormalizacaoService` converte campos heterogêneos em padrão único
    
- `DeduplicacaoService` evita registros repetidos
    
- `CorrespondenciaService` calcula relevância entre edital e perfil do cliente
    
- `AlertaService` gera e envia notificações
    

Essa camada precisa ser muito bem desenhada porque é nela que o produto realmente existe.

### 5.4 Camada de repositórios

Isola acesso ao banco de dados.

Vantagens:

- SQL fica centralizado
    
- manutenção mais simples
    
- menor acoplamento
    
- mais segurança contra duplicação de queries espalhadas
    

### 5.5 Camada de integrações

Cada fonte externa deve ter sua própria integração.

Nunca misture regras do PNCP com scraping do Compras.gov no mesmo arquivo. Isso aumenta acoplamento e torna manutenção inviável.

A regra deve ser:

- um client/scraper por fonte
    
- um mapper por fonte
    
- uma interface comum de ingestão
    

## 6. Arquitetura dos módulos do negócio

Agora a parte mais importante: como dividir o produto em módulos startup-ready.

### 6.1 Módulo de autenticação e acesso

Responsável por:

- login
    
- logout
    
- recuperação de senha
    
- cadastro
    
- sessão
    
- permissões
    

Perfis iniciais recomendados:

- super admin
    
- admin operacional
    
- cliente gestor
    
- cliente usuário
    

Estratégia simples e eficaz:

- senha com `password_hash()`
    
- sessão segura
    
- proteção CSRF
    
- controle de rotas autenticadas
    
- logs de login
    

### 6.2 Módulo de empresas e clientes

Cada conta SaaS deve estar ligada a uma empresa.

Entidades:

- usuários
    
- empresas
    
- planos
    
- assinaturas
    
- limites de uso
    

Esse módulo precisa prever desde cedo:

- múltiplos usuários por empresa
    
- plano ativo/inativo
    
- limite de palavras-chave
    
- limite de alertas
    
- limite de exportações
    

Mesmo que o MVP não monetize tudo ainda, a arquitetura precisa suportar isso.

### 6.3 Módulo de perfil de monitoramento

Esse módulo diferencia um simples “catálogo de editais” de um SaaS inteligente.

A empresa informa:

- segmentos de interesse
    
- palavras-chave
    
- estados/UFs
    
- modalidades
    
- faixas de valor
    
- órgãos favoritos
    
- frequência de alertas
    

O motor de correspondência utilizará esse perfil para ranquear oportunidades.

### 6.4 Módulo de fontes de coleta

Tabela e gestão de fontes:

- PNCP
    
- Compras.gov
    
- Licitações-e
    
- futuras integrações
    

Cada fonte deve ter:

- nome
    
- tipo de integração
    
- status ativa/inativa
    
- periodicidade
    
- parâmetros
    
- último processamento
    
- taxa de erro
    

### 6.5 Módulo de coleta

Esse é o núcleo operacional do negócio.

Pipeline ideal:

1. iniciar job
    
2. registrar execução
    
3. buscar dados brutos
    
4. validar retorno
    
5. mapear para padrão interno
    
6. deduplicar
    
7. persistir
    
8. gerar estatísticas
    
9. registrar logs e erros
    

Esse pipeline não deve depender da interface web. Ele precisa funcionar sozinho por cron.

### 6.6 Módulo de normalização

Fontes diferentes trazem nomes diferentes para os mesmos conceitos. O sistema precisa converter tudo para um schema padrão.

Schema canônico do edital:

- id
    
- fonte_id
    
- codigo_fonte
    
- numero_edital
    
- orgao_nome
    
- orgao_poder
    
- esfera
    
- uf
    
- municipio
    
- modalidade
    
- modo_disputa
    
- objeto
    
- descricao_resumida
    
- valor_estimado
    
- data_publicacao
    
- data_abertura
    
- data_encerramento
    
- situacao
    
- link_detalhe
    
- link_edital
    
- hash_unico
    
- score_relevancia
    
- criado_em
    
- atualizado_em
    

Sem normalização, você não consegue filtrar nem gerar inteligência.

### 6.7 Módulo de deduplicação

Esse módulo é obrigatório. O próprio documento já aponta a necessidade de anti-duplicidade por hash.

A estratégia correta não é depender só de MD5 do texto bruto, porque pequenas variações quebram a equivalência. O ideal é criar um hash com concatenação normalizada:

- órgão sanitizado
    
- número do edital, se existir
    
- objeto resumido sanitizado
    
- data de publicação
    
- fonte original
    

Além disso, criar regras complementares:

- se mesmo órgão + número + data já existe, bloquear
    
- se objeto altamente similar e mesma data, marcar como possível duplicado
    
- registrar duplicidade detectada em log
    

Para startup, primeiro você usa deduplicação determinística. Depois, se necessário, evolui para similaridade textual.

### 6.8 Módulo de catálogo e busca

Esse é o módulo que o cliente mais percebe.

Recursos mínimos:

- listagem paginada
    
- filtros por UF, modalidade, órgão, data, valor
    
- busca textual
    
- ordenação por relevância e data
    
- detalhamento do edital
    
- acesso ao link original
    

Indexação recomendada no banco:

- `data_publicacao`
    
- `data_encerramento`
    
- `uf`
    
- `modalidade`
    
- `orgao_nome`
    
- `situacao`
    
- `hash_unico`
    

### 6.9 Módulo de correspondência inteligente

Esse é o verdadeiro motor de valor do SaaS.

No MVP, recomendo um modelo de score simples, baseado em pontos:

- palavra-chave no título: +10
    
- palavra-chave no objeto: +7
    
- UF compatível: +3
    
- modalidade preferida: +2
    
- valor na faixa desejada: +3
    
- órgão preferido: +4
    

O sistema calcula um `score_relevancia`.

Faixas sugeridas:

- 0–4: baixa relevância
    
- 5–9: média
    
- 10–15: alta
    
- 16+: oportunidade prioritária
    

Isso é suficiente para MVP e já entrega inteligência prática sem IA pesada.

### 6.10 Módulo de alertas

O alerta não deve disparar a cada edital individualmente, ou o usuário vai se irritar.

Melhor abordagem:

- resumo diário
    
- resumo imediato opcional para editais prioritários
    
- alerta semanal analítico
    

Canais do MVP:

- painel interno
    
- e-mail
    

Canais futuros:

- WhatsApp
    
- Telegram
    
- webhook
    
- API
    

### 6.11 Módulo de favoritos e acompanhamento

O usuário precisa marcar editais como:

- favorito
    
- em análise
    
- proposta em preparação
    
- descartado
    
- acompanhado
    

Isso aumenta retenção e transforma o SaaS em ferramenta de rotina, não apenas consulta esporádica.

### 6.12 Módulo administrativo

Visão operacional interna.

Itens críticos:

- status das fontes
    
- última coleta
    
- falhas por fonte
    
- volume coletado por dia
    
- duplicados evitados
    
- empresas ativas
    
- volume de alertas enviados
    

Sem isso, você perde controle do produto.

## 7. Arquitetura do banco de dados

A seguir, a modelagem-base recomendada.

### 7.1 Tabelas principais

**usuarios**

- id
    
- empresa_id
    
- nome
    
- email
    
- senha_hash
    
- perfil
    
- status
    
- ultimo_login_em
    
- criado_em
    
- atualizado_em
    

**empresas**

- id
    
- razao_social
    
- nome_fantasia
    
- cnpj
    
- segmento
    
- plano_id
    
- status
    
- criado_em
    
- atualizado_em
    

**planos**

- id
    
- nome
    
- limite_usuarios
    
- limite_palavras_chave
    
- limite_alertas
    
- limite_exportacoes
    
- valor_mensal
    
- status
    

**assinaturas**

- id
    
- empresa_id
    
- plano_id
    
- status
    
- data_inicio
    
- data_fim
    
- gateway_referencia
    
- criado_em
    
- atualizado_em
    

**fontes_coleta**

- id
    
- nome
    
- tipo
    
- url_base
    
- ativa
    
- periodicidade_minutos
    
- configuracao_json
    
- criado_em
    
- atualizado_em
    

**coletas_execucao**

- id
    
- fonte_id
    
- iniciado_em
    
- finalizado_em
    
- status
    
- total_lidos
    
- total_inseridos
    
- total_atualizados
    
- total_duplicados
    
- total_erros
    
- log_resumo
    
- criado_em
    

**editais**

- id
    
- fonte_id
    
- codigo_fonte
    
- numero_edital
    
- orgao_nome
    
- esfera
    
- uf
    
- municipio
    
- modalidade
    
- objeto
    
- descricao_resumida
    
- valor_estimado
    
- data_publicacao
    
- data_abertura
    
- data_encerramento
    
- situacao
    
- link_detalhe
    
- link_edital
    
- hash_unico
    
- score_global
    
- criado_em
    
- atualizado_em
    

**edital_documentos**

- id
    
- edital_id
    
- nome_documento
    
- url_documento
    
- tipo_documento
    
- criado_em
    

**palavras_chave**

- id
    
- empresa_id
    
- termo
    
- peso
    
- categoria
    
- ativo
    
- criado_em
    

**perfis_monitoramento**

- id
    
- empresa_id
    
- nome
    
- ufs_json
    
- modalidades_json
    
- orgaos_json
    
- faixa_valor_min
    
- faixa_valor_max
    
- frequencia_alerta
    
- ativo
    
- criado_em
    
- atualizado_em
    

**correspondencias**

- id
    
- edital_id
    
- empresa_id
    
- perfil_monitoramento_id
    
- score
    
- nivel_relevancia
    
- motivo_json
    
- alertado_em
    
- criado_em
    

**favoritos**

- id
    
- empresa_id
    
- edital_id
    
- status_acompanhamento
    
- observacao
    
- criado_em
    
- atualizado_em
    

**alertas**

- id
    
- empresa_id
    
- tipo
    
- canal
    
- assunto
    
- conteudo_resumo
    
- status_envio
    
- enviado_em
    
- criado_em
    

**logs_sistema**

- id
    
- nivel
    
- contexto
    
- mensagem
    
- dados_json
    
- criado_em
    

### 7.2 Observação estratégica

Para startup, esse banco já é suficiente para operar bem por muito tempo. O erro seria tentar simplificar demais e depois ter que refatorar a estrutura quando começarem múltiplos clientes, múltiplos perfis e múltiplas fontes.

## 8. Fluxos principais do sistema

### 8.1 Fluxo de coleta

Cron → Job da fonte → Consulta API/scraping → Recebe dados brutos  
→ Mapper da fonte → Schema padrão interno  
→ Normalização → Deduplicação  
→ Salva no banco → Atualiza score preliminar  
→ Registra execução

### 8.2 Fluxo de correspondência

Novo edital salvo → Buscar perfis ativos  
→ Calcular score por empresa  
→ Persistir correspondência relevante  
→ Colocar em fila lógica de alerta

### 8.3 Fluxo do usuário

Login → Dashboard → Ver oportunidades relevantes  
→ Filtrar / buscar → Abrir edital  
→ Favoritar / acompanhar / exportar

### 8.4 Fluxo de alerta

Scheduler → Buscar correspondências novas não alertadas  
→ Agrupar por empresa  
→ Gerar resumo  
→ Enviar e-mail  
→ Marcar como alertado

## 9. Arquitetura dos jobs e automações

Esse sistema depende de rotinas agendadas. O documento já prevê uso de cron jobs em PHP, o que é coerente com a proposta.

Jobs recomendados:

- `coletar_pncp.php`
    
- `coletar_comprasgov.php`
    
- `coletar_licitacoese.php`
    
- `processar_correspondencias.php`
    
- `enviar_alertas.php`
    
- `limpar_logs_antigos.php`
    
- `recalcular_scores.php`
    

Periodicidade sugerida:

- PNCP: a cada 30 ou 60 minutos
    
- Compras.gov: 2 a 4 vezes ao dia
    
- Licitações-e: 2 a 4 vezes ao dia
    
- Correspondências: a cada 30 minutos
    
- Alertas: diário às 7h e imediato para alta prioridade
    

Crítico: cada job deve ter proteção contra execução simultânea. Sem isso, você pode duplicar processamento.

## 10. Arquitetura de segurança

Mesmo em MVP, não trate segurança como detalhe.

Itens mínimos:

- senhas com `password_hash`
    
- prepared statements com PDO
    
- CSRF token em formulários
    
- escape de saída HTML
    
- validação de upload
    
- controle por perfil
    
- logs de acesso
    
- limitação de tentativas de login
    
- sessões seguras
    
- bloqueio de rotas administrativas
    

Risco adicional do seu produto: scraping e integrações externas podem trazer dados malformados. Então todo conteúdo externo precisa ser sanitizado antes de persistir e antes de exibir.

## 11. Arquitetura de observabilidade e operação

Startup que não mede operação acaba apagando incêndio às cegas.

Você precisa de:

- log por execução de coleta
    
- log por erro de integração
    
- dashboard interno de saúde das fontes
    
- volume diário de editais inseridos
    
- taxa de duplicidade
    
- taxa de falha por fonte
    
- tempo médio de coleta
    

Indicadores essenciais:

- editais novos por dia
    
- oportunidades relevantes por empresa
    
- abertura de alertas
    
- número de favoritos
    
- conversão para uso recorrente
    

## 12. Arquitetura de frontend

Como o backend já terá muita complexidade operacional, o frontend precisa ser limpo e objetivo.

Páginas essenciais do MVP:

- login
    
- cadastro
    
- dashboard
    
- listagem de editais
    
- detalhes do edital
    
- favoritos
    
- perfil de monitoramento
    
- configurações da empresa
    
- administração
    

Componentes principais:

- header institucional
    
- menu lateral
    
- cards resumidos
    
- filtros avançados
    
- tabela responsiva
    
- paginação
    
- modais para ações secundárias
    
- gráficos simples
    

No seu próprio documento, há exigência de separação de HTML/PHP, CSS e JS, além de padrão visual consistente. Isso é correto e deve ser mantido, porque reduz acoplamento de interface.

## 13. Arquitetura de API interna

Mesmo que o sistema seja server-rendered no início, recomendo criar endpoints JSON para operações de interface.

Exemplos:

- `/api/editais`
    
- `/api/editais/{id}`
    
- `/api/favoritos/toggle`
    
- `/api/dashboard/metricas`
    
- `/api/perfis/salvar`
    

Vantagens:

- UI mais dinâmica
    
- reuso futuro
    
- facilita integração com app móvel
    
- prepara o sistema para expansão sem reescrever tudo
    

## 14. Arquitetura para escalabilidade futura

A arquitetura proposta suporta bem a fase inicial. Mas é importante saber o que poderá ser extraído depois.

Quando o sistema crescer, os primeiros candidatos a separação são:

- motor de coleta
    
- motor de correspondência
    
- envio de alertas
    

Esses módulos podem virar serviços independentes no futuro. Mas só vale fazer isso quando houver dor real de escala.

Até lá, mantenha tudo no monólito modular.

## 15. Riscos estratégicos da arquitetura

Agora o contraponto crítico: onde esse SaaS realmente pode falhar.

Primeiro, **achar que o produto é só dashboard**. Não é. O valor está na qualidade da coleta e da correspondência.

Segundo, **subestimar deduplicação**. Usuário tolera interface simples, mas não tolera edital duplicado ou irrelevante repetido.

Terceiro, **excesso de scraping cedo demais**. O núcleo deve começar forte com PNCP, porque fonte estável vale mais do que volume instável.

Quarto, **não medir uso real**. Se você não acompanhar quais alertas são abertos, quais editais são favoritados e quais filtros são usados, não saberá evoluir o produto.

## 16. Arquitetura final recomendada para seu caso

Minha recomendação objetiva é esta:

Você deve construir o SaaS em **PHP 8 com monólito modular MVC + camada de services + jobs por cron + MySQL normalizado + integrações separadas por fonte**. Essa é a arquitetura mais equilibrada entre velocidade, custo, manutenção e potencial de crescimento, e está plenamente alinhada à proposta funcional descrita no seu documento, que já prevê coleta automatizada, processamento, filtragem personalizada, alertas e evolução progressiva do produto.

A sequência certa de implementação é:

1. base do projeto e autenticação
    
2. modelagem do banco
    
3. módulo de empresas e perfis
    
4. integração PNCP
    
5. normalização e deduplicação
    
6. catálogo de editais
    
7. motor de correspondência
    
8. dashboard
    
9. alertas por e-mail
    
10. administração operacional
    
11. novas fontes

