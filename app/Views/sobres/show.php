<?php

declare(strict_types=1);

/** @var array<string, mixed> $sobre */
/** @var array<int, array<string, mixed>> $historialMovimientos */
/** @var array<int, array<string, mixed>> $custodiasActivas */
/** @var array<int, array<string, mixed>> $checklistDocumentos */

$escape = static fn(mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$statusClass = [
    'PRESENTE' => 'bg-success',
    'FUERA_DEL_SOBRE' => 'bg-warning text-dark',
    'EXTRAVIADO' => 'bg-danger',
    'FALTANTE' => 'bg-secondary',
];

$statusLabel = [
    'PRESENTE' => 'Disponible',
    'FUERA_DEL_SOBRE' => 'Fuera del sobre',
    'EXTRAVIADO' => 'Extraviado',
    'FALTANTE' => 'Faltante',
];
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

        <a
            href="/sobres/<?= $escape($sobre['id_sobre']) ?>/mover"
            class="btn btn-primary">
            Mover sobre
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
            <h2 class="h5 mb-3">Checklist de documentos</h2>

            <?php if ($checklistDocumentos === []): ?>
                <p class="mb-0 text-muted">
                    No se encontraron documentos requeridos para este trámite.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th class="text-center">Estado físico</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($checklistDocumentos as $documento): ?>
                                <?php
                                $estado = $documento['estado_checklist'];
                                $badgeClass = $statusClass[$estado] ?? 'bg-secondary';
                                $label = $statusLabel[$estado] ?? $estado;
                                ?>

                                <tr>
                                    <td>
                                        <?= $escape($documento['nombre_documento']) ?>
                                    </td>

                                    <td>
                                        <?= $escape($documento['tipo_documento']) ?>
                                    </td>

                                    <td class="text-center">
                                        <span class="badge <?= $escape($badgeClass) ?>">
                                            <?= $escape($label) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="custodia-card mt-4">
        <div class="custodia-card__body">
            <h2 class="h5 mb-3">Documentos fuera del sobre</h2>

            <?php if ($custodiasActivas === []): ?>
                <p class="mb-0 text-muted">
                    No hay documentos actualmente fuera de este sobre.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Estado</th>
                                <th>Ubicación</th>
                                <th>Custodio</th>
                                <th>Salida</th>
                                <th>Devolución esperada</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($custodiasActivas as $custodia): ?>
                                <tr>
                                    <td>
                                        <?= $escape($custodia['nombre_documento']) ?>
                                    </td>

                                    <td>
                                        <?= $escape($custodia['estado']) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $custodia['nombre_ubicacion']
                                                ?? 'Sin ubicación asignada'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $custodia['nombre_custodio']
                                                ?? 'Sin custodio asignado'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape($custodia['fecha_salida']) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $custodia['fecha_devolucion_esperada']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $custodia['observaciones']
                                                ?? '-'
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="custodia-card mt-4">
        <div class="custodia-card__body">
            <h2 class="h5 mb-3">Línea de tiempo</h2>

            <?php if ($historialMovimientos === []): ?>
                <p class="mb-0 text-muted">
                    Este sobre todavía no tiene movimientos registrados.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Evento</th>
                                <th>Documento</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Registrado por</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($historialMovimientos as $movimiento): ?>
                                <tr>
                                    <td>
                                        <?= $escape($movimiento['fecha_evento']) ?>
                                    </td>

                                    <td>
                                        <?= $escape($movimiento['tipo_evento']) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movimiento['nombre_documento']
                                                ?? 'Movimiento de sobre'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movimiento['nombre_ubicacion_origen']
                                                ?? $movimiento['nombre_responsable_origen']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movimiento['nombre_ubicacion_destino']
                                                ?? $movimiento['nombre_responsable_destino']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movimiento['nombre_usuario_registra']
                                                ?? '-'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $movimiento['observaciones']
                                                ?? '-'
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>