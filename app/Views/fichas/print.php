<?php

declare(strict_types=1);

use App\Core\Url;

/** @var array<int, array<string, mixed>> $fichas */
/** @var int $id_usuario_registra */
/** @var string $nombre_usuario_registra */

$escape = static fn(mixed $value): string => htmlspecialchars(
    (string) $value,
    ENT_QUOTES,
    'UTF-8'
);
?>

<link
    rel="stylesheet"
    href="<?= $escape(Url::to('/css/fichas-print.css')) ?>">

<section class="fichas-print-page">
    <div class="fichas-print-page__header no-print">
        <div>
            <h1>Fichas listas para imprimir</h1>

            <p>
                Imprime las fichas, pégalas en los sobres físicos y luego
                confirma que fueron impresas.
            </p>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <button
                type="button"
                class="btn btn-outline-primary"
                data-print-fichas>
                Imprimir fichas
            </button>

            <form
                method="post"
                action="<?= $escape(
                            Url::to('/sobres/confirmar-fichas-impresas')
                        ) ?>">

                <input
                    type="hidden"
                    name="id_usuario_registra"
                    value="<?= $escape($id_usuario_registra) ?>">

                <?php foreach ($fichas as $ficha): ?>
                    <input
                        type="hidden"
                        name="tramites[]"
                        value="<?= $escape($ficha['id_tramite']) ?>">
                <?php endforeach; ?>

                <button
                    type="submit"
                    class="btn btn-primary"
                    onclick="
                        return confirm(
                            '¿Confirmas que las fichas fueron impresas y pegadas en sus sobres físicos?'
                        );
                    ">
                    Confirmar fichas impresas
                </button>
            </form>
        </div>
    </div>

    <div class="fichas-grid">
        <?php foreach ($fichas as $ficha): ?>
            <article class="ficha-custodia">
                <div class="ficha-header">
                    <img
                        src="<?= $escape(
                                    Url::to('/img/logo-azul-asiste-mas.png')
                                ) ?>"
                        alt="Logo">
                </div>

                <dl class="ficha-custodia__data">
                    <div>
                        <dt>Placa:</dt>
                        <dd><?= $escape($ficha['placa']) ?></dd>
                    </div>

                    <div>
                        <dt>Siniestro:</dt>
                        <dd class="ficha-custodia__blank"></dd>
                    </div>

                    <div>
                        <dt>Analista:</dt>
                        <dd class="ficha-custodia__blank"></dd>
                    </div>

                    <div>
                        <dt>Destino:</dt>
                        <dd class="ficha-custodia__blank"></dd>
                    </div>

                    <div>
                        <dt>Aseguradora:</dt>
                        <dd class="ficha-custodia__blank"></dd>
                    </div>
                </dl>

                <div class="ficha-custodia__divider"></div>

                <svg
                    class="ficha-custodia__barcode"
                    data-barcode-value="<?= $escape(
                                            $ficha['codigo_sobre']
                                        ) ?>"
                    aria-label="Código de barras <?= $escape(
                                                        $ficha['codigo_sobre']
                                                    ) ?>"></svg>

                <p class="ficha-custodia__code">
                    <?= $escape($ficha['codigo_sobre']) ?>
                </p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="<?= $escape(Url::to('/js/fichas-print.js')) ?>"></script>