<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$errors = isset($errors) && is_array($errors) ? $errors : [];
$old = isset($old) && is_array($old) ? $old : [];
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$oldTipo = strtoupper((string) ($old['tipo'] ?? 'API'));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nova Fonte | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 920px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 16px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .actions { display: flex; gap: 8px; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .msg { margin-bottom: 10px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; }
        label { display: block; margin-bottom: 4px; font-weight: 700; }
        input, textarea, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        textarea { min-height: 120px; resize: vertical; }
        .error { color: #991b1b; font-size: 13px; margin-top: 2px; }
        .footer-actions { display: flex; gap: 8px; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div>
                <h1>Nova Fonte de Coleta</h1>
                <p>Operador: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/fontes">Voltar</a>
                <a class="btn" href="/dashboard">Dashboard</a>
            </div>
        </div>

        <section class="card">
            <?php if ($message !== null && $message !== ''): ?>
                <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="/fontes">
                <div class="grid">
                    <div>
                        <label for="nome">Nome</label>
                        <input id="nome" name="nome" type="text" maxlength="120" value="<?= htmlspecialchars((string) ($old['nome'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['nome'])): ?><div class="error"><?= htmlspecialchars((string) $errors['nome'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div>
                        <label for="codigo">Codigo</label>
                        <input id="codigo" name="codigo" type="text" maxlength="50" value="<?= htmlspecialchars((string) ($old['codigo'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['codigo'])): ?><div class="error"><?= htmlspecialchars((string) $errors['codigo'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div>
                        <label for="tipo">Tipo</label>
                        <select id="tipo" name="tipo">
                            <option value="API" <?= $oldTipo === 'API' ? 'selected' : '' ?>>API</option>
                            <option value="SCRAPING" <?= $oldTipo === 'SCRAPING' ? 'selected' : '' ?>>SCRAPING</option>
                            <option value="MANUAL" <?= $oldTipo === 'MANUAL' ? 'selected' : '' ?>>MANUAL</option>
                        </select>
                        <?php if (isset($errors['tipo'])): ?><div class="error"><?= htmlspecialchars((string) $errors['tipo'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                    <div>
                        <label for="periodicidade_minutos">Periodicidade (minutos)</label>
                        <input id="periodicidade_minutos" name="periodicidade_minutos" type="number" min="5" max="10080" value="<?= htmlspecialchars((string) ($old['periodicidade_minutos'] ?? '60'), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['periodicidade_minutos'])): ?><div class="error"><?= htmlspecialchars((string) $errors['periodicidade_minutos'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                </div>

                <div style="margin-top: 10px;">
                    <label for="url_base">URL Base</label>
                    <input id="url_base" name="url_base" type="text" maxlength="255" value="<?= htmlspecialchars((string) ($old['url_base'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?php if (isset($errors['url_base'])): ?><div class="error"><?= htmlspecialchars((string) $errors['url_base'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div style="margin-top: 10px;">
                    <label for="configuracao_json">Configuracao JSON (opcional)</label>
                    <textarea id="configuracao_json" name="configuracao_json"><?= htmlspecialchars((string) ($old['configuracao_json'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($errors['configuracao_json'])): ?><div class="error"><?= htmlspecialchars((string) $errors['configuracao_json'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div class="footer-actions">
                    <button class="btn btn-primary" type="submit">Criar Fonte</button>
                    <a class="btn" href="/fontes">Cancelar</a>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
