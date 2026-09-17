<?php

declare(strict_types=1);

function environmentValue(string $key): ?string
{
    $value = getenv($key);

    if ($value !== false) {
        return $value;
    }

    if (isset($_ENV[$key])) {
        return (string) $_ENV[$key];
    }

    if (isset($_SERVER[$key])) {
        return (string) $_SERVER[$key];
    }

    return null;
}

$environment = environmentValue('APP_ENV') ?? 'development';

$database = [
    'host' => null,
    'port' => 3306,
    'name' => null,
    'username' => null,
    'password' => null,
    'charset' => 'utf8mb4',
];

$localConfigPath = __DIR__ . '/local.php';

if ($environment === 'development' && file_exists($localConfigPath)) {
    $localConfig = require $localConfigPath;

    if (isset($localConfig['database']) && is_array($localConfig['database'])) {
        $database = array_replace(
            $database,
            $localConfig['database']
        );
    }
}

$environmentKeys = [
    'host' => 'DB_HOST',
    'port' => 'DB_PORT',
    'name' => 'DB_NAME',
    'username' => 'DB_USERNAME',
    'password' => 'DB_PASSWORD',
    'charset' => 'DB_CHARSET',
];

foreach ($environmentKeys as $configKey => $environmentKey) {
    $value = environmentValue($environmentKey);

    if ($value !== null) {
        $database[$configKey] = $value;
    }
}

foreach (['host', 'name', 'username', 'password'] as $key) {
    if ($database[$key] === null) {
        throw new RuntimeException(
            sprintf('Falta la configuración de base de datos: %s.', $key)
        );
    }
}

return $database;