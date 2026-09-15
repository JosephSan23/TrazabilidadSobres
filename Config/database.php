<?php

declare(strict_types=1);

function environmentValue(string $key, mixed $default = null): mixed
{
    $value = getenv($key);

    if ($value !== false) {
        return $value;
    }

    return $_ENV[$key] ?? $_SERVER[$key] ?? $default;
}

$environment = environmentValue('APP_ENV', 'development');

$database = [
    'host' => environmentValue('DB_HOST'),
    'port' => environmentValue('DB_PORT', 3306),
    'name' => environmentValue('DB_NAME'),
    'username' => environmentValue('DB_USERNAME'),
    'password' => environmentValue('DB_PASSWORD'),
    'charset' => environmentValue('DB_CHARSET', 'utf8mb4'),
];

$hasEnvironmentConfiguration = !empty($database['host'])
    && !empty($database['name'])
    && !empty($database['username']);

if (!$hasEnvironmentConfiguration && $environment === 'development') {
    $localConfigPath = __DIR__ . '/local.php';

    if (file_exists($localConfigPath)) {
        $localConfig = require $localConfigPath;
        $database = array_merge($database, $localConfig['database'] ?? []);
    }
}

$requiredKeys = ['host', 'port', 'name', 'username', 'password', 'charset'];

foreach ($requiredKeys as $key) {
    if ($database[$key] === null || $database[$key] === '') {
        throw new RuntimeException(
            sprintf('Falta la variable de entorno de base de datos: %s.', $key)
        );
    }
}

return $database;