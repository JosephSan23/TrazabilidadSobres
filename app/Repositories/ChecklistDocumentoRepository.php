<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class ChecklistDocumentoRepository
{
    public function __construct(
        private PDO $connection
    ) {}

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

    public function synchronizeValidations(
        int $idTramite,
        array $idsPermitidos,
        array $documentosMarcados,
        int $idUsuarioRegistra
    ): void {
        $marcados = array_flip($documentosMarcados);

        $statement = $this->connection->prepare(
            <<<'SQL'
        CALL Usp_PopupCrearValidacionDocumento(
            :id_tramite,
            :id_documento,
            :id_usuario,
            :validacion
        )
        SQL
        );

        $this->connection->beginTransaction();

        try {
            foreach ($idsPermitidos as $idDocumento) {
                $statement->execute([
                    'id_tramite' => $idTramite,
                    'id_documento' => $idDocumento,
                    'id_usuario' => $idUsuarioRegistra,
                    'validacion' => isset($marcados[$idDocumento]) ? 1 : 0,
                ]);
                
                $statement->closeCursor();
            }

            $this->connection->commit();
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }
}
