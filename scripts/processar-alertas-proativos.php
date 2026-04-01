<?php

declare(strict_types=1);

use App\Core\Env;
use App\Jobs\ProcessarAlertasPropostasJob;
use App\Repositories\EmpresaRepository;
use App\Services\LogService;

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/vendor/autoload.php';

if (is_file(BASE_PATH . '/.env')) {
    Env::loadFromFile(BASE_PATH . '/.env');
} elseif (is_file(BASE_PATH . '/.env.example')) {
    Env::loadFromFile(BASE_PATH . '/.env.example');
}

$timezone = (string) ($_ENV['APP_TIMEZONE'] ?? $_SERVER['APP_TIMEZONE'] ?? 'America/Fortaleza');
if (!@date_default_timezone_set($timezone)) {
    date_default_timezone_set('UTC');
}

$options = getopt('', ['empresa::', 'limite::', 'sem-email']);
$empresaIdFiltro = isset($options['empresa']) && is_numeric($options['empresa']) ? (int) $options['empresa'] : 0;
$limiteEmpresas = isset($options['limite']) && is_numeric($options['limite']) ? (int) $options['limite'] : 0;
$enviarEmail = !array_key_exists('sem-email', $options);

$logService = new LogService();
$job = new ProcessarAlertasPropostasJob();
$empresaRepository = new EmpresaRepository();

$empresaIds = [];
if ($empresaIdFiltro > 0) {
    $empresaIds[] = $empresaIdFiltro;
} else {
    $empresaIds = $empresaRepository->listIdsAtivas($limiteEmpresas > 0 ? $limiteEmpresas : null);
}

if ($empresaIds === []) {
    fwrite(STDOUT, "Nenhuma empresa ativa encontrada para processar alertas.\n");
    exit(0);
}

$inicio = microtime(true);
$totais = [
    'empresas' => 0,
    'novos' => 0,
    'resolvidos' => 0,
    'emails_enviados' => 0,
    'emails_falhos' => 0,
    'playbooks_criados' => 0,
    'playbooks_reabertos' => 0,
    'playbooks_escalonados' => 0,
    'playbooks_encerrados' => 0,
];
$falhas = 0;

fwrite(STDOUT, 'Processando alertas proativos para ' . count($empresaIds) . " empresa(s)...\n");

foreach ($empresaIds as $empresaId) {
    $empresaId = (int) $empresaId;
    if ($empresaId <= 0) {
        continue;
    }

    try {
        $resultado = $job->handle($empresaId, $enviarEmail);
        $totais['empresas']++;
        $totais['novos'] += (int) ($resultado['novos'] ?? 0);
        $totais['resolvidos'] += (int) ($resultado['resolvidos'] ?? 0);
        $totais['emails_enviados'] += (int) ($resultado['emails_enviados'] ?? 0);
        $totais['emails_falhos'] += (int) ($resultado['emails_falhos'] ?? 0);

        $orquestrador = isset($resultado['orquestrador']) && is_array($resultado['orquestrador'])
            ? $resultado['orquestrador']
            : [];
        $totais['playbooks_criados'] += (int) ($orquestrador['playbooks_criados'] ?? 0);
        $totais['playbooks_reabertos'] += (int) ($orquestrador['playbooks_reabertos'] ?? 0);
        $totais['playbooks_escalonados'] += (int) ($orquestrador['playbooks_escalonados'] ?? 0);
        $totais['playbooks_encerrados'] += (int) ($orquestrador['playbooks_encerrados'] ?? 0);

        $linha = [
            'empresa=' . $empresaId,
            'novos=' . (int) ($resultado['novos'] ?? 0),
            'resolvidos=' . (int) ($resultado['resolvidos'] ?? 0),
            'emails_ok=' . (int) ($resultado['emails_enviados'] ?? 0),
            'emails_fail=' . (int) ($resultado['emails_falhos'] ?? 0),
            'playbooks_criados=' . (int) ($orquestrador['playbooks_criados'] ?? 0),
            'playbooks_escalonados=' . (int) ($orquestrador['playbooks_escalonados'] ?? 0),
            'playbooks_encerrados=' . (int) ($orquestrador['playbooks_encerrados'] ?? 0),
        ];
        fwrite(STDOUT, implode(' | ', $linha) . "\n");
    } catch (Throwable $exception) {
        $falhas++;
        $logService->error('scripts.alertas_proativos', 'Falha no processamento de alertas por empresa.', [
            'empresa_id' => $empresaId,
            'exception' => $exception->getMessage(),
        ]);
        fwrite(STDERR, 'Falha empresa #' . $empresaId . ': ' . $exception->getMessage() . "\n");
    }
}

$duracao = round(microtime(true) - $inicio, 2);
fwrite(STDOUT, "\nResumo final:\n");
fwrite(STDOUT, 'Empresas processadas: ' . $totais['empresas'] . "\n");
fwrite(STDOUT, 'Alertas novos: ' . $totais['novos'] . "\n");
fwrite(STDOUT, 'Alertas resolvidos: ' . $totais['resolvidos'] . "\n");
fwrite(STDOUT, 'Emails enviados: ' . $totais['emails_enviados'] . "\n");
fwrite(STDOUT, 'Emails falhos: ' . $totais['emails_falhos'] . "\n");
fwrite(STDOUT, 'Playbooks criados: ' . $totais['playbooks_criados'] . "\n");
fwrite(STDOUT, 'Playbooks reabertos: ' . $totais['playbooks_reabertos'] . "\n");
fwrite(STDOUT, 'Playbooks escalonados: ' . $totais['playbooks_escalonados'] . "\n");
fwrite(STDOUT, 'Playbooks encerrados: ' . $totais['playbooks_encerrados'] . "\n");
fwrite(STDOUT, 'Duracao total (s): ' . number_format($duracao, 2, '.', '') . "\n");

exit($falhas > 0 ? 1 : 0);
