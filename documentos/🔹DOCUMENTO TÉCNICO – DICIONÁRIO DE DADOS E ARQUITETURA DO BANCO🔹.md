
DOCUMENTO TÉCNICO – DICIONÁRIO DE DADOS E ARQUITETURA DO BANCO
Sistema: SaaS de Monitoramento Inteligente de Editais
Banco de Dados: MySQL / MariaDB
Charset: utf8mb4

============================================================
1. VISÃO GERAL DO BANCO DE DADOS
============================================================
O banco de dados foi projetado para suportar um modelo SaaS multiempresa
capaz de coletar, normalizar, analisar e distribuir oportunidades de
licitações públicas para empresas assinantes do sistema.

Objetivos principais da modelagem:

• Suporte multiempresa (multi‑tenant)
• Alta rastreabilidade das coletas
• Evitar duplicidade de editais
• Permitir filtros inteligentes por perfil
• Registrar correspondência entre edital e empresa
• Gerar alertas automatizados
• Permitir auditoria e rastreamento de ações

Estrutura lógica do banco:

1. Identidade e acesso
2. Estrutura comercial SaaS
3. Fontes e coleta de dados
4. Catálogo de editais
5. Inteligência e correspondência
6. Operação e auditoria

============================================================
2. DIAGRAMA LÓGICO DAS ENTIDADES
============================================================

empresas
   └── usuarios
   └── assinaturas

planos
   └── assinaturas

fontes_coleta
   └── coletas_execucao
   └── editais

editais
   └── edital_documentos
   └── correspondencias
   └── favoritos

empresas
   └── perfis_monitoramento
        └── palavras_chave

correspondencias
   └── alertas

============================================================
3. DESCRIÇÃO DAS ENTIDADES PRINCIPAIS
============================================================

3.1 EMPRESAS

Finalidade
Representa cada cliente do SaaS.

Responsabilidades
• Isolamento de dados por cliente
• Controle de status da conta
• Identificação comercial

Campos principais
id
razao_social
cnpj
segmento
status

Relacionamentos
empresas 1:N usuarios
empresas 1:N perfis_monitoramento
empresas 1:N correspondencias
empresas 1:N favoritos
empresas 1:N alertas


------------------------------------------------------------
3.2 USUARIOS
------------------------------------------------------------

Finalidade
Armazena os usuários vinculados a cada empresa.

Responsabilidades
• Autenticação
• Controle de permissões
• Auditoria de acesso

Perfis possíveis
SUPER_ADMIN
ADMIN
GESTOR
USUARIO

Relacionamentos
usuarios N:1 empresas
usuarios 1:N exportacoes
usuarios 1:N auditorias


------------------------------------------------------------
3.3 PLANOS
------------------------------------------------------------

Finalidade
Define os planos comerciais disponíveis no SaaS.

Exemplos de planos
Básico
Profissional
Empresarial

Controles incluídos
• limite de usuários
• limite de palavras‑chave
• limite de perfis
• limite de exportações


------------------------------------------------------------
3.4 ASSINATURAS
------------------------------------------------------------

Finalidade
Relaciona uma empresa a um plano contratado.

Responsabilidades
• controle do status da assinatura
• gestão de período de vigência
• integração futura com gateway de pagamento


------------------------------------------------------------
3.5 FONTES_COLETA
------------------------------------------------------------

Finalidade
Cadastra as fontes externas de editais.

Tipos possíveis
API
SCRAPING
MANUAL

Exemplos
PNCP
Compras.gov
Licitações‑e

Campos relevantes
periodicidade_minutos
configuracao_json
ultima_execucao_em


------------------------------------------------------------
3.6 COLETAS_EXECUCAO
------------------------------------------------------------

Finalidade
Registrar cada execução de coleta.

Objetivo
Garantir rastreabilidade operacional.

Métricas registradas
• total_lidos
• total_inseridos
• total_atualizados
• total_duplicados
• total_erros

