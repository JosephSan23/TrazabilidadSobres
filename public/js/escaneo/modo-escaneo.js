(function () {
    'use strict';

    let modoActual = 'individual'; // 'individual' | 'lote'

    function obtenerModo() {
        return modoActual;
    }

    function activarModoLote() {
        modoActual = 'lote';
        document.dispatchEvent(new CustomEvent('modo-escaneo:cambiado', { detail: { modo: modoActual } }));
    }

    function activarModoIndividual() {
        modoActual = 'individual';
        document.dispatchEvent(new CustomEvent('modo-escaneo:cambiado', { detail: { modo: modoActual } }));
    }

    window.ModoEscaneo = {
        obtenerModo: obtenerModo,
        activarModoLote: activarModoLote,
        activarModoIndividual: activarModoIndividual,
    };
})();