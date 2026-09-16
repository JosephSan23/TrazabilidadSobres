<?php

declare(strict_types=1);

/** @var string $placa */
/** @var array<int, array<string, mixed>> $tramites */
/** @var string|null $error */

$escape = static fn (mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<section class="custodia-page">
    <div class="custodia-page__header">
        <div>
            <h1>Trámites sin sobre</h1>
            <p>Busca una placa para enlazar su trámite con un sobre físico.</p>
        </div>
    </div>

    <div class="custodia-card mb-4">
        <div class="custodia-card__body">
            <form method="get" action="/tramites/sin-sobre" class="row align-items-end">
                <div class="col-md-5">
                    <label for="placa" class="form-label">Placa del vehículo</label>

                    <input
                        type="search"
                        id="placa"
                        name="placa"
                        class="form-control"
                        value="<?= $escape($placa) ?>"
                        minlength="2"
                        maxlength="8"
                        placeholder="Ejemplo: ABC123"
                        required
                    >
                </div>

                <div class="col-md-auto mt-3 mt-md-0">
                    <button type="submit" class="btn btn-primary">
                        Buscar trámites
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($error !== null): ?>
        <div class="alert alert-warning" role="alert">
            <?= $escape($error) ?>
        </div>
    <?php endif; ?>

    <?php if ($placa !== '' && $error === null): ?>
        <div class="custodia-card">
            <div class="custodia-card__body">
                <?php if ($tramites === []): ?>
                    <p class="mb-0">
                        No se encontraron trámites sin sobre para la placa
                        <strong><?= $escape($placa) ?></strong>.
                    </p>
                <?php else: ?>
                    <table class="table table-striped table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Trámite</th>
                                <th>Placa</th>
                                <th>Fecha de creación</th>
                                <th>Estado</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($tramites as $tramite): ?>
                                <tr>
                                    <td><?= $escape($tramite['id_tramite']) ?></td>
                                    <td><?= $escape($tramite['placa']) ?></td>
                                    <td><?= $escape($tramite['fec_tramite'] ?? '') ?></td>
                                    <td><?= $escape($tramite['id_estado']) ?></td>
                                    <td class="text-center">
                                        <a
                                            href="/sobres/nuevo?id_tramite=<?= $escape($tramite['id_tramite']) ?>"
                                            class="btn btn-sm btn-primary"
                                        >
                                            Enlazar sobre
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</section>