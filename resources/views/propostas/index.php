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
$alertasResultado = isset($alertasResultado) && is_array($alertasResultado) ? $alertasResultado : [];
$painelWinLoss = isset($painelWinLoss) && is_array($painelWinLoss) ? $painelWinLoss : [];
$message = isset($message) ? (string) $message : null;

$alertasSemResultado = isset($alertasResultado['sem_resultado']) && is_array($alertasResultado['sem_resultado'])
    ? $alertasResultado['sem_resultado']
    : [];
$alertasJulgamentoParado = isset($alertasResultado['julgamento_parado']) && is_array($alertasResultado['julgamento_parado'])
    ? $alertasResultado['julgamento_parado']
    : [];
$configAlertas = isset($alertasResultado['config']) && is_array($alertasResultado['config']) ? $alertasResultado['config'] : [];
$winLossOrgao = isset($painelWinLoss['por_orgao']) && is_array($painelWinLoss['por_orgao']) ? $painelWinLoss['por_orgao'] : [];
$winLossModalidade = isset($painelWinLoss['por_modalidade']) && is_array($painelWinLoss['por_modalidade']) ? $painelWinLoss['por_modalidade'] : [];

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$queryBase = [
    'termo' => (string) ($filters['termo'] ?? ''),
    'status' => (string) ($filters['status'] ?? ''),
    'sort' => $sort,
    'per_page' => $perPage,
];

$buildPageUrl = static function (int $targetPage, array $base): string {
    $params = $base;
    $params['page'] = $targetPage;
    return '/propostas?' . http_build_query($params);
};

