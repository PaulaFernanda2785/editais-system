<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$loggedOut = isset($loggedOut) ? (bool) $loggedOut : false;
$auth = isset($auth) && is_array($auth) ? $auth : [];
$nome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logout | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f7fb; }
        .card { max-width: 520px; background: #fff; border: 1px solid #dfe6f0; padding: 20px; border-radius: 10px; }
        .ok { color: #166534; font-weight: 700; }
        .actions { display: flex; gap: 10px; margin-top: 10px; }
        a, button { text-decoration: none; border: 1px solid #c8d4e2; background: #fff; padding: 10px 14px; color: #111; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
    </style>
</head>
<body>
    <main class="card">
        <?php if ($loggedOut): ?>
            <h1>Sessao encerrada</h1>
            <div class="ok">Logout realizado com sucesso.</div>
            <p>Voce saiu do painel do <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?>.</p>
            <div class="actions">
                <a class="btn-primary" href="/login">Fazer login novamente</a>
            </div>
        <?php else: ?>
            <h1>Confirmar logout</h1>
            <p>Usuario atual: <strong><?= $nome ?></strong></p>
            <p>Ao confirmar, sua sessao sera encerrada imediatamente.</p>
            <form method="POST" action="/logout">
                <div class="actions">
                    <button class="btn-primary" type="submit">Confirmar logout</button>
                    <a class="btn-light" href="/dashboard">Cancelar</a>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
