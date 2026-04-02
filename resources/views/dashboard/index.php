<?php

declare(strict_types=1);

$appName = isset($appName) ? (string) $appName : 'SaaS Editais';
$auth = isset($auth) && is_array($auth) ? $auth : [];
$tenant = isset($tenant) && is_array($tenant) ? $tenant : [];
$assinatura = isset($assinatura) ? $assinatura : null;
$resumo = isset($resumo) && is_array($resumo) ? $resumo : [];
$notificacoesPropostas = isset($notificacoesPropostas) && is_array($notificacoesPropostas) ? $notificacoesPropostas : [];
$alertasMessage = isset($alertasMessage) ? (string) $alertasMessage : null;
$adminMessage = isset($adminMessage) ? (string) $adminMessage : null;

if (isset($_GET['modo'])) {
    $modoEntrada = strtolower(trim((string) $_GET['modo']));
    if (in_array($modoEntrada, ['iniciante', 'normal'], true)) {
        $_SESSION['ui_modo'] = $modoEntrada;
    }
}
$modoIniciante = strtolower((string) ($_SESSION['ui_modo'] ?? 'normal')) === 'iniciante';
$appendModo = static function (string $url) use ($modoIniciante): string {
    if (!$modoIniciante) {
        return $url;
    }

    return $url . (str_contains($url, '?') ? '&' : '?') . 'modo=iniciante';
};
$pathAtual = strtok((string) ($_SERVER['REQUEST_URI'] ?? '/dashboard'), '?');
if (!is_string($pathAtual) || trim($pathAtual) === '') {
    $pathAtual = '/dashboard';
}
$toggleModoUrl = $pathAtual . '?modo=' . ($modoIniciante ? 'normal' : 'iniciante');
$toggleModoLabel = $modoIniciante ? 'Modo normal' : 'Modo iniciante';

$alertasItems = isset($notificacoesPropostas['items']) && is_array($notificacoesPropostas['items'])
    ? $notificacoesPropostas['items']
    : [];
$alertasAtivosTotal = (int) ($notificacoesPropostas['total_ativos'] ?? count($alertasItems));
$alertasNovos = (int) ($notificacoesPropostas['novos'] ?? 0);
$orquestrador = isset($notificacoesPropostas['orquestrador']) && is_array($notificacoesPropostas['orquestrador'])
    ? $notificacoesPropostas['orquestrador']
    : [];
$orquestradorAtivo = ((int) ($orquestrador['ativo'] ?? 0)) === 1;
$orquestradorResumo = isset($orquestrador['resumo']) && is_array($orquestrador['resumo'])
    ? $orquestrador['resumo']
    : [];
$orquestradorExecutivo = isset($orquestrador['executivo']) && is_array($orquestrador['executivo'])
    ? $orquestrador['executivo']
    : [];
$orquestradorExecutivoResumo = isset($orquestradorExecutivo['resumo']) && is_array($orquestradorExecutivo['resumo'])
    ? $orquestradorExecutivo['resumo']
    : [];
$orquestradorExecutivoNivel = isset($orquestradorExecutivo['por_nivel']) && is_array($orquestradorExecutivo['por_nivel'])
    ? $orquestradorExecutivo['por_nivel']
    : [];
$orquestradorEscalonados = isset($orquestrador['escalonados']) && is_array($orquestrador['escalonados'])
    ? $orquestrador['escalonados']
    : [];
$orquestradorAprendizado = isset($orquestrador['aprendizado']) && is_array($orquestrador['aprendizado'])
    ? $orquestrador['aprendizado']
    : [];
$orquestradorTopContextos = isset($orquestrador['top_contextos_criticos']) && is_array($orquestrador['top_contextos_criticos'])
    ? $orquestrador['top_contextos_criticos']
    : [];
$orquestradorEvidencias = isset($orquestrador['evidencias']) && is_array($orquestrador['evidencias'])
    ? $orquestrador['evidencias']
    : [];

$guiaEtapa1 = (int) ($resumo['oportunidades_total'] ?? 0) > 0;
$guiaEtapa2 = (int) ($resumo['pipeline_total'] ?? 0) > 0;
$guiaEtapa3 = (int) ($resumo['propostas_total'] ?? 0) > 0;
$guiaEtapa4 = (int) ($resumo['propostas_enviadas'] ?? 0) > 0;
$guiaEtapa5 = $alertasAtivosTotal > 0
    || (int) ($orquestradorResumo['ativos'] ?? 0) > 0
    || (int) ($orquestradorExecutivoResumo['total_playbooks'] ?? 0) > 0;

