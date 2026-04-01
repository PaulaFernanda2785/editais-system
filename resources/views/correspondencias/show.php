<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$correspondencia = isset($correspondencia) ? $correspondencia : null;
$message = isset($message) ? (string) $message : null;

if ($correspondencia === null) {
    echo 'Oportunidade nao encontrada.';
    return;
}

$usuarioNome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? 'Empresa'), ENT_QUOTES, 'UTF-8');

$motivo = is_array($correspondencia->motivo ?? null) ? $correspondencia->motivo : [];
$palavrasEncontradas = isset($motivo['palavras_encontradas']) && is_array($motivo['palavras_encontradas'])
    ? $motivo['palavras_encontradas']
    : [];
$detalhes = isset($motivo['detalhes']) && is_array($motivo['detalhes'])
    ? $motivo['detalhes']
    : [];
$filtrosStatus = strtoupper((string) ($motivo['filtros'] ?? '-'));

$badgeClass = match (strtoupper($correspondencia->nivelRelevancia)) {
    'PRIORITARIA' => 'badge-prioritaria',
    'ALTA' => 'badge-alta',
    'MEDIA' => 'badge-media',
    default => 'badge-baixa',
};

$pipelineStatus = strtoupper((string) ($correspondencia->favoritoStatusAcompanhamento ?? ''));
if ($pipelineStatus === '') {
    $pipelineStatus = 'NAO_DECIDIDO';
}

