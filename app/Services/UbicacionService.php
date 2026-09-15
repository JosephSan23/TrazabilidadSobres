<?php

declare(strict_types = 1);

namespace App\Services;

use App\Repositories\UbicacionRepository;
use InvalidArgumentException;

final class UbicacionService {
    public function __construct(private readonly UbicacionRepository $ubiacionRepository)
    {
    }

    public function listActive(): array 
    {
        return $this->ubiacionRepository->findAllActive();
    }

    public function findById(int $idUbicacion): ?array 
    {
        if ($idUbicacion <= 0) {
            throw new InvalidArgumentException(
                'El identificador de la ubicación no es valido.'
            );
        }

        return $this->ubiacionRepository->findById($idUbicacion);
    }
}