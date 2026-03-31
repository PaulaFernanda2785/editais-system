<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$assinatura = isset($assinatura) ? $assinatura : null;
$adminMessage = isset($adminMessage) ? (string) $adminMessage : null;

$nome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string) ($auth['email'] ?? '-'), ENT_QUOTES, 'UTF-8');
$perfil = htmlspecialchars((string) ($auth['perfil'] ?? '-'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? '-'), ENT_QUOTES, 'UTF-8');
$empresaCnpj = htmlspecialchars((string) ($tenant['cnpj'] ?? '-'), ENT_QUOTES, 'UTF-8');

$planoNome = $assinatura?->plano?->nome ?? '-';
$statusAssinatura = $assinatura?->status ?? 'SEM_ASSINATURA';
$fimAssinatura = $assinatura?->dataFim ?? '-';
$perfilRaw = (string) ($auth['perfil'] ?? '');
$isAdmin = in_array(strtoupper($perfilRaw), ['SUPER_ADMIN', 'ADMIN'], true);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f7fb; }
        .container { max-width: 980px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 10px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { text-decoration: none; border: 1px solid #c8d4e2; background: #fff; padding: 8px 12px; color: #111; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; }
        .status-ok { color: #166534; font-weight: 700; }
        .status-warn { color: #b45309; font-weight: 700; }
        .msg { margin: 10px 0 14px; border: 1px solid #f59e0b; background: #fffbeb; padding: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Painel do <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Bem-vindo(a), <?= $nome ?>.</p>
            </div>
            <div class="actions">
                <a class="btn" href="/editais">Catalogo de editais</a>
                <a class="btn" href="/monitoramento">Perfis de monitoramento</a>
                <a class="btn" href="/oportunidades">Oportunidades</a>
                <?php if ($isAdmin): ?>
                    <a class="btn" href="/fontes">Fontes de coleta</a>
                    <a class="btn" href="/admin/coletas">Coletas</a>
                <?php endif; ?>
                <a class="btn" href="/assinatura/status">Status da assinatura</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($adminMessage !== null && $adminMessage !== ''): ?>
            <div class="msg"><?= htmlspecialchars($adminMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="grid">
            <article class="card">
                <h3>Empresa</h3>
                <p><?= $empresaNome ?></p>
            </article>
            <article class="card">
                <h3>CNPJ</h3>
                <p><?= $empresaCnpj ?></p>
            </article>
            <article class="card">
                <h3>Usuario</h3>
                <p><?= $nome ?></p>
                <p><?= $email ?></p>
            </article>
            <article class="card">
                <h3>Perfil</h3>
                <p><?= $perfil ?></p>
            </article>
            <article class="card">
                <h3>Plano atual</h3>
                <p><?= htmlspecialchars((string) $planoNome, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="card">
                <h3>Status da assinatura</h3>
                <?php if (in_array($statusAssinatura, ['ATIVA', 'TESTE'], true)): ?>
                    <p class="status-ok"><?= htmlspecialchars($statusAssinatura, ENT_QUOTES, 'UTF-8') ?></p>
                <?php else: ?>
                    <p class="status-warn"><?= htmlspecialchars($statusAssinatura, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <p>Validade: <?= htmlspecialchars((string) $fimAssinatura, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        </section>
    </div>
</body>
</html>
