<?php

declare(strict_types=1);

use App\Core\App;

define('BASE_PATH', dirname(__DIR__));

if (!file_exists(BASE_PATH . '/vendor/autoload.php')) {
    http_response_code(500);
    echo 'Dependencias nao encontradas. Execute "composer install".';
    exit;
}

require BASE_PATH . '/vendor/autoload.php';

$app = new App();
$app->run();
