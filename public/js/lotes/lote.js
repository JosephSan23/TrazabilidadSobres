(function () {
    'use strict';

    let loteActual = [];
    let panelElement = null;

    function crearPanelSiNoExiste() {
        if (panelElement) {
            return panelElement;
        }

        const panel = document.createElement('div');
        panel.id = 'panel-lote-sobres';
        panel.className = 'panel-lote';
        panel.style.display = 'none';

        panel.innerHTML = `
            <div class="panel-lote__header">
                <strong>Sobres seleccionados: <span class="panel-lote__contador">0</span></strong>
            </div>
            <ul class="panel-lote__lista"></ul>
            <div class="panel-lote__acciones">
                <button type="button" class="panel-lote__guardar">Guardar / Asignar todos</button>
                <button type="button" class="panel-lote__vaciar">Vaciar</button>
                <button type="button" class="panel-lote__cancelar">Cancelar modo lote</button>
            </div>
        `;

        document.body.appendChild(panel);

        panel.querySelector('.panel-lote__guardar').addEventListener('click', guardarLote);
        panel.querySelector('.panel-lote__vaciar').addEventListener('click', vaciarLote);
        panel.querySelector('.panel-lote__cancelar').addEventListener('click', cancelarModoLote);

        panelElement = panel;
        return panel;
    }

    function agregarSobreALote(datosSobre) {
        const yaEsta = loteActual.some(function (s) {
            return String(s.id_sobre) === String(datosSobre.id_sobre);
        });

        if (yaEsta) {
            console.log('[lote] Sobre duplicado, se ignora:', datosSobre.id_sobre);
            return;
        }

        loteActual.push(datosSobre);
        renderizarPanel();
    }

    function renderizarPanel() {
        const panel = crearPanelSiNoExiste();

        panel.querySelector('.panel-lote__contador').textContent = loteActual.length;

        const lista = panel.querySelector('.panel-lote__lista');
        lista.innerHTML = loteActual.map(function (s) {
            return '<li>Trámite #' + (s.id_tramite || '-') + ' · Placa ' + (s.placa || '-') + '</li>';
        }).join('');

        panel.style.display = loteActual.length > 0 || window.ModoEscaneo.obtenerModo() === 'lote'
            ? 'block'
            : 'none';
    }

    function vaciarLote() {
        loteActual = [];
        renderizarPanel();
    }

    function cancelarModoLote() {
        loteActual = [];
        window.ModoEscaneo.activarModoIndividual();
        if (panelElement) {
            panelElement.style.display = 'none';
        }
    }

    function guardarLote() {
        if (loteActual.length === 0) {
            alert('No hay sobres en la lista para asignar.');
            return;
        }

        // TODO: aquí falta el selector real de usuario/persona.
        // placeholder simple con prompt para probar el flujo.
        const idUsuarioResponsable = prompt('ID del usuario a quien asignar estos ' + loteActual.length + ' sobres:');

        if (!idUsuarioResponsable) {
            return;
        }

        const idsSobre = loteActual.map(function (s) { return s.id_sobre; });

        fetch('/sobres/asignar-lote', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                ids_sobre: idsSobre,
                id_usuario_responsable_destino: idUsuarioResponsable,
            }),
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Error al asignar el lote.');
                }
                return response.json();
            })
            .then(function (data) {
                console.log('[lote] Asignación exitosa:', data);
                loteActual = [];
                renderizarPanel();
            })
            .catch(function (error) {
                console.error('[lote] Error:', error);
                alert('Ocurrió un error al asignar los sobres.');
            });
    }


    // si no quedan sobres pendientes en el lote se oculta el panel y cambia a individual.
    document.addEventListener('modo-escaneo:cambiado', function (event) {
        if (event.detail.modo === 'lote') {
            renderizarPanel();
        } else if (loteActual.length === 0 && panelElement) {
            panelElement.style.display = 'none';
        }
    });

    window.agregarSobreALote = agregarSobreALote;
})();