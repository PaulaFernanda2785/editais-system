<?php

declare(strict_types=1);

$path = isset($path) ? (string) $path : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f7fb; }
        .card { max-width: 560px; background: #fff; border: 1px solid #d7e0ec; padding: 20px; border-radius: 8px; }
    </style>
</head>
<body>
    <main class="card">
        <h1>404 - Rota nao encontrada</h1>
        <p>A rota <code><?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?></code> nao existe.</p>
        <a href="/">Voltar ao inicio</a>
    </main>
</body>
</html>
