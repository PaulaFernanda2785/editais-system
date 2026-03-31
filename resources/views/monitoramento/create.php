<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$errors = isset($errors) && is_array($errors) ? $errors : [];
$old = isset($old) && is_array($old) ? $old : [];
$message = isset($message) ? (string) $message : null;

$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');
$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');

$oldNome = (string) ($old['nome'] ?? '');
$oldUfs = (string) ($old['ufs'] ?? '');
$oldModalidades = (string) ($old['modalidades'] ?? '');
$oldOrgaos = (string) ($old['orgaos'] ?? '');
$oldFaixaMin = (string) ($old['faixa_valor_min'] ?? '');
$oldFaixaMax = (string) ($old['faixa_valor_max'] ?? '');
$oldFreq = strtoupper((string) ($old['frequencia_alerta'] ?? 'DIARIO'));
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Novo Perfil | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 860px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 16px; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 12px; }
        .actions { display: flex; gap: 8px; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .msg { margin-bottom: 10px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 10px; }
        label { display: block; margin-bottom: 4px; font-weight: 700; }
        input, textarea, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        textarea { min-height: 78px; resize: vertical; }
        .error { color: #991b1b; font-size: 13px; margin-top: 2px; }
        .footer-actions { display: flex; gap: 8px; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="topbar">
            <div>
                <h1>Novo Perfil de Monitoramento</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/monitoramento">Voltar</a>
                <a class="btn" href="/dashboard">Dashboard</a>
            </div>
        </div>

        <section class="card">
            <?php if ($message !== null && $message !== ''): ?>
                <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

            <form method="POST" action="/monitoramento">
                <div class="grid">
                    <div>
                        <label for="nome">Nome do Perfil</label>
                        <input id="nome" name="nome" type="text" maxlength="120" value="<?= htmlspecialchars($oldNome, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['nome'])): ?><div class="error"><?= htmlspecialchars((string) $errors['nome'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div>
                        <label for="frequencia_alerta">Frequencia de Alerta</label>
                        <select id="frequencia_alerta" name="frequencia_alerta">
                            <option value="IMEDIATO" <?= $oldFreq === 'IMEDIATO' ? 'selected' : '' ?>>IMEDIATO</option>
                            <option value="DIARIO" <?= $oldFreq === 'DIARIO' ? 'selected' : '' ?>>DIARIO</option>
                            <option value="SEMANAL" <?= $oldFreq === 'SEMANAL' ? 'selected' : '' ?>>SEMANAL</option>
                        </select>
                        <?php if (isset($errors['frequencia_alerta'])): ?><div class="error"><?= htmlspecialchars((string) $errors['frequencia_alerta'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div>
                        <label for="faixa_valor_min">Faixa Valor Min</label>
                        <input id="faixa_valor_min" name="faixa_valor_min" type="text" value="<?= htmlspecialchars($oldFaixaMin, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: 1000.00">
                        <?php if (isset($errors['faixa_valor_min'])): ?><div class="error"><?= htmlspecialchars((string) $errors['faixa_valor_min'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div>
                        <label for="faixa_valor_max">Faixa Valor Max</label>
                        <input id="faixa_valor_max" name="faixa_valor_max" type="text" value="<?= htmlspecialchars($oldFaixaMax, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: 100000.00">
                        <?php if (isset($errors['faixa_valor_max'])): ?><div class="error"><?= htmlspecialchars((string) $errors['faixa_valor_max'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>
                </div>

                <div style="margin-top:10px;">
                    <label for="ufs">UFs (separe por virgula)</label>
                    <textarea id="ufs" name="ufs" placeholder="CE, SP, RJ"><?= htmlspecialchars($oldUfs, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($errors['ufs'])): ?><div class="error"><?= htmlspecialchars((string) $errors['ufs'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div style="margin-top:10px;">
                    <label for="modalidades">Modalidades (separe por virgula)</label>
                    <textarea id="modalidades" name="modalidades" placeholder="Pregao Eletronico, Concorrencia"><?= htmlspecialchars($oldModalidades, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($errors['modalidades'])): ?><div class="error"><?= htmlspecialchars((string) $errors['modalidades'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div style="margin-top:10px;">
                    <label for="orgaos">Orgaos de Interesse (separe por virgula)</label>
                    <textarea id="orgaos" name="orgaos" placeholder="Prefeitura de Fortaleza, Governo do Estado"><?= htmlspecialchars($oldOrgaos, ENT_QUOTES, 'UTF-8') ?></textarea>
                    <?php if (isset($errors['orgaos'])): ?><div class="error"><?= htmlspecialchars((string) $errors['orgaos'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                </div>

                <div class="footer-actions">
                    <button class="btn btn-primary" type="submit">Criar Perfil</button>
                    <a class="btn" href="/monitoramento">Cancelar</a>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
