<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

final class FichaCustodiaService
{
    public function __construct(
        private TramiteService $tramiteService,
        private UbicacionService $ubicacionService,
        private SobreService $sobreService
    ) {}

    public function generateInitial(
        array $idsTramite,
        array $ubicacionesPorTramite,
        int $idUsuarioRegistra,
        string $nombreUsuarioRegistra
    ): array {
        if ($idUsuarioRegistra <= 0) {
            throw new InvalidArgumentException(
                'Debes seleccionar el usuario que genera las fichas.'
            );
        }

        $nombreUsuarioRegistra = trim($nombreUsuarioRegistra);

        if ($nombreUsuarioRegistra === '') {
            throw new InvalidArgumentException(
                'El nombre del usuario que genera las fichas no es válido.'
            );
        }

        $ubicacionPendiente = $this->ubicacionService
            ->findActiveByCode('PEND-UBICAR');

        if ($ubicacionPendiente === null) {
            throw new RuntimeException(
                'No existe la ubicación activa PEND-UBICAR.'
            );
        }

        $tramites = $this->tramiteService
            ->findWithoutSobreByIds($idsTramite);

        if ($tramites === []) {
            throw new RuntimeException(
                'Los trámites seleccionados ya tienen ficha o no existen.'
            );
        }

        $sobresCreados = [];
        $ubicacionesConsultadas = [];

        foreach ($tramites as $tramite) {
            $idTramite = (int) $tramite['id_tramite'];

            $idUbicacion = filter_var(
                $ubicacionesPorTramite[$idTramite] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($idUbicacion === false || $idUbicacion <= 0) {
                $ubicacionInicial = $ubicacionPendiente;
            } else {
                if (!isset($ubicacionesConsultadas[$idUbicacion])) {
                    $ubicacionesConsultadas[$idUbicacion] = $this
                        ->ubicacionService
                        ->findById($idUbicacion);
                }

                $ubicacionInicial = $ubicacionesConsultadas[$idUbicacion];

                if (
                    $ubicacionInicial === null
                    || !(bool) $ubicacionInicial['activo']
                ) {
                    throw new RuntimeException(
                        sprintf(
                            'La ubicación seleccionada para el trámite %d no existe o está inactiva.',
                            $idTramite
                        )
                    );
                }
            }

            $sobreCreado = $this->sobreService->create(
                $idTramite,
                (int) $ubicacionInicial['id_ubicacion'],
                null,
                null,
                $idUsuarioRegistra,
                $nombreUsuarioRegistra,
                'Generación inicial de ficha de custodia.'
            );

            $sobresCreados[] = [
                'id_sobre' => $sobreCreado['id_sobre'],
                'codigo_sobre' => $sobreCreado['codigo_sobre'],
                'id_tramite' => $idTramite,
                'placa' => (string) $tramite['placa'],
                'ubicacion' => (string) $ubicacionInicial['nombre'],
            ];
        }

        return $sobresCreados;
    }
}
