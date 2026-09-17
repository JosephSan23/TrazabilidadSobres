<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\UsuarioRepository;

final class UsuarioService
{
    public function __construct(
        private UsuarioRepository $usuarioRepository
    ) {}

    public function listAll(): array
    {
        return $this->usuarioRepository->findAll();
    }

    public function findActiveById(int $idUsuario): ?array
    {
        if ($idUsuario <= 0) {
            throw new \InvalidArgumentException(
                'El identificador del usuario no es válido.'
            );
        }

        return $this->usuarioRepository->findActiveById($idUsuario);
    }
}
