<?php

/**
 * components/road-progress.php
 *
 * Componente reutilizable: barra de progreso tipo "carretera".
 * Inclúyelo una vez (require_once) y llama a road_progress_bar() donde lo necesites.
 */

/**
 * Devuelve el HTML de la barra de progreso.
 *
 * @param int    $percent  0 a 100
 * @param string $colorKey 'blue' | 'indigo' | 'emerald' | 'amber'
 * @return string HTML listo para hacer echo
 */
function road_progress_bar(int $percent, string $colorKey = 'blue'): string
{
    $p = max(0, min(100, $percent));
    $percentEsc = htmlspecialchars((string) $p, ENT_QUOTES);
    $colorEsc   = htmlspecialchars($colorKey, ENT_QUOTES);

    return <<<HTML
    <div class="road-track-container">
        <div class="road-dashes"></div>
        <div class="road-active-lane lane-{$colorEsc}" style="width:{$percentEsc}%;"></div>
        <div class="road-car" style="left:{$percentEsc}%;">
            <div class="road-car-badge car-{$colorEsc}">
                <svg viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.85 7h10.29l1.04 3H5.81l1.04-3zM19 17H5v-4.66l.12-.34h13.77l.11.34V17z"/><circle cx="7.5" cy="14.5" r="1.5"/><circle cx="16.5" cy="14.5" r="1.5"/></svg>
                <span>{$percentEsc}%</span>
            </div>
        </div>
        <div class="road-flag">
            <svg viewBox="0 0 24 24"><path d="M14.4 6L14 4H5v17h2v-7h5.6l.4 2h7V6z"/></svg>
        </div>
    </div>
    HTML;
}

/**
 * Opcional: mapea el estado del sobre a un color de la barra,
 * para que no tengas que decidirlo a mano en cada card.
 */
function road_progress_color_por_estado(string $estado): string
{
    return match (strtolower($estado)) {
        'creado'      => 'amber',
        'gestion'     => 'indigo',
        'radicado'    => 'emerald',
        'finalizado'  => 'emerald',
        default       => 'blue',
    };
}
