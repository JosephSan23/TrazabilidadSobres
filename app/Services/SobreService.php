<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\SobreRepository;
use InvalidArgumentException;

final class SobreService
{
    public function __construct(
        private SobreRepository $sobreRepository
    ) {}

    public function listAll(): array
    {
        return $this->sobreRepository->findAll();
    }

    /**
     * @param array{busqueda?: string} $filters
     *
     * @return array{
     *     records: array<int, array<string, mixed>>,
     *     total: int,
     *     page: int,
     *     per_page: int,
     *     total_pages: int
     * }
     */
    public function listPaginated(
        array $filters,
        int $page = 1,
        int $perPage = 25
    ): array {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);

        $total = $this->sobreRepository->countPaginated($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        return [
            'records' => $this->sobreRepository->findPaginated(
                $filters,
                $perPage,
                $offset
            ),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
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

    public function findByCodigo(string $codigoSobre): ?array {
        $codigo_sobre = trim($codigoSobre);

        if ($codigoSobre === '') {
            throw new InvalidArgumentException(
                'El código del sobre no es valido.'
            );
        }

        return $this->sobreRepository->findByCodigoSobre($codigoSobre);
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

        if ($idUbicacion !== null && $idUbicacion <= 0) {
            throw new InvalidArgumentException(
                'La ubicacion seleccionada no es valida.'
            );
        }

        if ($idUsuarioResponsable !== null && $idUsuarioResponsable <= 0) {
            throw new InvalidArgumentException(
                'El usuario responsable no es valido.'
            );
        }

        if ($idUsuarioRegistra !== null && $idUsuarioRegistra <= 0) {
            throw new InvalidArgumentException(
                'El usuario que registra no es valido.'
            );
        }


        $nombreResponsable = $this->normalizeText($nombreResponsable);
        $nombreUsuarioRegistra = $this->normalizeText($nombreUsuarioRegistra);
        $observaciones = $this->normalizeText($observaciones);

        if ($idUbicacion == null && $nombreResponsable == null) {
            throw new InvalidArgumentException(
                'Debes indicar una ubicación o un responsable para el sobre.'
            );
        }

        if ($nombreUsuarioRegistra === null) {
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
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    public function move(
        int $idSobre,
        ?int $idUbicacionDestino,
        ?string $nombreResponsableDestino,
        ?int $idUsuarioResponsableDestino,
        ?int $idUsuarioRegistra,
        ?string $nombreUsuarioRegistra,
        ?string $observaciones
    ): void {
        if ($idSobre <= 0) {
            throw new InvalidArgumentException(
                'El identificador del sobre no es válido.'
            );
        }

        if ($idUbicacionDestino !== null && $idUbicacionDestino <= 0) {
            throw new InvalidArgumentException(
                'La ubicación destino no es válida.'
            );
        }

        if (
            $idUsuarioResponsableDestino !== null
            && $idUsuarioResponsableDestino <= 0
        ) {
            throw new InvalidArgumentException(
                'El responsable seleccionado no es válido.'
            );
        }

        if ($idUsuarioRegistra !== null && $idUsuarioRegistra <= 0) {
            throw new InvalidArgumentException(
                'El usuario que registra no es válido.'
            );
        }

        $nombreResponsableDestino = $this->normalizeText(
            $nombreResponsableDestino
        );

        $nombreUsuarioRegistra = $this->normalizeText(
            $nombreUsuarioRegistra
        );

        $observaciones = $this->normalizeText($observaciones);

        if (
            $idUbicacionDestino === null
            && $nombreResponsableDestino === null
        ) {
            throw new InvalidArgumentException(
                'Debes indicar una ubicación o un responsable destino.'
            );
        }

        $this->sobreRepository->move(
            $idSobre,
            $idUbicacionDestino,
            $nombreResponsableDestino,
            $idUsuarioResponsableDestino,
            $idUsuarioRegistra,
            $nombreUsuarioRegistra,
            $observaciones
        );
    }
}
