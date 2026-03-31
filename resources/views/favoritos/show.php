<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$favorito = isset($favorito) ? $favorito : null;
$tarefas = isset($tarefas) && is_array($tarefas) ? $tarefas : [];
$recomendacao = isset($recomendacao) && is_array($recomendacao) ? $recomendacao : [];
$statusPermitidos = isset($statusPermitidos) && is_array($statusPermitidos) ? $statusPermitidos : [];
$statusTarefaPermitidos = isset($statusTarefaPermitidos) && is_array($statusTarefaPermitidos) ? $statusTarefaPermitidos : [];
$message = isset($message) ? (string) $message : null;

if ($favorito === null) {
    echo 'Item do pipeline nao encontrado.';
    return;
}

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$badgeStatusClass = match (strtoupper((string) ($favorito->statusAcompanhamento ?? 'FAVORITO'))) {
    'EM_ANALISE' => 'badge-analise',
    'PROPOSTA' => 'badge-proposta',
    'DESCARTADO' => 'badge-descartado',
    'ENCERRADO' => 'badge-encerrado',
    default => 'badge-favorito',
};

$badgeTarefaClass = static function (?string $status): string {
    return match (strtoupper((string) $status)) {
        'EM_ANDAMENTO' => 'badge-tarefa-andamento',
        'CONCLUIDA' => 'badge-tarefa-concluida',
        'BLOQUEADA' => 'badge-tarefa-bloqueada',
        default => 'badge-tarefa-pendente',
    };
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pipeline #<?= (int) $favorito->id ?> | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1200px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .btn-danger { border-color: #fecaca; color: #991b1b; background: #fff5f5; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        textarea, input, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-favorito { background: #dbeafe; color: #1d4ed8; }
        .badge-analise { background: #ffedd5; color: #9a3412; }
        .badge-proposta { background: #dcfce7; color: #166534; }
        .badge-descartado { background: #fee2e2; color: #991b1b; }
        .badge-encerrado { background: #e5e7eb; color: #374151; }
        .badge-tarefa-pendente { background: #f1f5f9; color: #475569; }
        .badge-tarefa-andamento { background: #ffedd5; color: #9a3412; }
        .badge-tarefa-concluida { background: #dcfce7; color: #166534; }
        .badge-tarefa-bloqueada { background: #fee2e2; color: #991b1b; }
        .muted { color: #475569; font-size: 13px; }
        .inline-form { display: grid; grid-template-columns: 1fr auto auto; gap: 6px; align-items: center; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Pipeline #<?= (int) $favorito->id ?></h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/favoritos">Voltar ao pipeline</a>
                <a class="btn" href="/oportunidades">Oportunidades</a>
                <?php if (($favorito->correspondenciaId ?? null) !== null && (int) $favorito->correspondenciaId > 0): ?>
                    <a class="btn" href="/oportunidades/<?= (int) $favorito->correspondenciaId ?>">Oportunidade origem</a>
                <?php endif; ?>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel grid">
            <article>
                <h3>Status</h3>
                <p><span class="badge <?= $badgeStatusClass ?>"><?= htmlspecialchars((string) ($favorito->statusAcompanhamento ?? '-'), ENT_QUOTES, 'UTF-8') ?></span></p>
            </article>
            <article>
                <h3>Score de aderencia</h3>
                <p><?= $favorito->correspondenciaScore !== null ? number_format((float) $favorito->correspondenciaScore, 2, ',', '.') : '-' ?></p>
            </article>
            <article>
                <h3>Nivel</h3>
                <p><?= htmlspecialchars((string) ($favorito->correspondenciaNivelRelevancia ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Edital</h3>
                <p>#<?= htmlspecialchars((string) ($favorito->editalNumero ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Orgao</h3>
                <p><?= htmlspecialchars((string) ($favorito->editalOrgaoNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>UF / Modalidade</h3>
                <p><?= htmlspecialchars((string) ($favorito->editalUf ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) ($favorito->editalModalidade ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Prazo de encerramento</h3>
                <p><?= htmlspecialchars((string) ($favorito->editalDataEncerramento ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Tarefas</h3>
                <p>Total: <?= (int) ($favorito->totalTarefas ?? 0) ?> | Abertas: <?= (int) ($favorito->tarefasAbertas ?? 0) ?></p>
            </article>
        </section>

        <section class="panel">
            <h3>Recomendacao de decisao</h3>
            <p><strong><?= htmlspecialchars((string) ($recomendacao['nivel'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong></p>
            <p><?= htmlspecialchars((string) ($recomendacao['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            <p class="muted">Status sugerido: <?= htmlspecialchars((string) ($recomendacao['status_sugerido'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="panel">
            <h3>Atualizar status e observacao</h3>
            <form method="POST" action="/favoritos/<?= (int) $favorito->id ?>/status">
                <div class="grid">
                    <article>
                        <label for="status_acompanhamento">Status</label>
                        <select id="status_acompanhamento" name="status_acompanhamento">
                            <?php foreach ($statusPermitidos as $status): ?>
                                <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($favorito->statusAcompanhamento ?? '') === (string) $status) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </article>
                    <article>
                        <label for="observacao">Observacao operacional</label>
                        <textarea id="observacao" name="observacao" rows="3"><?= htmlspecialchars((string) ($favorito->observacao ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                </div>
                <input type="hidden" name="redirect_to" value="/favoritos/<?= (int) $favorito->id ?>">
                <div class="actions" style="margin-top: 10px;">
                    <button class="btn btn-primary" type="submit">Salvar atualizacao</button>
                    <?php if (($favorito->editalLinkDetalhe ?? null) !== null && $favorito->editalLinkDetalhe !== ''): ?>
                        <a class="btn" href="<?= htmlspecialchars($favorito->editalLinkDetalhe, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Abrir detalhe externo</a>
                    <?php endif; ?>
                    <?php if (($favorito->editalLinkEdital ?? null) !== null && $favorito->editalLinkEdital !== ''): ?>
                        <a class="btn" href="<?= htmlspecialchars($favorito->editalLinkEdital, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Abrir edital</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Checklist de execucao</h3>
            <?php if ($tarefas === []): ?>
                <p>Nenhuma tarefa cadastrada. Adicione tarefas abaixo.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Tarefa</th>
                            <th>Responsavel / Prazo</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($tarefas as $tarefa): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars((string) ($tarefa->titulo ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if (($tarefa->descricao ?? null) !== null && $tarefa->descricao !== ''): ?>
                                    <p class="muted"><?= nl2br(htmlspecialchars((string) $tarefa->descricao, ENT_QUOTES, 'UTF-8')) ?></p>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="muted">Responsavel: <?= htmlspecialchars((string) ($tarefa->responsavel ?? '-'), ENT_QUOTES, 'UTF-8') ?></span><br>
                                <span class="muted">Prazo: <?= htmlspecialchars((string) ($tarefa->dataLimite ?? '-'), ENT_QUOTES, 'UTF-8') ?></span><br>
                                <span class="muted">Concluida em: <?= htmlspecialchars((string) ($tarefa->concluidaEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $badgeTarefaClass($tarefa->status ?? null) ?>"><?= htmlspecialchars((string) ($tarefa->status ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                                <form class="inline-form" method="POST" action="/favoritos/<?= (int) $favorito->id ?>/tarefas/<?= (int) $tarefa->id ?>/status">
                                    <select name="status">
                                        <?php foreach ($statusTarefaPermitidos as $statusTarefa): ?>
                                            <option value="<?= htmlspecialchars((string) $statusTarefa, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($tarefa->status ?? '') === (string) $statusTarefa) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars((string) $statusTarefa, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn" type="submit">Atualizar</button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="/favoritos/<?= (int) $favorito->id ?>/tarefas/<?= (int) $tarefa->id ?>/delete" onsubmit="return confirm('Remover tarefa?');">
                                    <button class="btn btn-danger" type="submit">Remover</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Adicionar tarefa</h3>
            <form method="POST" action="/favoritos/<?= (int) $favorito->id ?>/tarefas">
                <div class="grid">
                    <article>
                        <label for="titulo">Titulo</label>
                        <input id="titulo" name="titulo" type="text" maxlength="180" required>
                    </article>
                    <article>
                        <label for="responsavel">Responsavel</label>
                        <input id="responsavel" name="responsavel" type="text" maxlength="120">
                    </article>
                    <article>
                        <label for="data_limite">Data limite</label>
                        <input id="data_limite" name="data_limite" type="date">
                    </article>
                    <article style="grid-column: 1 / -1;">
                        <label for="descricao">Descricao</label>
                        <textarea id="descricao" name="descricao" rows="3"></textarea>
                    </article>
                </div>
                <div class="actions" style="margin-top: 10px;">
                    <button class="btn btn-primary" type="submit">Adicionar tarefa</button>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
