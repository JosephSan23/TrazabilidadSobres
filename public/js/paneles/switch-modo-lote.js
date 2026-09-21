(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const checkbox = document.getElementById('toggle-modo-lote');

        if (!checkbox) {
            return;
        }

        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                window.ModoEscaneo.activarModoLote();
            } else {
                window.ModoEscaneo.activarModoIndividual();
            }
        });

        document.addEventListener('modo-escaneo:cambiado', function (event) {
            checkbox.checked = (event.detail.modo === 'lote');
        });
    });
})();