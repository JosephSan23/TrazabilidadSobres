<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use RuntimeException;

final class SobreRepository
{
    public function __construct(
        private PDO $connection
    ) {}


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

    public function findByCodigoSobre(string $codigoSobre): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT
                s.id_sobre,
                s.id_tramite,
                s.codigo_sobre,
                s.estado,
                s.nombre_responsable,
                s.id_usuario_responsable,
                v.placa
            FROM sobre s
            INNER JOIN tramite t ON t.id_tramite = s.id_tramite
            INNER JOIN vehiculo v ON v.id_vehiculo = t.id_vehiculo
            WHERE s.codigo_sobre = :codigo_sobre
            LIMIT 1'
        );

        $statement->execute([
            'codigo_sobre' => $codigoSobre,
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

    public function move(
        int $idSobre,
        ?int $idUbicacionDestino,
        ?string $nombreResponsableDestino,
        ?int $idUsuarioResponsableDestino,
        ?int $idUsuarioRegistra,
        ?string $nombreUsuarioRegistra,
        ?string $observaciones
    ): void {
        $statement = $this->connection->prepare(
            'CALL sp_custodia_mover_sobre(?, ?, ?, ?, ?, ?, ?)'
        );

        try {
            $statement->execute([
                $idSobre,
                $idUbicacionDestino,
                $nombreResponsableDestino,
                $idUsuarioResponsableDestino,
                $idUsuarioRegistra,
                $nombreUsuarioRegistra,
                $observaciones,
            ]);
        } finally {
            $statement->closeCursor();
        }
    }

    /**
     * @param array{busqueda?: string} $filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function findPaginated(
        array $filters,
        int $limit,
        int $offset
    ): array {
        [$where, $parameters] = $this->buildListFilters($filters);

        $sql = <<<'SQL'
    SELECT
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
    INNER JOIN tramite t
        ON t.id_tramite = s.id_tramite
    INNER JOIN vehiculo v
        ON v.id_vehiculo = t.id_vehiculo
    LEFT JOIN ubicaciones u
        ON u.id_ubicacion = s.id_ubicacion
    SQL;

        $sql .= $where;
        $sql .= ' ORDER BY s.fecha_ultimo_movimiento DESC, s.id_sobre DESC';
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

    /**
     * @param array{busqueda?: string} $filters
     */
    public function countPaginated(array $filters): int
    {
        [$where, $parameters] = $this->buildListFilters($filters);

        $sql = <<<'SQL'
    SELECT COUNT(*)
    FROM sobre s
    INNER JOIN tramite t
        ON t.id_tramite = s.id_tramite
    INNER JOIN vehiculo v
        ON v.id_vehiculo = t.id_vehiculo
    LEFT JOIN ubicaciones u
        ON u.id_ubicacion = s.id_ubicacion
    SQL;

        $statement = $this->connection->prepare($sql . $where);
        $statement->execute($parameters);

        return (int) $statement->fetchColumn();
    }

    private function buildListFilters(array $filters): array
    {
        $conditions = [];
        $parameters = [];

        $busqueda = trim($filters['busqueda'] ?? '');

        if ($busqueda !== '') {
            $conditions[] = '(
            s.codigo_sobre LIKE :busqueda1
            OR v.placa LIKE :busqueda2
            OR CAST(t.id_tramite AS CHAR) LIKE :busqueda3
        )';

            $valor = '%' . $busqueda . '%';
            $parameters['busqueda1'] = $valor;
            $parameters['busqueda2'] = $valor;
            $parameters['busqueda3'] = $valor;
        }

        return [
            $conditions === []
                ? ''
                : ' WHERE ' . implode(' AND ', $conditions),
            $parameters,
        ];
    }

    public function assignResponsableBulk(
        array $idsSobre,
        int $idUsuarioResponsable,
        string $nombreResponsable,
        int $idUsuarioRegistra,
        string $nombreUsuarioRegistra
    ): int {
        $this->connection->beginTransaction();

        try {
            $statement = $this->connection->prepare(
                'CALL sp_custodia_mover_sobre_core(?, ?, ?, ?, ?, ?, ?)'
            );

            $actualizados = 0;

            foreach ($idsSobre as $idSobre) {
                $statement->execute([
                    (int) $idSobre,
                    null, // sin ubicación
                    $nombreResponsable,
                    $idUsuarioResponsable,
                    $idUsuarioRegistra,
                    $nombreUsuarioRegistra,
                    null, // sin observaciones
                ]);

                $statement->closeCursor();
                $actualizados++;
            }

            $this->connection->commit();

            return $actualizados;
        } catch (\Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }

            throw $exception;
        }
    }

    public function assignResponsible(
        int $idSobre,
        int $idResponsable,
        int $idUsuarioRegistra,
        string $nombreUsuarioRegistra
    ): void {
        $statement = $this->connection->prepare(
            <<<'SQL'
        CALL sp_custodia_asignar_responsable(
            :id_sobre,
            :id_usuario_responsable,
            :id_usuario_registra,
            :nombre_usuario_registra
        )
        SQL
        );

        $statement->execute([
            'id_sobre' => $idSobre,
            'id_usuario_responsable' => $idResponsable,
            'id_usuario_registra' => $idUsuarioRegistra,
            'nombre_usuario_registra' => $nombreUsuarioRegistra,
        ]);

        $statement->closeCursor();
    }

    public function confirmPrintedFicha(
        int $idTramite,
        int $idUsuarioRegistra,
        string $nombreUsuarioRegistra
    ): array {
        $statement = $this->connection->prepare(
            <<<'SQL'
        CALL sp_custodia_confirmar_ficha_impresa(
            :id_tramite,
            :id_usuario_registra,
            :nombre_usuario_registra
        )
        SQL
        );

        $statement->execute([
            'id_tramite' => $idTramite,
            'id_usuario_registra' => $idUsuarioRegistra,
            'nombre_usuario_registra' => $nombreUsuarioRegistra,
        ]);

        $sobre = $statement->fetch();

        $statement->closeCursor();

        if ($sobre === false) {
            throw new RuntimeException(
                'No fue posible confirmar la ficha impresa.'
            );
        }

        return $sobre;
    }
}
