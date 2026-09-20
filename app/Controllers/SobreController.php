<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\SobreRepository;
use App\Repositories\UbicacionRepository;
use App\Repositories\UsuarioRepository;
use App\Repositories\TramiteRepository;
use App\Repositories\HistorialMovimientoRepository;
use App\Repositories\CustodiaDocumentoRepository;
use App\Repositories\ChecklistDocumentoRepository;
use App\Services\SobreService;
use App\Services\UbicacionService;
use App\Services\UsuarioService;
use App\Services\TramiteService;
use App\Services\HistorialMovimientoService;
use App\Services\CustodiaDocumentoService;
use App\Services\ChecklistDocumentoService;

final class SobreController
{
    private SobreService $sobreService;

    private UbicacionService $ubicacionService;

    private UsuarioService $usuarioService;

    private TramiteService $tramiteService;

    private HistorialMovimientoService $historialMovimientoService;

    private CustodiaDocumentoService $custodiaDocumentoService;

    private ChecklistDocumentoService $checklistDocumentoService;

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

        $this->historialMovimientoService = new HistorialMovimientoService(
            new HistorialMovimientoRepository($connection)
        );

        $this->custodiaDocumentoService = new CustodiaDocumentoService(
            new CustodiaDocumentoRepository($connection)
        );

        $this->checklistDocumentoService = new ChecklistDocumentoService(
            new ChecklistDocumentoRepository($connection),
            $this->custodiaDocumentoService
        );
    }
    /**
     * @param array<string, string> $parameters
     */
    public function index(array $parameters = []): void
    {
        $filters = [
            'busqueda' => trim((string) ($_GET['busqueda'] ?? '')),
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

        $result = $this->sobreService->listPaginated(
            $filters,
            (int) $page
        );

        View::render('sobres.index', [
            'title' => 'Sobres de custodia',
            'sobres' => $result['records'],
            'filters' => $filters,
            'totalSobres' => $result['total'],
            'currentPage' => $result['page'],
            'totalPages' => $result['total_pages'],
            'perPage' => $result['per_page'],
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
            'historialMovimientos' => $this->historialMovimientoService->listBySobreId($idSobre),
            'custodiasActivas' => $this->custodiaDocumentoService
                ->listActiveBySobreId($idSobre),
            'checklistDocumentos' => $this->checklistDocumentoService->listBySobre(
                $idSobre,
                (int) $sobre['id_tramite']
            ),
        ]);
    }

    public function buscarPorCodigo(array $parameters = []): void
{
    header('Content-Type: application/json; charset=UTF-8');

    $codigo = trim((string) ($_GET['codigo'] ?? ''));

    if ($codigo === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Debes enviar un código.']);
        return;
    }

    try {
        $sobre = $this->sobreService->findByCodigo($codigo);
    } catch (\InvalidArgumentException $exception) {
        http_response_code(400);
        echo json_encode(['error' => $exception->getMessage()]);
        return;
    }

    if ($sobre === null) {
        http_response_code(404);
        echo json_encode(['error' => 'No se encontró ningún sobre con ese código.']);
        return;
    }

    echo json_encode([
        'id_sobre' => (int) $sobre['id_sobre'],
        'id_tramite' => (int) $sobre['id_tramite'],
        'codigo_sobre' => $sobre['codigo_sobre'],
        'placa' => $sobre['placa'],
        'estado' => $sobre['estado'],
        'responsable' => $sobre['nombre_responsable'] ?? 'Sin responsable',
    ]);
}

    public function moveForm(array $parameters = []): void
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

        View::render('sobres.move', [
            'title' => 'Mover sobre',
            'sobre' => $sobre,
            'ubicaciones' => $this->ubicacionService->listActive(),
            'usuarios' => $this->usuarioService->listAll(),
        ]);
    }

    /**
     * @param array<string, string> $parameters
     */
    public function move(array $parameters = []): void
    {
        $idSobre = (int) ($parameters['id_sobre'] ?? 0);

        $idUbicacionDestino = $this->nullablePositiveInteger(
            $_POST['id_ubicacion_destino'] ?? null
        );

        $idUsuarioResponsableDestino = $this->nullablePositiveInteger(
            $_POST['id_usuario_responsable_destino'] ?? null
        );

        $idUsuarioRegistra = $this->nullablePositiveInteger(
            $_POST['id_usuario_registra'] ?? null
        );

        $observaciones = trim((string) ($_POST['observaciones'] ?? ''));

        try {
            if ($idUsuarioRegistra === null) {
                throw new \InvalidArgumentException(
                    'Debes seleccionar quién registra el movimiento.'
                );
            }

            $usuarioRegistra = $this->usuarioService->findActiveById(
                $idUsuarioRegistra
            );

            if ($usuarioRegistra === null) {
                throw new \RuntimeException(
                    'El usuario que registra no existe o está inactivo.'
                );
            }

            $nombreResponsableDestino = null;

            if ($idUsuarioResponsableDestino !== null) {
                $responsableDestino = $this->usuarioService->findActiveById(
                    $idUsuarioResponsableDestino
                );

                if ($responsableDestino === null) {
                    throw new \RuntimeException(
                        'El responsable seleccionado no existe o está inactivo.'
                    );
                }

                $nombreResponsableDestino = $responsableDestino['nombre_completo'];
            }

            if (
                $idUbicacionDestino === null
                && $idUsuarioResponsableDestino === null
            ) {
                throw new \InvalidArgumentException(
                    'Selecciona una nueva ubicación o un nuevo responsable.'
                );
            }

            $this->sobreService->move(
                $idSobre,
                $idUbicacionDestino,
                $nombreResponsableDestino,
                $idUsuarioResponsableDestino,
                $idUsuarioRegistra,
                $usuarioRegistra['nombre_completo'],
                $observaciones === '' ? null : $observaciones
            );

            header('Location: /sobres/' . $idSobre, true, 302);
            exit;
        } catch (\Throwable $exception) {
            http_response_code(422);

            echo htmlspecialchars(
                $exception->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );
        }
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

    // PENDIENTE VER COMO FUNCIONA EL LOGIN Y QUE RECIBE $_SESSION

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

    private function nullablePositiveInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = filter_var($value, FILTER_VALIDATE_INT);

        if ($number === false || $number <= 0) {
            return null;
        }

        return $number;
    }
}
