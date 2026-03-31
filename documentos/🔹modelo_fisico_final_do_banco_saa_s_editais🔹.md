# Modelo Físico Final do Banco de Dados
Sistema: SaaS de Monitoramento Inteligente de Editais
Banco: MySQL / MariaDB

## 1. Objetivo do modelo físico

Este modelo físico final consolida o ERD lógico em uma estrutura pronta para implementação, definindo:

- tabelas físicas
- chaves primárias
- chaves estrangeiras
- cardinalidades
- regras de integridade
- regras de negócio por relacionamento
- comportamento esperado em exclusões e atualizações

O modelo foi projetado para um ambiente SaaS multiempresa, com foco em:

- isolamento por empresa
- rastreabilidade operacional
- deduplicação de editais
- motor de correspondência
- alertas e auditoria

---

## 2. Convenções adotadas

### 2.1 Convenções de chave

- Todas as tabelas usam `id` como chave primária técnica.
- As PKs são do tipo `BIGINT UNSIGNED AUTO_INCREMENT`.
- As FKs seguem o padrão `<tabela_origem>_id`.

### 2.2 Convenções de integridade

- `ON DELETE CASCADE` quando a entidade filha não faz sentido sem a entidade pai.
- `ON DELETE RESTRICT` quando a exclusão da entidade pai comprometeria integridade histórica.
- `ON DELETE SET NULL` quando o histórico precisa permanecer, mesmo sem a entidade relacionada.
- `ON UPDATE CASCADE` para manter coerência técnica em caso de atualização da chave referenciada.

### 2.3 Convenções de isolamento SaaS

- Toda informação de uso do cliente é vinculada à tabela `empresas`.
- Nenhum dado de operação do cliente deve existir sem contexto de empresa, exceto dados globais do sistema.

---

## 3. Modelo físico por entidade

### 3.1 Tabela `empresas`

**Finalidade**: entidade-mãe do modelo multiempresa.

**Chave primária**
- `id`

**Relacionamentos diretos**
- 1:N com `usuarios`
- 1:N com `assinaturas`
- 1:N com `perfis_monitoramento`
- 1:N com `palavras_chave`
- 1:N com `correspondencias`
- 1:N com `favoritos`
- 1:N com `alertas`
- 1:N com `exportacoes`
- 1:N com `auditorias`

**Regra de negócio**
- uma empresa representa um cliente SaaS
- empresa inativa ou suspensa não deve acessar rotas operacionais
- exclusão de empresa em ambiente real deve ser lógica, não física, salvo ambiente de teste

**Cardinalidade física**
- uma empresa pode possuir zero ou muitos usuários
- uma empresa pode possuir zero ou muitas assinaturas ao longo do tempo
- uma empresa pode possuir zero ou muitos perfis de monitoramento

---

### 3.2 Tabela `usuarios`

**Finalidade**: usuários autenticáveis do sistema.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`

**Cardinalidade**
- `empresas (1) : (N) usuarios`

**Integridade**
- `ON DELETE CASCADE`

**Justificativa**
- se a empresa for removida fisicamente, seus usuários não fazem mais sentido operacionalmente

**Regras de negócio**
- e-mail deve ser único no sistema
- perfil define autorização funcional
- usuário bloqueado não pode autenticar
- pelo menos um usuário administrador deve existir na empresa ativa em produção

---

### 3.3 Tabela `planos`

**Finalidade**: catálogo global de planos do SaaS.

**PK**
- `id`

**Relacionamentos**
- 1:N com `assinaturas`

**Cardinalidade**
- `planos (1) : (N) assinaturas`

**Integridade**
- exclusão física deve ser evitada se houver assinaturas históricas
- `ON DELETE RESTRICT`

**Regras de negócio**
- plano define limites operacionais
- plano inativo não pode ser oferecido para novas assinaturas
- mudança de limites deve ser auditável

---

### 3.4 Tabela `assinaturas`

**Finalidade**: registrar o vínculo comercial entre empresa e plano.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`
- `plano_id` → `planos.id`

**Cardinalidades**
- `empresas (1) : (N) assinaturas`
- `planos (1) : (N) assinaturas`

**Integridade**
- `empresa_id` com `ON DELETE CASCADE`
- `plano_id` com `ON DELETE RESTRICT`

**Regras de negócio**
- uma empresa pode ter várias assinaturas históricas, mas apenas uma ativa por vez no fluxo normal
- assinatura em status `SUSPENSA` deve bloquear acesso a recursos pagos
- assinatura em `TESTE` deve respeitar limitações específicas

---

### 3.5 Tabela `tokens_recuperacao_senha`

**Finalidade**: recuperação segura de senha.

**PK**
- `id`

**FKs**
- `usuario_id` → `usuarios.id`

