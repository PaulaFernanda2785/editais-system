<?php

declare(strict_types=1);

namespace App\Helpers;

class ExternalLinkHelper
{
    public static function resolveForDetail(
        ?string $url,
        ?string $numeroEdital,
        ?string $orgaoNome,
        ?string $codigoFonte,
        string $tipo
    ): ?string {
        $normalizada = self::sanitize($url);
        if ($normalizada !== null && !self::isPlaceholderHost($normalizada)) {
            return $normalizada;
        }

        $partes = array_filter([
            trim((string) $numeroEdital),
            trim((string) $orgaoNome),
            trim((string) $codigoFonte),
            $tipo === 'edital' ? 'arquivo edital pdf' : 'detalhe edital',
        ], static fn(string $item): bool => $item !== '');

        if ($partes === []) {
            return null;
        }

        return 'https://www.google.com/search?q=' . rawurlencode(implode(' ', $partes));
    }

    public static function sanitize(?string $url): ?string
    {
        if ($url === null) {
            return null;
        }

        $url = trim($url);
        if ($url === '') {
            return null;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . ltrim($url, '/');
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    public static function isPlaceholderHost(string $url): bool
    {
        $host = strtolower((string) (parse_url($url, PHP_URL_HOST) ?? ''));
        if ($host === '') {
            return true;
        }

        if ($host === 'example.local' || $host === 'localhost' || $host === '127.0.0.1') {
            return true;
        }

        return str_ends_with($host, '.local');
    }
}
