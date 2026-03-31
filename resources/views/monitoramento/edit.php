<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$perfil = isset($perfil) ? $perfil : null;
$palavras = isset($palavras) && is_array($palavras) ? $palavras : [];
$message = isset($message) ? (string) $message : null;
$errors = isset($errors) && is_array($errors) ? $errors : [];
$old = isset($old) && is_array($old) ? $old : [];
$palavraErrors = isset($palavraErrors) && is_array($palavraErrors) ? $palavraErrors : [];
$palavraOld = isset($palavraOld) && is_array($palavraOld) ? $palavraOld : [];

if ($perfil === null) {
    echo 'Perfil nao encontrado.';
    return;
}

$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');
$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');

$oldNome = (string) ($old['nome'] ?? $perfil->nome);
$oldUfs = (string) ($old['ufs'] ?? implode(', ', $perfil->ufs));
$oldModalidades = (string) ($old['modalidades'] ?? implode(', ', $perfil->modalidades));
$oldOrgaos = (string) ($old['orgaos'] ?? implode(', ', $perfil->orgaos));
$oldFaixaMin = (string) ($old['faixa_valor_min'] ?? ($perfil->faixaValorMin !== null ? (string) $perfil->faixaValorMin : ''));
$oldFaixaMax = (string) ($old['faixa_valor_max'] ?? ($perfil->faixaValorMax !== null ? (string) $perfil->faixaValorMax : ''));
$oldFreq = strtoupper((string) ($old['frequencia_alerta'] ?? $perfil->frequenciaAlerta));