Essa tabela permite identificar falhas em robôs de coleta.


------------------------------------------------------------
3.7 EDITAIS
------------------------------------------------------------

Finalidade
Tabela central do sistema.

Armazena os editais normalizados após coleta.

Campos estratégicos
orgao_nome
modalidade
valor_estimado
uf
municipio
situacao

Campo crítico
hash_unico

Função
Evitar duplicidade entre fontes.

Estratégia
hash MD5 baseado em:
orgao
numero_edital
objeto
fonte


------------------------------------------------------------
3.8 EDITAL_DOCUMENTOS
------------------------------------------------------------

Finalidade
Relaciona documentos anexos ao edital.

Exemplos
• edital PDF
• anexos técnicos
• termos de referência


------------------------------------------------------------
3.9 PERFIS_MONITORAMENTO
------------------------------------------------------------

Finalidade
Define os filtros de interesse de cada empresa.

Filtros possíveis
• UF
• modalidade
• órgão
• faixa de valor

Frequência de alertas
IMEDIATO
DIARIO
SEMANAL


------------------------------------------------------------
3.10 PALAVRAS_CHAVE
------------------------------------------------------------

Finalidade
Armazena termos monitorados.

Exemplo
"engenharia"
"software"
"construção"

Cada palavra possui peso
utilizado no cálculo de relevância.


------------------------------------------------------------
3.11 CORRESPONDENCIAS
------------------------------------------------------------

Finalidade
Relaciona editais com empresas.

Representa o resultado do motor de matching.

Campos principais
score
nivel_relevancia
motivo_json

Classificação
BAIXA
MEDIA
ALTA
PRIORITARIA


------------------------------------------------------------
3.12 FAVORITOS
------------------------------------------------------------

Finalidade
Permite acompanhar editais estratégicos.

Estados possíveis
FAVORITO
EM_ANALISE
PROPOSTA
DESCARTADO
ENCERRADO


------------------------------------------------------------
3.13 ALERTAS
------------------------------------------------------------

Finalidade
Histórico de alertas enviados.

Tipos
RESUMO_DIARIO
OPORTUNIDADE
SISTEMA

Canais
EMAIL
PAINEL


------------------------------------------------------------
3.14 LOGS_SISTEMA
------------------------------------------------------------

Finalidade
Registrar eventos operacionais.

Níveis
INFO
WARNING
ERROR
CRITICAL


------------------------------------------------------------
3.15 EXPORTACOES
------------------------------------------------------------

Finalidade
Registrar exportações de relatórios.

Tipos
CSV
PDF


------------------------------------------------------------
3.16 AUDITORIAS
------------------------------------------------------------

Finalidade
Registrar ações críticas executadas no sistema.

Exemplos
• criação de empresa
• alteração de plano
• exclusão de usuário
• alteração de perfil


============================================================
4. PRINCIPAIS ÍNDICES DO BANCO
============================================================

Editais

idx_editais_data_publicacao
idx_editais_data_encerramento
idx_editais_modalidade
idx_editais_orgao_nome
idx_editais_uf

Correspondencias

idx_correspondencias_empresa
idx_correspondencias_score
idx_correspondencias_nivel

Alertas

idx_alertas_empresa
idx_alertas_status


============================================================
5. ESTRATÉGIA DE ESCALABILIDADE
============================================================

O banco foi estruturado para permitir:

• novas fontes de coleta
• novos planos SaaS
• expansão do motor de matching
• criação de relatórios analíticos
• integração com sistemas externos


============================================================
6. CONSIDERAÇÕES TÉCNICAS IMPORTANTES
============================================================

7) JSON foi utilizado em algumas tabelas para flexibilidade.

8) O campo hash_unico é essencial para evitar duplicidades.

9) O controle de correspondências deve ser feito também
   no backend para evitar duplicações por NULL.

10) Índices foram definidos para acelerar consultas
   utilizadas nos dashboards.


============================================================
FIM DO DOCUMENTO
============================================================
