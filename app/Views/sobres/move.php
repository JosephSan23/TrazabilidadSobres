<?php

declare(strict_types=1);

/** @var array<string, mixed> $sobre */
/** @var array<int, array<string, mixed>> $ubicaciones */
/** @var array<int, array<string, mixed>> $usuarios */

$escape = static fn(mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div>
            <h1>Mover sobre</h1>
            <p>
                <?= $escape($sobre['codigo_sobre']) ?>
                -
                Placa <?= $escape($sobre['placa']) ?>
            </p>
        </div>

        <a
            href="/sobres/<?= $escape($sobre['id_sobre']) ?>"
            class="btn btn-outline-secondary">
            Volver al detalle
        </a>
    </div>

    <div class="custodia-card mb-4">
        <div class="custodia-card__body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Ubicación actual</dt>
                <dd class="col-sm-9">
                    <?= $escape(
                        $sobre['nombre_ubicacion'] ?? 'Sin ubicación asignada'
                    ) ?>
                </dd>

                <dt class="col-sm-3">Responsable actual</dt>
                <dd class="col-sm-9">
                    <?= $escape(
                        $sobre['nombre_responsable'] ?? 'Sin responsable asignado'
                    ) ?>
                </dd>
            </dl>
        </div>
    </div>

    <div class="custodia-card">
        <div class="custodia-card__body">
            <form
                method="post"
                action="/sobres/<?= $escape($sobre['id_sobre']) ?>/mover">
                <input
                    type="hidden"
                    name="id_sobre"
                    value="<?= $escape($sobre['id_sobre']) ?>">

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="id_ubicacion_destino" class="form-label">
                            Nueva ubicación
                        </label>

                        <select
                            id="id_ubicacion_destino"
                            name="id_ubicacion_destino"
                            class="form-select">
                            <option value="">
                                Mantener ubicación actual
                            </option>

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
                        <label
                            for="id_usuario_responsable_destino"
                            class="form-label">
                            Nuevo responsable
                        </label>

                        <select
                            id="id_usuario_responsable_destino"
                            name="id_usuario_responsable_destino"
                            class="form-select">
                            <option value="">
                                Mantener responsable actual
                            </option>

                            <?php foreach ($usuarios as $usuario): ?>
                                <option value="<?= $escape($usuario['id_usuario']) ?>">
                                    <?= $escape($usuario['nombre_completo']) ?>
                                    -
                                    <?= $escape($usuario['nombre_rol']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="id_usuario_registra" class="form-label">
                            Movimiento registrado por
                        </label>

                        <select
                            id="id_usuario_registra"
                            name="id_usuario_registra"
                            class="form-select"
                            required>
                            <option value="">Seleccione el usuario</option>

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
                            maxlength="5000"></textarea>
                    </div>
                </div>

                <p class="text-muted">
                    Selecciona al menos una nueva ubicación o un nuevo responsable.
                </p>

                <button type="submit" class="btn btn-primary">
                    Registrar movimiento
                </button>
            </form>
        </div>
    </div>
</section>