<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

class View
{
    public static function make(string $view, array $data = []): string
    {
        $viewPath = self::resolvePath($view);

        if (!file_exists($viewPath)) {
            throw new RuntimeException(sprintf('View "%s" nao encontrada.', $viewPath));
        }

        extract($data, EXTR_SKIP);

        ob_start();
        require $viewPath;
        return (string) ob_get_clean();
    }

    public static function exists(string $view): bool
    {
        return file_exists(self::resolvePath($view));
    }

    public static function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    private static function resolvePath(string $view): string
    {
        $normalized = str_replace(['.', '\\'], '/', $view);
        if (!str_ends_with($normalized, '.php')) {
            $normalized .= '.php';
        }

        return BASE_PATH . '/resources/views/' . ltrim($normalized, '/');
    }
}
