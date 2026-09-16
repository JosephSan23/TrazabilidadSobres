<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\SobreRepository;
use App\Repositories\UbicacionRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\TramiteRepository;
use App\Services\SobreService;
use App\Services\UbicacionService;
use App\Services\UsuarioService;
use App\Services\TramiteService;

final class SobreController
{
    private SobreService $sobreService;

    private UbicacionService $ubicacionService;

    private UsuarioService $usuarioService;

    private TramiteService $tramiteService;

    public function __construct()
    {
        $connection = Database::connection();

        $this->sobreService = new SobreService(
            new SobreRepository($connection)
        );

        $this->ubicacionService = new UbicacionService(
            new UbicacionRepository($connection)
        );

        $this->usuarioService = new UsuarioService(
            new UsuarioRepository($connection)
        );

        $this->tramiteService = new TramiteService(
            new TramiteRepository($connection)
        );
    }
    public function index(array $parameters = []): void
    {
        View::render('sobres.index', [
            'title' => 'Sobres de custodia',
            'sobres' => $this->sobreService->listAll(),
        ]);
    }


    public function show(array $parameters = []): void
    {
        $idSobre = (int) ($parameters['id_sobre'] ?? 0);

        if ($idSobre <= 0) {
            http_response_code(400);

            echo 'El identificador del sobre no es válido.';
            return;
        }

        $sobre = $this->sobreService->findById($idSobre);

        if ($sobre === null) {
            http_response_code(404);

            echo 'No se encontró el sobre solicitado.';

            return;
        }

        View::render('sobres.show', [
            'title' => 'Detalle del sobre',
            'sobre' => $sobre,
        ]);
    }

    public function create(array $parameters = []): void
    {
        $idTramite = (int) ($_GET['id_tramite'] ?? 0);

        if ($idTramite <= 0) {
            http_response_code(400);

            echo 'Debes seleccionar un trámite antes de enlazar un sobre.';

            return;
        }

        $tramite = $this->tramiteService->findWithoutSobreById($idTramite);

        if ($tramite === null) {
            http_response_code(404);

            echo 'El trámite no existe o ya tiene un sobre asociado.';

            return;
        }

        View::render('sobres.create', [
            'title' => 'Enlazar sobre físico',
            'tramite' => $tramite,
            'ubicaciones' => $this->ubicacionService->listActive(),
            'usuarios' => $this->usuarioService->listAll(),
        ]);
    }

    // public function store(array $parameters = []): void
    // {
    //     $idTramite = (int) ($_POST['id_tramite'] ?? 0);
    //     $idUbicacion = $this->nullablePositiveInteger(
    //         $_POST['id_ubicacion'] ?? null
    //     );
    //     $idUsuarioResponsable = $this->nullablePositiveInteger(
    //         $_POST['id_usuario_responsable'] ?? null
    //     );
    //     $observaciones = trim((string) ($_POST['observaciones'] ?? ''));

    //     $usuarioActual = $this->currentUser();

    //     $sobreCreado = $this->sobreService->create(
    //         $idTramite,
    //         $idUbicacion,
    //         $usuarioActual['nombre_completo'],
    //         $idUsuarioResponsable,
    //         $usuarioActual['id_usuario'],
    //         $usuarioActual['nombre_completo'],
    //         $observaciones === '' ? null : $observaciones
    //     );

    //     header(
    //         'Location: /sobres/' . $sobreCreado['id_sobre'],
    //         true,
    //         302
    //     );

    //     exit;
    // }

    // private function nullablePositiveInteger(mixed $value): ?int
    // {
    //     if ($value === null || $value === '') {
    //         return null;
    //     }

    //     $number = filter_var($value, FILTER_VALIDATE_INT);

    //     if ($number === false || $number <= 0) {
    //         return null;
    //     }

    //     return $number;
    // }
}