<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UbicacionRepository;
use InvalidArgumentException;

final class UbicacionService
{
    public function __construct(private UbicacionRepository $ubicacionRepository) {}

    public function listActive(): array
    {
        return $this->ubicacionRepository->findAllActive();
    }

    public function findById(int $idUbicacion): ?array
    {
        if ($idUbicacion <= 0) {
            throw new InvalidArgumentException(
                'El identificador de la ubicación no es valido.'
            );
        }

        return $this->ubicacionRepository->findById($idUbicacion);
    }

    public function findActiveByCode(string $codigo): ?array
    {
        $codigo = trim($codigo);

        if ($codigo === '') {
            throw new InvalidArgumentException(
                'El código de ubicación no es válido.'
            );
        }

        return $this->ubicacionRepository->findActiveByCode(
            $codigo
        );
    }
}
