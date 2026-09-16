<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;
use App\Repositories\TramiteRepository;
use App\Services\TramiteService;
use InvalidArgumentException;

final class TramiteController
{
    private TramiteService $tramiteService;

    public function __construct()
    {
        $this->tramiteService = new TramiteService(
            new TramiteRepository(Database::connection())
        );
    }

    public function searchWithoutSobre(array $parameters = []): void
    {
        $placa = trim((string) ($_GET['placa'] ?? ''));
        $tramites = [];
        $error = null;

        if ($placa !== '') {
            try {
                $tramites = $this->tramiteService
                    ->searchWithoutSobreByPlaca($placa);
            } catch (InvalidArgumentException $exception) {
                $error = $exception->getMessage();
            }
        }

        View::render('tramites.sin-sobre', [
            'title' => 'Trámites sin sobre',
            'placa' => $placa,
            'tramites' => $tramites,
            'error' => $error,
        ]);
    }
}