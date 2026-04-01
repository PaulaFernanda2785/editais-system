<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(405);
    echo 'Este script deve ser executado via linha de comando.';
    exit;
}

require __DIR__ . '/scripts/processar-alertas-proativos.php';
