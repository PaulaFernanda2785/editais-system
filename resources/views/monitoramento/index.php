<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$perfis = isset($perfis) && is_array($perfis) ? $perfis : [];
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfis de Monitoramento | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1120px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .msg { margin: 12px 0; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #dfe6f0; }
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
                <h1>Perfis de Monitoramento</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn btn-primary" href="/monitoramento/novo">Novo perfil</a>
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($perfis === []): ?>
            <div class="empty">
                Nenhum perfil cadastrado ainda. Clique em <strong>Novo perfil</strong> para iniciar seu monitoramento inteligente.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Frequencia</th>
                        <th>Faixa de Valor</th>
                        <th>Palavras-chave</th>
                        <th>Status</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($perfis as $perfil): ?>
                    <?php
                        $status = $perfil->ativo ? 'ATIVO' : 'INATIVO';
                        $classeStatus = $perfil->ativo ? 'status-on' : 'status-off';
                        $faixaMin = $perfil->faixaValorMin !== null ? number_format($perfil->faixaValorMin, 2, ',', '.') : '-';
                        $faixaMax = $perfil->faixaValorMax !== null ? number_format($perfil->faixaValorMax, 2, ',', '.') : '-';
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($perfil->nome, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($perfil->frequenciaAlerta, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $faixaMin ?> ate <?= $faixaMax ?></td>
                        <td><?= (int) $perfil->totalPalavras ?></td>
                        <td><span class="<?= $classeStatus ?>"><?= $status ?></span></td>
                        <td>
                            <div class="row-actions">
                                <a class="btn" href="/monitoramento/<?= (int) $perfil->id ?>/editar">Editar</a>

                                <form class="inline" method="POST" action="/monitoramento/<?= (int) $perfil->id ?>/toggle">
                                    <button class="btn" type="submit">
                                        <?= $perfil->ativo ? 'Inativar' : 'Ativar' ?>
                                    </button>
                                </form>

                                <form class="inline" method="POST" action="/monitoramento/<?= (int) $perfil->id ?>/delete">
                                    <button class="btn" type="submit" onclick="return confirm('Deseja excluir este perfil? Esta acao remove palavras-chave associadas.');">
                                        Excluir
                                    </button>
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
