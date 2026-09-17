<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\SobreRepository;
use App\Repositories\TramiteRepository;
use App\Repositories\UbicacionRepository;
use App\Repositories\UsuarioRepository;
use App\Services\FichaCustodiaService;
use App\Services\SobreService;
use App\Services\TramiteService;
use App\Services\UbicacionService;
use App\Services\UsuarioService;
use Throwable;

final class FichaCustodiaController
{
    private FichaCustodiaService $fichaCustodiaService;

    private UsuarioService $usuarioService;

    public function __construct()
    {
        $connection = Database::connection();

        $tramiteService = new TramiteService(
            new TramiteRepository($connection)
        );

        $ubicacionService = new UbicacionService(
            new UbicacionRepository($connection)
        );

        $sobreService = new SobreService(
            new SobreRepository($connection)
        );

        $this->usuarioService = new UsuarioService(
            new UsuarioRepository($connection)
        );

        $this->fichaCustodiaService = new FichaCustodiaService(
            $tramiteService,
            $ubicacionService,
            $sobreService
        );
    }

    /**
     * @param array<string, string> $parameters
     */
    public function generate(array $parameters = []): void
    {
        $idsTramite = $_POST['tramites'] ?? [];
        $idUsuarioRegistra = (int) ($_POST['id_usuario_registra'] ?? 0);
        $ubicacionesPorTramite = $_POST['ubicaciones'] ?? [];

        if (!is_array($ubicacionesPorTramite)) {
            $ubicacionesPorTramite = [];
        }

        if (!is_array($idsTramite)) {
            $idsTramite = [];
        }

        try {
            $usuarioRegistra = $this->usuarioService->findActiveById(
                $idUsuarioRegistra
            );

            if ($usuarioRegistra === null) {
                throw new \RuntimeException(
                    'El usuario seleccionado no existe o está inactivo.'
                );
            }

            $fichas = $this->fichaCustodiaService->generateInitial(
                $idsTramite,
                $ubicacionesPorTramite,
                $idUsuarioRegistra,
                $usuarioRegistra['nombre_completo']
            );

            View::render('fichas.print', [
                'title' => 'Fichas de custodia generadas',
                'fichas' => $fichas,
            ]);
        } catch (Throwable $exception) {
            http_response_code(422);

            echo htmlspecialchars(
                $exception->getMessage(),
                ENT_QUOTES,
                'UTF-8'
            );
        }
    }
}