**Cardinalidade**
- `usuarios (1) : (N) tokens_recuperacao_senha`

**Integridade**
- `ON DELETE CASCADE`

**Regras de negócio**
- token deve ser único
- token expirado não pode ser usado
- token utilizado deve ser invalidado logicamente por `usado_em`

---

### 3.6 Tabela `fontes_coleta`

**Finalidade**: catálogo das fontes externas de ingestão.

**PK**
- `id`

**Relacionamentos**
- 1:N com `coletas_execucao`
- 1:N com `editais`

**Cardinalidade**
- `fontes_coleta (1) : (N) coletas_execucao`
- `fontes_coleta (1) : (N) editais`

**Regras de negócio**
- código da fonte deve ser único
- fonte inativa não deve ser processada pelos jobs automáticos
- periodicidade orienta cron/scheduler

---

### 3.7 Tabela `coletas_execucao`

**Finalidade**: registrar execuções de coleta.

**PK**
- `id`

**FKs**
- `fonte_id` → `fontes_coleta.id`

**Cardinalidade**
- `fontes_coleta (1) : (N) coletas_execucao`

**Integridade**
- `ON DELETE CASCADE`

**Regras de negócio**
- cada execução deve registrar status final
- a soma lógica entre lidos, inseridos, atualizados, duplicados e erros deve ser coerente com o processamento
- execuções simultâneas da mesma fonte devem ser evitadas por controle no backend/job lock

---

### 3.8 Tabela `editais`

**Finalidade**: núcleo operacional do sistema.

**PK**
- `id`

**FKs**
- `fonte_id` → `fontes_coleta.id`

**Relacionamentos filhos**
- 1:N com `edital_documentos`
- 1:N com `correspondencias`
- 1:N com `favoritos`

**Cardinalidade**
- `fontes_coleta (1) : (N) editais`
- `editais (1) : (N) edital_documentos`
- `editais (1) : (N) correspondencias`
- `editais (1) : (N) favoritos`

**Integridade**
- `fonte_id` com `ON DELETE RESTRICT`
- relacionamentos filhos com `ON DELETE CASCADE`

**Justificativa**
- não é desejável excluir uma fonte se já há editais históricos vinculados
- se um edital for removido, seus anexos, correspondências e favoritos perdem sentido

**Regras de negócio**
- `hash_unico` deve ser único e estável
- um edital representa um registro já normalizado
- atualizações do mesmo edital devem ocorrer por upsert controlado, não inserção cega
- datas devem respeitar consistência mínima: publicação não posterior ao encerramento, quando ambos existirem

---

### 3.9 Tabela `edital_documentos`

**Finalidade**: anexos e documentos do edital.

**PK**
- `id`

**FKs**
- `edital_id` → `editais.id`

**Cardinalidade**
- `editais (1) : (N) edital_documentos`

**Integridade**
- `ON DELETE CASCADE`

**Regras de negócio**
- um edital pode não possuir documentos cadastrados no MVP
- URLs devem ser válidas ou ao menos bem formadas no backend
- duplicidade de documento por mesmo edital deve ser tratada na ingestão, se necessário

---

### 3.10 Tabela `perfis_monitoramento`

**Finalidade**: filtros estratégicos por empresa.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`

**Relacionamentos filhos**
- 1:N com `palavras_chave`
- 1:N com `correspondencias`

**Cardinalidade**
- `empresas (1) : (N) perfis_monitoramento`
- `perfis_monitoramento (1) : (N) palavras_chave`
- `perfis_monitoramento (1) : (N) correspondencias`

**Integridade**
- `ON DELETE CASCADE` em palavras-chave
- `ON DELETE SET NULL` em correspondências

**Justificativa**
- apagar um perfil deve apagar suas palavras-chave
- histórico de correspondência pode permanecer mesmo se o perfil for excluído

**Regras de negócio**
- empresa pode ter vários perfis, respeitando limite do plano
- perfil inativo não participa do matching
- faixa mínima não pode ser maior que faixa máxima

---

### 3.11 Tabela `palavras_chave`

**Finalidade**: termos usados no motor de matching.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`
- `perfil_monitoramento_id` → `perfis_monitoramento.id`

**Cardinalidades**
- `empresas (1) : (N) palavras_chave`
- `perfis_monitoramento (1) : (N) palavras_chave`

**Integridade**
- `empresa_id` com `ON DELETE CASCADE`
- `perfil_monitoramento_id` com `ON DELETE CASCADE`

**Regras de negócio**
- palavra-chave pode ser global da empresa ou vinculada a perfil específico, conforme estratégia adotada no service
- peso deve ser maior ou igual a 1
- termos inativos não entram no cálculo de score

---

### 3.12 Tabela `correspondencias`

