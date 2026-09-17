<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\CustodiaDocumentoRepository;
use InvalidArgumentException;

final class CustodiaDocumentoService
{
    public function __construct(
        private CustodiaDocumentoRepository $custodiaDocumentoRepository
    ) {
    }

    public function listActiveBySobreId(int $idSobre): array
    {
        if ($idSobre <= 0) {
            throw new InvalidArgumentException(
                'El identificador del sobre no es válido.'
            );
        }

        return $this->custodiaDocumentoRepository
            ->findActiveBySobreId($idSobre);
    }
}