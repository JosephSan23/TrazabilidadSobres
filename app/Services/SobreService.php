<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SobreRepository;
use InvalidArgumentException;

final class SobreService
{
    public function __construct(
        private readonly SobreRepository $sobreRepository
    ) {

    }

    public function listAll(): array
    {
        return $this->sobreRepository->findAll();
    }

    public function findById(int $idSobre): ?array
    {
        if ($idSobre <= 0) {
            throw new InvalidArgumentException(
                'El identificador del sobre no es válido.'
            );
        }

        return $this->sobreRepository->findById($idSobre);
    }

    public function create(
        int $idTramite,
        ?int $idUbicacion,
        ?string $nombreResponsable,
        ?int $idUsuarioResponsable,
        ?int $idUsuarioRegistra,
        ?string $nombreUsuarioRegistra,
        ?string $observaciones
    ): array {
        if ($idTramite <= 0) {
            throw new InvalidArgumentException(
                'El tramite seleccionado no es valido.'
            );
        }

        if($idUbicacion !== null && $idUbicacion <= 0) {
            throw new InvalidArgumentException(
                'La ubicacion seleccionada no es valida.'
            );
        }

        if($idUsuarioResponsable !== null && $idUsuarioResponsable <= 0) {
            throw new InvalidArgumentException(
                'El usuario responsable no es valido.'
            );
        }

        if($idUsuarioRegistra !== null && $idUsuarioRegistra <= 0) {
            throw new InvalidArgumentException(
                'El usuario que registra no es valido.'
            );
        }


        $nombreResponsable = $this->normalizeText($nombreResponsable);
        $nombreUsuarioRegistra = $this->normalizeText($nombreUsuarioRegistra);
        $observaciones = $this->normalizeText($observaciones);

        if($idUbicacion == null && $nombreResponsable == null) {
            throw new InvalidArgumentException(
                'Debes indicar una ubicación o un responsable para el sobre.'
            );
        }

        if($nombreUsuarioRegistra === null) {
            throw new InvalidArgumentException(
                'Debes indicar quien registra el movimiento.'
            );
        }

        return $this->sobreRepository->create(
            $idTramite,
            $idUbicacion,
            $nombreResponsable,
            $idUsuarioResponsable,
            $idUsuarioRegistra,
            $nombreUsuarioRegistra,
            $observaciones
        );
    }

    private function normalizeText(?string $value): ?string
    {
        if($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}

?>