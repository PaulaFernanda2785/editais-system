<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = (string) self::env('DB_CONNECTION', 'mysql');
        $host = (string) self::env('DB_HOST', '127.0.0.1');
        $port = (string) self::env('DB_PORT', '3306');
        $database = (string) self::env('DB_DATABASE', '');
        $charset = (string) self::env('DB_CHARSET', 'utf8mb4');
        $username = (string) self::env('DB_USERNAME', 'root');
        $password = (string) self::env('DB_PASSWORD', '');

        if ($database === '') {
            throw new RuntimeException('DB_DATABASE nao definido no .env.');
        }

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $driver,
            $host,
            $port,
            $database,
            $charset
        );

        try {
            self::$connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new RuntimeException('Falha de conexao com o banco de dados.', 0, $exception);
        }

        return self::$connection;
    }

    public static function disconnect(): void
    {
        self::$connection = null;
    }

    private static function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
    }
}
