<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$proposta = isset($proposta) ? $proposta : null;
$favorito = isset($favorito) ? $favorito : null;
$tarefas = isset($tarefas) && is_array($tarefas) ? $tarefas : [];
$aprovacoes = isset($aprovacoes) && is_array($aprovacoes) ? $aprovacoes : [];
$submissoes = isset($submissoes) && is_array($submissoes) ? $submissoes : [];
$resultados = isset($resultados) && is_array($resultados) ? $resultados : [];
$ultimoResultado = $ultimoResultado ?? null;
$aprovacaoPendente = $aprovacaoPendente ?? null;
$canaisSubmissao = isset($canaisSubmissao) && is_array($canaisSubmissao) ? $canaisSubmissao : [];
$situacoesResultado = isset($situacoesResultado) && is_array($situacoesResultado) ? $situacoesResultado : [];
$message = isset($message) ? (string) $message : null;

if ($proposta === null) {
    echo 'Proposta nao encontrada.';
    return;
}

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');
$statusAtual = strtoupper((string) ($proposta->status ?? 'RASCUNHO'));

$badgeStatusClass = match ($statusAtual) {
    'EM_REVISAO' => 'badge-revisao',
    'APROVADA' => 'badge-aprovada',
    'ENVIADA' => 'badge-enviada',
    default => 'badge-rascunho',
};

$badgeResultadoClass = static function (?string $situacao): string {
    return match (strtoupper((string) $situacao)) {
        'VENCEDORA' => 'badge-aprovada',
        'NAO_VENCEDORA', 'DESCLASSIFICADA', 'ANULADA' => 'badge-revisao',
        default => 'badge-enviada',
    };
};

$formatarDataHora = static function (?string $value): string {
    if ($value === null || trim($value) === '') {
        return '-';
    }

    $raw = trim($value);
    $formatos = ['Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d'];
    foreach ($formatos as $formato) {
        $date = DateTimeImmutable::createFromFormat($formato, $raw);
        if ($date instanceof DateTimeImmutable) {
            return $date->format($formato === 'Y-m-d' ? 'd/m/Y' : 'd/m/Y H:i');
        }
    }

    $timestamp = strtotime($raw);
    if ($timestamp === false) {
        return $raw;
    }

    return date('d/m/Y H:i', $timestamp);
};

