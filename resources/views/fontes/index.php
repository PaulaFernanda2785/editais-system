<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$fontes = isset($fontes) && is_array($fontes) ? $fontes : [];
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$perfil = htmlspecialchars((string) ($auth['perfil'] ?? '-'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fontes de Coleta | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1160px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .msg { margin: 12px 0; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #dfe6f0; margin-top: 12px; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .status-on { color: #166534; font-weight: 700; }
        .status-off { color: #b45309; font-weight: 700; }
        .row-actions { display: flex; gap: 6px; flex-wrap: wrap; }
        .inline { display: inline-block; margin: 0; }
        .empty { margin-top: 14px; padding: 12px; border: 1px dashed #c8d4e2; background: #fff; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Fontes de Coleta</h1>
                <p>Operador: <strong><?= $usuarioNome ?></strong> (<?= $perfil ?>)</p>
            </div>
            <div class="actions">
                <a class="btn btn-primary" href="/fontes/novo">Nova fonte</a>
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($fontes === []): ?>
            <div class="empty">Nenhuma fonte cadastrada.</div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Codigo</th>
                        <th>Tipo</th>
                        <th>Periodicidade</th>
                        <th>Status</th>
                        <th>Execucoes</th>
                        <th>Ultima Execucao</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($fontes as $fonte): ?>
                    <tr>
                        <td><?= htmlspecialchars($fonte->nome, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fonte->codigo, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($fonte->tipo, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= (int) $fonte->periodicidadeMinutos ?> min</td>
                        <td>
                            <?php if ($fonte->ativa): ?>
                                <span class="status-on">ATIVA</span>
                            <?php else: ?>
                                <span class="status-off">INATIVA</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            Total: <?= (int) $fonte->totalExecucoes ?><br>
                            Sucesso: <?= (int) $fonte->totalSucesso ?><br>
                            Falhas: <?= (int) $fonte->totalFalhas ?>
                        </td>
                        <td>
                            Status: <?= htmlspecialchars((string) ($fonte->ultimaExecucaoStatus ?? '-'), ENT_QUOTES, 'UTF-8') ?><br>
                            Data: <?= htmlspecialchars((string) ($fonte->ultimaExecucaoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                        </td>
                        <td>
                            <div class="row-actions">
                                <a class="btn" href="/fontes/<?= (int) $fonte->id ?>">Detalhes</a>
                                <a class="btn" href="/fontes/<?= (int) $fonte->id ?>/editar">Editar</a>
                                <form class="inline" method="POST" action="/fontes/<?= (int) $fonte->id ?>/toggle">
                                    <button class="btn" type="submit"><?= $fonte->ativa ? 'Inativar' : 'Ativar' ?></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
