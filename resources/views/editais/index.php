<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$items = isset($items) && is_array($items) ? $items : [];
$filters = isset($filters) && is_array($filters) ? $filters : [];
$sort = isset($sort) ? (string) $sort : 'data_publicacao_desc';
$total = isset($total) ? (int) $total : 0;
$page = isset($page) ? (int) $page : 1;
$perPage = isset($perPage) ? (int) $perPage : 20;
$totalPages = isset($totalPages) ? (int) $totalPages : 1;
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$queryBase = [
    'termo' => (string) ($filters['termo'] ?? ''),
    'uf' => (string) ($filters['uf'] ?? ''),
    'orgao_nome' => (string) ($filters['orgao_nome'] ?? ''),
    'modalidade' => (string) ($filters['modalidade'] ?? ''),
    'fonte_id' => (string) ($filters['fonte_id'] ?? ''),
    'data_publicacao_de' => (string) ($filters['data_publicacao_de'] ?? ''),
    'data_publicacao_ate' => (string) ($filters['data_publicacao_ate'] ?? ''),
    'valor_min' => (string) ($filters['valor_min'] ?? ''),
    'valor_max' => (string) ($filters['valor_max'] ?? ''),
    'sort' => $sort,
    'per_page' => $perPage,
];

$buildPageUrl = static function (int $targetPage, array $base): string {
    $params = $base;
    $params['page'] = $targetPage;
    return '/editais?' . http_build_query($params);
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Catalogo de Editais | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1200px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 8px; }
        input, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .pagination { display: flex; gap: 6px; flex-wrap: wrap; margin-top: 12px; }
        .muted { color: #475569; font-size: 13px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Catalogo de Editais</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/monitoramento">Perfis</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel">
            <form method="GET" action="/editais">
                <div class="grid">
                    <div>
                        <label for="termo">Termo</label>
                        <input id="termo" name="termo" type="text" value="<?= htmlspecialchars((string) ($filters['termo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="uf">UF</label>
                        <input id="uf" name="uf" type="text" maxlength="2" value="<?= htmlspecialchars((string) ($filters['uf'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="orgao_nome">Orgao</label>
                        <input id="orgao_nome" name="orgao_nome" type="text" value="<?= htmlspecialchars((string) ($filters['orgao_nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="modalidade">Modalidade</label>
                        <input id="modalidade" name="modalidade" type="text" value="<?= htmlspecialchars((string) ($filters['modalidade'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="data_publicacao_de">Publicacao de</label>
                        <input id="data_publicacao_de" name="data_publicacao_de" type="date" value="<?= htmlspecialchars((string) ($filters['data_publicacao_de'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="data_publicacao_ate">Publicacao ate</label>
                        <input id="data_publicacao_ate" name="data_publicacao_ate" type="date" value="<?= htmlspecialchars((string) ($filters['data_publicacao_ate'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="valor_min">Valor minimo</label>
                        <input id="valor_min" name="valor_min" type="number" step="0.01" value="<?= htmlspecialchars((string) ($filters['valor_min'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="valor_max">Valor maximo</label>
                        <input id="valor_max" name="valor_max" type="number" step="0.01" value="<?= htmlspecialchars((string) ($filters['valor_max'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div>
                        <label for="sort">Ordenacao</label>
                        <select id="sort" name="sort">
                            <option value="data_publicacao_desc" <?= $sort === 'data_publicacao_desc' ? 'selected' : '' ?>>Publicacao (recente)</option>
                            <option value="data_publicacao_asc" <?= $sort === 'data_publicacao_asc' ? 'selected' : '' ?>>Publicacao (antiga)</option>
                            <option value="data_abertura_desc" <?= $sort === 'data_abertura_desc' ? 'selected' : '' ?>>Abertura (recente)</option>
                            <option value="valor_desc" <?= $sort === 'valor_desc' ? 'selected' : '' ?>>Valor (maior)</option>
                            <option value="valor_asc" <?= $sort === 'valor_asc' ? 'selected' : '' ?>>Valor (menor)</option>
                            <option value="relevancia_desc" <?= $sort === 'relevancia_desc' ? 'selected' : '' ?>>Relevancia</option>
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
                    <a class="btn" href="/editais">Limpar filtros</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Resultados</h3>
            <p class="muted">Total encontrado: <?= $total ?></p>

            <?php if ($items === []): ?>
                <p>Nenhum edital encontrado para os filtros informados.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Numero</th>
                            <th>Orgao</th>
                            <th>UF</th>
                            <th>Modalidade</th>
                            <th>Publicacao</th>
                            <th>Valor</th>
                            <th>Fonte</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td>#<?= (int) $item->id ?></td>
                            <td><?= htmlspecialchars((string) ($item->numeroEdital ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($item->orgaoNome, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item->uf ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item->modalidade ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item->dataPublicacao ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($item->valorEstimado !== null): ?>
                                    R$ <?= number_format($item->valorEstimado, 2, ',', '.') ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars((string) ($item->fonteCodigo ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><a class="btn" href="/editais/<?= (int) $item->id ?>">Detalhes</a></td>
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

