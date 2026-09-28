"use strict";

(function () {
  let modalElement = null;
  let sobreActual = null;
  let alCerrar = null;

  function crearModal() {
    if (modalElement) {
      return modalElement;
    }

    modalElement = document.createElement("div");

    modalElement.id = "modal-disponibilidad-sobre";
    modalElement.className = "modal-disponibilidad-overlay";
    modalElement.style.display = "none";

    modalElement.innerHTML = `
            <div
                class="modal-disponibilidad-caja"
                role="dialog"
                aria-modal="true"
                aria-labelledby="modal-disponibilidad-titulo">

                <button
                    type="button"
                    class="modal-disponibilidad-cerrar"
                    aria-label="Cerrar">
                    &times;
                </button>

                <div class="modal-disponibilidad-encabezado">
                    <h2 id="modal-disponibilidad-titulo">
                        Devolver a Front
                    </h2>

                    <p class="modal-disponibilidad-sobre"></p>
                </div>

                <div class="modal-disponibilidad-contenido">
                    <p class="modal-disponibilidad-mensaje" aria-live="polite"></p>

                    <p>
                        El sobre dejará de estar asignado a
                        <strong data-responsable-actual></strong>
                        y quedará disponible en Front.
                    </p>

                    <div class="mb-3">
                        <label
                            for="select-usuario-devuelve"
                            class="form-label">
                            Registrado por
                        </label>

                        <div data-select-usuario-devuelve>
                            Cargando usuarios...
                        </div>
                    </div>

                    <div class="modal-disponibilidad-botones">
                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-volver-opciones>
                            Volver
                        </button>

                        <button
                            type="button"
                            class="btn btn-primary"
                            data-confirmar-devolucion>
                            Confirmar devolución
                        </button>
                    </div>
                </div>
            </div>
        `;

    document.body.appendChild(modalElement);

    modalElement
      .querySelector(".modal-disponibilidad-cerrar")
      .addEventListener("click", function () {
        cerrar(true);
      });

    modalElement.addEventListener("click", function (event) {
      if (event.target === modalElement) {
        cerrar(true);
      }
    });

    modalElement
      .querySelector("[data-volver-opciones]")
      .addEventListener("click", volverAOpciones);

    modalElement
      .querySelector("[data-confirmar-devolucion]")
      .addEventListener("click", confirmarDevolucion);

    return modalElement;
  }

  function abrir(sobre, callbackCerrar) {
    const modal = crearModal();

    if (!sobre || !sobre.id_sobre) {
      return;
    }

    sobreActual = sobre;
    alCerrar = callbackCerrar || null;

    const responsable = String(
      sobre.responsable || sobre.nombre_responsable || "",
    ).trim();

    if (responsable === "") {
      alert("El sobre ya está disponible en Front.");
      cerrar(true);
      return;
    }

    modal.querySelector(".modal-disponibilidad-sobre").textContent =
      (sobre.codigo_sobre || "") +
      " · Trámite #" +
      (sobre.id_tramite || "-") +
      " · Placa " +
      (sobre.placa || "-");

    modal.querySelector("[data-responsable-actual]").textContent = responsable;

    mostrarMensaje("");
    modal.style.display = "flex";

    cargarUsuarios();
  }

  function cargarUsuarios() {
    const destino = modalElement.querySelector(
      "[data-select-usuario-devuelve]",
    );

    if (
      !window.SelectorUsuarios ||
      typeof window.SelectorUsuarios.obtenerUsuarios !== "function" ||
      typeof window.SelectorUsuarios.construirSelectHTML !== "function"
    ) {
      destino.textContent = "No se pudo cargar el selector de usuarios.";

      return;
    }

    window.SelectorUsuarios.obtenerUsuarios()
      .then(function (usuarios) {
        destino.innerHTML = window.SelectorUsuarios.construirSelectHTML(
          "select-usuario-devuelve",
          usuarios,
          "Selecciona quién registra...",
        );
      })
      .catch(function () {
        destino.textContent = "No fue posible cargar los usuarios.";
      });
  }

  function confirmarDevolucion() {
    const selector = modalElement.querySelector("#select-usuario-devuelve");

    if (!selector || !selector.value) {
      mostrarMensaje("Selecciona quién registra la devolución.", "error");

      return;
    }

    const boton = modalElement.querySelector("[data-confirmar-devolucion]");

    boton.disabled = true;
    boton.textContent = "Guardando...";

    const datos = new URLSearchParams();

    datos.set("id_usuario_registra", selector.value);

    fetch(
      (window.APP_BASE || "") +
        "/api/sobres/" +
        encodeURIComponent(sobreActual.id_sobre) +
        "/devolver-front",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
          Accept: "application/json",
        },
        body: datos.toString(),
      },
    )
      .then(function (response) {
        return response.json().then(function (respuesta) {
          if (!response.ok || !respuesta.ok) {
            throw new Error(
              respuesta.message || "No fue posible devolver el sobre a Front.",
            );
          }

          return respuesta;
        });
      })
      .then(function (respuesta) {
        sobreActual.responsable = "";
        sobreActual.nombre_responsable = null;
        sobreActual.situacion = respuesta.sobre.situacion;

        mostrarMensaje(respuesta.message, "success");

        boton.textContent = "Devolución registrada";

        window.setTimeout(function () {
          cerrar(true);
        }, 700);
      })
      .catch(function (error) {
        mostrarMensaje(error.message, "error");

        boton.disabled = false;
        boton.textContent = "Confirmar devolución";
      });
  }

  function volverAOpciones() {
    const sobre = sobreActual;

    cerrar(false);

    if (sobre && typeof window.abrirModalOpciones === "function") {
      window.abrirModalOpciones(sobre);
    }
  }

  function cerrar(notificar) {
    if (!modalElement) {
      return;
    }

    modalElement.style.display = "none";

    const callback = alCerrar;

    sobreActual = null;
    alCerrar = null;

    if (notificar && typeof callback === "function") {
      callback();
    }
  }

  function mostrarMensaje(mensaje, tipo) {
    const elemento = modalElement.querySelector(
      ".modal-disponibilidad-mensaje",
    );

    elemento.textContent = mensaje || "";
    elemento.dataset.tipo = tipo || "";
  }

  window.ModalDisponibilidad = {
    abrir: abrir,
  };
})();
