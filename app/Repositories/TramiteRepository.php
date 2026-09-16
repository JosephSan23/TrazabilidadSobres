<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class TramiteRepository
{
    public function __construct(
        private readonly PDO $connection
    ) {
    }

    public function findWithoutSobreByPlaca(string $placa): array
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
            SELECT
                t.id_tramite,
                t.fec_tramite,
                t.id_estado,
                v.id_vehiculo,
                v.placa
            FROM tramite t
            INNER JOIN vehiculo v
                ON v.id_vehiculo = t.id_vehiculo
            LEFT JOIN sobre s
                ON s.id_tramite = t.id_tramite
            WHERE v.placa LIKE :placa
              AND s.id_sobre IS NULL
            ORDER BY t.fec_tramite DESC, t.id_tramite DESC
            SQL
        );

        $statement->execute([
            'placa' => '%' . trim($placa) . '%',
        ]);

        return $statement->fetchAll();
    }
    public function findWithoutSobreById(int $idTramite): ?array
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
            SELECT
                t.id_tramite,
                t.fec_tramite,
                t.id_estado,
                v.id_vehiculo,
                v.placa
            FROM tramite t
            INNER JOIN vehiculo v
                ON v.id_vehiculo = t.id_vehiculo
            LEFT JOIN sobre s
                ON s.id_tramite = t.id_tramite
            WHERE t.id_tramite = :id_tramite
              AND s.id_sobre IS NULL
            LIMIT 1
            SQL
        );

        $statement->execute([
            'id_tramite' => $idTramite,
        ]);

        $tramite = $statement->fetch();

        return $tramite === false ? null : $tramite;
    }
}