<?php

declare(strict_types=1);

use App\Core\Env;
use App\Services\AlertaEmailDestinatarioService;
use App\Services\EmailService;

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

$options = getopt('', ['empresa::', 'email::', 'enviar-teste']);
$empresaId = isset($options['empresa']) && is_numeric($options['empresa']) ? (int) $options['empresa'] : 0;
$emailInformado = isset($options['email']) ? trim((string) $options['email']) : '';
$enviarTeste = array_key_exists('enviar-teste', $options);

if ($empresaId <= 0 && $emailInformado === '') {
    fwrite(STDERR, "Uso: php scripts/validar-email-alertas.php --empresa=1 [--enviar-teste]\n");
    fwrite(STDERR, "  ou: php scripts/validar-email-alertas.php --email=usuario@dominio.com [--enviar-teste]\n");
    exit(1);
}

$destinatarioService = new AlertaEmailDestinatarioService();
$email = $emailInformado;
$origem = 'parametro.email';

if ($email === '' && $empresaId > 0) {
    $resolucao = $destinatarioService->resolverPrincipal($empresaId);
    $email = (string) ($resolucao['email'] ?? '');
    $origem = (string) ($resolucao['origem'] ?? 'nao_encontrado');
}

if ($email === '') {
    fwrite(STDERR, "Nenhum email de destino encontrado para validacao.\n");
    fwrite(STDERR, "Empresa: " . $empresaId . " | origem: " . $origem . "\n");
    exit(2);
}

$validacao = $destinatarioService->validarEmail($email);
if (($validacao['valido'] ?? false) !== true) {
    fwrite(STDERR, "EMAIL INVALIDO: " . $email . "\n");
    fwrite(STDERR, "Motivo: " . (string) ($validacao['erro'] ?? 'desconhecido') . "\n");
    if (isset($validacao['dominio'])) {
        fwrite(STDERR, "Dominio: " . (string) $validacao['dominio'] . "\n");
    }
    exit(3);
}

$emailValido = (string) ($validacao['email'] ?? $email);
fwrite(STDOUT, "EMAIL VALIDO: " . $emailValido . "\n");
fwrite(STDOUT, "Origem: " . $origem . "\n");
$mxAtivoRaw = $_ENV['ALERTA_EMAIL_VALIDAR_MX'] ?? $_SERVER['ALERTA_EMAIL_VALIDAR_MX'] ?? 'true';
$mxAtivo = filter_var((string) $mxAtivoRaw, FILTER_VALIDATE_BOOLEAN);
fwrite(STDOUT, "Validacao MX ativa: " . ($mxAtivo ? 'sim' : 'nao') . "\n");

if (!$enviarTeste) {
    exit(0);
}

$appName = (string) ($_ENV['APP_NAME'] ?? $_SERVER['APP_NAME'] ?? 'SaaS Editais');
$body = implode(PHP_EOL, [
    'Teste de envio de alerta de editais.',
    'Este e-mail confirma a configuracao de entrega do sistema.',
    'Data/hora: ' . date('Y-m-d H:i:s'),
    'Empresa: ' . ($empresaId > 0 ? (string) $empresaId : 'n/a'),
]);

$emailService = new EmailService();
$envio = $emailService->sendPlainText(
    $emailValido,
    '[' . $appName . '] Teste de alerta de editais',
    $body
);

if (($envio['sucesso'] ?? false) !== true) {
    fwrite(STDERR, "Falha no envio de teste: " . (string) ($envio['erro'] ?? 'erro_desconhecido') . "\n");
    exit(4);
}

fwrite(STDOUT, "Envio de teste realizado com sucesso para " . $emailValido . ".\n");
exit(0);
