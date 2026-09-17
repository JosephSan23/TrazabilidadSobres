<?php

declare(strict_types=1);

namespace App\Core;

final class Url
{
    public static function to(string $path, array $query = []): string
    {
        $scriptName = str_replace(
            '\\',
            '/',
            $_SERVER['SCRIPT_NAME'] ?? '/index.php'
        );

        $basePath = rtrim(
            preg_replace('#/index\.php$#', '', $scriptName) ?? '',
            '/'
        );

        $url = $basePath . '/' . ltrim($path, '/');

        $query = array_filter(
            $query,
            static fn ($value): bool => $value !== null && $value !== ''
        );

        if ($query === []) {
            return $url;
        }

        return $url . '?' . http_build_query($query);
    }

    private function __construct()
    {
    }
}