$pipelineBadgeClass = match ($pipelineStatus) {
    'EM_ANALISE' => 'badge-pipeline-analise',
    'PROPOSTA' => 'badge-pipeline-proposta',
    'DESCARTADO' => 'badge-pipeline-descartado',
    'ENCERRADO' => 'badge-pipeline-encerrado',
    'FAVORITO' => 'badge-pipeline-favorito',
    default => 'badge-pipeline-nao-definido',
};
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Oportunidade #<?= (int) $correspondencia->id ?> | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 28px; background: #f4f7fb; }
        .container { max-width: 1100px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid #c8d4e2; background: #fff; color: #111; padding: 8px 12px; text-decoration: none; cursor: pointer; }
        .btn-primary { background: #00509d; color: #fff; border-color: #00509d; }
        .panel { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; margin-top: 12px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; border: 1px solid #dfe6f0; }
        th, td { padding: 10px; border-bottom: 1px solid #e8edf5; text-align: left; vertical-align: top; }
        th { background: #f8fafc; }
        .msg { margin-top: 12px; border: 1px solid #93c5fd; background: #eff6ff; padding: 10px; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-prioritaria { background: #fee2e2; color: #991b1b; }
        .badge-alta { background: #ffedd5; color: #9a3412; }
        .badge-media { background: #fef9c3; color: #854d0e; }
        .badge-baixa { background: #dcfce7; color: #166534; }
        .badge-pipeline-favorito { background: #dbeafe; color: #1d4ed8; }
        .badge-pipeline-analise { background: #ffedd5; color: #9a3412; }
        .badge-pipeline-proposta { background: #dcfce7; color: #166534; }
        .badge-pipeline-descartado { background: #fee2e2; color: #991b1b; }
        .badge-pipeline-encerrado { background: #e5e7eb; color: #374151; }
        .badge-pipeline-nao-definido { background: #f1f5f9; color: #475569; }
        textarea, select { width: 100%; box-sizing: border-box; padding: 8px; border: 1px solid #cfd8e3; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Detalhe da Oportunidade #<?= (int) $correspondencia->id ?></h1>
                <p>Empresa: <strong><?= $empresaNome ?></strong> | Usuario: <strong><?= $usuarioNome ?></strong></p>
            </div>
            <div class="actions">
                <a class="btn" href="/oportunidades">Voltar</a>
                <a class="btn" href="/dashboard">Dashboard</a>
                <a class="btn" href="/propostas">Propostas</a>
            </div>
        </header>

        <?php if ($message !== null && $message !== ''): ?>
            <div class="msg"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="panel grid">
            <article>
                <h3>Relevancia</h3>
                <p><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($correspondencia->nivelRelevancia, ENT_QUOTES, 'UTF-8') ?></span></p>
            </article>
            <article>
                <h3>Score</h3>
                <p><?= number_format((float) $correspondencia->score, 2, ',', '.') ?></p>
            </article>
            <article>
                <h3>Perfil de monitoramento</h3>
                <p><?= htmlspecialchars((string) ($correspondencia->perfilNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Status dos filtros</h3>
                <p><?= htmlspecialchars($filtrosStatus, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Edital</h3>
                <p>#<?= htmlspecialchars((string) ($correspondencia->editalNumero ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Orgao</h3>
                <p><?= htmlspecialchars((string) ($correspondencia->editalOrgaoNome ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>UF / Modalidade</h3>
                <p><?= htmlspecialchars((string) ($correspondencia->editalUf ?? '-'), ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars((string) ($correspondencia->editalModalidade ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Publicacao</h3>
                <p><?= htmlspecialchars((string) ($correspondencia->editalDataPublicacao ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article>
                <h3>Valor estimado</h3>
                <p>
                    <?php if ($correspondencia->editalValorEstimado !== null): ?>
                        R$ <?= number_format($correspondencia->editalValorEstimado, 2, ',', '.') ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </p>
            </article>
            <article>
                <h3>Processado em</h3>
                <p><?= htmlspecialchars((string) ($correspondencia->criadoEm ?? '-'), ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        </section>

        <section class="panel">
            <h3>Decisao e Execucao</h3>
            <p>
                Status atual no pipeline:
                <span class="badge <?= $pipelineBadgeClass ?>"><?= htmlspecialchars($pipelineStatus, ENT_QUOTES, 'UTF-8') ?></span>
            </p>
            <form method="POST" action="/oportunidades/<?= (int) $correspondencia->id ?>/decidir">
                <div class="grid">
                    <article>
                        <label for="status_acompanhamento">Status de acompanhamento</label>
                        <select id="status_acompanhamento" name="status_acompanhamento">
                            <option value="EM_ANALISE" <?= $pipelineStatus === 'EM_ANALISE' ? 'selected' : '' ?>>Em analise</option>
                            <option value="PROPOSTA" <?= $pipelineStatus === 'PROPOSTA' ? 'selected' : '' ?>>Proposta</option>
                            <option value="FAVORITO" <?= $pipelineStatus === 'FAVORITO' ? 'selected' : '' ?>>Favorito</option>
                            <option value="DESCARTADO" <?= $pipelineStatus === 'DESCARTADO' ? 'selected' : '' ?>>Descartado</option>
                            <option value="ENCERRADO" <?= $pipelineStatus === 'ENCERRADO' ? 'selected' : '' ?>>Encerrado</option>
                        </select>
                    </article>
                    <article>
                        <label for="observacao">Observacao operacional</label>
                        <textarea id="observacao" name="observacao" rows="3"><?= htmlspecialchars((string) ($correspondencia->favoritoObservacao ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </article>
                </div>
                <div class="actions" style="margin-top: 10px;">
                    <input type="hidden" name="redirect_to" value="/oportunidades/<?= (int) $correspondencia->id ?>">
                    <button class="btn btn-primary" type="submit">Salvar decisao</button>
                    <button class="btn" name="abrir_pipeline" value="1" type="submit">Salvar e abrir pipeline</button>
                    <?php if (($correspondencia->favoritoId ?? null) !== null && (int) $correspondencia->favoritoId > 0): ?>
                        <a class="btn" href="/favoritos/<?= (int) $correspondencia->favoritoId ?>">Abrir item existente</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="panel">
            <h3>Palavras-chave encontradas</h3>
            <?php if ($palavrasEncontradas === []): ?>
                <p>Nenhuma palavra-chave encontrada.</p>
            <?php else: ?>
                <p><?= htmlspecialchars(implode(', ', array_map(static fn(mixed $termo): string => (string) $termo, $palavrasEncontradas)), ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Detalhamento do score</h3>
            <?php if ($detalhes === []): ?>
                <p>Sem detalhamento disponivel para esta correspondencia.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Termo</th>
                            <th>Peso</th>
                            <th>Ocorrencias</th>
                            <th>Incremento</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($detalhes as $linha): ?>
                        <?php
                            $termo = is_array($linha) ? (string) ($linha['termo'] ?? '-') : '-';
                            $peso = is_array($linha) ? (int) ($linha['peso'] ?? 0) : 0;
                            $ocorrencias = is_array($linha) ? (int) ($linha['ocorrencias'] ?? 0) : 0;
                            $incremento = is_array($linha) ? (float) ($linha['incremento'] ?? 0.0) : 0.0;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($termo, ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $peso ?></td>
                            <td><?= $ocorrencias ?></td>
                            <td><?= number_format($incremento, 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

        <section class="panel">
            <h3>Links do edital</h3>
            <div class="actions">
                <?php if (($correspondencia->editalLinkDetalhe ?? null) !== null && $correspondencia->editalLinkDetalhe !== ''): ?>
                    <a class="btn btn-primary" target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($correspondencia->editalLinkDetalhe, ENT_QUOTES, 'UTF-8') ?>">Abrir detalhe externo</a>
                <?php endif; ?>
                <?php if (($correspondencia->editalLinkEdital ?? null) !== null && $correspondencia->editalLinkEdital !== ''): ?>
                    <a class="btn" target="_blank" rel="noopener noreferrer" href="<?= htmlspecialchars($correspondencia->editalLinkEdital, ENT_QUOTES, 'UTF-8') ?>">Abrir edital</a>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>
