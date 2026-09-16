<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $ubicaciones */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div>
            <h1>Ubicaciones de Sobres y Documentos</h1>
            <p>Administra los lugares internos y externos donde se encuentran sobres o documentos.</p>
        </div>

        <a href="/ubicaciones/nueva" class="btn btn-primary">
            Crear nueva ubicación
        </a>
    </div>

    <div class="custodia-card">
        <div class="custodia-card__body">
            <table
                id="tabla-ubicaciones"
                class="table table-striped table-hover"
                data-custodia-table
            >
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($ubicaciones as $ubicacion): ?>
                        <tr>
                            <td><?= $escape($ubicacion['codigo']) ?></td>
                            <td><?= $escape($ubicacion['nombre']) ?></td>
                            <td><?= $escape($ubicacion['tipo']) ?></td>
                            <td><?= $escape($ubicacion['descripcion'] ?? '') ?></td>
                            <td class="text-center">
                                <a
                                    href="/ubicaciones/<?= $escape($ubicacion['id_ubicacion']) ?>"
                                    class="btn btn-sm btn-outline-primary"
                                >
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>