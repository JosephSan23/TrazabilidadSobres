<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CustodiaDocumentoRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findActiveBySobreId(int $idSobre): array
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
            SELECT
                sdc.id_custodia,
                sdc.estado,
                sdc.nombre_custodio,
                sdc.fecha_salida,
                sdc.fecha_devolucion_esperada,
                sdc.observaciones,
                d.Id_documento,
                d.Documento AS nombre_documento,
                u.codigo AS codigo_ubicacion,
                u.nombre AS nombre_ubicacion
            FROM sobre_documento_custodia sdc
            INNER JOIN Documentos d
                ON d.Id_documento = sdc.id_documento
            LEFT JOIN ubicaciones u
                ON u.id_ubicacion = sdc.id_ubicacion
            WHERE sdc.id_sobre = :id_sobre
              AND sdc.vigente = 1
            ORDER BY sdc.fecha_salida DESC, sdc.id_custodia DESC
            SQL
        );

        $statement->execute([
            'id_sobre' => $idSobre,
        ]);

        return $statement->fetchAll();
    }
}