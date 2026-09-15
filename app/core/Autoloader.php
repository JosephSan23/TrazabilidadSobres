<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public static function register(): void
    {
        spl_autoload_register(
            static function (string $className): void {
                $namespacePrefix = 'App\\';

                if (!str_starts_with($className, $namespacePrefix)) {
                    return;
                }

                $relativeClass = substr($className, strlen($namespacePrefix));

                $filePath = dirname(__DIR__)
                    . DIRECTORY_SEPARATOR
                    . str_replace('\\', DIRECTORY_SEPARATOR, $relativeClass)
                    . '.php';

                if (is_file($filePath)) {
                    require_once $filePath;
                }
            }
        );
    }

    private function __construct()
    {
    }
}