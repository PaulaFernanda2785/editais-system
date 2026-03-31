<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$execucao = isset($execucao) ? $execucao : null;
$message = isset($message) ? (string) $message : null;

if ($execucao === null) {
    echo 'Execucao nao encontrada.';
    return;
}

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$status = strtoupper((string) $execucao->status);
$statusClass = 'status-warn';
if ($status === 'SUCESSO') {
    $statusClass = 'status-ok';
} elseif ($status === 'ERRO') {
    $statusClass = 'status-err';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Execucao #<?= (int) $execucao->id ?> | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 980px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; }
        .status-ok { color: #166534; font-weight: 700; }
        .status-warn { color: #b45309; font-weight: 700; }
        .status-err { color: #991b1b; font-weight: 700; }
        pre { background: #0f172a; color: #e2e8f0; border-radius: 6px; padding: 12px; overflow: auto; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Execucao de Coleta #<?= (int) $execucao->id ?></h1>
                <p>Operador: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/admin/coletas">Voltar</a>
                <a class="btn" href="/dashboard">Dashboard</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="card grid">
            <article>
                <h3>Fonte</h3>
                <p><?= htmlspecialchars((string) ($execucao->fonteCodigo ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                <p><?= htmlspecialchars((string) ($execucao->fonteNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Status</h3>
                <p class="<?= $statusClass ?>"><?= htmlspecialchars($status, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Inicio</h3>
                <p><?= htmlspecialchars($execucao->iniciadoEm, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Fim</h3>
                <p><?= htmlspecialchars((string) ($execucao->finalizadoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        </section>

        <section class="card grid">
            <article>
                <h3>Total lidos</h3>
                <p><?= (int) $execucao->totalLidos ?></p>
            </article>
            <article>
                <h3>Inseridos</h3>
                <p><?= (int) $execucao->totalInseridos ?></p>
            </article>
            <article>
                <h3>Atualizados</h3>
                <p><?= (int) $execucao->totalAtualizados ?></p>
            </article>
            <article>
                <h3>Duplicados</h3>
                <p><?= (int) $execucao->totalDuplicados ?></p>
            </article>
            <article>
                <h3>Erros</h3>
                <p><?= (int) $execucao->totalErros ?></p>
            </article>
        </section>

        <section class="card">
            <h3>Resumo</h3>
            <p><?= htmlspecialchars((string) ($execucao->mensagemResumo ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
        </section>

        <section class="card">
            <h3>Log detalhado</h3>
            <pre><?= htmlspecialchars((string) ($execucao->logDetalhado ?? 'Sem log detalhado.'), ENT_QUOTES, 'UTF-8') ?></pre>
        </section>
    </div>
</body>
</html>