$guiaProximoTitulo = 'Concluido: fluxo principal validado.';
$guiaProximoDescricao = 'Acompanhe alertas e aprendizado.';
$guiaProximoLink = '/dashboard';
$guiaProximoAcao = 'Atualizar';

if (!$guiaEtapa1) {
    $guiaProximoTitulo = 'Passo 1: gerar oportunidades';
    $guiaProximoDescricao = 'Gere as oportunidades.';
    $guiaProximoLink = '/oportunidades';
    $guiaProximoAcao = 'Ir';
} elseif (!$guiaEtapa2) {
    $guiaProximoTitulo = 'Passo 2: classificar no pipeline';
    $guiaProximoDescricao = 'Classifique no pipeline.';
    $guiaProximoLink = '/oportunidades';
    $guiaProximoAcao = 'Ir';
} elseif (!$guiaEtapa3) {
    $guiaProximoTitulo = 'Passo 3: gerar proposta';
    $guiaProximoDescricao = 'Gere a proposta.';
    $guiaProximoLink = '/favoritos';
    $guiaProximoAcao = 'Ir';
} elseif (!$guiaEtapa4) {
    $guiaProximoTitulo = 'Passo 4: enviar proposta';
    $guiaProximoDescricao = 'Envie a proposta.';
    $guiaProximoLink = '/propostas';
    $guiaProximoAcao = 'Ir';
} elseif (!$guiaEtapa5) {
    $guiaProximoTitulo = 'Passo 5: validar alertas e playbooks';
    $guiaProximoDescricao = 'Valide alertas e playbooks.';
    $guiaProximoLink = '/dashboard';
    $guiaProximoAcao = 'Atualizar';
}
$wizardNextLink = $appendModo($guiaProximoLink);

