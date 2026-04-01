# Checklist de validacao manual - envio controlado de propostas

## 1) Pre-condicoes
- Existe ao menos uma proposta em `RASCUNHO` vinculada a um item de pipeline.
- Usuario de teste consegue autenticar no sistema.
- Migration `20260331_004_envio_controlado_propostas.sql` aplicada no ambiente.

## 2) Fluxo esperado no navegador
1. Acesse `/propostas` e abra uma proposta em `RASCUNHO`.
2. Na tela de detalhe, confirme que existe o bloco `Workflow de aprovacao e envio`.
3. Preencha `Contexto para aprovacao` e clique em `Solicitar aprovacao`.
4. Valide:
- Mensagem de sucesso exibida.
- Status mudou para `EM_REVISAO`.
- Nova linha no `Historico de aprovacoes` com status `PENDENTE`.
5. Ainda na mesma proposta, em `EM_REVISAO`, selecione `Aprovar` e informe um parecer.
6. Clique em `Registrar decisao`.
7. Valide:
- Mensagem de sucesso exibida.
- Status mudou para `APROVADA`.
- Historico de aprovacoes atualizado com decisor, data e parecer.
8. Preencha o formulario de submissao (`canal`, `protocolo`, `data/hora`, `valor`, opcional `comprovante`) e clique em `Registrar submissao`.
9. Valide:
- Mensagem de sucesso exibida.
- Status mudou para `ENVIADA`.
- Nova linha no `Historico de submissoes` com os dados informados.

## 3) Regras negativas obrigatorias
1. Tente registrar submissao com proposta em `RASCUNHO` ou `EM_REVISAO`.
- Resultado esperado: bloqueio com mensagem de validacao.
2. Tente decidir aprovacao sem solicitacao pendente.
- Resultado esperado: bloqueio com mensagem `Nao existe solicitacao pendente para decidir`.
3. Tente solicitar aprovacao novamente sem voltar para `RASCUNHO`.
- Resultado esperado: bloqueio por status invalido para a transicao.

## 4) Conferencia no banco (pos-validacao)
```sql
SELECT id, status FROM propostas_execucao ORDER BY id DESC LIMIT 10;
SELECT id, proposta_id, status_decisao, solicitado_em, decidido_em
FROM proposta_aprovacoes
ORDER BY id DESC LIMIT 20;
SELECT id, proposta_id, canal, protocolo, data_submissao
FROM proposta_submissoes
ORDER BY id DESC LIMIT 20;
```

## 5) Comando de rollout da migration 004 (homolog/producao)
Exemplo com `mysql` no PATH:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\migrate-004-envio-controlado.ps1 `
  -DbHost "SEU_HOST_DB" `
  -Port "3306" `
  -Database "SEU_BANCO" `
  -User "SEU_USUARIO" `
  -Password "SUA_SENHA"
```

Exemplo com caminho completo do mysql:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\migrate-004-envio-controlado.ps1 `
  -DbHost "SEU_HOST_DB" `
  -Port "3306" `
  -Database "SEU_BANCO" `
  -User "SEU_USUARIO" `
  -Password "SUA_SENHA" `
  -MysqlPath "D:\wamp64\bin\mysql\mysql8.4.7\bin\mysql.exe"
```
