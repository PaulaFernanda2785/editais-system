<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$proposta = isset($proposta) ? $proposta : null;
$favorito = isset($favorito) ? $favorito : null;
$tarefas = isset($tarefas) && is_array($tarefas) ? $tarefas : [];
$statusPermitidos = isset($statusPermitidos) && is_array($statusPermitidos) ? $statusPermitidos : [];
$message = isset($message) ? (string) $message : null;

if ($proposta === null) {
    echo 'Proposta nao encontrada.';
    return;
}

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$badgeStatusClass = match (strtoupper((string) ($proposta->status ?? 'RASCUNHO'))) {
    'EM_REVISAO' => 'badge-revisao',
    'APROVADA' => 'badge-aprovada',
    'ENVIADA' => 'badge-enviada',
    default => 'badge-rascunho',
};
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
            <h3>Edicao da proposta</h3>
            <form method="POST" action="/propostas/<?= (int) $proposta->id ?>">
                <div class="grid">
                    <article>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <?php foreach ($statusPermitidos as $status): ?>
                                <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) $proposta->status === (string) $status) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </article>
                    <article>
                        <label for="valor_proposta">Valor da proposta (R$)</label>
                        <input id="valor_proposta" name="valor_proposta" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string) ($proposta->valorProposta ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="titulo">Titulo</label>
                        <input id="titulo" name="titulo" type="text" maxlength="220" value="<?= htmlspecialchars((string) ($proposta->titulo ?? ''), ENT_QUOTES, 'UTF-8') ?>">
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
