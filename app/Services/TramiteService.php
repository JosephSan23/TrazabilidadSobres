<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TramiteRepository;
use InvalidArgumentException;

final class TramiteService
{
    public function __construct(
        private TramiteRepository $tramiteRepository
    ) {}

    public function listWithoutSobrePaginated(
        array $filters,
        int $page = 1,
        int $perPage = 25
    ): array {
        $page = max(1, $page);
        $perPage = min(max(1, $perPage), 100);

        $total = $this->tramiteRepository->countWithoutSobre($filters);
        $totalPages = max(1, (int) ceil($total / $perPage));

        if ($page > $totalPages) {
            $page = $totalPages;
        }

        $offset = ($page - 1) * $perPage;

        return [
            'records' => $this->tramiteRepository
                ->findWithoutSobrePaginated($filters, $perPage, $offset),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $totalPages,
        ];
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


    public function findWithoutSobreByIds(array $idsTramite): array
    {
        $idsValidos = [];

        foreach ($idsTramite as $idTramite) {
            $idTramite = filter_var(
                $idTramite,
                FILTER_VALIDATE_INT
            );

            if ($idTramite === false || $idTramite <= 0) {
                continue;
            }

            $idsValidos[] = (int) $idTramite;
        }

        $idsValidos = array_values(array_unique($idsValidos));

        if ($idsValidos === []) {
            throw new InvalidArgumentException(
                'Debes seleccionar al menos un trámite válido.'
            );
        }

        return $this->tramiteRepository->findWithoutSobreByIds(
            $idsValidos
        );
    }
}
