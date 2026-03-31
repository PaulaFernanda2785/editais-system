<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$fontes = isset($fontes) && is_array($fontes) ? $fontes : [];
$execucoes = isset($execucoes) && is_array($execucoes) ? $execucoes : [];
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$perfil = htmlspecialchars((string) ($auth['perfil'] ?? '-'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coletas | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1180px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 10px; }
        .status-ok { color: #166534; font-weight: 700; }
        .status-warn { color: #b45309; font-weight: 700; }
        .status-err { color: #991b1b; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #dfe6f0; margin-top: 10px; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .inline { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
        .input { padding: 8px; border: 1px solid #cfd8e3; width: 96px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Painel de Coletas</h1>
                <p>Operador: <strong><?= $usuarioNome ?></strong> (<?= $perfil ?>)</p>
            </div>
            <div class="actions">
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/fontes">Fontes</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel">
            <h3>Disparo Manual</h3>
            <div class="grid">
                <form method="POST" action="/admin/coletas/pncp" class="inline">
                    <strong>PNCP</strong>
                    <input class="input" type="number" name="limite" min="1" max="500" value="50">
                    <button class="btn btn-primary" type="submit">Executar</button>
                </form>

                <form method="POST" action="/admin/coletas/comprasgov" class="inline">
                    <strong>Compras.gov</strong>
                    <input class="input" type="number" name="limite" min="1" max="500" value="40">
                    <button class="btn" type="submit">Executar</button>
                </form>

                <form method="POST" action="/admin/coletas/licitacoese" class="inline">
                    <strong>Licitacoes-e</strong>
                    <input class="input" type="number" name="limite" min="1" max="500" value="40">
                    <button class="btn" type="submit">Executar</button>
                </form>
            </div>
        </section>

        <section class="panel">
            <h3>Resumo por Fonte</h3>
            <?php if ($fontes === []): ?>
                <p>Nenhuma fonte cadastrada.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fonte</th>
                            <th>Codigo</th>
                            <th>Status</th>
                            <th>Execucoes</th>
                            <th>Ultima execucao</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($fontes as $fonte): ?>
                        <tr>
                            <td><?= htmlspecialchars($fonte->nome, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($fonte->codigo, ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($fonte->ativa): ?>
                                    <span class="status-ok">ATIVA</span>
                                <?php else: ?>
                                    <span class="status-warn">INATIVA</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                Total: <?= (int) $fonte->totalExecucoes ?><br>
                                Sucesso: <?= (int) $fonte->totalSucesso ?><br>
                                Falhas: <?= (int) $fonte->totalFalhas ?>
                            </td>
                            <td>
                                Data: <?= htmlspecialchars((string) ($fonte->ultimaExecucaoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?><br>
                                Status: <?= htmlspecialchars((string) ($fonte->ultimaExecucaoStatus ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Historico de Execucoes</h3>
            <?php if ($execucoes === []): ?>
                <p>Nenhuma execucao encontrada.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fonte</th>
                            <th>Status</th>
                            <th>Inicio</th>
                            <th>Fim</th>
                            <th>Lidos</th>
                            <th>Inseridos</th>
                            <th>Atualizados</th>
                            <th>Duplicados</th>
                            <th>Erros</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($execucoes as $execucao): ?>
                        <?php
                            $status = strtoupper((string) $execucao->status);
                            $class = 'status-warn';
                            if ($status === 'SUCESSO') {
                                $class = 'status-ok';
                            } elseif ($status === 'ERRO') {
                                $class = 'status-err';
                            }
                        ?>
                        <tr>
                            <td>#<?= (int) $execucao->id ?></td>
                            <td>
                                <?= htmlspecialchars((string) ($execucao->fonteCodigo ?? '-'), ENT_QUOTES, 'UTF-8') ?><br>
                                <?= htmlspecialchars((string) ($execucao->fonteNome ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td><span class="<?= $class ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($execucao->iniciadoEm, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($execucao->finalizadoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $execucao->totalLidos ?></td>
                            <td><?= (int) $execucao->totalInseridos ?></td>
                            <td><?= (int) $execucao->totalAtualizados ?></td>
                            <td><?= (int) $execucao->totalDuplicados ?></td>
                            <td><?= (int) $execucao->totalErros ?></td>
                            <td><a class="btn" href="/admin/coletas/<?= (int) $execucao->id ?>">Detalhes</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>

