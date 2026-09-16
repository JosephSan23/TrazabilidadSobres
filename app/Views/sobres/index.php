<?php

declare(strict_types=1);
/** @var array<int, array<string, mixed>> $sobres */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div>
            <h1>Sobres de custodia</h1>
            <p>Consulta la ubicación, responsable y estado de los sobres asociados a cada trámite.</p>
        </div>

        <a href="/sobres/nuevo" class="btn btn-primary">
            Crear nuevo sobre
        </a>
    </div>

    <div class="custodia-card">
        <div class="custodia-card__body">
            <table
                id="tabla-sobres"
                class="table table-striped table-hover"
                data-custodia-table
            >
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Placa</th>
                        <th>Trámite</th>
                        <th>Ubicación</th>
                        <th>Responsable</th>
                        <th>Estado</th>
                        <th>Último movimiento</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($sobres as $sobre): ?>
                        <tr>
                            <td><?= $escape($sobre['codigo_sobre']) ?></td>
                            <td><?= $escape($sobre['placa']) ?></td>
                            <td><?= $escape($sobre['id_tramite']) ?></td>
                            <td>
                                <?= $escape(
                                    $sobre['nombre_ubicacion']
                                        ?? 'Sin ubicación'
                                ) ?>
                            </td>
                            <td>
                                <?= $escape(
                                    $sobre['nombre_responsable']
                                        ?? 'Sin responsable'
                                ) ?>
                            </td>
                            <td><?= $escape($sobre['estado']) ?></td>
                            <td><?= $escape($sobre['fecha_ultimo_movimiento']) ?></td>
                            <td class="text-center">
                                <a
                                    href="/sobres/<?= $escape($sobre['id_sobre']) ?>"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>