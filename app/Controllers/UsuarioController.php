<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Repositories\UsuarioRepository;
use App\Services\UsuarioService;

final class UsuarioController
{
    private UsuarioService $usuarioService;

    public function __construct()
    {
        $connection = Database::connection();

        $this->usuarioService = new UsuarioService(
            new UsuarioRepository($connection)
        );
    }

    public function disponibles(array $parameters = []): void
    {
        header('Content-Type: application/json; charset=UTF-8');

        $usuarios = $this->usuarioService->listAll();

        echo json_encode($usuarios);
    }
}
