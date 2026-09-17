<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class TramiteRepository
{
    public function __construct(
        private PDO $connection
    ) {}

    public function findWithoutSobrePaginated(
        array $filters,
        int $limit,
        int $offset
    ): array {
        [$where, $parameters] = $this->buildWithoutSobreFilters($filters);

        $sql = <<<'SQL'
        SELECT
            t.id_tramite,
            t.fec_tramite,
            t.id_estado,
            e.descripcion AS nombre_estado,
            v.id_vehiculo,
            v.placa
        FROM tramite t
        INNER JOIN vehiculo v
            ON v.id_vehiculo = t.id_vehiculo
        LEFT JOIN estado e
        ON e.id_estado = t.id_estado
        LEFT JOIN sobre s
        ON s.id_tramite = t.id_tramite
        SQL;

        $sql .= $where;
        $sql .= ' ORDER BY t.fec_tramite DESC, t.id_tramite DESC';
        $sql .= ' LIMIT :limit OFFSET :offset';

        $statement = $this->connection->prepare($sql);

        foreach ($parameters as $name => $value) {
            $statement->bindValue($name, $value, PDO::PARAM_STR);
        }

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);

        $statement->execute();

        return $statement->fetchAll();
    }

    public function countWithoutSobre(array $filters): int
    {
        [$where, $parameters] = $this->buildWithoutSobreFilters($filters);

        $sql = <<<'SQL'
        SELECT COUNT(*)
        FROM tramite t
        INNER JOIN vehiculo v
            ON v.id_vehiculo = t.id_vehiculo
        LEFT JOIN sobre s
            ON s.id_tramite = t.id_tramite
        SQL;

        $statement = $this->connection->prepare($sql . $where);

        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    public function findWithoutSobreById(int $idTramite): ?array
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
            SELECT
                t.id_tramite,
                t.fec_tramite,
                t.id_estado,
                e.descripcion AS nombre_estado,
                v.id_vehiculo,
                v.placa
            FROM tramite t
            INNER JOIN vehiculo v
                ON v.id_vehiculo = t.id_vehiculo
            LEFT JOIN estado e
                ON e.id_estado = t.id_estado
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

    private function buildWithoutSobreFilters(array $filters): array
    {
        $conditions = [
            's.id_sobre IS NULL',
        ];

        $parameters = [];

        $placa = trim($filters['placa'] ?? '');

        if ($placa !== '') {
            $conditions[] = 'v.placa LIKE :placa';
            $parameters['placa'] = '%' . $placa . '%';
        }

        $idTramite = trim($filters['id_tramite'] ?? '');

        if ($idTramite !== '') {
            $conditions[] = 'CAST(t.id_tramite AS CHAR) LIKE :id_tramite';
            $parameters['id_tramite'] = '%' . $idTramite . '%';
        }

        return [
            ' WHERE ' . implode(' AND ', $conditions),
            $parameters,
        ];
    }

    /**
     * @param array<int, int> $idsTramite
     *
     * @return array<int, array<string, mixed>>
     */
    public function findWithoutSobreByIds(array $idsTramite): array
    {
        if ($idsTramite === []) {
            return [];
        }

        $placeholders = implode(
            ', ',
            array_fill(0, count($idsTramite), '?')
        );

        $statement = $this->connection->prepare(
            <<<SQL
        SELECT
            t.id_tramite,
            v.placa,
            t.id_estado,
            e.descripcion AS nombre_estado
        FROM tramite t
        INNER JOIN vehiculo v
            ON v.id_vehiculo = t.id_vehiculo
        LEFT JOIN estado e
            ON e.id_estado = t.id_estado
        LEFT JOIN sobre s
            ON s.id_tramite = t.id_tramite
        WHERE t.id_tramite IN ($placeholders)
          AND s.id_sobre IS NULL
        ORDER BY t.id_tramite ASC
        SQL
        );

        $statement->execute($idsTramite);

        return $statement->fetchAll();
    }
}
