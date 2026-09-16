<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TramiteRepository;
use InvalidArgumentException;

final class TramiteService
{
    public function __construct(
        private readonly TramiteRepository $tramiteRepository
    ) {
    }

    public function searchWithoutSobreByPlaca(string $placa): array
    {
        $placa = trim($placa);

        if ($placa === '') {
            throw new InvalidArgumentException(
                'Debes ingresar una placa para realizar la búsqueda.'
            );
        }

        if (mb_strlen($placa) < 2) {
            throw new InvalidArgumentException(
                'Ingresa al menos dos caracteres de la placa.'
            );
        }

        return $this->tramiteRepository->findWithoutSobreByPlaca($placa);
    }

    public function findWithoutSobreById(int $idTramite): ?array
    {
        if ($idTramite <= 0) {
            throw new InvalidArgumentException(
                'El identificador del trámite no es válido.'
            );
        }

        return $this->tramiteRepository->findWithoutSobreById($idTramite);
    }
}