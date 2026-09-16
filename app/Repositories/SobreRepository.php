<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

final class SobreRepository
{
    public function __construct(
        private readonly PDO $connection
    ) {
    }


    public function findAll(): array
    {
        $statement = $this->connection->query(
            'SELECT
                s.id_sobre,
                s.codigo_sobre,
                s.estado,
                s.nombre_responsable,
                s.fecha_creacion,
                s.fecha_ultimo_movimiento,
                t.id_tramite,
                v.placa,
                u.codigo AS codigo_ubicacion,
                u.nombre AS nombre_ubicacion
            FROM sobre s
            INNER JOIN tramite t ON t.id_tramite = s.id_tramite
            INNER JOIN vehiculo v ON v.id_vehiculo = t.id_vehiculo
            LEFT JOIN ubicaciones u ON u.id_ubicacion = s.id_ubicacion
            ORDER BY s.fecha_ultimo_movimiento DESC'
        );

        return $statement->fetchAll();
    }


    public function findById(int $idSobre): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                s.id_sobre,
                s.id_tramite,
                s.codigo_sobre,
                s.estado,
                s.nombre_responsable,
                s.id_usuario_responsable,
                s.observaciones,
                s.fecha_creacion,
                s.fecha_ultimo_movimiento,
                v.placa,
                u.codigo AS codigo_ubicacion,
                u.nombre AS nombre_ubicacion
            FROM sobre s
            INNER JOIN tramite t ON t.id_tramite = s.id_tramite
            INNER JOIN vehiculo v ON v.id_vehiculo = t.id_vehiculo
            LEFT JOIN ubicaciones u ON u.id_ubicacion = s.id_ubicacion
            WHERE s.id_sobre = :id_sobre
            LIMIT 1'
        );

        $statement->execute([
            'id_sobre' => $idSobre,
        ]);

        $sobre = $statement->fetch();

        return $sobre === false ? null : $sobre;
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
        $statement = $this->connection->prepare(
            'CALL sp_custodia_crear_sobre(?, ?, ?, ?, ?, ?, ?)'
        );

        try {
            $statement->execute([
                $idTramite,
                $idUbicacion,
                $nombreResponsable,
                $idUsuarioResponsable,
                $idUsuarioRegistra,
                $nombreUsuarioRegistra,
                $observaciones,
            ]);

            $result = $statement->fetch();

            if ($result === false) {
                throw new RuntimeException(
                    'No fue posible obtener el sobre creado.'
                );
            }

            return [
                'id_sobre' => (int) $result['id_sobre'],
                'codigo_sobre' => (string) $result['codigo_sobre'],
            ];
        } finally {
            $statement->closeCursor();
        }
    }
}