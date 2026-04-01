<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$items = isset($items) && is_array($items) ? $items : [];
$total = isset($total) ? (int) $total : 0;
$page = isset($page) ? (int) $page : 1;
$perPage = isset($perPage) ? (int) $perPage : 20;
$totalPages = isset($totalPages) ? (int) $totalPages : 1;
$sort = isset($sort) ? (string) $sort : 'atualizado_desc';
$filters = isset($filters) && is_array($filters) ? $filters : [];
$statusPermitidos = isset($statusPermitidos) && is_array($statusPermitidos) ? $statusPermitidos : [];
$resumo = isset($resumo) && is_array($resumo) ? $resumo : [];
$alertasPrazo = isset($alertasPrazo) && is_array($alertasPrazo) ? $alertasPrazo : [];
$conversao = isset($conversao) && is_array($conversao) ? $conversao : [];
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$queryBase = [
    'termo' => (string) ($filters['termo'] ?? ''),
    'status_acompanhamento' => (string) ($filters['status_acompanhamento'] ?? ''),
    'sort' => $sort,
    'per_page' => $perPage,
];

$buildPageUrl = static function (int $targetPage, array $base): string {
    $params = $base;
    $params['page'] = $targetPage;
    return '/favoritos?' . http_build_query($params);
};

$badgeStatusClass = static function (?string $status): string {
    return match (strtoupper((string) $status)) {
        'EM_ANALISE' => 'badge-analise',
        'PROPOSTA' => 'badge-proposta',
        'DESCARTADO' => 'badge-descartado',
        'ENCERRADO' => 'badge-encerrado',
        default => 'badge-favorito',
    };
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pipeline | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1240px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 8px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 8px; margin-top: 10px; }
        .summary-card { border: 1px solid #dfe6f0; border-radius: 6px; padding: 10px; background: #f8fafc; }
        input, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .pagination { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 12px; }
        .muted { color: #475569; font-size: 13px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-favorito { background: #dbeafe; color: #1d4ed8; }
        .badge-analise { background: #ffedd5; color: #9a3412; }
        .badge-proposta { background: #dcfce7; color: #166534; }
        .badge-descartado { background: #fee2e2; color: #991b1b; }
        .badge-encerrado { background: #e5e7eb; color: #374151; }
        .inline-form { display: grid; grid-template-columns: 1fr auto; gap: 6px; margin-top: 8px; }
        .line { display: block; margin-bottom: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Pipeline de Decisao e Execucao</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/oportunidades">Oportunidades</a>
                <a class="btn" href="/propostas">Propostas</a>
                <a class="btn" href="/favoritos/relatorio/conversao">Relatorio de conversao</a>
                <a class="btn" href="/editais">Catalogo</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel">
            <h3>Visao rapida do funil</h3>
            <div class="summary">
                <article class="summary-card">
                    <strong>Total</strong>
                    <div><?= (int) ($resumo['TOTAL'] ?? 0) ?></div>
                </article>
                <article class="summary-card">
                    <strong>Favorito</strong>
                    <div><?= (int) ($resumo['FAVORITO'] ?? 0) ?></div>
                </article>
                <article class="summary-card">
                    <strong>Em analise</strong>
                    <div><?= (int) ($resumo['EM_ANALISE'] ?? 0) ?></div>
                </article>
                <article class="summary-card">
                    <strong>Proposta</strong>
                    <div><?= (int) ($resumo['PROPOSTA'] ?? 0) ?></div>
                </article>
                <article class="summary-card">
                    <strong>Encerrado</strong>
                    <div><?= (int) ($resumo['ENCERRADO'] ?? 0) ?></div>
                </article>
                <article class="summary-card">
                    <strong>Descartado</strong>
                    <div><?= (int) ($resumo['DESCARTADO'] ?? 0) ?></div>
                </article>
                <article class="summary-card">
                    <strong>Conv. Analise -> Proposta</strong>
                    <div><?= number_format((float) ($conversao['taxas']['analise_para_proposta'] ?? 0), 1, ',', '.') ?>%</div>
                </article>
                <article class="summary-card">
                    <strong>Conv. Proposta -> Encerrado</strong>
                    <div><?= number_format((float) ($conversao['taxas']['proposta_para_encerrado'] ?? 0), 1, ',', '.') ?>%</div>
                </article>
            </div>
        </section>

        <section class="panel">
            <h3>Alertas de prazo (48h)</h3>
            <p class="muted">
                Vencendo em ate 48h: <?= (int) ($alertasPrazo['totais']['vencendo'] ?? 0) ?>
                | Vencidas: <?= (int) ($alertasPrazo['totais']['vencidas'] ?? 0) ?>
            </p>
            <div class="grid">
                <article>
                    <h4>Vencendo em ate 48h</h4>
                    <?php $vencendo = isset($alertasPrazo['vencendo']) && is_array($alertasPrazo['vencendo']) ? $alertasPrazo['vencendo'] : []; ?>
                    <?php if ($vencendo === []): ?>
                        <p class="muted">Sem tarefas para as proximas 48h.</p>
                    <?php else: ?>
                        <?php foreach ($vencendo as $linha): ?>
                            <p class="muted">
                                <strong>#<?= htmlspecialchars((string) ($linha['numero_edital'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                                - <?= htmlspecialchars((string) ($linha['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                (<?= htmlspecialchars((string) ($linha['data_limite'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>)
                                <a href="/favoritos/<?= (int) ($linha['favorito_id'] ?? 0) ?>">abrir</a>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </article>
                <article>
                    <h4>Vencidas</h4>
                    <?php $vencidas = isset($alertasPrazo['vencidas']) && is_array($alertasPrazo['vencidas']) ? $alertasPrazo['vencidas'] : []; ?>
                    <?php if ($vencidas === []): ?>
                        <p class="muted">Sem tarefas vencidas.</p>
                    <?php else: ?>
                        <?php foreach ($vencidas as $linha): ?>
                            <p class="muted">
                                <strong>#<?= htmlspecialchars((string) ($linha['numero_edital'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong>
                                - <?= htmlspecialchars((string) ($linha['titulo'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                (<?= htmlspecialchars((string) ($linha['data_limite'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>)
                                <a href="/favoritos/<?= (int) ($linha['favorito_id'] ?? 0) ?>">abrir</a>
                            </p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </article>
            </div>
        </section>

        <section class="panel">
            <form method="GET" action="/favoritos">
                <div class="grid">
                    <div>
                        <label for="termo">Termo</label>
                        <input id="termo" name="termo" type="text" value="<?= htmlspecialchars((string) ($filters['termo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="status_acompanhamento">Status</label>
                        <select id="status_acompanhamento" name="status_acompanhamento">
                            <option value="">Todos</option>
                            <?php foreach ($statusPermitidos as $status): ?>
                                <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($filters['status_acompanhamento'] ?? '') === (string) $status) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="sort">Ordenacao</label>
                        <select id="sort" name="sort">
                            <option value="atualizado_desc" <?= $sort === 'atualizado_desc' ? 'selected' : '' ?>>Atualizacao (recente)</option>
                            <option value="atualizado_asc" <?= $sort === 'atualizado_asc' ? 'selected' : '' ?>>Atualizacao (antiga)</option>
                            <option value="score_desc" <?= $sort === 'score_desc' ? 'selected' : '' ?>>Score oportunidade</option>
                            <option value="prazo_asc" <?= $sort === 'prazo_asc' ? 'selected' : '' ?>>Prazo de encerramento</option>
                        </select>
                    </div>
                    <div>
                        <label for="per_page">Itens por pagina</label>
                        <select id="per_page" name="per_page">
                            <option value="10" <?= $perPage === 10 ? 'selected' : '' ?>>10</option>
                            <option value="20" <?= $perPage === 20 ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= $perPage === 50 ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= $perPage === 100 ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 10px; display: flex; gap: 8px; flex-wrap: wrap;">
                    <button class="btn btn-primary" type="submit">Filtrar</button>
                    <a class="btn" href="/favoritos">Limpar filtros</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Itens do Pipeline</h3>
            <p class="muted">Total encontrado: <?= $total ?></p>

            <?php if ($items === []): ?>
                <p>Nenhum item de pipeline encontrado para os filtros informados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Edital</th>
                            <th>Score</th>
                            <th>Tarefas</th>
                            <th>Atualizado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <span class="badge <?= $badgeStatusClass($item->statusAcompanhamento ?? null) ?>">
                                    <?= htmlspecialchars((string) ($item->statusAcompanhamento ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <form class="inline-form" method="POST" action="/favoritos/<?= (int) $item->id ?>/status">
                                    <select name="status_acompanhamento">
                                        <?php foreach ($statusPermitidos as $status): ?>
                                            <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) $item->statusAcompanhamento === (string) $status) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars((string) ($_SERVER['REQUEST_URI'] ?? '/favoritos'), ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="btn" type="submit">Salvar</button>
                                </form>
                            </td>
                            <td>
                                <span class="line">#<?= htmlspecialchars((string) ($item->editalNumero ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="line"><?= htmlspecialchars((string) ($item->editalOrgaoNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="muted">UF: <?= htmlspecialchars((string) ($item->editalUf ?? '-'), ENT_QUOTES, 'UTF-8') ?> | Modalidade: <?= htmlspecialchars((string) ($item->editalModalidade ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="line"><?= $item->correspondenciaScore !== null ? number_format((float) $item->correspondenciaScore, 2, ',', '.') : '-' ?></span>
                                <span class="muted"><?= htmlspecialchars((string) ($item->correspondenciaNivelRelevancia ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <span class="line">Total: <?= (int) ($item->totalTarefas ?? 0) ?></span>
                                <span class="muted">Abertas: <?= (int) ($item->tarefasAbertas ?? 0) ?></span>
                            </td>
                            <td><?= htmlspecialchars((string) ($item->atualizadoEm ?? $item->criadoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="/favoritos/<?= (int) $item->id ?>">Detalhes</a>
                                    <?php if (($item->correspondenciaId ?? null) !== null && (int) $item->correspondenciaId > 0): ?>
                                        <a class="btn" href="/oportunidades/<?= (int) $item->correspondenciaId ?>">Oportunidade</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a class="btn" href="<?= htmlspecialchars($buildPageUrl($page - 1, $queryBase), ENT_QUOTES, 'UTF-8') ?>">Anterior</a>
                <?php endif; ?>

                <?php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                    for ($p = $start; $p <= $end; $p++):
                ?>
                    <?php if ($p === $page): ?>
                        <span class="btn btn-primary"><?= $p ?></span>
                    <?php else: ?>
                        <a class="btn" href="<?= htmlspecialchars($buildPageUrl($p, $queryBase), ENT_QUOTES, 'UTF-8') ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a class="btn" href="<?= htmlspecialchars($buildPageUrl($page + 1, $queryBase), ENT_QUOTES, 'UTF-8') ?>">Proxima</a>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
