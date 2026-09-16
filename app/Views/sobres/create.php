<?php

declare(strict_types=1);

/** @var array<int, array<string, mixed>> $ubicaciones */
/** @var array<int, array<string, mixed>> $usuarios */
/** @var array<string, mixed> $tramite */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div>
            <h1>Crear sobre de custodia</h1>
            <p>Asocia un sobre a un trámite y registra su ubicación o responsable inicial.</p>
        </div>

        <a href="/sobres" class="btn btn-outline-secondary">
            Volver al listado
        </a>
    </div>

    <div class="custodia-card mb-4">
        <div class="custodia-card__body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Trámite</dt>
                <dd class="col-sm-9"><?= $escape($tramite['id_tramite']) ?></dd>

                <dt class="col-sm-3">Placa</dt>
                <dd class="col-sm-9"><?= $escape($tramite['placa']) ?></dd>

                <dt class="col-sm-3">Fecha de creación</dt>
                <dd class="col-sm-9">
                    <?= $escape($tramite['fec_tramite'] ?? 'Sin fecha registrada') ?>
                </dd>
            </dl>
        </div>
    </div>

    <div class="custodia-card">
        <div class="custodia-card__body">
            <form method="post" action="/sobres">
                <input
                    type="hidden"
                    name="id_tramite"
                    value="<?= $escape($tramite['id_tramite']) ?>"
                >
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_ubicacion" class="form-label">
                            Ubicación inicial
                        </label>

                        <select
                            id="id_ubicacion"
                            name="id_ubicacion"
                            class="form-select"
                        >
                            <option value="">Seleccione una ubicación</option>

                            <?php foreach ($ubicaciones as $ubicacion): ?>
                                <option value="<?= $escape($ubicacion['id_ubicacion']) ?>">
                                    <?= $escape($ubicacion['codigo']) ?>
                                    -
                                    <?= $escape($ubicacion['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="id_usuario_responsable" class="form-label">
                            Responsable inicial
                        </label>

                        <select
                            id="id_usuario_responsable"
                            name="id_usuario_responsable"
                            class="form-select"
                        >
                            <option value="">Seleccione un responsable</option>

                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $escape($usuario['id_usuario']) ?>">
                                    <?= $escape($usuario['nombre_completo']) ?>
                                    -
                                    <?= $escape($usuario['nombre_rol']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 mb-3">
                        <label for="observaciones" class="form-label">
                            Observaciones
                        </label>

                        <textarea
                            id="observaciones"
                            name="observaciones"
                            class="form-control"
                            rows="4"
                            maxlength="5000"
                        ></textarea>
                    </div>
                </div>

                <p class="text-muted">
                    Debes seleccionar una ubicación, un responsable o ambos.
                </p>

                <button type="submit" class="btn btn-primary">
                    Crear sobre
                </button>
            </form>
        </div>
    </div>
</section>