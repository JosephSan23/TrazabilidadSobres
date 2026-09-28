<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;
use RuntimeException;

final class FichaCustodiaService
{
    public function __construct(
        private TramiteService $tramiteService,
        private SobreService $sobreService
    ) {}

    public function prepareForPrint(array $idsTramite): array
    {
        $idsUnicos = $this->normalizeIds($idsTramite);

        $tramites = $this->tramiteService->findWithoutSobreByIds(
            $idsUnicos
        );

        if (count($tramites) !== count($idsUnicos)) {
            throw new RuntimeException(
                'Uno o más trámites ya tienen una ficha de custodia.'
            );
        }

        $fichas = [];

        foreach ($tramites as $tramite) {
            $idTramite = (int) $tramite['id_tramite'];
            $placa = strtoupper(
                trim((string) ($tramite['placa'] ?? ''))
            );

            if ($placa === '') {
                throw new RuntimeException(
                    'Uno de los trámites seleccionados no tiene placa.'
                );
            }

            $fichas[] = [
                'id_tramite' => $idTramite,
                'placa' => $placa,
                'codigo_sobre' => $idTramite . '-' . $placa,
            ];
        }

        return $fichas;
    }

    public function confirmPrinted(
        array $idsTramite,
        int $idUsuarioRegistra,
        string $nombreUsuarioRegistra
    ): array {
        if ($idUsuarioRegistra < 1) {
            throw new InvalidArgumentException(
                'El usuario que confirma la impresión es inválido.'
            );
        }

        $nombreUsuarioRegistra = trim($nombreUsuarioRegistra);

        if ($nombreUsuarioRegistra === '') {
            throw new InvalidArgumentException(
                'El nombre del usuario que confirma la impresión es inválido.'
            );
        }

        $idsUnicos = $this->normalizeIds($idsTramite);

        /*
         * Se valida de nuevo porque alguien pudo haber confirmado
         * una ficha mientras el usuario la estaba imprimiendo.
         */
        $tramitesSinSobre = $this->tramiteService
            ->findWithoutSobreByIds($idsUnicos);

        if (count($tramitesSinSobre) !== count($idsUnicos)) {
            throw new RuntimeException(
                'Uno o más trámites ya tienen una ficha confirmada.'
            );
        }

        $sobresCreados = [];

        foreach ($idsUnicos as $idTramite) {
            $sobresCreados[] = $this->sobreService
                ->confirmPrintedFicha(
                    $idTramite,
                    $idUsuarioRegistra,
                    $nombreUsuarioRegistra
                );
        }

        return $sobresCreados;
    }

    private function normalizeIds(array $idsTramite): array
    {
        $idsUnicos = [];

        foreach ($idsTramite as $idTramite) {
            $idTramite = filter_var(
                $idTramite,
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]]
            );

            if ($idTramite === false) {
                throw new InvalidArgumentException(
                    'Uno de los trámites seleccionados es inválido.'
                );
            }

            $idsUnicos[(int) $idTramite] = (int) $idTramite;
        }

        $idsUnicos = array_values($idsUnicos);

        if ($idsUnicos === []) {
            throw new InvalidArgumentException(
                'Selecciona al menos un trámite.'
            );
        }

        return $idsUnicos;
    }
}
