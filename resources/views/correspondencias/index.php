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
$sort = isset($sort) ? (string) $sort : 'score_desc';
$filters = isset($filters) && is_array($filters) ? $filters : [];
$perfis = isset($perfis) && is_array($perfis) ? $perfis : [];
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$queryBase = [
    'termo' => (string) ($filters['termo'] ?? ''),
    'nivel_relevancia' => (string) ($filters['nivel_relevancia'] ?? ''),
    'perfil_id' => (string) ($filters['perfil_id'] ?? ''),
    'sort' => $sort,
    'per_page' => $perPage,
];

$buildPageUrl = static function (int $targetPage, array $base): string {
    $params = $base;
    $params['page'] = $targetPage;
    return '/oportunidades?' . http_build_query($params);
};

$badgeClass = static function (string $nivel): string {
    return match (strtoupper($nivel)) {
        'PRIORITARIA' => 'badge-prioritaria',
        'ALTA' => 'badge-alta',
        'MEDIA' => 'badge-media',
        default => 'badge-baixa',
    };
};

$pipelineBadgeClass = static function (?string $status): string {
    return match (strtoupper((string) $status)) {
        'EM_ANALISE' => 'badge-pipeline-analise',
        'PROPOSTA' => 'badge-pipeline-proposta',
        'DESCARTADO' => 'badge-pipeline-descartado',
        'ENCERRADO' => 'badge-pipeline-encerrado',
        'FAVORITO' => 'badge-pipeline-favorito',
        default => 'badge-pipeline-nao-definido',
    };
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oportunidades | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1220px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; }
        input, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .pagination { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 12px; }
        .muted { color: #475569; font-size: 13px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-prioritaria { background: #fee2e2; color: #991b1b; }
        .badge-alta { background: #ffedd5; color: #9a3412; }
        .badge-media { background: #fef9c3; color: #854d0e; }
        .badge-baixa { background: #dcfce7; color: #166534; }
        .badge-pipeline-favorito { background: #dbeafe; color: #1d4ed8; }
        .badge-pipeline-analise { background: #ffedd5; color: #9a3412; }
        .badge-pipeline-proposta { background: #dcfce7; color: #166534; }
        .badge-pipeline-descartado { background: #fee2e2; color: #991b1b; }
        .badge-pipeline-encerrado { background: #e5e7eb; color: #374151; }
        .badge-pipeline-nao-definido { background: #f1f5f9; color: #475569; }
        .line { display: block; margin-bottom: 3px; }
        .inline { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; margin-top: 10px; }
        .input-limit { width: 110px; }
        .quick-decision { margin-top: 8px; display: grid; grid-template-columns: 1fr auto; gap: 6px; }
        .quick-decision select { min-width: 130px; }
        .quick-decision button { white-space: nowrap; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Oportunidades Inteligentes</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/editais">Catalogo de editais</a>
                <a class="btn" href="/monitoramento">Perfis</a>
                <a class="btn" href="/favoritos">Pipeline</a>
                <a class="btn" href="/propostas">Propostas</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel">
            <h3>Atualizar correspondencias</h3>
            <form method="POST" action="/oportunidades/processar" class="inline">
                <label for="limite_editais">Limite de editais recentes</label>
                <input class="input-limit" id="limite_editais" name="limite_editais" type="number" min="50" max="5000" value="800">
                <button class="btn btn-primary" type="submit">Processar agora</button>
            </form>
        </section>

        <section class="panel">
            <form method="GET" action="/oportunidades">
                <div class="grid">
                    <div>
                        <label for="termo">Termo</label>
                        <input id="termo" name="termo" type="text" value="<?= htmlspecialchars((string) ($filters['termo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="nivel_relevancia">Nivel</label>
                        <select id="nivel_relevancia" name="nivel_relevancia">
                            <option value="" <?= ((string) ($filters['nivel_relevancia'] ?? '') === '') ? 'selected' : '' ?>>Todos</option>
                            <option value="PRIORITARIA" <?= ((string) ($filters['nivel_relevancia'] ?? '') === 'PRIORITARIA') ? 'selected' : '' ?>>Prioritaria</option>
                            <option value="ALTA" <?= ((string) ($filters['nivel_relevancia'] ?? '') === 'ALTA') ? 'selected' : '' ?>>Alta</option>
                            <option value="MEDIA" <?= ((string) ($filters['nivel_relevancia'] ?? '') === 'MEDIA') ? 'selected' : '' ?>>Media</option>
                            <option value="BAIXA" <?= ((string) ($filters['nivel_relevancia'] ?? '') === 'BAIXA') ? 'selected' : '' ?>>Baixa</option>
                        </select>
                    </div>
                    <div>
                        <label for="perfil_id">Perfil</label>
                        <select id="perfil_id" name="perfil_id">
                            <option value="0">Todos</option>
                            <?php foreach ($perfis as $perfil): ?>
                                <?php $perfilId = (int) $perfil->id; ?>
                                <option
                                    value="<?= $perfilId ?>"
                                    <?= ((int) ($filters['perfil_id'] ?? 0) === $perfilId) ? 'selected' : '' ?>
                                >
                                    <?= htmlspecialchars($perfil->nome, ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="sort">Ordenacao</label>
                        <select id="sort" name="sort">
                            <option value="score_desc" <?= $sort === 'score_desc' ? 'selected' : '' ?>>Score (maior)</option>
                            <option value="score_asc" <?= $sort === 'score_asc' ? 'selected' : '' ?>>Score (menor)</option>
                            <option value="edital_data_desc" <?= $sort === 'edital_data_desc' ? 'selected' : '' ?>>Publicacao (recente)</option>
                            <option value="edital_data_asc" <?= $sort === 'edital_data_asc' ? 'selected' : '' ?>>Publicacao (antiga)</option>
                            <option value="criado_em_asc" <?= $sort === 'criado_em_asc' ? 'selected' : '' ?>>Processamento (antigo)</option>
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
                    <a class="btn" href="/oportunidades">Limpar filtros</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Resultados</h3>
            <p class="muted">Total encontrado: <?= $total ?></p>

            <?php if ($items === []): ?>
                <p>Nenhuma oportunidade encontrada para os filtros informados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Relevancia</th>
                            <th>Edital</th>
                            <th>Orgao</th>
                            <th>Perfil</th>
                            <th>Publicacao</th>
                            <th>Valor</th>
                            <th>Pipeline</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <?php
                            $pipelineStatus = strtoupper((string) ($item->favoritoStatusAcompanhamento ?? ''));
                            if ($pipelineStatus === '') {
                                $pipelineStatus = 'NAO_DECIDIDO';
                            }
                            $redirectTo = (string) ($_SERVER['REQUEST_URI'] ?? '/oportunidades');
                        ?>
                        <tr>
                            <td>#<?= (int) $item->id ?></td>
                            <td>
                                <span class="badge <?= $badgeClass($item->nivelRelevancia) ?>">
                                    <?= htmlspecialchars($item->nivelRelevancia, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="line">Score: <?= number_format((float) $item->score, 2, ',', '.') ?></span>
                            </td>
                            <td>
                                <span class="line">#<?= htmlspecialchars((string) ($item->editalNumero ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="muted">UF: <?= htmlspecialchars((string) ($item->editalUf ?? '-'), ENT_QUOTES, 'UTF-8') ?></span><br>
                                <span class="muted">Modalidade: <?= htmlspecialchars((string) ($item->editalModalidade ?? '-'), ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td><?= htmlspecialchars((string) ($item->editalOrgaoNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item->perfilNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item->editalDataPublicacao ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($item->editalValorEstimado !== null): ?>
                                    R$ <?= number_format($item->editalValorEstimado, 2, ',', '.') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $pipelineBadgeClass($pipelineStatus) ?>">
                                    <?= htmlspecialchars($pipelineStatus, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <form method="POST" action="/oportunidades/<?= (int) $item->id ?>/decidir" class="quick-decision">
                                    <select name="status_acompanhamento">
                                        <option value="EM_ANALISE" <?= $pipelineStatus === 'EM_ANALISE' ? 'selected' : '' ?>>Em analise</option>
                                        <option value="PROPOSTA" <?= $pipelineStatus === 'PROPOSTA' ? 'selected' : '' ?>>Proposta</option>
                                        <option value="FAVORITO" <?= $pipelineStatus === 'FAVORITO' ? 'selected' : '' ?>>Favorito</option>
                                        <option value="DESCARTADO" <?= $pipelineStatus === 'DESCARTADO' ? 'selected' : '' ?>>Descartado</option>
                                        <option value="ENCERRADO" <?= $pipelineStatus === 'ENCERRADO' ? 'selected' : '' ?>>Encerrado</option>
                                    </select>
                                    <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($redirectTo, ENT_QUOTES, 'UTF-8') ?>">
                                    <button class="btn" type="submit">Aplicar</button>
                                </form>
                                <?php if (($item->favoritoId ?? null) !== null && (int) $item->favoritoId > 0): ?>
                                    <div style="margin-top: 6px;">
                                        <a class="btn" href="/favoritos/<?= (int) $item->favoritoId ?>">Abrir</a>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><a class="btn" href="/oportunidades/<?= (int) $item->id ?>">Detalhes</a></td>
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

