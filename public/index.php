<?php

declare(strict_types= 1);

use App\Core\Autoloader;
use App\Core\Router;

require_once dirname(__DIR__) . '/app/Core/Autoloader.php';

Autoloader::register();

$router = require dirname(__DIR__) . '/routes/web.php';

if (!$router instanceof Router) {
    throw new RuntimeException(
        'El archivo routes/web.php debe retornar una instancia de App\\Core\\Router.'
    );
}

$router->dispatch(
    $_SERVER['REQUEST_METHOD'] ?? 'GET',
    $_SERVER['REQUEST_URI'] ?? '/'
);