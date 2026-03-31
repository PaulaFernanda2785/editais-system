<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$edital = isset($edital) ? $edital : null;
$fonte = isset($fonte) ? $fonte : null;
$documentos = isset($documentos) && is_array($documentos) ? $documentos : [];
$message = isset($message) ? (string) $message : null;

if ($edital === null) {
    echo 'Edital nao encontrado.';
    return;
}

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edital #<?= (int) $edital->id ?> | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1080px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        pre { margin: 0; white-space: pre-wrap; word-break: break-word; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Detalhe do Edital #<?= (int) $edital->id ?></h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/editais">Voltar</a>
                <a class="btn" href="/dashboard">Dashboard</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel grid">
            <article>
                <h3>Numero</h3>
                <p><?= htmlspecialchars((string) ($edital->numeroEdital ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Codigo da fonte</h3>
                <p><?= htmlspecialchars((string) ($edital->codigoFonte ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Fonte</h3>
                <p><?= htmlspecialchars((string) ($fonte?->codigo ?? $edital->fonteCodigo ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars((string) ($fonte?->nome ?? $edital->fonteNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Orgao</h3>
                <p><?= htmlspecialchars($edital->orgaoNome, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>UF / Municipio</h3>
                <p><?= htmlspecialchars((string) ($edital->uf ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) ($edital->municipio ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Modalidade</h3>
                <p><?= htmlspecialchars((string) ($edital->modalidade ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Situacao</h3>
                <p><?= htmlspecialchars((string) ($edital->situacao ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Valor estimado</h3>
                <p>
                    <?php if ($edital->valorEstimado !== null): ?>
                        R$ <?= number_format($edital->valorEstimado, 2, ',', '.') ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </article>
            <article>
                <h3>Publicacao</h3>
                <p><?= htmlspecialchars((string) ($edital->dataPublicacao ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Abertura</h3>
                <p><?= htmlspecialchars((string) ($edital->dataAbertura ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Encerramento</h3>
                <p><?= htmlspecialchars((string) ($edital->dataEncerramento ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Score global</h3>
                <p><?= number_format((float) $edital->scoreGlobal, 2, ',', '.') ?></p>
            </article>
        </section>

        <section class="panel">
            <h3>Objeto</h3>
            <pre><?= htmlspecialchars($edital->objeto, ENT_QUOTES, 'UTF-8') ?></pre>
        </section>

        <section class="panel">
            <h3>Descricao resumida</h3>
            <p><?= htmlspecialchars((string) ($edital->descricaoResumida ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="panel">
            <h3>Links</h3>
            <div class="actions">
                <?php if ($edital->linkDetalhe !== null): ?>
                    <a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($edital->linkDetalhe, ENT_QUOTES, 'UTF-8') ?>">Abrir detalhe externo</a>
                <?php endif; ?>
                <?php if ($edital->linkEdital !== null): ?>
                    <a class="btn" target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($edital->linkEdital, ENT_QUOTES, 'UTF-8') ?>">Abrir edital</a>
                <?php endif; ?>
            </div>
        </section>

        <section class="panel">
            <h3>Documentos anexos</h3>
            <?php if ($documentos === []): ?>
                <p>Nenhum documento cadastrado para este edital.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Tipo</th>
                            <th>URL</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($documentos as $documento): ?>
                        <tr>
                            <td><?= htmlspecialchars($documento->nomeDocumento, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($documento->tipoDocumento ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <a class="btn" target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($documento->urlDocumento, ENT_QUOTES, 'UTF-8') ?>">Abrir</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>

