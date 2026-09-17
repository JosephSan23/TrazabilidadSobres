<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\HistorialMovimientoRepository;
use InvalidArgumentException;

final class HistorialMovimientoService
{
    public function __construct(
        private HistorialMovimientoRepository $historialRepository
    ) {
    }

    public function listBySobreId(int $idSobre): array
    {
        if ($idSobre <= 0) {
            throw new InvalidArgumentException(
                'El identificador del sobre no es válido.'
            );
        }

        return $this->historialRepository->findBySobreId($idSobre);
    }
}