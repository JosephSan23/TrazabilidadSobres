<?php

declare(strict_types=1);

use App\Components\Pagination;
use App\Core\Url;

/** @var array{placa: string, id_tramite: string} $filters */
/** @var array<int, array<string, mixed>> $tramites */
/** @var int $totalTramites */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $perPage */
/** @var array<int, array<string, mixed>> $usuarios */
/** @var array<int, array<string, mixed>> $ubicaciones */

$escape = static fn(mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$firstRecord = $totalTramites === 0
    ? 0
    : (($currentPage - 1) * $perPage) + 1;

$lastRecord = min(
    $currentPage * $perPage,
    $totalTramites
);
?>

<link
    rel="stylesheet"
    href="<?= $escape(Url::to('/css/components/sidebar.css')) ?>">
<link
    rel="stylesheet"
    href="<?= $escape(Url::to('/css/index.css')) ?>">

<?php require __DIR__ . '../../../Components/sidebar.php'; ?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div>
            <h1>Bienvenidos</h1>
            <p>
                Aca es donde podras filtrar los tramites que ya cuenten con un sobre fisico y generarle la nueva ficha.
            </p>
        </div>
    </div>

    <div class="custodia-card mb-4">
        <div class="custodia-card__body">
            <form
                method="get"
                action="<?= $escape(Url::to('/tramites/sin-sobre')) ?>"
                class="row align-items-end" data-pending-filter-form>
                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="placa" class="form-label">
                        Placa
                    </label>

                    <input
                        type="search"
                        id="placa"
                        name="placa"
                        class="form-control"
                        value="<?= $escape($filters['placa']) ?>"
                        maxlength="8"
                        placeholder="Ejemplo: ABC123">
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <label for="id_tramite" class="form-label">
                        Número de trámite
                    </label>

                    <input
                        type="search"
                        id="id_tramite"
                        name="id_tramite"
                        class="form-control"
                        value="<?= $escape($filters['id_tramite']) ?>"
                        inputmode="numeric"
                        placeholder="Ejemplo: 12540">
                </div>

                <div class="col-md-auto">
                    <a
                        href="<?= $escape(Url::to('/tramites/sin-sobre')) ?>"
                        class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="custodia-card">
        <div class="custodia-card__body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">
                    <strong><?= $escape($totalTramites) ?></strong>
                    trámite(s) pendiente(s) de enlazar.
                </p>

                <?php if ($totalTramites > 0): ?>
                    <small class="text-muted">
                        Mostrando <?= $escape($firstRecord) ?>
                        a <?= $escape($lastRecord) ?>
                    </small>
                <?php endif; ?>
            </div>

            <?php if ($tramites === []): ?>
                <p class="mb-0 text-muted">
                    No hay trámites pendientes que coincidan con los filtros.
                </p>
            <?php else: ?>
                <div class="table-responsive">
                    <form
                        method="post"
                        action="<?= $escape(Url::to('/sobres/generar-fichas')) ?>"
                        class="row align-items-end mb-3"
                        data-bulk-fichas-form>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <p class="mb-0">
                                <strong data-selected-count>0</strong>
                                trámite(s) seleccionado(s).
                            </p>
                        </div>

                        <div class="col-md-auto">
                            <button
                                type="button"
                                class="btn btn-primary"
                                data-open-generation-modal
                                disabled>
                                Generar fichas de custodia
                            </button>
                        </div>

                        <dialog class="custodia-modal" data-generation-modal>
                            <div class="custodia-modal__header">
                                <div>
                                    <h2>Generar fichas de custodia</h2>
                                    <p>
                                        Confirma las placas y selecciona la ubicación inicial
                                        que se registrará para este grupo de sobres.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    class="btn-close"
                                    aria-label="Cerrar"
                                    data-close-generation-modal></button>
                            </div>

                            <div class="custodia-modal__body">
                                <h3 class="h6">Ubicación inicial por sobre</h3>

                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Placa</th>
                                                <th>Trámite</th>
                                                <th>Ubicación inicial</th>
                                            </tr>
                                        </thead>

                                        <tbody data-selected-plates></tbody>
                                    </table>
                                </div>

                                <template data-location-select-template>
                                    <select class="form-select form-select-sm" required>
                                        <?php foreach ($ubicaciones as $ubicacion): ?>
                                            <option
                                                value="<?= $escape($ubicacion['id_ubicacion']) ?>"
                                                <?= $ubicacion['codigo'] === 'PEND-UBICAR'
                                                    ? 'selected'
                                                    : '' ?>>
                                                <?= $escape($ubicacion['codigo']) ?>
                                                -
                                                <?= $escape($ubicacion['nombre']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </template>

                                <div class="mb-3">
                                    <label
                                        for="id_usuario_registra"
                                        class="form-label">
                                        Generado por
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
                            </div>

                            <div class="custodia-modal__footer">
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    data-close-generation-modal>
                                    Cancelar
                                </button>

                                <button type="submit" class="btn btn-primary">
                                    Confirmar y generar fichas
                                </button>
                            </div>
                        </dialog>
                    </form>
                    <table class="table table-striped table-hover mb-3">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input
                                        type="checkbox"
                                        aria-label="Seleccionar los trámites de esta página"
                                        data-select-current-page>
                                </th>
                                <th>Trámite</th>
                                <th>Placa</th>
                                <th>Fecha de creación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($tramites as $tramite): ?>
                                <tr>
                                    <td class="text-center">
                                        <input
                                            type="checkbox"
                                            value="<?= $escape($tramite['id_tramite']) ?>"
                                            data-tramite-placa="<?= $escape($tramite['placa']) ?>"
                                            aria-label="Seleccionar trámite <?= $escape($tramite['id_tramite']) ?>"
                                            data-tramite-selection>
                                    </td>

                                    <td>
                                        <?= $escape($tramite['id_tramite']) ?>
                                    </td>

                                    <td>
                                        <?= $escape($tramite['placa']) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $tramite['fec_tramite']
                                                ?? 'Sin fecha registrada'
                                        ) ?>
                                    </td>

                                    <td>
                                        <?= $escape(
                                            $tramite['nombre_estado']
                                                ?? 'Estado no identificado'
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php Pagination::render(
                    $currentPage,
                    $totalPages,
                    '/tramites/sin-sobre',
                    $filters
                ); ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script
    src="<?= $escape(
                Url::to('/js/tramites-pendientes.js', [
                    'v' => '2',
                ])
            ) ?>"></script>