<?php
declare (strict_types= 1);

namespace App\Services;

use App\Repositories\UsuarioRepository;

final class UsuarioService {
    public function __construct(
        private readonly UsuarioRepository $usuarioRepository
    ) {

    }

    public function listAll(): array {
        return $this->usuarioRepository->findAll();
    }
}


?>