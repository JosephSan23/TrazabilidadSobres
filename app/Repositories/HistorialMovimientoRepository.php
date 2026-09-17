<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class HistorialMovimientoRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function findBySobreId(int $idSobre): array
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
            SELECT
                hm.id_movimiento,
                hm.tipo_evento,
                hm.fecha_evento,
                hm.estado_anterior,
                hm.estado_nuevo,
                hm.nombre_responsable_origen,
                hm.nombre_responsable_destino,
                hm.nombre_usuario_registra,
                hm.observaciones,
                d.Documento AS nombre_documento,
                uo.codigo AS codigo_ubicacion_origen,
                uo.nombre AS nombre_ubicacion_origen,
                ud.codigo AS codigo_ubicacion_destino,
                ud.nombre AS nombre_ubicacion_destino
            FROM historial_movimientos hm
            LEFT JOIN Documentos d
                ON d.Id_documento = hm.id_documento
            LEFT JOIN ubicaciones uo
                ON uo.id_ubicacion = hm.id_ubicacion_origen
            LEFT JOIN ubicaciones ud
                ON ud.id_ubicacion = hm.id_ubicacion_destino
            WHERE hm.id_sobre = :id_sobre
            ORDER BY hm.fecha_evento DESC, hm.id_movimiento DESC
            SQL
        );

        $statement->execute([
            'id_sobre' => $idSobre,
        ]);

        return $statement->fetchAll();
    }
}