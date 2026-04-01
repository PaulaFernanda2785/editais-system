<?php

declare(strict_types=1);

namespace App\Services;

class EmailService
{
    private LogService $logService;

    public function __construct(?LogService $logService = null)
    {
        $this->logService = $logService ?? new LogService();
    }

    /**
     * @param string|array<int, string> $to
     * @return array{sucesso: bool, erro?: string}
     */
    public function sendPlainText(
        string|array $to,
        string $subject,
        string $body,
        ?string $fromAddress = null,
        ?string $fromName = null
    ): array {
        $destinatarios = $this->normalizarDestinatarios($to);
        if ($destinatarios === []) {
            return ['sucesso' => false, 'erro' => 'destinatario_nao_informado'];
        }

        $fromAddress = trim((string) ($fromAddress ?? $this->envString('MAIL_FROM_ADDRESS', $this->envString('ALERTA_EMAIL_FROM', 'no-reply@example.com'))));
        $fromName = trim((string) ($fromName ?? $this->envString('MAIL_FROM_NAME', $this->envString('ALERTA_EMAIL_NOME', $this->envString('APP_NAME', 'SaaS Editais')))));
        if ($fromAddress === '' || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
            return ['sucesso' => false, 'erro' => 'from_invalido'];
        }

        $driver = strtolower($this->envString('MAIL_DRIVER', 'mail'));
        if ($driver === 'smtp') {
            $resultado = $this->sendViaSmtp($destinatarios, $subject, $body, $fromAddress, $fromName);
            if (($resultado['sucesso'] ?? false) === true) {
                return ['sucesso' => true];
            }

            $fallback = $this->envBool('MAIL_FALLBACK_TO_MAIL', false);
            if (!$fallback) {
                return $resultado;
            }

            $this->logService->warning('mail.smtp.fallback', 'Falha no envio SMTP; usando fallback mail().', [
                'erro' => $resultado['erro'] ?? 'smtp_falhou',
            ]);
        }

        return $this->sendViaMailFunction($destinatarios, $subject, $body, $fromAddress, $fromName);
    }

    /**
     * @param array<int, string> $destinatarios
     * @return array{sucesso: bool, erro?: string}
     */
    private function sendViaMailFunction(
        array $destinatarios,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName
    ): array {
        $headers = [];
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'From: ' . $this->formatFromHeader($fromName, $fromAddress);

        $ok = @mail(
            implode(',', $destinatarios),
            $this->encodeHeaderValue($subject),
            $body,
            implode("\r\n", $headers)
        );

        if (!$ok) {
            return ['sucesso' => false, 'erro' => 'falha_mail_php'];
        }

        return ['sucesso' => true];
    }

    /**
     * @param array<int, string> $destinatarios
     * @return array{sucesso: bool, erro?: string}
     */
    private function sendViaSmtp(
        array $destinatarios,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName
    ): array {
        $host = trim($this->envString('MAIL_SMTP_HOST', ''));
        if ($host === '') {
            return ['sucesso' => false, 'erro' => 'smtp_host_nao_configurado'];
        }

        $port = $this->envInt('MAIL_SMTP_PORT', 587);
        $secure = strtolower(trim($this->envString('MAIL_SMTP_SECURE', 'tls')));
        if (!in_array($secure, ['tls', 'ssl', 'none'], true)) {
            $secure = 'tls';
        }
        $auth = $this->envBool('MAIL_SMTP_AUTH', true);
        $username = trim($this->envString('MAIL_SMTP_USER', ''));
        $password = (string) ($this->envRaw('MAIL_SMTP_PASS') ?? '');
        $timeout = max(5, min(60, $this->envInt('MAIL_SMTP_TIMEOUT', 15)));

        $transportHost = $secure === 'ssl' ? ('ssl://' . $host) : $host;
        $errno = 0;
        $errstr = '';
        $socket = @stream_socket_client(
            $transportHost . ':' . $port,
            $errno,
            $errstr,
            $timeout,
            STREAM_CLIENT_CONNECT
        );
        if (!is_resource($socket)) {
            return ['sucesso' => false, 'erro' => 'smtp_conexao_falhou:' . $errstr];
        }

        stream_set_timeout($socket, $timeout);

        try {
            $this->expectResponse($socket, [220]);
            $hostname = preg_replace('/[^A-Za-z0-9.-]/', '', gethostname() ?: 'localhost');
            if ($hostname === '' || !is_string($hostname)) {
                $hostname = 'localhost';
            }

            $this->command($socket, 'EHLO ' . $hostname, [250]);

            if ($secure === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                $crypto = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                if ($crypto !== true) {
                    return ['sucesso' => false, 'erro' => 'smtp_starttls_falhou'];
                }
                $this->command($socket, 'EHLO ' . $hostname, [250]);
            }

            if ($auth) {
                if ($username === '' || $password === '') {
                    return ['sucesso' => false, 'erro' => 'smtp_auth_incompleta'];
                }

                $this->command($socket, 'AUTH LOGIN', [334]);
                $this->command($socket, base64_encode($username), [334]);
                $this->command($socket, base64_encode($password), [235]);
            }

            $this->command($socket, 'MAIL FROM:<' . $fromAddress . '>', [250]);
            foreach ($destinatarios as $destinatario) {
                $this->command($socket, 'RCPT TO:<' . $destinatario . '>', [250, 251]);
            }

            $this->command($socket, 'DATA', [354]);

            $mensagem = $this->buildSmtpMessage($destinatarios, $subject, $body, $fromAddress, $fromName);
            fwrite($socket, $mensagem . "\r\n.\r\n");
            $this->expectResponse($socket, [250]);

            $this->command($socket, 'QUIT', [221]);
        } catch (\Throwable $exception) {
            @fwrite($socket, "QUIT\r\n");
            @fclose($socket);
            return ['sucesso' => false, 'erro' => 'smtp_erro:' . $exception->getMessage()];
        }

        @fclose($socket);
        return ['sucesso' => true];
    }

