<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ChecklistDocumentoRepository;
use InvalidArgumentException;

final class ChecklistDocumentoService
{
    public function __construct(
        private ChecklistDocumentoRepository $checklistRepository,
        private CustodiaDocumentoService $custodiaDocumentoService
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listBySobre(
        int $idSobre,
        int $idTramite
    ): array {
        if ($idSobre <= 0 || $idTramite <= 0) {
            throw new InvalidArgumentException(
                'La información del sobre o trámite no es válida.'
            );
        }

        $custodiasPorDocumento = [];

        foreach (
            $this->custodiaDocumentoService->listActiveBySobreId($idSobre)
            as $custodia
        ) {
            $custodiasPorDocumento[(int) $custodia['Id_documento']] = $custodia;
        }

        $checklist = $this->checklistRepository->findByTramiteId($idTramite);

        foreach ($checklist as &$documento) {
            $custodia = $custodiasPorDocumento[
                (int) $documento['id_documento']
            ] ?? null;

            if ($custodia !== null) {
                $documento['estado_custodia'] = $custodia['estado'];

                $documento['estado_checklist'] =
                    $custodia['estado'] === 'EXTRAVIADO'
                        ? 'EXTRAVIADO'
                        : 'FUERA_DEL_SOBRE';

                continue;
            }

            $documento['estado_custodia'] = null;
            $documento['estado_checklist'] = $documento['validado']
                ? 'PRESENTE'
                : 'FALTANTE';
        }

        unset($documento);

        return $checklist;
    }
}