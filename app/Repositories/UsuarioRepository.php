<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class UsuarioRepository
{
    public function __construct(
        private PDO $connection
    ) {
    }

    public function findAll(): array
    {
        $statement = $this->connection->query(
            <<<'SQL'
            SELECT
                u.id_usuario,
                CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo,
                r.rol AS nombre_rol
            FROM usuarios u
            INNER JOIN roles r ON r.id_rol = u.id_rol
            WHERE r.Estado = b'1'
            ORDER BY u.nombres ASC, u.apellidos ASC
            SQL
        );

        return $statement->fetchAll();
    }


    public function findActiveById(int $idUsuario): ?array
    {
        $statement = $this->connection->prepare(
            <<<'SQL'
            SELECT
                u.id_usuario,
                CONCAT(u.nombres, ' ', u.apellidos) AS nombre_completo,
                r.rol AS nombre_rol
            FROM usuarios u
            INNER JOIN roles r
                ON r.id_rol = u.id_rol
            WHERE u.id_usuario = :id_usuario
            AND r.Estado = b'1'
            LIMIT 1
            SQL
        );

        $statement->execute([
            'id_usuario' => $idUsuario,
        ]);

        $usuario = $statement->fetch();

        return $usuario === false ? null : $usuario;
    }
    
}