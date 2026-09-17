<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UbicacionRepository
{
    public function __construct(
        private PDO $connection
    ) {}

    public function findAllActive(): array
    {
        $statement = $this->connection->query(
            'SELECT
                id_ubicacion,
                codigo,
                nombre,
                descripcion,
                tipo,
                id_ubicacion_padre,
                activo
            FROM ubicaciones
            WHERE activo = 1
            ORDER BY tipo ASC, nombre ASC'
        );

        return $statement->fetchAll();
    }

    public function findById(int $idUbicacion): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                id_ubicacion,
                codigo,
                nombre,
                descripcion,
                tipo,
                id_ubicacion_padre,
                activo
            FROM ubicaciones
            WHERE id_ubicacion = :id_ubicacion
            LIMIT 1'
        );

        $statement->execute([
            'id_ubicacion' => $idUbicacion,
        ]);

        $ubicacion = $statement->fetch();

        return $ubicacion === false ? null : $ubicacion;
    }

    public function findActiveByCode(string $codigo): ?array
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
        SELECT
            id_ubicacion,
            codigo,
            nombre,
            descripcion,
            tipo,
            activo
        FROM ubicaciones
        WHERE codigo = :codigo
          AND activo = 1
        LIMIT 1
        SQL
        );

        $statement->execute([
            'codigo' => $codigo,
        ]);

        $ubicacion = $statement->fetch();

        return $ubicacion === false ? null : $ubicacion;
    }
}
