<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$assinatura = isset($assinatura) ? $assinatura : null;
$message = isset($message) ? (string) $message : null;

$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');
$planoNome = $assinatura?->plano?->nome ?? 'Nenhum plano';
$status = $assinatura?->status ?? 'SEM_ASSINATURA';
$inicio = $assinatura?->dataInicio ?? '-';
$fim = $assinatura?->dataFim ?? '-';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Assinatura | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f7fb; }
        .container { max-width: 720px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 18px; }
        .msg { background: #fff8e1; border: 1px solid #facc15; padding: 10px; margin-bottom: 12px; }
        .actions { display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; padding: 8px 12px; text-decoration: none; color: #111; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
    </style>
</head>
<body>
    <main class="container">
        <section class="card">
            <h1>Status da Assinatura</h1>
            <p><strong>Empresa:</strong> <?= $empresaNome ?></p>

            <?php if ($message !== null && $message !== ''): ?>
                <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <p><strong>Plano:</strong> <?= htmlspecialchars((string) $planoNome, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars((string) $status, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Inicio:</strong> <?= htmlspecialchars((string) $inicio, ENT_QUOTES, 'UTF-8') ?></p>
            <p><strong>Fim:</strong> <?= htmlspecialchars((string) $fim, ENT_QUOTES, 'UTF-8') ?></p>

            <div class="actions">
                <?php if ($assinatura === null || !in_array($status, ['ATIVA', 'TESTE'], true)): ?>
                    <form method="POST" action="/assinatura/ativar-teste">
                        <button class="btn btn-primary" type="submit">Ativar periodo de teste</button>
                    </form>
                <?php endif; ?>

                <a class="btn" href="/dashboard">Voltar ao painel</a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </section>
    </main>
</body>
</html>