**Finalidade**: tabela central da inteligência do sistema.

**PK**
- `id`

**FKs**
- `edital_id` → `editais.id`
- `empresa_id` → `empresas.id`
- `perfil_monitoramento_id` → `perfis_monitoramento.id`

**Cardinalidades**
- `editais (1) : (N) correspondencias`
- `empresas (1) : (N) correspondencias`
- `perfis_monitoramento (1) : (N) correspondencias`

**Integridade**
- `edital_id` com `ON DELETE CASCADE`
- `empresa_id` com `ON DELETE CASCADE`
- `perfil_monitoramento_id` com `ON DELETE SET NULL`

**Regra de unicidade**
- unicidade composta por `edital_id`, `empresa_id`, `perfil_monitoramento_id`

**Risco técnico conhecido**
- como `perfil_monitoramento_id` pode ser `NULL`, o backend também deve controlar duplicidades

**Regras de negócio**
- score deve ser maior ou igual a zero
- correspondência é gerada por job ou service de matching
- `nivel_relevancia` deriva de regra de classificação do score
- `alertado_em` só deve ser preenchido após envio efetivo do alerta

---

### 3.13 Tabela `favoritos`

**Finalidade**: acompanhamento operacional de editais.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`
- `edital_id` → `editais.id`

**Cardinalidades**
- `empresas (1) : (N) favoritos`
- `editais (1) : (N) favoritos`

**Integridade**
- `ON DELETE CASCADE`

**Regra de unicidade**
- uma empresa não pode favoritar o mesmo edital mais de uma vez

**Regras de negócio**
- favorito pode mudar de status ao longo do funil interno
- observação é livre e não obrigatória

---

### 3.14 Tabela `alertas`

**Finalidade**: registrar notificações geradas pelo sistema.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`
- `usuario_id` → `usuarios.id`

**Cardinalidades**
- `empresas (1) : (N) alertas`
- `usuarios (1) : (N) alertas`

**Integridade**
- `empresa_id` com `ON DELETE CASCADE`
- `usuario_id` com `ON DELETE SET NULL`

**Justificativa**
- histórico da empresa pode permanecer mesmo se o usuário destinatário for excluído

**Regras de negócio**
- alerta pode ser por empresa ou por usuário, dependendo do fluxo
- status_envio precisa refletir o resultado real do canal de envio
- alertas duplicados devem ser evitados pelo job agregador

---

### 3.15 Tabela `logs_sistema`

**Finalidade**: telemetria operacional.

**PK**
- `id`

**Relacionamentos**
- não depende de outras tabelas para existir

**Regras de negócio**
- deve registrar eventos críticos de coleta, matching, alertas e falhas
- contexto deve identificar claramente o módulo de origem

---

### 3.16 Tabela `exportacoes`

