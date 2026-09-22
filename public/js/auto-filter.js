'use strict';

(() => {
    const resultsId = 'sobres-resultados';
    const resultsContainer = document.getElementById(resultsId);

    if (!resultsContainer) {
        return;
    }

    let timer;

    const actualizarResultados = async (form) => {
        const params = new URLSearchParams(new FormData(form));
        const url = `${form.action}?${params.toString()}`;


        const activo = document.activeElement;
        const habiaFoco = resultsContainer.contains(activo) && activo.name;
        const nombreCampo = habiaFoco ? activo.name : null;
        const posicionCursor = habiaFoco ? activo.selectionStart : null;

        let response;
        try {
            response = await fetch(url, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
        } catch {
            form.submit();
            return;
        }

        if (!response.ok) {
            form.submit();
            return;
        }

        const html = await response.text();
        const doc = new DOMParser().parseFromString(html, 'text/html');
        const nuevoContenido = doc.getElementById(resultsId);

        if (nuevoContenido) {
            resultsContainer.innerHTML = nuevoContenido.innerHTML;
        }

        window.history.replaceState(null, '', url);

        if (nombreCampo) {
            const nuevoCampo = resultsContainer.querySelector(
                `[name="${nombreCampo}"]`
            );

            if (nuevoCampo) {
                nuevoCampo.focus();
                if (posicionCursor !== null) {
                    nuevoCampo.setSelectionRange(posicionCursor, posicionCursor);
                }
            }
        }
    };


    resultsContainer.addEventListener('input', (e) => {
        const input = e.target.closest('input[type="search"]');
        const form = e.target.closest('[data-auto-filter-form]');

        if (!input || !form) {
            return;
        }

        window.clearTimeout(timer);
        timer = window.setTimeout(() => actualizarResultados(form), 400);
    });

    resultsContainer.addEventListener('submit', (e) => {
        if (e.target.matches('[data-auto-filter-form]')) {
            e.preventDefault();
        }
    });
})();