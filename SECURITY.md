# Politica de Seguranca

## Escopo
Este repositorio contem um SaaS multi-tenant com dados sensiveis de autenticacao, assinatura e integracoes de pagamento.

## Dados sensiveis que NAO podem entrar no Git
- `.env` real
- senha de banco
- token/chave da API Mercado Pago
- credenciais SMTP
- cookies/sessoes
- dumps completos de banco de producao

## Controles aplicados neste projeto
- `.gitignore` bloqueando arquivos de ambiente e artefatos locais
- scanner local de segredos em `scripts/scan-secrets.ps1`
- hook de pre-commit em `.git/hooks/pre-commit`
- workflow de CI para varredura de segredos (`.github/workflows/secret-scan.yml`)

## Resposta a incidente
Se qualquer segredo for exposto em commit:
1. Revogar/rotacionar imediatamente o segredo no provedor.
2. Remover o segredo do historico Git com ferramenta apropriada.
3. Forcar troca das credenciais dependentes.
4. Abrir registro em auditoria interna e documentar impacto.

## Contato
Responsavel tecnico do projeto: equipe de engenharia do SaaS Editais.