**Finalidade**: histórico de geração de arquivos.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`
- `usuario_id` → `usuarios.id`

**Cardinalidades**
- `empresas (1) : (N) exportacoes`
- `usuarios (1) : (N) exportacoes`

**Integridade**
- `empresa_id` com `ON DELETE CASCADE`
- `usuario_id` com `ON DELETE SET NULL`

**Regras de negócio**
- deve respeitar limite mensal por plano
- filtro usado na exportação pode ser armazenado em JSON para rastreabilidade

---

### 3.17 Tabela `auditorias`

**Finalidade**: rastrear ações críticas.

**PK**
- `id`

**FKs**
- `empresa_id` → `empresas.id`
- `usuario_id` → `usuarios.id`

**Cardinalidades**
- `empresas (1) : (N) auditorias`
- `usuarios (1) : (N) auditorias`

**Integridade**
- `ON DELETE SET NULL`

**Justificativa**
- o histórico deve permanecer mesmo se usuário ou empresa forem removidos fisicamente em ambiente excepcional

**Regras de negócio**
- toda alteração crítica deve gerar auditoria
- entidade e entidade_id devem apontar o alvo da ação
- detalhes_json deve armazenar contexto mínimo para reconstituição do evento

---

## 4. Regras de cardinalidade consolidadas

### 4.1 Relações 1:N principais

- `empresas 1:N usuarios`
- `empresas 1:N assinaturas`
- `planos 1:N assinaturas`
- `fontes_coleta 1:N coletas_execucao`
- `fontes_coleta 1:N editais`
- `editais 1:N edital_documentos`
- `empresas 1:N perfis_monitoramento`
- `perfis_monitoramento 1:N palavras_chave`
- `editais 1:N correspondencias`
- `empresas 1:N correspondencias`
- `empresas 1:N favoritos`
- `editais 1:N favoritos`
- `empresas 1:N alertas`
- `usuarios 1:N alertas`

### 4.2 Relações de associação funcional

Embora `correspondencias` e `favoritos` sejam tabelas próprias, elas funcionam como entidades de associação entre:

- edital ↔ empresa
- edital ↔ empresa ↔ perfil

Ou seja, o modelo físico resolve relações conceitualmente N:N por meio de tabelas associativas com atributos adicionais.

---

## 5. Regras de negócio por relacionamento

### 5.1 Empresa × Usuário

- uma empresa pode ter vários usuários
- um usuário pertence a uma única empresa
- ao excluir empresa fisicamente, usuários são removidos por cascata
- regra operacional: empresa ativa deve ter ao menos um usuário com capacidade administrativa

### 5.2 Empresa × Assinatura × Plano

- empresa pode trocar de plano ao longo do tempo
- assinatura registra histórico da contratação
- plano não deve ser apagado se houver histórico vinculado
- o backend deve garantir apenas uma assinatura ativa por empresa

### 5.3 Fonte × Edital

- todo edital deve possuir uma fonte de origem
- fonte pode existir sem editais ainda
- fonte histórica não deve ser apagada se possuir editais vinculados

### 5.4 Fonte × ColetaExecucao

- uma fonte gera várias execuções
- toda execução pertence a uma única fonte
- histórico de execução é descartável apenas junto com a fonte em cenários controlados

### 5.5 Empresa × PerfilMonitoramento

- empresa pode criar múltiplos perfis
- perfil existe apenas dentro de uma empresa
- exclusão de empresa apaga perfis por cascata
- quantidade de perfis depende do plano contratado

### 5.6 PerfilMonitoramento × PalavraChave

- perfil pode possuir múltiplas palavras-chave
- palavra-chave vinculada ao perfil não sobrevive à exclusão do perfil
- peso influencia score do matching

### 5.7 Edital × Correspondência × Empresa

- um edital pode ser relevante para várias empresas
- uma empresa pode receber muitas correspondências
- correspondência é o elo central entre dado coletado e valor entregue ao cliente
- exclusão do edital ou da empresa remove a correspondência

### 5.8 Edital × Favorito × Empresa

- um edital pode ser favoritado por muitas empresas
- cada empresa só pode favoritar uma vez o mesmo edital
- favorito possui ciclo operacional próprio

### 5.9 Empresa/Usuário × Alerta

- alertas podem ser gerais da empresa ou direcionados a usuário
- histórico da empresa é mais importante que a permanência do usuário específico
- por isso `usuario_id` pode virar `NULL`

### 5.10 Usuário/Empresa × Auditoria

- auditoria deve sobreviver à remoção do vínculo direto sempre que possível
- por isso utiliza `SET NULL` nas FKs opcionais
- o objetivo é preservar histórico forense do sistema

---

## 6. Regras físicas de exclusão e atualização

### Exclusões com CASCADE

Usar quando o dado filho não tem valor sem o pai:

- empresas → usuarios
- empresas → perfis_monitoramento
- empresas → favoritos
- editais → edital_documentos
- editais → correspondencias

### Exclusões com RESTRICT

Usar quando o pai não deve ser removido se houver histórico dependente:

- planos → assinaturas
- fontes_coleta → editais

### Exclusões com SET NULL

Usar quando o histórico deve permanecer:

- perfis_monitoramento → correspondencias
- usuarios → alertas
- usuarios → exportacoes
- usuarios → auditorias

---

## 7. Restrições físicas recomendadas

### Unicidade

- `usuarios.email` único
- `planos.nome` único
- `fontes_coleta.codigo` único
- `tokens_recuperacao_senha.token` único
- `editais.hash_unico` único
- `favoritos (empresa_id, edital_id)` único
- `correspondencias (edital_id, empresa_id, perfil_monitoramento_id)` único, com controle complementar no backend

### Checks lógicos

- peso de palavra-chave >= 1
- score de correspondência >= 0
- faixa_valor_min <= faixa_valor_max

---

## 8. Regras operacionais que não devem ficar só no banco

Estas regras devem ser reforçadas no backend:

- impedir mais de uma assinatura ativa por empresa
- impedir duplicidade lógica de correspondência quando `perfil_monitoramento_id` for `NULL`
- validar coerência temporal entre datas de edital
- bloquear acesso conforme status da empresa, assinatura e usuário
- controlar limites por plano
- impedir execução concorrente indevida dos jobs de coleta

---

## 9. Conclusão técnica

O modelo físico final está adequado para implantação inicial do SaaS e já contempla:

- isolamento multiempresa
- histórico comercial
- rastreabilidade de coleta
- catálogo normalizado de editais
- motor de matching
- alertas, favoritos, exportações e auditoria

A estrutura foi desenhada para suportar MVP com baixo risco de refatoração imediata, mas sem cair em simplificações que comprometam crescimento.

