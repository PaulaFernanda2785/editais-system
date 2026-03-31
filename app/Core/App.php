<?php

declare(strict_types=1);

namespace App\Core;

use App\Services\LogService;
use Throwable;

class App
{
    private Router $router;
    private bool $debug = false;

    public function __construct()
    {
        $this->loadEnvironment();
        $this->configureTimezone();
        $this->configureSession();
        $this->configureErrorHandling();

        $this->router = new Router();
        $this->loadRoutes();
    }

    public function run(): void
    {
        $request = Request::capture();
        $response = new Response();

        $this->router->dispatch($request, $response);
    }

    private function loadEnvironment(): void
    {
        if (file_exists(BASE_PATH . '/.env')) {
            Env::loadFromFile(BASE_PATH . '/.env');
            return;
        }

        if (file_exists(BASE_PATH . '/.env.example')) {
            Env::loadFromFile(BASE_PATH . '/.env.example');
        }
    }

    private function configureTimezone(): void
    {
        $timezone = (string) $this->env('APP_TIMEZONE', 'UTC');
        if (!@date_default_timezone_set($timezone)) {
            date_default_timezone_set('UTC');
        }
    }

    private function configureSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $sessionName = (string) $this->env('SESSION_NAME', 'editais_session');
        $secureCookie = filter_var($this->env('SESSION_SECURE_COOKIE', false), FILTER_VALIDATE_BOOLEAN);
        $sameSite = (string) $this->env('SESSION_SAME_SITE', 'Lax');

        session_name($sessionName);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $secureCookie,
            'httponly' => true,
            'samesite' => $sameSite,
        ]);
        session_start();
    }

    private function configureErrorHandling(): void
    {
        $this->debug = filter_var($this->env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN);

        error_reporting(E_ALL);
        ini_set('display_errors', $this->debug ? '1' : '0');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $exception): void {
            $this->handleException($exception);
        });

        register_shutdown_function(function (): void {
            $error = error_get_last();

            if ($error === null) {
                return;
            }

            if (!in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
                return;
            }

            $exception = new \ErrorException(
                $error['message'],
                0,
                $error['type'],
                $error['file'],
                $error['line']
            );

            $this->handleException($exception);
        });
    }

    private function handleException(Throwable $exception): void
    {
        (new LogService())->error(
            'app.exception',
            $exception->getMessage(),
            [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $this->debug ? $exception->getTraceAsString() : null,
            ]
        );

        if (headers_sent()) {
            echo $this->debug ? nl2br(htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8')) : 'Erro interno.';
            return;
        }

        $response = new Response();
        $response->setStatusCode(500);

        if ($this->debug) {
            $response->setContent(
                '<h1>Erro Interno</h1><pre>' . htmlspecialchars((string) $exception, ENT_QUOTES, 'UTF-8') . '</pre>'
            );
        } elseif (View::exists('errors/500')) {
            $response->setContent(View::make('errors/500'));
        } else {
            $response->setContent('Erro interno do servidor.');
        }

        $response->send();
    }

    private function loadRoutes(): void
    {
        $router = $this->router;
        require BASE_PATH . '/routes/web.php';
    }

    private function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}