$palavraOldTermo = (string) ($palavraOld['termo'] ?? '');
$palavraOldPeso = (string) ($palavraOld['peso'] ?? '1');
$palavraOldCategoria = (string) ($palavraOld['categoria'] ?? '');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Perfil | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1120px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 14px; }
        .msg { margin-bottom: 10px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        label { display: block; margin-bottom: 4px; font-weight: 700; }
        input, textarea, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        textarea { min-height: 70px; resize: vertical; }
        .error { color: #991b1b; font-size: 13px; margin-top: 2px; }
        .row-actions { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #e8edf5; padding: 8px; text-align: left; vertical-align: middle; }
        th { background: #f8fafc; }
        .status-on { color: #166534; font-weight: 700; }
        .status-off { color: #b45309; font-weight: 700; }
        .inline-form { display: inline-block; margin: 0 4px 0 0; }
        .wide { grid-column: 1 / -1; }
        @media (max-width: 920px) {
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Editar Perfil de Monitoramento</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/monitoramento">Voltar</a>
                <a class="btn" href="/dashboard">Dashboard</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <div class="grid">
            <section class="card">
                <h2>Dados do Perfil</h2>
                <form method="POST" action="/monitoramento/<?= (int) $perfil->id ?>">
                    <div>
                        <label for="nome">Nome do Perfil</label>
                        <input id="nome" name="nome" type="text" maxlength="120" value="<?= htmlspecialchars($oldNome, ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($errors['nome'])): ?><div class="error"><?= htmlspecialchars((string) $errors['nome'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div style="margin-top:8px;">
                        <label for="frequencia_alerta">Frequencia de Alerta</label>
                        <select id="frequencia_alerta" name="frequencia_alerta">
                            <option value="IMEDIATO" <?= $oldFreq === 'IMEDIATO' ? 'selected' : '' ?>>IMEDIATO</option>
                            <option value="DIARIO" <?= $oldFreq === 'DIARIO' ? 'selected' : '' ?>>DIARIO</option>
                            <option value="SEMANAL" <?= $oldFreq === 'SEMANAL' ? 'selected' : '' ?>>SEMANAL</option>
                        </select>
                        <?php if (isset($errors['frequencia_alerta'])): ?><div class="error"><?= htmlspecialchars((string) $errors['frequencia_alerta'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div class="grid" style="grid-template-columns:1fr 1fr; gap:8px; margin-top:8px;">
                        <div>
                            <label for="faixa_valor_min">Faixa Valor Min</label>
                            <input id="faixa_valor_min" name="faixa_valor_min" type="text" value="<?= htmlspecialchars($oldFaixaMin, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (isset($errors['faixa_valor_min'])): ?><div class="error"><?= htmlspecialchars((string) $errors['faixa_valor_min'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                        </div>
                        <div>
                            <label for="faixa_valor_max">Faixa Valor Max</label>
                            <input id="faixa_valor_max" name="faixa_valor_max" type="text" value="<?= htmlspecialchars($oldFaixaMax, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (isset($errors['faixa_valor_max'])): ?><div class="error"><?= htmlspecialchars((string) $errors['faixa_valor_max'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-top:8px;">
                        <label for="ufs">UFs (separe por virgula)</label>
                        <textarea id="ufs" name="ufs"><?= htmlspecialchars($oldUfs, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if (isset($errors['ufs'])): ?><div class="error"><?= htmlspecialchars((string) $errors['ufs'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div style="margin-top:8px;">
                        <label for="modalidades">Modalidades (separe por virgula)</label>
                        <textarea id="modalidades" name="modalidades"><?= htmlspecialchars($oldModalidades, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if (isset($errors['modalidades'])): ?><div class="error"><?= htmlspecialchars((string) $errors['modalidades'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div style="margin-top:8px;">
                        <label for="orgaos">Orgaos de Interesse (separe por virgula)</label>
                        <textarea id="orgaos" name="orgaos"><?= htmlspecialchars($oldOrgaos, ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if (isset($errors['orgaos'])): ?><div class="error"><?= htmlspecialchars((string) $errors['orgaos'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div class="row-actions">
                        <button class="btn btn-primary" type="submit">Salvar Perfil</button>
                    </div>
                </form>

                <div class="row-actions">
                    <form class="inline-form" method="POST" action="/monitoramento/<?= (int) $perfil->id ?>/toggle">
                        <button class="btn" type="submit"><?= $perfil->ativo ? 'Inativar Perfil' : 'Ativar Perfil' ?></button>
                    </form>
                    <form class="inline-form" method="POST" action="/monitoramento/<?= (int) $perfil->id ?>/delete">
                        <button class="btn" type="submit" onclick="return confirm('Confirmar exclusao do perfil?');">Excluir Perfil</button>
                    </form>
                </div>
            </section>

            <section class="card">
                <h2>Palavras-chave do Perfil</h2>
                <form method="POST" action="/monitoramento/<?= (int) $perfil->id ?>/palavras">
                    <div>
                        <label for="termo">Termo</label>
                        <input id="termo" name="termo" type="text" maxlength="150" value="<?= htmlspecialchars($palavraOldTermo, ENT_QUOTES, 'UTF-8') ?>" placeholder="Ex: servicos de limpeza">
                        <?php if (isset($palavraErrors['termo'])): ?><div class="error"><?= htmlspecialchars((string) $palavraErrors['termo'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                    </div>

                    <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 8px; margin-top:8px;">
                        <div>
                            <label for="peso">Peso</label>
                            <input id="peso" name="peso" type="number" min="1" step="1" value="<?= htmlspecialchars($palavraOldPeso, ENT_QUOTES, 'UTF-8') ?>">
                            <?php if (isset($palavraErrors['peso'])): ?><div class="error"><?= htmlspecialchars((string) $palavraErrors['peso'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                        </div>
                        <div>
                            <label for="categoria">Categoria</label>
                            <input id="categoria" name="categoria" type="text" maxlength="80" value="<?= htmlspecialchars($palavraOldCategoria, ENT_QUOTES, 'UTF-8') ?>" placeholder="Opcional">
                            <?php if (isset($palavraErrors['categoria'])): ?><div class="error"><?= htmlspecialchars((string) $palavraErrors['categoria'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                        </div>
                    </div>

                    <div class="row-actions">
                        <button class="btn btn-primary" type="submit">Adicionar Palavra-chave</button>
                    </div>
                </form>

                <?php if ($palavras === []): ?>
                    <p style="margin-top:12px;">Nenhuma palavra-chave cadastrada neste perfil.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Termo</th>
                                <th>Peso</th>
                                <th>Categoria</th>
                                <th>Status</th>
                                <th>Acoes</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($palavras as $palavra): ?>
                            <tr>
                                <td><?= htmlspecialchars($palavra->termo, ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) $palavra->peso ?></td>
                                <td><?= htmlspecialchars((string) ($palavra->categoria ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($palavra->ativo): ?>
                                        <span class="status-on">ATIVA</span>
                                    <?php else: ?>
                                        <span class="status-off">INATIVA</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form class="inline-form" method="POST" action="/monitoramento/<?= (int) $perfil->id ?>/palavras/<?= (int) $palavra->id ?>/toggle">
                                        <button class="btn" type="submit"><?= $palavra->ativo ? 'Inativar' : 'Ativar' ?></button>
                                    </form>
                                    <form class="inline-form" method="POST" action="/monitoramento/<?= (int) $perfil->id ?>/palavras/<?= (int) $palavra->id ?>/delete">
                                        <button class="btn" type="submit" onclick="return confirm('Excluir esta palavra-chave?');">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>
