'use strict';

(() => {
    const forms = document.querySelectorAll('[data-auto-filter-form]');

    forms.forEach((form) => {
        const resultsId = 'sobres-resultados';
        const resultsContainer = document.getElementById(resultsId);
        let timer;

        if (!resultsContainer) {
            return;
        }

        const actualizarResultados = async () => {
            const params = new URLSearchParams(new FormData(form));
            const url = `${form.action}?${params.toString()}`;

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
        };

        form.querySelectorAll('input[type="search"]').forEach((input) => {
            input.addEventListener('input', () => {
                window.clearTimeout(timer);
                timer = window.setTimeout(actualizarResultados, 400);
            });
        });
    });
})();