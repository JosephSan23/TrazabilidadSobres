(function () {
    'use strict';

    document.addEventListener('click', function (event) {
        const boton = event.target.closest('[data-abrir-modal-opciones]');

        if (!boton) {
            return;
        }

        const datosSobre = {
            id_sobre: boton.dataset.idSobre,
            id_tramite: boton.dataset.idTramite,
            codigo_sobre: boton.dataset.codigoSobre,
            placa: boton.dataset.placa,
            estado: boton.dataset.estado,
            responsable: boton.dataset.responsable,
        };

        if (typeof window.abrirModalOpciones === 'function') {
            window.abrirModalOpciones(datosSobre);
        } else {
            console.error('[sobres-card] abrirModalOpciones no está definido. ¿Está cargado modal-opciones.js?');
        }
    });
})();