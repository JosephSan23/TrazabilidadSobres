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

$asset = fn(string $ruta) => $escape(Url::to($ruta, ['v' => '1']));
?>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<link
    rel="stylesheet"
    href="<?= $escape(Url::to('/css/components/sidebar.css')) ?>">
<link
    rel="stylesheet"
    href="<?= $escape(Url::to('/css/index.css')) ?>">

<link
    rel="stylesheet"
    href="<?= $escape(Url::to('/css/components/pagination.css')) ?>">

<link
    rel="stylesheet"
    href="<?= $escape(Url::to('/css/modal.css')) ?>">

<?php require __DIR__ . '../../../Components/sidebar.php'; ?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div class="custodia-page__title-group">
            <h1>
                QUITAR UBICACION
                Sobres que ya tienen ficha actualizada generada
            </h1>
            <p>Consulta la ubicación, responsable y estado de los sobres asociados a cada trámite.</p>
        </div>

        <div class="custodia-page__header-actions">
            <a href="tramites/sin-sobre" class="btn-custodia btn-custodia--primary">
                <i class="bi bi-plus-lg"></i> Generar Ficha
            </a>
        </div>
    </div>


    <div class="custodia-card">
        <div id="sobres-resultados">
            <div class="custodia-card__body">
                <div class="custodia-results-row">
                    <!-- Grupo izquierdo: Contador + Buscador alineados -->
                    <div class="custodia-results-row__left">
                        <p class="mb-0">
                            <strong><?= $escape($totalSobres) ?></strong>
                            sobre(s) encontrado(s).
                        </p>

                        <form
                            method="get"
                            action="<?= $escape(Url::to('/sobres')) ?>"
                            class="custodia-toolbar"
                            data-auto-filter-form>

                            <div class="custodia-search">
                                <span class="custodia-search__icon">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input
                                    type="search"
                                    id="busqueda"
                                    name="busqueda"
                                    class="form-control"
                                    value="<?= $escape($filters['busqueda']) ?>"
                                    placeholder="Buscar por código, placa, trámite...">
                            </div>
                        </form>
                    </div>

                    <div class="switch-modo-lote">
                        <label>
                            <input type="checkbox" id="toggle-modo-lote">
                            Modo asignación masiva
                        </label>
                    </div>

                    <!-- Grupo derecho: Pestañas de filtro -->
                    <div class="custodia-results-row__actions">
                        <div class="filter-tabs">
                            <a href="?estado=todos" class="filter-tab filter-tab--todos is-active">Todos</a>
                            <a href="?estado=creados" class="filter-tab filter-tab--creados">Creados</a>
                            <a href="?estado=gestion" class="filter-tab filter-tab--gestion">Gestion</a>
                            <a href="?estado=incompletos" class="filter-tab filter-tab--incompletos">Incompletos</a>
                            <a href="?estado=completos" class="filter-tab filter-tab--completos">Completos</a>
                            <a href="?estado=radicados" class="filter-tab filter-tab--radicados">Radicados</a>
                            <a href="?estado=finalizados" class="filter-tab filter-tab--finalizados">Finalizados</a>
                        </div>
                    </div>
                </div>
                <div class="custodia-grid" id="tabla-sobres" data-custodia-table>
                    <?php foreach ($sobres as $sobre): ?>
                        <?php $tieneUbicacion = (bool) $sobre['nombre_ubicacion']; ?>
                        <div class="custodia-card-item">
                            <div class="custodia-card-item__header">
                                <span class="custodia-card-item__label">SOBRE</span>
                                <?php if ($tieneUbicacion): ?>
                                    <span class="custodia-corner-tag custodia-corner-tag--activo">
                                        <i class="bi bi-check-circle"></i> Con ubicación
                                    </span>
                                <?php else: ?>
                                    <span class="custodia-corner-tag custodia-corner-tag--pendiente">
                                        <i class="bi bi-exclamation-triangle"></i> Sin Ubicación
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="custodia-card-item__body">
                                <div class="custodia-card-item__title">
                                    <h3><i class="bi bi-upc-scan"></i> <?= $escape($sobre['codigo_sobre']) ?></h3>
                                    <span class="badge-placa"><?= $escape($sobre['placa']) ?></span>
                                </div>

                                <ul class="custodia-card-item__fields">
                                    <li>
                                        <span class="field-icon-label">
                                            <i class="bi bi-hash"></i>
                                            <span class="field-label">Nº Trámite:</span>
                                        </span>
                                        <span class="field-value">#<?= $escape($sobre['id_tramite']) ?></span>
                                    </li>

                                    <li>
                                        <!-- <span class="field-icon-label">
                                            <i class="bi bi-geo-alt"></i>
                                            <span class="field-label">Ubicación:</span>
                                        </span>
                                        <?php if ($tieneUbicacion): ?>
                                            <span class="tag-ubicacion tag-ubicacion--ok">
                                                <i class="bi bi-box-seam"></i> <?= $escape($sobre['nombre_ubicacion']) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="tag-ubicacion tag-ubicacion--pendiente">
                                                <i class="bi bi-exclamation-triangle"></i> Pendiente de ubicar
                                            </span>
                                        <?php endif; ?> -->
                                        <span class="field-icon-label">
                                            <i class="bi bi-car-front"></i>
                                            <span class="field-label">Aseguradora:</span>
                                        </span>
                                    </li>
                                    <li>
                                        <span class="field-icon-label">
                                            <i class="bi bi-person"></i>
                                            <span class="field-label">Lo tiene: </span>
                                        </span>
                                        <span class="field-value" data-campo-responsable>
                                            <?php if ($sobre['nombre_responsable']): ?>
                                                <?= $escape($sobre['nombre_responsable']) ?>
                                            <?php else: ?>
                                                <span class="text-muted-italic">Front</span>
                                            <?php endif; ?>
                                        </span>
                                    </li>
                                    <li>
                                        <span class="field-icon-label">
                                            <i class="bi bi-clock-history"></i>
                                            <span class="field-label">Último mov.:</span>
                                        </span>
                                        <span class="field-value"><?= $escape($sobre['fecha_ultimo_movimiento']) ?></span>
                                    </li>
                                </ul>
                            </div>

                            <div class="custodia-card-item__footer">
                                <span class="badge-estado badge-estado--<?= strtolower($escape($sobre['estado'])) ?>">
                                    ● <?= $escape($sobre['estado']) ?>
                                </span>
                                <button
                                    type="button"
                                    class="btn-icon"
                                    data-abrir-modal-opciones
                                    data-id-sobre="<?= $escape($sobre['id_sobre']) ?>"
                                    data-id-tramite="<?= $escape($sobre['id_tramite']) ?>"
                                    data-codigo-sobre="<?= $escape($sobre['codigo_sobre']) ?>"
                                    data-placa="<?= $escape($sobre['placa']) ?>"
                                    data-estado="<?= $escape($sobre['estado']) ?>"
                                    data-responsable="<?= $escape($sobre['nombre_responsable'] ?? 'Sin responsable') ?>">
                                    <i class="bi bi-sliders"></i> Opciones
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="pagination-info">
                    Mostrando <?= $escape($firstRecord) ?> a <?= $escape($lastRecord) ?> de <?= $escape($totalSobres) ?> resultados
                </div>
                <?php Pagination::render(
                    $currentPage,
                    $totalPages,
                    '/sobres',
                    $filters
                ); ?>
            </div>
        </div>
    </div>
</section>

<script>
    window.APP_BASE = <?= json_encode(rtrim(Url::to('/'), '/')) ?>;
</script>
<script src="<?= $asset('/js/auto-filter.js') ?>"></script>
<script src="<?= $asset('/js/escaneo/modo-escaneo.js') ?>"></script>
<script src="<?= $asset('/js/utilidades/selector-usuarios.js') ?>"></script>

<!-- 1. El árbitro y los modales deben ir primero -->
<script src="<?= $asset('/js/gestor-flujo-sobres.js') ?>"></script>
<script src="<?= $asset('/js/modales/modal-documentos.js') ?>"></script>
<script src="<?= $asset('/js/modales/modal-opciones.js') ?>"></script>
<script src="<?= $asset('/js/modales/modal-asignar.js') ?>"></script>

<!-- 2. Paneles y lógica dependiente -->
<script src="<?= $asset('/js/paneles/lote.js') ?>"></script>
<script src="<?= $asset('/js/paneles/switch-modo-lote.js') ?>"></script>

<!-- 3. Las tarjetas van después porque llaman a los modales anteriores -->
<script src="<?= $asset('/js/modales/sobres-card.js') ?>"></script>

<script src="<?= $asset('/js/escaneo/escanner.js') ?>"></script>