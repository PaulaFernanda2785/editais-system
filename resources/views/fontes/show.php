<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$fonte = isset($fonte) ? $fonte : null;
$historico = isset($historico) && is_array($historico) ? $historico : [];
$message = isset($message) ? (string) $message : null;

if ($fonte === null) {
    echo 'Fonte nao encontrada.';
    return;
}

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$statusTexto = $fonte->ativa ? 'ATIVA' : 'INATIVA';
$statusClasse = $fonte->ativa ? 'status-on' : 'status-off';
$configuracaoPretty = $fonte->configuracao !== null
    ? json_encode($fonte->configuracao, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
    : '{}';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fonte | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1120px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; margin-bottom: 12px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .msg { margin-bottom: 10px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; margin-bottom: 12px; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; }
        .status-on { color: #166534; font-weight: 700; }
        .status-off { color: #b45309; font-weight: 700; }
        pre { margin: 0; background: #0f172a; color: #e2e8f0; padding: 10px; overflow: auto; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Detalhes da Fonte: <?= htmlspecialchars($fonte->nome, ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Operador: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/fontes">Voltar</a>
                <a class="btn" href="/fontes/<?= (int) $fonte->id ?>/editar">Editar</a>
                <form method="POST" action="/fontes/<?= (int) $fonte->id ?>/toggle">
                    <button class="btn btn-primary" type="submit"><?= $fonte->ativa ? 'Inativar' : 'Ativar' ?></button>
                </form>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="grid">
            <article class="card">
                <h3>Codigo</h3>
                <p><?= htmlspecialchars($fonte->codigo, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="card">
                <h3>Tipo</h3>
                <p><?= htmlspecialchars($fonte->tipo, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="card">
                <h3>Status</h3>
                <p class="<?= $statusClasse ?>"><?= $statusTexto ?></p>
            </article>
            <article class="card">
                <h3>Periodicidade</h3>
                <p><?= (int) $fonte->periodicidadeMinutos ?> min</p>
            </article>
            <article class="card">
                <h3>URL Base</h3>
                <p><?= htmlspecialchars((string) ($fonte->urlBase ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="card">
                <h3>Ultima Execucao</h3>
                <p><?= htmlspecialchars((string) ($fonte->ultimaExecucaoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
                <p>Status: <?= htmlspecialchars((string) ($fonte->ultimaExecucaoStatus ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        </section>

        <section class="card" style="margin-bottom: 12px;">
            <h3>Configuracao JSON</h3>
            <pre><?= htmlspecialchars((string) $configuracaoPretty, ENT_QUOTES, 'UTF-8') ?></pre>
        </section>

        <section class="card">
            <h3>Historico de Execucoes</h3>
            <?php if ($historico === []): ?>
                <p>Nenhuma execucao registrada para esta fonte.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Inicio</th>
                            <th>Fim</th>
                            <th>Status</th>
                            <th>Lidos</th>
                            <th>Inseridos</th>
                            <th>Atualizados</th>
                            <th>Duplicados</th>
                            <th>Erros</th>
                            <th>Resumo</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($historico as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item->iniciadoEm, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string) ($item->finalizadoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($item->status, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= (int) $item->totalLidos ?></td>
                            <td><?= (int) $item->totalInseridos ?></td>
                            <td><?= (int) $item->totalAtualizados ?></td>
                            <td><?= (int) $item->totalDuplicados ?></td>
                            <td><?= (int) $item->totalErros ?></td>
                            <td><?= htmlspecialchars((string) ($item->mensagemResumo ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
