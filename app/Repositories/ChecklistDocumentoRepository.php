<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ChecklistDocumentoRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function findByTramiteId(int $idTramite): array
    {
        $tiposStatement = $this->connection->query(
            'SELECT Id_TipoDocumento
             FROM TipoDocumentos
             ORDER BY Id_TipoDocumento ASC'
        );

        $tiposDocumento = $tiposStatement->fetchAll();

        $documentos = [];

        $consultaStatement = $this->connection->prepare(
            'CALL Usp_PopupConsultaDocumentosTramite(?, ?)'
        );

        foreach ($tiposDocumento as $tipoDocumento) {
            $consultaStatement->execute([
                $idTramite,
                (int) $tipoDocumento['Id_TipoDocumento'],
            ]);

            foreach ($consultaStatement->fetchAll() as $documento) {
                $idDocumento = (int) $documento['Id_documento'];

                $documentos[$idDocumento] = [
                    'id_documento' => $idDocumento,
                    'nombre_documento' => $documento['Documento'],
                    'tipo_documento' => $documento['TipoDocumento'],
                    'validado' => (int) $documento['flagdb'] > 0,
                ];
            }

            while ($consultaStatement->nextRowset()) {
                // Libera posibles resultados adicionales del procedimiento.
            }
        }

        $consultaStatement->closeCursor();

        return array_values($documentos);
    }
}