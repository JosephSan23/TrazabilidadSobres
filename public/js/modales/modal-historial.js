(function () {
    "use strict";

    let modalElement = null;

    function crearModal() {
        if (modalElement) {
            return modalElement;
        }

        modalElement = document.createElement("dialog");
        modalElement.className = "custodia-modal";
        modalElement.dataset.modalHistorial = "";

        modalElement.innerHTML = `
            <div class="custodia-modal__header">
                <div>
                    <h2>Historial del sobre</h2>
                    <p data-historial-subtitulo></p>
                </div>

                <button
                    type="button"
                    class="btn-close"
                    aria-label="Cerrar"
                    data-cerrar-historial>
                </button>
            </div>

            <div class="custodia-modal__body">
                <div data-historial-contenido>
                    Cargando movimientos...
                </div>
            </div>

            <div class="custodia-modal__footer">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    data-cerrar-historial>
                    Cerrar
                </button>
            </div>
        `;

        document.body.appendChild(modalElement);

        modalElement.addEventListener("click", function (event) {
            if (event.target.closest("[data-cerrar-historial]")) {
                modalElement.close();
            }
        });

        return modalElement;
    }

    function textoEvento(tipoEvento) {
        const eventos = {
            CREACION_SOBRE: "Ficha confirmada",
            CAMBIO_RESPONSABLE_SOBRE: "Cambio de responsable",
            MOVIMIENTO_SOBRE: "Movimiento del sobre",
            ENTREGA_SOBRE: "Entrega del sobre",
            DEVOLUCION_SOBRE: "Devolución del sobre",
        };

        return eventos[tipoEvento] || tipoEvento;
    }

    function textoResponsable(movimiento) {
        const origen = movimiento.nombre_responsable_origen || "Front";
        const destino = movimiento.nombre_responsable_destino || "Front";

        if (origen === destino) {
            return destino;
        }

        return origen + " → " + destino;
    }

    function crearElementoMovimiento(movimiento) {
        const item = document.createElement("article");
        item.className = "historial-movimiento";

        const titulo = document.createElement("h3");
        titulo.className = "historial-movimiento__titulo";
        titulo.textContent = textoEvento(movimiento.tipo_evento);

        const fecha = document.createElement("p");
        fecha.className = "historial-movimiento__fecha";
        fecha.textContent = movimiento.fecha_evento || "Fecha no disponible";

        const detalle = document.createElement("p");
        detalle.className = "historial-movimiento__detalle";
        detalle.textContent = "Responsable: " + textoResponsable(movimiento);

        const usuario = document.createElement("p");
        usuario.className = "historial-movimiento__usuario";
        usuario.textContent =
            "Registrado por: " +
            (movimiento.nombre_usuario_registra || "Usuario no identificado");

        item.append(titulo, fecha, detalle, usuario);

        if (movimiento.observaciones) {
            const observaciones = document.createElement("p");
            observaciones.className = "historial-movimiento__observaciones";
            observaciones.textContent = movimiento.observaciones;

            item.appendChild(observaciones);
        }

        return item;
    }

    function cargarHistorial(sobre) {
        const modal = crearModal();
        const subtitulo = modal.querySelector("[data-historial-subtitulo]");
        const contenido = modal.querySelector("[data-historial-contenido]");

        subtitulo.textContent =
            (sobre.codigo_sobre || "Sobre") +
            " · Trámite #" +
            (sobre.id_tramite || "");

        contenido.textContent = "Cargando movimientos...";

        fetch(
            (window.APP_BASE || "") +
                "/api/sobres/" +
                encodeURIComponent(sobre.id_sobre) +
                "/historial",
            {
                headers: {
                    Accept: "application/json",
                },
            },
        )
            .then(function (response) {
                return response.json().then(function (respuesta) {
                    if (!response.ok || !respuesta.ok) {
                        throw new Error(
                            respuesta.message ||
                                "No fue posible consultar el historial.",
                        );
                    }

                    return respuesta;
                });
            })
            .then(function (respuesta) {
                const movimientos = respuesta.movimientos || [];

                contenido.replaceChildren();

                if (movimientos.length === 0) {
                    contenido.textContent =
                        "Este sobre aún no tiene movimientos registrados.";
                    return;
                }

                const lista = document.createElement("div");
                lista.className = "historial-movimientos";

                movimientos.forEach(function (movimiento) {
                    lista.appendChild(crearElementoMovimiento(movimiento));
                });

                contenido.appendChild(lista);
            })
            .catch(function (error) {
                contenido.textContent = error.message;
            });
    }

    window.ModalHistorial = {
        abrir: function (sobre) {
            if (!sobre || !sobre.id_sobre) {
                return;
            }

            const modal = crearModal();

            if (!modal.open) {
                modal.showModal();
            }

            cargarHistorial(sobre);
        },
    };
})();