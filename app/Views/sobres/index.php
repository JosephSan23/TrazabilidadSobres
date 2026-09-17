<?php

declare(strict_types=1);

use App\Components\Pagination;
use App\Core\Url;

/** @var array<int, array<string, mixed>> $sobres */
/** @var array{placa: string, id_tramite: string} $filters */
/** @var int $totalSobres */
/** @var int $currentPage */
/** @var int $totalPages */
/** @var int $perPage */


$escape = static fn(mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);

$firstRecord = $totalSobres === 0
    ? 0
    : (($currentPage - 1) * $perPage) + 1;

$lastRecord = min(
    $currentPage * $perPage,
    $totalSobres
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
            <h1>Sobres de custodia</h1>
            <p>Consulta la ubicación, responsable y estado de los sobres asociados a cada trámite.</p>
        </div>

        <a href="tramites/sin-sobre" class="btn btn-primary">
            Generar Ficha
        </a>
    </div>

    <div class="custodia-card mb-4">
        <div class="custodia-card__body">
            <form
                method="get"
                action="<?= $escape(Url::to('/sobres')) ?>"
                class="row align-items-end" data-auto-filter-form>
                <div class="col-md-6">
                    <label for="busqueda" class="form-label">
                        Buscar sobre
                    </label>

                    <input
                        type="search"
                        id="busqueda"
                        name="busqueda"
                        class="form-control"
                        value="<?= $escape($filters['busqueda']) ?>"
                        placeholder="Código de barras, placa o número de trámite">
                </div>

                <div class="col-md-auto mt-3 mt-md-0">
                    <a
                        href="<?= $escape(Url::to('/sobres')) ?>"
                        class="btn btn-outline-secondary">
                        Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="custodia-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <p class="mb-0">
                <strong><?= $escape($totalSobres) ?></strong>
                sobre(s) encontrado(s).
            </p>
        </div>
        <div class="custodia-card__body">
            <table
                id="tabla-sobres"
                class="table table-striped table-hover"
                data-custodia-table>
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
                                    href="<?= $escape(
                                                Url::to('/sobres/' . $sobre['id_sobre'])
                                            ) ?>"
                                    class="btn btn-sm btn-outline-primary">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php Pagination::render(
                $currentPage,
                $totalPages,
                '/sobres',
                $filters
            ); ?>
        </div>
    </div>
</section>

<script
    src="<?= $escape(
        Url::to('/js/auto-filter.js', ['v' => '1'])
    ) ?>"
></script>