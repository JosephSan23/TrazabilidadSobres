<?php

declare(strict_types=1);

/** @var array<string, mixed> $sobre */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div>
            <h1>Detalle del sobre</h1>
            <p>
                <?= $escape($sobre['codigo_sobre']) ?>
                -
                Placa <?= $escape($sobre['placa']) ?>
            </p>
        </div>

        <a href="/sobres" class="btn btn-outline-secondary">
            Volver al listado
        </a>
    </div>

    <div class="custodia-card">
        <div class="custodia-card__body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Código del sobre</span>
                    <strong><?= $escape($sobre['codigo_sobre']) ?></strong>
                </div>

                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Estado</span>
                    <strong><?= $escape($sobre['estado']) ?></strong>
                </div>

                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Trámite</span>
                    <strong><?= $escape($sobre['id_tramite']) ?></strong>
                </div>

                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Placa</span>
                    <strong><?= $escape($sobre['placa']) ?></strong>
                </div>

                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Ubicación actual</span>
                    <strong>
                        <?= $escape(
                            $sobre['nombre_ubicacion']
                                ?? 'Sin ubicación asignada'
                        ) ?>
                    </strong>

                    <?php if (!empty($sobre['codigo_ubicacion'])): ?>
                        <span class="text-muted">
                            (<?= $escape($sobre['codigo_ubicacion']) ?>)
                        </span>
                    <?php endif; ?>
                </div>

                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Responsable actual</span>
                    <strong>
                        <?= $escape(
                            $sobre['nombre_responsable']
                                ?? 'Sin responsable asignado'
                        ) ?>
                    </strong>
                </div>

                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Fecha de creación</span>
                    <strong><?= $escape($sobre['fecha_creacion']) ?></strong>
                </div>

                <div class="col-md-6 mb-3">
                    <span class="text-muted d-block">Último movimiento</span>
                    <strong><?= $escape($sobre['fecha_ultimo_movimiento']) ?></strong>
                </div>

                <?php if (!empty($sobre['observaciones'])): ?>
                    <div class="col-12">
                        <span class="text-muted d-block">Observaciones</span>
                        <p class="mb-0"><?= $escape($sobre['observaciones']) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="custodia-card mt-4">
        <div class="custodia-card__body">
            <h2 class="h5">Próximamente</h2>

            <p class="mb-0">
                En esta pantalla añadiremos el checklist de documentos,
                documentos fuera del sobre y la línea de tiempo de movimientos.
            </p>
        </div>
    </div>
</section>