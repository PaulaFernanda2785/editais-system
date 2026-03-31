<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$errors = isset($errors) && is_array($errors) ? $errors : [];
$old = isset($old) && is_array($old) ? $old : [];
$message = isset($message) ? (string) $message : null;
$email = htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f7fb; }
        .card { max-width: 420px; background: #fff; border: 1px solid #dfe6f0; padding: 20px; border-radius: 10px; }
        .field { margin-bottom: 12px; }
        input { width: 100%; height: 40px; padding: 8px; }
        .alert, .error { color: #b91c1c; font-size: 14px; margin: 6px 0; }
        button { width: 100%; height: 40px; background: #00509d; border: 0; color: #fff; cursor: pointer; }
    </style>
</head>
<body>
    <main class="card">
        <h1>Acessar painel</h1>
        <p><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form action="/login" method="POST" novalidate>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" name="email" type="email" value="<?= $email ?>" autocomplete="email" required>
                <?php if (isset($errors['email'])): ?>
                    <p class="error"><?= htmlspecialchars((string) $errors['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="senha">Senha</label>
                <input id="senha" name="senha" type="password" autocomplete="current-password" required>
                <?php if (isset($errors['senha'])): ?>
                    <p class="error"><?= htmlspecialchars((string) $errors['senha'], ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
            </div>

            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
