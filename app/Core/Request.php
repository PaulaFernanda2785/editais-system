<?php

declare(strict_types=1);

namespace App\Core;

class Request
{
    private string $method;
    private string $path;
    private array $input;
    private array $server;

    /**
     * @var array<string, string>
     */
    private array $routeParams = [];

    public function __construct(string $method, string $path, array $input = [], array $server = [])
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->input = $input;
        $this->server = $server;
    }

    public static function capture(): self
    {
        $method = (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        $basePath = self::detectBasePath();
        if (
            $basePath !== ''
            && $basePath !== '/'
            && ($path === $basePath || str_starts_with($path, $basePath . '/'))
        ) {
            $path = substr($path, strlen($basePath)) ?: '/';
        }

        return new self($method, $path, array_merge($_GET, $_POST), $_SERVER);
    }

    private static function detectBasePath(): string
    {
        $documentRoot = self::normalizePath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $scriptFilename = self::normalizePath((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));

        if ($documentRoot !== '' && $scriptFilename !== '' && str_starts_with($scriptFilename, $documentRoot)) {
            $relativeScript = substr($scriptFilename, strlen($documentRoot));
            $relativeDir = str_replace('\\', '/', dirname($relativeScript));

            if ($relativeDir === '/' || $relativeDir === '.' || $relativeDir === '\\') {
                return '';
            }

            return '/' . trim($relativeDir, '/');
        }

        $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptDir = str_replace('\\', '/', dirname($scriptName));
        if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\') {
            return '';
        }

        return '/' . trim($scriptDir, '/');
    }

    private static function normalizePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        if ($path === '') {
            return '';
        }

        $real = realpath($path);
        if ($real === false) {
            return $path;
        }

        return str_replace('\\', '/', $real);
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function input(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return $this->input;
        }

        return $this->input[$key] ?? $default;
    }

    public function ip(): string
    {
        $forwarded = (string) ($this->server['HTTP_X_FORWARDED_FOR'] ?? '');
        if ($forwarded !== '') {
            return trim(explode(',', $forwarded)[0]);
        }

        return (string) ($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /**
     * @param array<string, string> $params
     */
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }

    public function routeParam(string $key, mixed $default = null): mixed
    {
        return $this->routeParams[$key] ?? $default;
    }

    public function session(?string $key = null, mixed $default = null): mixed
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            return $default;
        }

        if ($key === null) {
            return $_SESSION;
        }

        if (!str_contains($key, '.')) {
            return $_SESSION[$key] ?? $default;
        }

        $segments = explode('.', $key);
        $value = $_SESSION;

        foreach ($segments as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    public function setSession(string $key, mixed $value): void
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }

        if (!str_contains($key, '.')) {
            $_SESSION[$key] = $value;
            return;
        }

        $segments = explode('.', $key);
        $current = &$_SESSION;

        foreach ($segments as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                $current[$segment] = [];
            }
            $current = &$current[$segment];
        }

        $current = $value;
    }

    public function pullSession(string $key, mixed $default = null): mixed
    {
        $value = $this->session($key, $default);
        $this->forgetSession($key);
        return $value;
    }

    public function forgetSession(string $key): void
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            return;
        }

        if (!str_contains($key, '.')) {
            unset($_SESSION[$key]);
            return;
        }

        $segments = explode('.', $key);
        $last = array_pop($segments);
        $current = &$_SESSION;

        foreach ($segments as $segment) {
            if (!isset($current[$segment]) || !is_array($current[$segment])) {
                return;
            }
            $current = &$current[$segment];
        }

        if ($last !== null) {
            unset($current[$last]);
        }
    }

    public function invalidateSession(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