    /**
     * @param resource $socket
     * @param array<int, int> $statusEsperados
     */
    private function command($socket, string $command, array $statusEsperados): void
    {
        fwrite($socket, $command . "\r\n");
        $this->expectResponse($socket, $statusEsperados);
    }

    /**
     * @param resource $socket
     * @param array<int, int> $statusEsperados
     */
    private function expectResponse($socket, array $statusEsperados): void
    {
        [$codigo, $resposta] = $this->readResponse($socket);
        if (!in_array($codigo, $statusEsperados, true)) {
            throw new \RuntimeException('SMTP ' . $codigo . ' ' . trim($resposta));
        }
    }

    /**
     * @param resource $socket
     * @return array{0: int, 1: string}
     */
    private function readResponse($socket): array
    {
        $response = '';
        $code = 0;

        while (!feof($socket)) {
            $line = fgets($socket, 515);
            if ($line === false) {
                break;
            }

            $response .= $line;
            if (strlen($line) < 4) {
                continue;
            }

            $currentCode = (int) substr($line, 0, 3);
            $separator = substr($line, 3, 1);
            if ($currentCode > 0) {
                $code = $currentCode;
            }

            if ($separator === ' ') {
                break;
            }
        }

        if ($code <= 0) {
            throw new \RuntimeException('Resposta SMTP invalida.');
        }

        return [$code, $response];
    }

    /**
     * @param array<int, string> $destinatarios
     */
    private function buildSmtpMessage(
        array $destinatarios,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName
    ): string {
        $headers = [];
        $headers[] = 'Date: ' . gmdate('D, d M Y H:i:s') . ' +0000';
        $headers[] = 'From: ' . $this->formatFromHeader($fromName, $fromAddress);
        $headers[] = 'To: ' . implode(', ', $destinatarios);
        $headers[] = 'Subject: ' . $this->encodeHeaderValue($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/plain; charset=UTF-8';
        $headers[] = 'Content-Transfer-Encoding: 8bit';

        $body = str_replace(["\r\n", "\r"], "\n", $body);
        $body = str_replace("\n", "\r\n", $body);

        $linhas = explode("\r\n", $body);
        foreach ($linhas as &$linha) {
            if (str_starts_with($linha, '.')) {
                $linha = '.' . $linha;
            }
        }
        unset($linha);

        return implode("\r\n", $headers) . "\r\n\r\n" . implode("\r\n", $linhas);
    }

    /**
     * @param string|array<int, string> $to
     * @return array<int, string>
     */
    private function normalizarDestinatarios(string|array $to): array
    {
        $raw = is_array($to) ? $to : [$to];
        $normalizados = [];

        foreach ($raw as $item) {
            $email = strtolower(trim((string) $item));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            $normalizados[] = $email;
        }

        return array_values(array_unique($normalizados));
    }

    private function encodeHeaderValue(string $value): string
    {
        if ($value === '' || !preg_match('/[^\x20-\x7E]/', $value)) {
            return $value;
        }

        return '=?UTF-8?B?' . base64_encode($value) . '?=';
    }

    private function formatFromHeader(string $name, string $email): string
    {
        $safeName = trim($name);
        if ($safeName === '') {
            return $email;
        }

        return $this->encodeHeaderValue($safeName) . ' <' . $email . '>';
    }

    private function envRaw(string $key): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? null;
    }

    private function envInt(string $key, int $default): int
    {
        $raw = $this->envRaw($key);
        if ($raw === null || $raw === '' || !is_numeric($raw)) {
            return $default;
        }

        return (int) $raw;
    }

    private function envBool(string $key, bool $default): bool
    {
        $raw = $this->envRaw($key);
        if ($raw === null || $raw === '') {
            return $default;
        }

        return filter_var((string) $raw, FILTER_VALIDATE_BOOLEAN);
    }

    private function envString(string $key, string $default): string
    {
        $raw = $this->envRaw($key);
        if ($raw === null) {
            return $default;
        }

        $value = trim((string) $raw);
        return $value !== '' ? $value : $default;
    }
}