$badgeStatusClass = static function (?string $status): string {
    return match (strtoupper((string) $status)) {
        'EM_REVISAO' => 'badge-revisao',
        'APROVADA' => 'badge-aprovada',
        'ENVIADA' => 'badge-enviada',
        default => 'badge-rascunho',
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
    return $timestamp === false ? $raw : date('d/m/Y H:i', $timestamp);
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Propostas | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1260px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 8px; }
        .summary-card { border: 1px solid #dfe6f0; border-radius: 6px; padding: 10px; background: #f8fafc; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; }
        input, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .pagination { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 12px; }
        .muted { color: #475569; font-size: 13px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-rascunho { background: #f1f5f9; color: #475569; }
        .badge-revisao { background: #ffedd5; color: #9a3412; }
        .badge-aprovada { background: #dcfce7; color: #166534; }
        .badge-enviada { background: #dbeafe; color: #1d4ed8; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Assistente de Propostas</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/favoritos">Pipeline</a>
                <a class="btn" href="/oportunidades">Oportunidades</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel">
            <h3>Resumo</h3>
            <div class="summary">
                <article class="summary-card"><strong>Total</strong><div><?= (int) ($resumo['TOTAL'] ?? 0) ?></div></article>
                <article class="summary-card"><strong>Rascunho</strong><div><?= (int) ($resumo['RASCUNHO'] ?? 0) ?></div></article>
                <article class="summary-card"><strong>Em revisao</strong><div><?= (int) ($resumo['EM_REVISAO'] ?? 0) ?></div></article>
                <article class="summary-card"><strong>Aprovada</strong><div><?= (int) ($resumo['APROVADA'] ?? 0) ?></div></article>
                <article class="summary-card"><strong>Enviada</strong><div><?= (int) ($resumo['ENVIADA'] ?? 0) ?></div></article>
                <article class="summary-card"><strong>Resultados</strong><div><?= (int) ($resumo['RESULTADOS_TOTAL'] ?? 0) ?></div></article>
                <article class="summary-card"><strong>Vencedoras</strong><div><?= (int) ($resumo['RESULTADO_VENCEDORA'] ?? 0) ?></div></article>
                <article class="summary-card"><strong>Taxa de sucesso</strong><div><?= number_format((float) ($resumo['TAXA_SUCESSO_ENVIADAS'] ?? 0), 1, ',', '.') ?>%</div></article>
            </div>
        </section>

        <section class="panel">
            <h3>Alertas automáticos de resultado/julgamento</h3>
            <p class="muted">
                Sem resultado apos <?= (int) ($configAlertas['dias_sem_resultado'] ?? 7) ?> dias de envio
                e julgamento sem atualizacao apos <?= (int) ($configAlertas['dias_julgamento_parado'] ?? 10) ?> dias.
            </p>

            <div class="grid">
                <article>
                    <h4>Sem resultado</h4>
                    <?php if ($alertasSemResultado === []): ?>
                        <p class="muted">Nenhum alerta nesta categoria.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($alertasSemResultado as $item): ?>
                                <li>
                                    <a href="/propostas/<?= (int) ($item['proposta_id'] ?? 0) ?>">Proposta #<?= (int) ($item['proposta_id'] ?? 0) ?></a>
                                    - <?= htmlspecialchars((string) ($item['orgao_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    - ultimo envio: <?= htmlspecialchars($formatarDataHora(isset($item['ultima_submissao']) ? (string) $item['ultima_submissao'] : null), ENT_QUOTES, 'UTF-8') ?>
                                    - <?= (int) ($item['dias_sem_retorno'] ?? 0) ?> dias
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
                <article>
                    <h4>Julgamento parado</h4>
                    <?php if ($alertasJulgamentoParado === []): ?>
                        <p class="muted">Nenhum alerta nesta categoria.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($alertasJulgamentoParado as $item): ?>
                                <li>
                                    <a href="/propostas/<?= (int) ($item['proposta_id'] ?? 0) ?>">Proposta #<?= (int) ($item['proposta_id'] ?? 0) ?></a>
                                    - <?= htmlspecialchars((string) ($item['orgao_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                    - atualizacao: <?= htmlspecialchars($formatarDataHora(isset($item['ultima_atualizacao_resultado']) ? (string) $item['ultima_atualizacao_resultado'] : null), ENT_QUOTES, 'UTF-8') ?>
                                    - <?= (int) ($item['dias_em_julgamento'] ?? 0) ?> dias
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </article>
            </div>
        </section>

        <section class="panel">
            <h3>Painel win/loss</h3>
            <div class="grid">
                <article>
                    <h4>Por orgao</h4>
                    <?php if ($winLossOrgao === []): ?>
                        <p class="muted">Sem dados de resultado por orgao.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Orgao</th>
                                    <th>Vitorias</th>
                                    <th>Derrotas</th>
                                    <th>Em julgamento</th>
                                    <th>Taxa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($winLossOrgao as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($item['dimensao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) ($item['vitorias'] ?? 0) ?></td>
                                        <td><?= (int) ($item['derrotas'] ?? 0) ?></td>
                                        <td><?= (int) ($item['em_julgamento'] ?? 0) ?></td>
                                        <td><?= number_format((float) ($item['taxa_sucesso'] ?? 0), 1, ',', '.') ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </article>
                <article>
                    <h4>Por modalidade</h4>
                    <?php if ($winLossModalidade === []): ?>
                        <p class="muted">Sem dados de resultado por modalidade.</p>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Modalidade</th>
                                    <th>Vitorias</th>
                                    <th>Derrotas</th>
                                    <th>Em julgamento</th>
                                    <th>Taxa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($winLossModalidade as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars((string) ($item['dimensao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><?= (int) ($item['vitorias'] ?? 0) ?></td>
                                        <td><?= (int) ($item['derrotas'] ?? 0) ?></td>
                                        <td><?= (int) ($item['em_julgamento'] ?? 0) ?></td>
                                        <td><?= number_format((float) ($item['taxa_sucesso'] ?? 0), 1, ',', '.') ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </article>
            </div>
        </section>

        <section class="panel">
            <form method="GET" action="/propostas">
                <div class="grid">
                    <div>
                        <label for="termo">Termo</label>
                        <input id="termo" name="termo" type="text" value="<?= htmlspecialchars((string) ($filters['termo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">Todos</option>
                            <?php foreach ($statusPermitidos as $status): ?>
                                <option value="<?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>" <?= ((string) ($filters['status'] ?? '') === (string) $status) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="sort">Ordenacao</label>
                        <select id="sort" name="sort">
                            <option value="atualizado_desc" <?= $sort === 'atualizado_desc' ? 'selected' : '' ?>>Atualizacao (recente)</option>
                            <option value="criado_desc" <?= $sort === 'criado_desc' ? 'selected' : '' ?>>Criacao (recente)</option>
                            <option value="valor_desc" <?= $sort === 'valor_desc' ? 'selected' : '' ?>>Valor proposta</option>
                            <option value="prazo_asc" <?= $sort === 'prazo_asc' ? 'selected' : '' ?>>Prazo edital</option>
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
                    <a class="btn" href="/propostas">Limpar filtros</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Propostas</h3>
            <p class="muted">Total encontrado: <?= $total ?></p>

            <?php if ($items === []): ?>
                <p>Nenhuma proposta encontrada. Gere rascunhos a partir do pipeline.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Titulo</th>
                            <th>Edital</th>
                            <th>Score</th>
                            <th>Valor</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <span class="badge <?= $badgeStatusClass($item->status ?? null) ?>">
                                    <?= htmlspecialchars((string) ($item->status ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars((string) ($item->titulo ?? '-'), ENT_QUOTES, 'UTF-8') ?></strong><br>
                                <span class="muted">Atualizado: <?= htmlspecialchars((string) ($item->atualizadoEm ?? $item->criadoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                #<?= htmlspecialchars((string) ($item->editalNumero ?? '-'), ENT_QUOTES, 'UTF-8') ?><br>
                                <span class="muted"><?= htmlspecialchars((string) ($item->editalOrgaoNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?= $item->correspondenciaScore !== null ? number_format((float) $item->correspondenciaScore, 2, ',', '.') : '-' ?><br>
                                <span class="muted"><?= htmlspecialchars((string) ($item->correspondenciaNivelRelevancia ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td>
                                <?= $item->valorProposta !== null ? 'R$ ' . number_format((float) $item->valorProposta, 2, ',', '.') : '-' ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="btn" href="/propostas/<?= (int) $item->id ?>">Abrir</a>
                                    <a class="btn" href="/favoritos/<?= (int) $item->favoritoId ?>">Pipeline</a>
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
