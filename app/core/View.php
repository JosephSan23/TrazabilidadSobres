<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class View
{

    public static function render(string $view, array $data = []): void
    {
        if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $view)) {
            throw new RuntimeException('El nombre de la vista no es válido.');
        }

        $viewPath = dirname(__DIR__)
            . '/Views/'
            . str_replace('.', '/', $view)
            . '.php';

        if (!is_file($viewPath)) {
            throw new RuntimeException(
                sprintf('No existe la vista solicitada: %s.', $view)
            );
        }

        extract($data, EXTR_SKIP);

        require $viewPath;
    }

    private function __construct()
    {
    }
}