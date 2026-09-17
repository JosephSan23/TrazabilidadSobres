<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\TramiteRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\UbicacionRepository;
use App\Services\TramiteService;
use App\Services\UsuarioService;
use App\Services\UbicacionService;
use InvalidArgumentException;

final class TramiteController
{
    private TramiteService $tramiteService;

    private UsuarioService $usuarioService;

    private UbicacionService $ubicacionService;

    public function __construct()
    {
        $connection = Database::connection();

        $this->tramiteService = new TramiteService(
            new TramiteRepository($connection)
        );

        $this->usuarioService = new UsuarioService(
            new UsuarioRepository($connection)
        );

        $this->ubicacionService = new UbicacionService(
            new UbicacionRepository($connection)
        );
    }
    public function searchWithoutSobre(array $parameters = []): void
    {
        $filters = [
            'placa' => trim((string) ($_GET['placa'] ?? '')),
            'id_tramite' => trim((string) ($_GET['id_tramite'] ?? '')),
        ];

        $page = filter_input(
            INPUT_GET,
            'page',
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'default' => 1,
                    'min_range' => 1,
                ],
            ]
        );

        $result = $this->tramiteService->listWithoutSobrePaginated(
            $filters,
            (int) $page
        );

        View::render('tramites.sin-sobre', [
            'title' => 'Trámites sin sobre',
            'filters' => $filters,
            'tramites' => $result['records'],
            'totalTramites' => $result['total'],
            'currentPage' => $result['page'],
            'totalPages' => $result['total_pages'],
            'perPage' => $result['per_page'],
            'usuarios' => $this->usuarioService->listAll(),
            'ubicaciones' => $this->ubicacionService->listActive(),
        ]);
    }
}