$dataSubmissaoDefault = date('Y-m-d\\TH:i');
$dataResultadoDefault = date('Y-m-d\\TH:i');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Proposta #<?= (int) $proposta->id ?> | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1200px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        textarea, input, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .muted { color: #475569; font-size: 13px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-rascunho { background: #f1f5f9; color: #475569; }
        .badge-revisao { background: #ffedd5; color: #9a3412; }
        .badge-aprovada { background: #dcfce7; color: #166534; }
        .badge-enviada { background: #dbeafe; color: #1d4ed8; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        ul { margin: 8px 0 0 18px; padding: 0; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Proposta #<?= (int) $proposta->id ?></h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/propostas">Voltar</a>
                <?php if ($favorito !== null): ?>
                    <a class="btn" href="/favoritos/<?= (int) $favorito->id ?>">Pipeline</a>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel grid">
            <article>
                <h3>Status</h3>
                <p><span class="badge <?= $badgeStatusClass ?>"><?= htmlspecialchars((string) ($proposta->status ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></p>
            </article>
            <article>
                <h3>Edital</h3>
                <p>#<?= htmlspecialchars((string) ($proposta->editalNumero ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Orgao</h3>
                <p><?= htmlspecialchars((string) ($proposta->editalOrgaoNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Score / Nivel</h3>
                <p>
                    <?= $proposta->correspondenciaScore !== null ? number_format((float) $proposta->correspondenciaScore, 2, ',', '.') : '-' ?>
                    / <?= htmlspecialchars((string) ($proposta->correspondenciaNivelRelevancia ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                </p>
            </article>
            <article>
                <h3>Prazo do edital</h3>
                <p><?= htmlspecialchars((string) ($proposta->editalDataEncerramento ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Geracao</h3>
                <p><?= $proposta->geradaAutomatica ? 'Automatica (assistente)' : 'Manual' ?></p>
            </article>
            <article>
                <h3>Resultado atual</h3>
                <?php if ($ultimoResultado !== null): ?>
                    <p>
                        <span class="badge <?= $badgeResultadoClass($ultimoResultado->situacao ?? null) ?>">
                            <?= htmlspecialchars((string) ($ultimoResultado->situacao ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </p>
                <?php else: ?>
                    <p>-</p>
                <?php endif; ?>
            </article>
        </section>

        <section class="panel">
            <div class="actions">
                <form method="POST" action="/favoritos/<?= (int) $proposta->favoritoId ?>/proposta/gerar">
                    <input type="hidden" name="abrir_detalhe" value="1">
                    <button class="btn" type="submit">Regenerar rascunho automatico</button>
                </form>
            </div>
        </section>

        <section class="panel">
            <h3>Workflow de aprovacao e envio</h3>
            <p class="muted">Fluxo controlado: RASCUNHO -> EM_REVISAO -> APROVADA -> ENVIADA.</p>

            <?php if ($statusAtual === 'RASCUNHO'): ?>
                <form method="POST" action="/propostas/<?= (int) $proposta->id ?>/solicitar-aprovacao">
                    <label for="observacao_solicitacao">Contexto para aprovacao</label>
                    <textarea id="observacao_solicitacao" name="observacao_solicitacao" rows="4" placeholder="Ex.: proposta revisada pela area tecnica, pronta para validacao comercial."></textarea>
                    <div class="actions" style="margin-top: 10px;">
                        <button class="btn btn-primary" type="submit">Solicitar aprovacao</button>
                    </div>
                </form>
            <?php elseif ($statusAtual === 'EM_REVISAO'): ?>
                <?php if ($aprovacaoPendente !== null): ?>
                    <p class="muted">
                        Solicitacao pendente #<?= (int) $aprovacaoPendente->id ?>
                        (aberta em <?= htmlspecialchars($formatarDataHora($aprovacaoPendente->solicitadoEm ?? null), ENT_QUOTES, 'UTF-8') ?>).
                    </p>
                    <form method="POST" action="/propostas/<?= (int) $proposta->id ?>/decisao-aprovacao">
                        <input type="hidden" name="aprovacao_id" value="<?= (int) $aprovacaoPendente->id ?>">
                        <div class="grid">
                            <article>
                                <label for="decisao">Decisao</label>
                                <select id="decisao" name="decisao">
                                    <option value="APROVADA">Aprovar</option>
                                    <option value="REPROVADA">Reprovar</option>
                                </select>
                            </article>
                            <article style="grid-column: 1 / -1;">
                                <label for="parecer">Parecer</label>
                                <textarea id="parecer" name="parecer" rows="4" placeholder="Ex.: aprovado com ajuste no cronograma e no valor final."></textarea>
                            </article>
                        </div>
                        <div class="actions" style="margin-top: 10px;">
                            <button class="btn btn-primary" type="submit">Registrar decisao</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="muted">Nao existe solicitacao pendente no momento. Reenvie para aprovacao se necessario.</p>
                <?php endif; ?>
            <?php elseif ($statusAtual === 'APROVADA'): ?>
                <form method="POST" action="/propostas/<?= (int) $proposta->id ?>/registrar-submissao">
                    <div class="grid">
                        <article>
                            <label for="canal">Canal de envio</label>
                            <select id="canal" name="canal">
                                <?php foreach ($canaisSubmissao as $canal): ?>
                                    <option value="<?= htmlspecialchars((string) $canal, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $canal, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </article>
                        <article>
                            <label for="protocolo">Protocolo</label>
                            <input id="protocolo" name="protocolo" type="text" maxlength="150" placeholder="Numero do protocolo">
                        </article>
                        <article>
                            <label for="data_submissao">Data/hora da submissao</label>
                            <input id="data_submissao" name="data_submissao" type="datetime-local" value="<?= htmlspecialchars($dataSubmissaoDefault, ENT_QUOTES, 'UTF-8') ?>">
                        </article>
                        <article>
                            <label for="valor_enviado">Valor enviado (R$)</label>
                            <input id="valor_enviado" name="valor_enviado" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string) ($proposta->valorProposta ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        </article>
                        <article style="grid-column: 1 / -1;">
                            <label for="link_comprovante">Link do comprovante</label>
                            <input id="link_comprovante" name="link_comprovante" type="url" maxlength="500" placeholder="https://...">
                        </article>
                        <article style="grid-column: 1 / -1;">
                            <label for="observacao_submissao">Observacao da submissao</label>
                            <textarea id="observacao_submissao" name="observacao" rows="4"></textarea>
                        </article>
                    </div>
                    <div class="actions" style="margin-top: 10px;">
                        <button class="btn btn-primary" type="submit">Registrar submissao</button>
                    </div>
                </form>
            <?php else: ?>
                <p class="muted">Envio registrado. Agora acompanhe o resultado oficial da disputa.</p>
                <?php if ($ultimoResultado !== null): ?>
                    <p class="muted">
                        Ultimo resultado: <strong><?= htmlspecialchars((string) ($ultimoResultado->situacao ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                        em <?= htmlspecialchars($formatarDataHora($ultimoResultado->dataResultado ?? null), ENT_QUOTES, 'UTF-8') ?>.
                    </p>
                <?php endif; ?>
                <form method="POST" action="/propostas/<?= (int) $proposta->id ?>/registrar-resultado">
                    <div class="grid">
                        <article>
                            <label for="situacao">Situacao do resultado</label>
                            <select id="situacao" name="situacao">
                                <?php foreach ($situacoesResultado as $situacao): ?>
                                    <option value="<?= htmlspecialchars((string) $situacao, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $situacao, ENT_QUOTES, 'UTF-8') ?></option>
                                <?php endforeach; ?>
                            </select>
                        </article>
                        <article>
                            <label for="data_resultado">Data/hora do resultado</label>
                            <input id="data_resultado" name="data_resultado" type="datetime-local" value="<?= htmlspecialchars($dataResultadoDefault, ENT_QUOTES, 'UTF-8') ?>">
                        </article>
                        <article>
                            <label for="valor_homologado">Valor homologado (R$)</label>
                            <input id="valor_homologado" name="valor_homologado" type="number" min="0" step="0.01">
                        </article>
                        <article>
                            <label for="colocacao">Colocacao</label>
                            <input id="colocacao" name="colocacao" type="number" min="1" step="1" placeholder="Ex.: 1">
                        </article>
                        <article style="grid-column: 1 / -1;">
                            <label for="link_ata">Link da ata/resultado</label>
                            <input id="link_ata" name="link_ata" type="url" maxlength="500" placeholder="https://...">
                        </article>
                        <article style="grid-column: 1 / -1;">
                            <label for="motivo">Motivo do resultado</label>
                            <textarea id="motivo" name="motivo" rows="3"></textarea>
                        </article>
                        <article style="grid-column: 1 / -1;">
                            <label for="observacao_resultado">Observacao interna</label>
                            <textarea id="observacao_resultado" name="observacao" rows="3"></textarea>
                        </article>
                    </div>
                    <div class="actions" style="margin-top: 10px;">
                        <button class="btn btn-primary" type="submit">Registrar resultado</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Edicao da proposta</h3>
            <form method="POST" action="/propostas/<?= (int) $proposta->id ?>">
                <div class="grid">
                    <article style="grid-column: 1 / -1;">
                        <label for="titulo">Titulo</label>
                        <input id="titulo" name="titulo" type="text" maxlength="220" value="<?= htmlspecialchars((string) ($proposta->titulo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </article>
                    <article>
                        <label for="valor_proposta">Valor da proposta (R$)</label>
                        <input id="valor_proposta" name="valor_proposta" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string) ($proposta->valorProposta ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </article>
                    <article>
                        <label>Status atual</label>
                        <input type="text" value="<?= htmlspecialchars($statusAtual, ENT_QUOTES, 'UTF-8') ?>" readonly>
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="resumo_executivo">Resumo executivo</label>
                        <textarea id="resumo_executivo" name="resumo_executivo" rows="5"><?= htmlspecialchars((string) ($proposta->resumoExecutivo ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="estrategia_proposta">Estrategia da proposta</label>
                        <textarea id="estrategia_proposta" name="estrategia_proposta" rows="5"><?= htmlspecialchars((string) ($proposta->estrategiaProposta ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="escopo_entrega">Escopo de entrega</label>
                        <textarea id="escopo_entrega" name="escopo_entrega" rows="5"><?= htmlspecialchars((string) ($proposta->escopoEntrega ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="diferenciais">Diferenciais</label>
                        <textarea id="diferenciais" name="diferenciais" rows="5"><?= htmlspecialchars((string) ($proposta->diferenciais ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="cronograma_macro">Cronograma macro</label>
                        <textarea id="cronograma_macro" name="cronograma_macro" rows="5"><?= htmlspecialchars((string) ($proposta->cronogramaMacro ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="risco_principal">Risco principal</label>
                        <textarea id="risco_principal" name="risco_principal" rows="4"><?= htmlspecialchars((string) ($proposta->riscoPrincipal ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="observacoes">Observacoes internas</label>
                        <textarea id="observacoes" name="observacoes" rows="4"><?= htmlspecialchars((string) ($proposta->observacoes ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                </div>
                <div class="actions" style="margin-top: 10px;">
                    <button class="btn btn-primary" type="submit">Salvar proposta</button>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Historico de aprovacoes</h3>
            <?php if ($aprovacoes === []): ?>
                <p class="muted">Nenhuma solicitacao de aprovacao registrada.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Solicitacao</th>
                            <th>Decisao</th>
                            <th>Parecer</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aprovacoes as $aprovacao): ?>
                            <tr>
                                <td>#<?= (int) ($aprovacao->id ?? 0) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars((string) ($aprovacao->statusDecisao ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                    <span class="muted">Por: <?= htmlspecialchars((string) ($aprovacao->solicitadoPorUsuarioNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></span><br>
                                    <span class="muted">Em: <?= htmlspecialchars($formatarDataHora($aprovacao->solicitadoEm ?? null), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <span class="muted">Decisor: <?= htmlspecialchars((string) ($aprovacao->decididoPorUsuarioNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></span><br>
                                    <span class="muted">Quando: <?= htmlspecialchars($formatarDataHora($aprovacao->decididoEm ?? null), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td><?= nl2br(htmlspecialchars((string) ($aprovacao->parecer ?? $aprovacao->observacaoSolicitacao ?? '-'), ENT_QUOTES, 'UTF-8')) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Historico de submissoes</h3>
            <?php if ($submissoes === []): ?>
                <p class="muted">Nenhuma submissao registrada.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Canal</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Protocolo</th>
                            <th>Responsavel</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissoes as $submissao): ?>
                            <tr>
                                <td>#<?= (int) ($submissao->id ?? 0) ?></td>
                                <td><?= htmlspecialchars((string) ($submissao->canal ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($formatarDataHora($submissao->dataSubmissao ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?= $submissao->valorEnviado !== null
                                        ? 'R$ ' . number_format((float) $submissao->valorEnviado, 2, ',', '.')
                                        : '-' ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) ($submissao->protocolo ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($submissao->linkComprovante)): ?>
                                        <br><a href="<?= htmlspecialchars((string) $submissao->linkComprovante, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Comprovante</a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars((string) ($submissao->usuarioNome ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    <?php if (!empty($submissao->observacao)): ?>
                                        <br><span class="muted"><?= nl2br(htmlspecialchars((string) $submissao->observacao, ENT_QUOTES, 'UTF-8')) ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Historico de resultados</h3>
            <?php if ($resultados === []): ?>
                <p class="muted">Nenhum resultado registrado ate o momento.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Situacao</th>
                            <th>Data</th>
                            <th>Valor/Colocacao</th>
                            <th>Registro</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultados as $resultado): ?>
                            <tr>
                                <td>#<?= (int) ($resultado->id ?? 0) ?></td>
                                <td>
                                    <span class="badge <?= $badgeResultadoClass($resultado->situacao ?? null) ?>">
                                        <?= htmlspecialchars((string) ($resultado->situacao ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($formatarDataHora($resultado->dataResultado ?? null), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?= $resultado->valorHomologado !== null ? 'R$ ' . number_format((float) $resultado->valorHomologado, 2, ',', '.') : '-' ?>
                                    <br><span class="muted">Colocacao: <?= htmlspecialchars((string) ($resultado->colocacao ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                                </td>
                                <td>
                                    <span class="muted">Por: <?= htmlspecialchars((string) ($resultado->usuarioNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></span><br>
                                    <?= nl2br(htmlspecialchars((string) ($resultado->motivo ?? $resultado->observacao ?? '-'), ENT_QUOTES, 'UTF-8')) ?>
                                    <?php if (!empty($resultado->linkAta)): ?>
                                        <br><a href="<?= htmlspecialchars((string) $resultado->linkAta, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Ata/resultado</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Checklist relacionado</h3>
            <?php if ($tarefas === []): ?>
                <p class="muted">Sem tarefas no pipeline deste item.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($tarefas as $tarefa): ?>
                        <li>
                            <?= htmlspecialchars((string) ($tarefa->titulo ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            - prazo: <?= htmlspecialchars((string) ($tarefa->dataLimite ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            - status: <?= htmlspecialchars((string) ($tarefa->status ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
