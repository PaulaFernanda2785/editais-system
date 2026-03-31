<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$relatorio = isset($relatorio) && is_array($relatorio) ? $relatorio : [];
$alertasPrazo = isset($alertasPrazo) && is_array($alertasPrazo) ? $alertasPrazo : [];
$message = isset($message) ? (string) $message : null;

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$periodo = isset($relatorio['periodo']) && is_array($relatorio['periodo']) ? $relatorio['periodo'] : [];
$totais = isset($relatorio['totais']) && is_array($relatorio['totais']) ? $relatorio['totais'] : [];
$taxas = isset($relatorio['taxas']) && is_array($relatorio['taxas']) ? $relatorio['taxas'] : [];
$funil = isset($relatorio['funil']) && is_array($relatorio['funil']) ? $relatorio['funil'] : [];
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Relatorio de Conversao | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1120px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .muted { color: #475569; font-size: 13px; }
        input { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Relatorio de Conversao por Fase</h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/favoritos">Pipeline</a>
                <a class="btn" href="/oportunidades">Oportunidades</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel">
            <h3>Periodo do relatorio</h3>
            <form method="GET" action="/favoritos/relatorio/conversao">
                <div class="grid">
                    <article>
                        <label for="data_de">Data inicial</label>
                        <input id="data_de" name="data_de" type="date" value="<?= htmlspecialchars((string) ($periodo['data_de'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </article>
                    <article>
                        <label for="data_ate">Data final</label>
                        <input id="data_ate" name="data_ate" type="date" value="<?= htmlspecialchars((string) ($periodo['data_ate'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    </article>
                </div>
                <div class="actions" style="margin-top: 10px;">
                    <button class="btn btn-primary" type="submit">Atualizar relatorio</button>
                    <a class="btn" href="/favoritos/relatorio/conversao">Restaurar padrao (90 dias)</a>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Indicadores de conversao</h3>
            <div class="grid">
                <article>
                    <h4>EM_ANALISE</h4>
                    <p><?= (int) ($totais['em_analise'] ?? 0) ?></p>
                </article>
                <article>
                    <h4>PROPOSTA</h4>
                    <p><?= (int) ($totais['proposta'] ?? 0) ?></p>
                </article>
                <article>
                    <h4>ENCERRADO</h4>
                    <p><?= (int) ($totais['encerrado'] ?? 0) ?></p>
                </article>
                <article>
                    <h4>Taxa Analise -> Proposta</h4>
                    <p><?= number_format((float) ($taxas['analise_para_proposta'] ?? 0), 1, ',', '.') ?>%</p>
                </article>
                <article>
                    <h4>Taxa Proposta -> Encerrado</h4>
                    <p><?= number_format((float) ($taxas['proposta_para_encerrado'] ?? 0), 1, ',', '.') ?>%</p>
                </article>
                <article>
                    <h4>Tarefas Vencendo 48h</h4>
                    <p><?= (int) ($alertasPrazo['totais']['vencendo'] ?? 0) ?></p>
                </article>
            </div>
        </section>

        <section class="panel">
            <h3>Funil consolidado</h3>
            <?php if ($funil === []): ?>
                <p class="muted">Sem dados para o periodo selecionado.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Fase</th>
                            <th>Quantidade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($funil as $linha): ?>
                            <tr>
                                <td><?= htmlspecialchars((string) ($linha['fase'] ?? '-'), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= (int) ($linha['quantidade'] ?? 0) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
