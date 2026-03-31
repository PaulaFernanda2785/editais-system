<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

class LogService
{
    private string $logsDirectory;

    public function __construct(?string $logsDirectory = null)
    {
        $this->logsDirectory = $logsDirectory ?? BASE_PATH . '/storage/logs';

        if (!is_dir($this->logsDirectory)) {
            mkdir($this->logsDirectory, 0775, true);
        }
    }

    public function info(string $context, string $message, array $data = []): void
    {
        $this->log('INFO', $context, $message, $data);
    }

    public function warning(string $context, string $message, array $data = []): void
    {
        $this->log('WARNING', $context, $message, $data);
    }

    public function error(string $context, string $message, array $data = []): void
    {
        $this->log('ERROR', $context, $message, $data);
    }

    public function critical(string $context, string $message, array $data = []): void
    {
        $this->log('CRITICAL', $context, $message, $data);
    }

    public function log(string $level, string $context, string $message, array $data = []): void
    {
        $context = substr($context, 0, 100);
        $message = substr($message, 0, 255);
        $line = sprintf(
            '[%s] [%s] [%s] %s',
            date('Y-m-d H:i:s'),
            strtoupper($level),
            $context,
            $message
        );

        if ($data !== []) {
            $encoded = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $line .= ' ' . ($encoded !== false ? $encoded : '{"error":"json_encode_fail"}');
        }

        file_put_contents(
            $this->logsDirectory . '/app-' . date('Y-m-d') . '.log',
            $line . PHP_EOL,
            FILE_APPEND
        );

        $this->persistInDatabase(strtoupper($level), $context, $message, $data);
    }

    private function persistInDatabase(string $level, string $context, string $message, array $data): void
    {
        try {
            $stmt = Database::connection()->prepare(
                'INSERT INTO logs_sistema (nivel, contexto, mensagem, dados_json, criado_em)
                 VALUES (:nivel, :contexto, :mensagem, :dados_json, NOW())'
            );

            $stmt->execute([
                'nivel' => $level,
                'contexto' => $context,
                'mensagem' => $message,
                'dados_json' => $data !== []
                    ? json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                    : null,
            ]);
        } catch (Throwable) {
            // Em bootstrap inicial, o banco pode ainda nao estar disponivel.
        }
    }
}