$nome = htmlspecialchars((string) ($auth['nome'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string) ($auth['email'] ?? '-'), ENT_QUOTES, 'UTF-8');
$perfil = htmlspecialchars((string) ($auth['perfil'] ?? '-'), ENT_QUOTES, 'UTF-8');
$empresaNome = htmlspecialchars((string) ($tenant['nome_fantasia'] ?? $tenant['razao_social'] ?? '-'), ENT_QUOTES, 'UTF-8');
$empresaCnpj = htmlspecialchars((string) ($tenant['cnpj'] ?? '-'), ENT_QUOTES, 'UTF-8');

$planoNome = $assinatura?->plano?->nome ?? '-';
$statusAssinatura = $assinatura?->status ?? 'SEM_ASSINATURA';
$fimAssinatura = $assinatura?->dataFim ?? '-';
$perfilRaw = (string) ($auth['perfil'] ?? '');
$isAdmin = in_array(strtoupper($perfilRaw), ['SUPER_ADMIN', 'ADMIN'], true);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Painel | <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #f4f7fb; }
        .container { max-width: 980px; margin: 0 auto; }
        .topbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; gap: 10px; }
        .actions { display: flex; gap: 8px; flex-wrap: wrap; }
        .btn { text-decoration: none; border: 1px solid #c8d4e2; background: #fff; padding: 8px 12px; color: #111; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 10px; }
        .card { background: #fff; border: 1px solid #dfe6f0; border-radius: 8px; padding: 12px; }
        .status-ok { color: #166534; font-weight: 700; }
        .status-warn { color: #b45309; font-weight: 700; }
        .msg { margin: 10px 0 14px; border: 1px solid #f59e0b; background: #fffbeb; padding: 10px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 999px; font-size: 12px; font-weight: 700; }
        .badge-new { background: #fee2e2; color: #991b1b; }
        .badge-type { background: #dbeafe; color: #1d4ed8; }
        .badge-escalado { background: #fecaca; color: #7f1d1d; }
        .badge-prio-alta { background: #fee2e2; color: #7f1d1d; }
        .badge-prio-media { background: #fef3c7; color: #92400e; }
        .badge-prio-baixa { background: #dcfce7; color: #166534; }
        .table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .table th, .table td { border: 1px solid #dfe6f0; padding: 6px; text-align: left; }
        .muted { color: #58657a; }
        .guide-status-ok { color: #166534; font-weight: 700; }
        .guide-status-pendente { color: #b45309; font-weight: 700; }
        .wizard-next {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 99;
            border: 1px solid #00509d;
            background: #00509d;
            color: #fff;
            text-decoration: none;
            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 700;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.16);
        }
        ul { margin: 8px 0 0 18px; padding: 0; }
    </style>
</head>
<body>
    <div class="container">
        <header class="topbar">
            <div>
                <h1>Painel do <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?></h1>
                <p>Bem-vindo(a), <?= $nome ?>.</p>
            </div>
            <div class="actions">
                <a class="btn" href="<?= htmlspecialchars($appendModo('/editais'), ENT_QUOTES, 'UTF-8') ?>">Catalogo de editais</a>
                <a class="btn" href="<?= htmlspecialchars($appendModo('/monitoramento'), ENT_QUOTES, 'UTF-8') ?>">Perfis de monitoramento</a>
                <a class="btn" href="<?= htmlspecialchars($appendModo('/oportunidades'), ENT_QUOTES, 'UTF-8') ?>">Oportunidades</a>
                <a class="btn" href="<?= htmlspecialchars($appendModo('/favoritos'), ENT_QUOTES, 'UTF-8') ?>">Pipeline</a>
                <a class="btn" href="<?= htmlspecialchars($appendModo('/propostas'), ENT_QUOTES, 'UTF-8') ?>">Propostas</a>
                <a class="btn" href="<?= htmlspecialchars($appendModo('/favoritos/relatorio/conversao'), ENT_QUOTES, 'UTF-8') ?>">Relatorio de conversao</a>
                <?php if ($isAdmin): ?>
                    <a class="btn" href="<?= htmlspecialchars($appendModo('/fontes'), ENT_QUOTES, 'UTF-8') ?>">Fontes de coleta</a>
                    <a class="btn" href="<?= htmlspecialchars($appendModo('/admin/coletas'), ENT_QUOTES, 'UTF-8') ?>">Coletas</a>
                <?php endif; ?>
                <a class="btn" href="<?= htmlspecialchars($appendModo('/assinatura/status'), ENT_QUOTES, 'UTF-8') ?>">Status da assinatura</a>
                <a class="btn" href="<?= htmlspecialchars($toggleModoUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($toggleModoLabel, ENT_QUOTES, 'UTF-8') ?></a>
                <a class="btn" href="/logout">Sair</a>
            </div>
        </header>

        <?php if ($adminMessage !== null && $adminMessage !== ''): ?>
            <div class="msg"><?= htmlspecialchars($adminMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($alertasMessage !== null && $alertasMessage !== ''): ?>
            <div class="msg"><?= htmlspecialchars($alertasMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <section class="card" style="margin-bottom: 10px;">
            <h3>Fluxo</h3>
            <p>
                1 Oportunidades <?= $guiaEtapa1 ? 'OK' : 'PENDENTE' ?> |
                2 Pipeline <?= $guiaEtapa2 ? 'OK' : 'PENDENTE' ?> |
                3 Proposta <?= $guiaEtapa3 ? 'OK' : 'PENDENTE' ?> |
                4 Envio <?= $guiaEtapa4 ? 'OK' : 'PENDENTE' ?> |
                5 Alertas <?= $guiaEtapa5 ? 'OK' : 'PENDENTE' ?>
            </p>
            <p>
                <strong><?= htmlspecialchars($guiaProximoTitulo, ENT_QUOTES, 'UTF-8') ?></strong>
                - <?= htmlspecialchars($guiaProximoDescricao, ENT_QUOTES, 'UTF-8') ?>
                <a class="btn" href="<?= htmlspecialchars($wizardNextLink, ENT_QUOTES, 'UTF-8') ?>" style="margin-left: 6px;"><?= htmlspecialchars($guiaProximoAcao, ENT_QUOTES, 'UTF-8') ?></a>
            </p>
        </section>

        <section class="grid">
            <article class="card">
                <h3>Empresa</h3>
                <p><?= $empresaNome ?></p>
            </article>
            <article class="card">
                <h3>CNPJ</h3>
                <p><?= $empresaCnpj ?></p>
            </article>
            <article class="card">
                <h3>Usuario</h3>
                <p><?= $nome ?></p>
                <p><?= $email ?></p>
            </article>
            <article class="card">
                <h3>Perfil</h3>
                <p><?= $perfil ?></p>
            </article>
            <article class="card">
                <h3>Plano atual</h3>
                <p><?= htmlspecialchars((string) $planoNome, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
            <article class="card">
                <h3>Status da assinatura</h3>
                <?php if (in_array($statusAssinatura, ['ATIVA', 'TESTE'], true)): ?>
                    <p class="status-ok"><?= htmlspecialchars($statusAssinatura, ENT_QUOTES, 'UTF-8') ?></p>
                <?php else: ?>
                    <p class="status-warn"><?= htmlspecialchars($statusAssinatura, ENT_QUOTES, 'UTF-8') ?></p>
                <?php endif; ?>
                <p>Validade: <?= htmlspecialchars((string) $fimAssinatura, ENT_QUOTES, 'UTF-8') ?></p>
            </article>
        </section>

        <section class="grid" style="margin-top: 10px;">
            <article class="card">
                <h3>Oportunidades mapeadas</h3>
                <p><?= (int) ($resumo['oportunidades_total'] ?? 0) ?></p>
            </article>
            <article class="card">
                <h3>Itens em pipeline</h3>
                <p><?= (int) ($resumo['pipeline_total'] ?? 0) ?></p>
            </article>
            <article class="card">
                <h3>Em analise</h3>
                <p><?= (int) ($resumo['em_analise'] ?? 0) ?></p>
            </article>
            <article class="card">
                <h3>Em proposta</h3>
                <p><?= (int) ($resumo['proposta'] ?? 0) ?></p>
            </article>
            <article class="card">
                <h3>Descartados</h3>
                <p><?= (int) ($resumo['descartado'] ?? 0) ?></p>
            </article>
            <article class="card">
                <h3>Encerrados</h3>
                <p><?= (int) ($resumo['encerrado'] ?? 0) ?></p>
            </article>
            <?php if (!$modoIniciante): ?>
                <article class="card">
                    <h3>Taxa de decisao</h3>
                    <p><?= number_format((float) ($resumo['taxa_decisao'] ?? 0), 1, ',', '.') ?>%</p>
                </article>
                <article class="card">
                    <h3>Taxa de conclusao</h3>
                    <p><?= number_format((float) ($resumo['taxa_conclusao'] ?? 0), 1, ',', '.') ?>%</p>
                </article>
                <article class="card">
                    <h3>Tarefas vencendo (48h)</h3>
                    <p><?= (int) ($resumo['tarefas_vencendo_48h'] ?? 0) ?></p>
                </article>
                <article class="card">
                    <h3>Tarefas vencidas</h3>
                    <p><?= (int) ($resumo['tarefas_vencidas'] ?? 0) ?></p>
                </article>
                <article class="card">
                    <h3>Conv. Analise -> Proposta</h3>
                    <p><?= number_format((float) ($resumo['taxa_analise_para_proposta'] ?? 0), 1, ',', '.') ?>%</p>
                </article>
                <article class="card">
                    <h3>Conv. Proposta -> Encerrado</h3>
                    <p><?= number_format((float) ($resumo['taxa_proposta_para_encerrado'] ?? 0), 1, ',', '.') ?>%</p>
                </article>
                <article class="card">
                    <h3>Propostas totais</h3>
                    <p><?= (int) ($resumo['propostas_total'] ?? 0) ?></p>
                </article>
                <article class="card">
                    <h3>Propostas em revisao</h3>
                    <p><?= (int) ($resumo['propostas_em_revisao'] ?? 0) ?></p>
                </article>
                <article class="card">
                    <h3>Propostas enviadas</h3>
                    <p><?= (int) ($resumo['propostas_enviadas'] ?? 0) ?></p>
                </article>
                <article class="card">
                    <h3>Resultados de propostas</h3>
                    <p><?= (int) ($resumo['propostas_resultados_total'] ?? 0) ?></p>
                </article>
                <article class="card">
                    <h3>Propostas vencedoras</h3>
                    <p><?= (int) ($resumo['propostas_vencedoras'] ?? 0) ?></p>
                </article>
                <article class="card">
                    <h3>Taxa sucesso propostas</h3>
                    <p><?= number_format((float) ($resumo['taxa_sucesso_propostas'] ?? 0), 1, ',', '.') ?>%</p>
                </article>
            <?php endif; ?>
        </section>

        <section class="card" style="margin-top: 10px;">
            <h3>Alertas proativos de propostas</h3>
            <p>
                Ativos: <strong><?= $alertasAtivosTotal ?></strong>
                | Novos: <strong><?= $alertasNovos ?></strong>
            </p>
            <?php if ($alertasNovos > 0): ?>
                <form method="POST" action="/dashboard/alertas/vistos" style="margin-bottom: 10px;">
                    <button class="btn" type="submit">Marcar alertas como vistos</button>
                </form>
            <?php endif; ?>

            <?php if ($alertasItems === []): ?>
                <p>Nenhum alerta ativo no momento.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($alertasItems as $alerta): ?>
                        <?php
                            $tipo = (string) ($alerta['tipo_alerta'] ?? '-');
                            $rotuloTipo = $tipo === 'SEM_RESULTADO' ? 'Sem resultado' : 'Julgamento parado';
                            $dias = (int) ($alerta['dias_referencia'] ?? 0);
                        ?>
                        <li>
                            <?php if ((int) ($alerta['novo'] ?? 0) === 1): ?>
                                <span class="badge badge-new">NOVO</span>
                            <?php endif; ?>
                            <span class="badge badge-type"><?= htmlspecialchars($rotuloTipo, ENT_QUOTES, 'UTF-8') ?></span>
                            <a href="<?= htmlspecialchars($appendModo('/propostas/' . (int) ($alerta['proposta_id'] ?? 0)), ENT_QUOTES, 'UTF-8') ?>">
                                Proposta #<?= (int) ($alerta['proposta_id'] ?? 0) ?>
                            </a>
                            - <?= htmlspecialchars((string) ($alerta['orgao_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                            - <?= $dias ?> dia(s)
                        </li>
                    <?php endforeach; ?>
                </ul>
                <?php if ($alertasAtivosTotal > count($alertasItems)): ?>
                    <p style="margin-top: 8px;">
                        Exibindo os <?= count($alertasItems) ?> alertas mais recentes de um total de <?= $alertasAtivosTotal ?>.
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        </section>

        <section class="card" style="margin-top: 10px;">
            <h3>Orquestrador automatico</h3>
            <?php if (!$orquestradorAtivo): ?>
                <p class="muted">Orquestrador automatico desativado no ambiente.</p>
            <?php else: ?>
                <p>
                    Playbooks ativos: <strong><?= (int) ($orquestradorResumo['ativos'] ?? 0) ?></strong>
                    | Sem progresso: <strong><?= (int) ($orquestradorResumo['sem_progresso'] ?? 0) ?></strong>
                    | Escalados: <strong><?= (int) ($orquestradorResumo['escalados'] ?? 0) ?></strong>
                </p>

                <h4>Indicadores executivos</h4>
                <p>
                    Total historico: <strong><?= (int) ($orquestradorExecutivoResumo['total_playbooks'] ?? 0) ?></strong>
                    | Encerrados no SLA: <strong><?= (int) ($orquestradorExecutivoResumo['encerrados_no_prazo'] ?? 0) ?>/<?= (int) ($orquestradorExecutivoResumo['encerrados_total'] ?? 0) ?></strong>
                    | Taxa SLA: <strong><?= number_format((float) ($orquestradorExecutivoResumo['taxa_sla_percentual'] ?? 0), 1, ',', '.') ?>%</strong>
                    | Taxa escalonamento: <strong><?= number_format((float) ($orquestradorExecutivoResumo['taxa_escalonamento_percentual'] ?? 0), 1, ',', '.') ?>%</strong>
                </p>
                <p>
                    Tempo medio 1a acao: <strong><?= number_format((float) ($orquestradorExecutivoResumo['tempo_medio_primeira_atividade_horas'] ?? 0), 2, ',', '.') ?>h</strong>
                    | Tempo medio encerramento: <strong><?= number_format((float) ($orquestradorExecutivoResumo['tempo_medio_encerramento_horas'] ?? 0), 2, ',', '.') ?>h</strong>
                </p>
                <p>
                    Risco de atraso: <strong><?= number_format((float) ($orquestradorExecutivoResumo['risco_atraso_percentual'] ?? 0), 1, ',', '.') ?>%</strong>
                    | SLA sugerido: <strong><?= number_format((float) ($orquestradorExecutivoResumo['sla_sugerido_horas'] ?? 0), 1, ',', '.') ?>h</strong>
                </p>

                <?php if (!$modoIniciante): ?>
                <h4>Escalonamento por nivel</h4>
                <?php if ($orquestradorExecutivoNivel === []): ?>
                    <p class="muted">Sem historico de escalonamento por nivel.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($orquestradorExecutivoNivel as $nivel): ?>
                            <li>
                                Nivel L<?= (int) ($nivel['nivel'] ?? 0) ?>:
                                <strong><?= (int) ($nivel['total'] ?? 0) ?></strong> caso(s)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h4>Escalonamentos em aberto</h4>
                <?php if ($orquestradorEscalonados === []): ?>
                    <p class="muted">Nenhum playbook escalado no momento.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($orquestradorEscalonados as $item): ?>
                            <?php
                                $tipo = (string) ($item['tipo_alerta'] ?? '');
                                $tipoLabel = $tipo === 'JULGAMENTO_PARADO' ? 'Julgamento parado' : 'Sem resultado';
                                $prioridade = strtoupper((string) ($item['prioridade'] ?? 'MEDIA'));
                                $prioridadeClass = $prioridade === 'ALTA'
                                    ? 'badge-prio-alta'
                                    : ($prioridade === 'BAIXA' ? 'badge-prio-baixa' : 'badge-prio-media');
                                $progresso = (float) ($item['progresso_percentual'] ?? 0);
                                $nivelEscalonamento = max(1, (int) ($item['escalonamento_nivel'] ?? 1));
                                $contextoOrgao = (string) ($item['contexto_orgao_nome'] ?? $item['orgao_nome'] ?? 'GERAL');
                                $contextoModalidade = (string) ($item['contexto_modalidade'] ?? $item['modalidade'] ?? 'GERAL');
                                $riscoAtrasoItem = (float) ($item['risco_atraso_percentual'] ?? 0);
                                $slaSugeridoItem = (float) ($item['sla_sugerido_horas'] ?? 0);
                            ?>
                            <li>
                                <span class="badge badge-escalado">ESCALADO</span>
                                <span class="badge badge-type"><?= htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="badge <?= $prioridadeClass ?>">Prioridade <?= htmlspecialchars($prioridade, ENT_QUOTES, 'UTF-8') ?></span>
                                <a href="/propostas/<?= (int) ($item['proposta_id'] ?? 0) ?>">
                                    Proposta #<?= (int) ($item['proposta_id'] ?? 0) ?>
                                </a>
                                - <?= htmlspecialchars((string) ($item['orgao_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                - progresso <?= number_format($progresso, 1, ',', '.') ?>%
                                - nivel L<?= $nivelEscalonamento ?>
                                - responsavel <?= htmlspecialchars((string) ($item['responsavel_nome'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                - SLA <?= htmlspecialchars((string) ($item['prazo_sla_em'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                - SLA sugerido <?= number_format($slaSugeridoItem, 1, ',', '.') ?>h
                                - risco atraso <?= number_format($riscoAtrasoItem, 1, ',', '.') ?>%
                                - contexto <?= htmlspecialchars($contextoOrgao, ENT_QUOTES, 'UTF-8') ?>/<?= htmlspecialchars($contextoModalidade, ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h4>Trilha de evidencia (eventos recentes)</h4>
                <?php if ($orquestradorEvidencias === []): ?>
                    <p class="muted">Sem eventos registrados para exibicao.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($orquestradorEvidencias as $evento): ?>
                            <?php
                                $tipoEvento = strtoupper((string) ($evento['tipo_evento'] ?? ''));
                                $tipoEventoLabel = match ($tipoEvento) {
                                    'PLAYBOOK_CRIADO' => 'PLAYBOOK CRIADO',
                                    'PROGRESSO_ATUALIZADO' => 'PROGRESSO',
                                    'ESCALONADO' => 'ESCALONADO',
                                    'ENCERRADO' => 'ENCERRADO',
                                    'REABERTO' => 'REABERTO',
                                    'APRENDIZADO_ATUALIZADO' => 'APRENDIZADO',
                                    default => $tipoEvento !== '' ? $tipoEvento : 'EVENTO',
                                };
                                $detalhesEvento = isset($evento['detalhes']) && is_array($evento['detalhes'])
                                    ? $evento['detalhes']
                                    : [];
                                $resumoDetalhe = '';
                                if ($tipoEvento === 'PLAYBOOK_CRIADO' || $tipoEvento === 'REABERTO') {
                                    $slaSugeridoEvento = isset($detalhesEvento['sla_sugerido_horas']) ? (float) $detalhesEvento['sla_sugerido_horas'] : null;
                                    $riscoEvento = isset($detalhesEvento['risco_atraso_percentual']) ? (float) $detalhesEvento['risco_atraso_percentual'] : null;
                                    if ($slaSugeridoEvento !== null) {
                                        $resumoDetalhe = ' | SLA sugerido ' . number_format($slaSugeridoEvento, 1, ',', '.') . 'h';
                                    }
                                    if ($riscoEvento !== null) {
                                        $resumoDetalhe .= ' | risco atraso ' . number_format($riscoEvento, 1, ',', '.') . '%';
                                    }
                                } elseif ($tipoEvento === 'PROGRESSO_ATUALIZADO') {
                                    $anterior = isset($detalhesEvento['progresso_anterior']) ? (float) $detalhesEvento['progresso_anterior'] : null;
                                    $atual = isset($detalhesEvento['progresso_atual']) ? (float) $detalhesEvento['progresso_atual'] : null;
                                    if ($anterior !== null && $atual !== null) {
                                        $resumoDetalhe = ' | progresso ' . number_format($anterior, 1, ',', '.') . '% -> '
                                            . number_format($atual, 1, ',', '.') . '%';
                                    }
                                } elseif ($tipoEvento === 'ESCALONADO') {
                                    $motivo = trim((string) ($detalhesEvento['motivo'] ?? ''));
                                    $nivelEvento = isset($detalhesEvento['escalonamento_nivel']) ? (int) $detalhesEvento['escalonamento_nivel'] : 0;
                                    if ($nivelEvento > 0) {
                                        $resumoDetalhe = ' | nivel L' . $nivelEvento;
                                    }
                                    if ($motivo !== '') {
                                        $resumoDetalhe .= ' | motivo: ' . $motivo;
                                    }
                                } elseif ($tipoEvento === 'ENCERRADO') {
                                    $resultadoWinLoss = strtoupper(trim((string) ($detalhesEvento['resultado_win_loss'] ?? '')));
                                    if ($resultadoWinLoss !== '') {
                                        $resumoDetalhe = ' | resultado: ' . $resultadoWinLoss;
                                    }
                                } elseif ($tipoEvento === 'APRENDIZADO_ATUALIZADO') {
                                    $riscoEvento = isset($detalhesEvento['risco_atraso_percentual']) ? (float) $detalhesEvento['risco_atraso_percentual'] : null;
                                    $slaSugeridoEvento = isset($detalhesEvento['sla_sugerido_horas']) ? (float) $detalhesEvento['sla_sugerido_horas'] : null;
                                    $casosEvento = isset($detalhesEvento['total_casos']) ? (int) $detalhesEvento['total_casos'] : null;
                                    if ($casosEvento !== null) {
                                        $resumoDetalhe = ' | casos: ' . $casosEvento;
                                    }
                                    if ($riscoEvento !== null) {
                                        $resumoDetalhe .= ' | risco atraso ' . number_format($riscoEvento, 1, ',', '.') . '%';
                                    }
                                    if ($slaSugeridoEvento !== null) {
                                        $resumoDetalhe .= ' | SLA sugerido ' . number_format($slaSugeridoEvento, 1, ',', '.') . 'h';
                                    }
                                }
                            ?>
                            <li>
                                <span class="badge badge-type"><?= htmlspecialchars($tipoEventoLabel, ENT_QUOTES, 'UTF-8') ?></span>
                                <?= htmlspecialchars((string) ($evento['criado_em'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                - Playbook #<?= (int) ($evento['playbook_id'] ?? 0) ?>
                                - <a href="/propostas/<?= (int) ($evento['proposta_id'] ?? 0) ?>">
                                    Proposta #<?= (int) ($evento['proposta_id'] ?? 0) ?>
                                </a>
                                - <?= htmlspecialchars((string) ($evento['descricao'] ?? '-'), ENT_QUOTES, 'UTF-8') ?>
                                <?= htmlspecialchars($resumoDetalhe, ENT_QUOTES, 'UTF-8') ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>

                <h4>Top contextos criticos</h4>
                <?php if ($orquestradorTopContextos === []): ?>
                    <p class="muted">Sem contexto critico consolidado no momento.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Orgao</th>
                                <th>Modalidade</th>
                                <th>Casos</th>
                                <th>Risco atraso</th>
                                <th>SLA sugerido</th>
                                <th>Escalonamento</th>
                                <th>Win rate</th>
                                <th>Prioridade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orquestradorTopContextos as $row): ?>
                                <?php
                                    $tipo = (string) ($row['tipo_alerta'] ?? '');
                                    $tipoLabel = $tipo === 'JULGAMENTO_PARADO' ? 'JULGAMENTO_PARADO' : 'SEM_RESULTADO';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($row['orgao_nome_contexto'] ?? 'GERAL'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($row['modalidade_contexto'] ?? 'GERAL'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) ($row['total_casos'] ?? 0) ?></td>
                                    <td><?= number_format((float) ($row['risco_atraso_percentual'] ?? 0), 1, ',', '.') ?>%</td>
                                    <td><?= number_format((float) ($row['sla_sugerido_horas'] ?? 0), 1, ',', '.') ?>h</td>
                                    <td><?= number_format((float) ($row['taxa_escalonamento_percentual'] ?? 0), 1, ',', '.') ?>%</td>
                                    <td><?= number_format((float) ($row['win_rate'] ?? 0), 1, ',', '.') ?>%</td>
                                    <td><?= htmlspecialchars((string) ($row['prioridade_sugerida'] ?? 'MEDIA'), ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <h4>Aprendizado win/loss por contexto</h4>
                <?php if ($orquestradorAprendizado === []): ?>
                    <p class="muted">Sem historico suficiente para ajustar priorizacao.</p>
                <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Orgao</th>
                                <th>Modalidade</th>
                                <th>Casos</th>
                                <th>Wins</th>
                                <th>Losses</th>
                                <th>Neutros</th>
                                <th>Win rate</th>
                                <th>Tempo 1a acao</th>
                                <th>Escalonamento</th>
                                <th>Risco atraso</th>
                                <th>SLA sugerido</th>
                                <th>Prioridade sugerida</th>
                                <th>Fator</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orquestradorAprendizado as $row): ?>
                                <?php
                                    $tipo = (string) ($row['tipo_alerta'] ?? '');
                                    $tipoLabel = $tipo === 'JULGAMENTO_PARADO' ? 'JULGAMENTO_PARADO' : 'SEM_RESULTADO';
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($tipoLabel, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($row['orgao_nome_contexto'] ?? 'GERAL'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars((string) ($row['modalidade_contexto'] ?? 'GERAL'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= (int) ($row['total_casos'] ?? 0) ?></td>
                                    <td><?= (int) ($row['wins'] ?? 0) ?></td>
                                    <td><?= (int) ($row['losses'] ?? 0) ?></td>
                                    <td><?= (int) ($row['neutros'] ?? 0) ?></td>
                                    <td><?= number_format((float) ($row['win_rate'] ?? 0), 1, ',', '.') ?>%</td>
                                    <td><?= number_format((float) ($row['tempo_medio_primeira_acao_horas'] ?? 0), 2, ',', '.') ?>h</td>
                                    <td><?= number_format((float) ($row['taxa_escalonamento_percentual'] ?? 0), 1, ',', '.') ?>%</td>
                                    <td><?= number_format((float) ($row['risco_atraso_percentual'] ?? 0), 1, ',', '.') ?>%</td>
                                    <td><?= number_format((float) ($row['sla_sugerido_horas'] ?? 0), 1, ',', '.') ?>h</td>
                                    <td><?= htmlspecialchars((string) ($row['prioridade_sugerida'] ?? 'MEDIA'), ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= number_format((float) ($row['fator_priorizacao'] ?? 1), 2, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </div>
    <a class="wizard-next" href="<?= htmlspecialchars($wizardNextLink, ENT_QUOTES, 'UTF-8') ?>">Proxima etapa</a>
</body>
</html>
