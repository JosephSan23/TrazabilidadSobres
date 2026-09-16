<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\UbicacionRepository;
use App\Services\UbicacionService;

final class UbicacionController 
{
    private UbicacionService $ubicacionService;

    public function __construct()
    {
        $repository = new UbicacionRepository(Database::connection());

        $this->ubicacionService = new UbicacionService($repository);
    }

    public function index(array $parameters = []): void
    {
        View::render('ubicaciones.index', [
            'title' => 'Ubicaciones custodia',
            'ubicaciones' => $this->ubicacionService->listActive(),
        ]);
    }
}

?>