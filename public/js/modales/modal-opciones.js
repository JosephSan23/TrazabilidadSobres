(function () {
  "use strict";

  let modalElement = null;
  let sobreActual = null;
  let colaIndividual = [];

  function crearModalSiNoExiste() {
    if (modalElement) {
      return modalElement;
    }

    const wrapper = document.createElement("div");
    wrapper.id = "modal-opciones-sobre";
    wrapper.className = "modal-opciones-overlay";
    wrapper.style.display = "none";

    // Requisito del árbitro de flujos
    wrapper.setAttribute("data-flujo-sobres", "true");

    wrapper.innerHTML = `
            <div class="modal-opciones-caja">
                <button type="button" class="modal-opciones-cerrar" aria-label="Cerrar">&times;</button>

                <div class="modal-opciones-encabezado">
                    <h2 class="modal-opciones-codigo"></h2>
                    <p class="modal-opciones-subtitulo"></p>
                    <p class="modal-opciones-badge-cola" style="display:none;"></p>
                    <button type="button" class="modal-opciones-agregar-lote">
                        + Agregar a asignación múltiple
                    </button>
                </div>

                <div class="modal-opciones-acciones">
                    <button type="button" class="modal-opciones-btn" data-accion="marcar-documentos">
                        Marcar documentos
                    </button>
                    <button type="button" class="modal-opciones-btn" data-accion="cambiar-estado">
                        Cambiar estado
                    </button>
                    <button type="button" class="modal-opciones-btn" data-accion="asignar-persona">
                        Asignar a persona
                    </button>
                </div>
            </div>
        `;

    document.body.appendChild(wrapper);

    wrapper
      .querySelector(".modal-opciones-cerrar")
      .addEventListener("click", cerrarModalOpciones);

    wrapper.addEventListener("click", function (event) {
      if (event.target === wrapper) {
        cerrarModalOpciones();
      }
    });

    wrapper.querySelectorAll(".modal-opciones-btn").forEach(function (boton) {
      boton.addEventListener("click", function () {
        const accion = boton.getAttribute("data-accion");
        manejarAccion(accion, sobreActual);
      });
    });

    wrapper
      .querySelector(".modal-opciones-agregar-lote")
      .addEventListener("click", function () {
        if (sobreActual && typeof window.agregarSobreALote === "function") {
          window.ModoEscaneo.activarModoLote();
          window.agregarSobreALote(sobreActual);
          cerrarModalOpciones();
        }
      });

    modalElement = wrapper;
    return wrapper;
  }

  function abrirModalOpciones(datosSobre) {
    const modal = crearModalSiNoExiste();

    if (modal.style.display === "flex") {
      agregarACola(datosSobre);
      return;
    }

    mostrarSobreEnModal(datosSobre);
  }

  function agregarACola(datosSobre) {
    const yaEsta =
      sobreActual &&
      String(sobreActual.id_sobre) === String(datosSobre.id_sobre);
    const yaEnCola = colaIndividual.some(function (s) {
      return String(s.id_sobre) === String(datosSobre.id_sobre);
    });

    if (yaEsta || yaEnCola) {
      console.log(
        "[modal-opciones] Sobre duplicado, se ignora:",
        datosSobre.id_sobre,
      );
      return;
    }

    colaIndividual.push(datosSobre);
    actualizarBadgeCola();
  }

  function actualizarBadgeCola() {
    if (!modalElement) return;
    const badge = modalElement.querySelector(".modal-opciones-badge-cola");

    if (colaIndividual.length > 0) {
      badge.style.display = "block";
      badge.textContent = colaIndividual.length + " en espera";
    } else {
      badge.style.display = "none";
    }
  }

  function mostrarSobreEnModal(datosSobre) {
    const modal = crearModalSiNoExiste();

    sobreActual = datosSobre;

    modal.querySelector(".modal-opciones-codigo").textContent =
      datosSobre.codigo_sobre || "";
    modal.querySelector(".modal-opciones-subtitulo").textContent =
      "Trámite #" +
      (datosSobre.id_tramite || "-") +
      " · Placa " +
      (datosSobre.placa || "-");

    actualizarBadgeCola();

    modal.style.display = "flex";
  }

  function procesarSiguienteSobre() {
    sobreActual = null;

    if (colaIndividual.length === 0) {
      if (window.GestorFlujoSobres) {
        window.GestorFlujoSobres.notificarPosibleFinDeFlujo();
      }
      return;
    }

    const siguiente = colaIndividual.shift();
    mostrarSobreEnModal(siguiente);

    if (window.GestorFlujoSobres) {
      window.GestorFlujoSobres.notificarPosibleFinDeFlujo();
    }
  }

  function cerrarModalOpciones() {
    if (!modalElement) return;

    modalElement.style.display = "none";
    procesarSiguienteSobre();
  }

  function manejarAccion(accion, sobre) {
    if (!sobre) {
      return;
    }

    let modalDestino = null;

    switch (accion) {
      case "marcar-documentos":
        modalDestino = window.ModalDocumento;
        break;

      case "cambiar-estado":
        modalDestino = window.ModalEstado;
        break;

      case "asignar-persona":
        modalDestino = window.ModalAsignar;
        break;
    }

    if (!modalDestino || typeof modalDestino.abrir !== "function") {
      alert("El modal seleccionado aún no está disponible.");
      return;
    }

    modalElement.style.display = "none";
    modalDestino.abrir(sobre, procesarSiguienteSobre);
  }

  // Exposición global obligatoria
  window.abrirModalOpciones = abrirModalOpciones;
  window.cerrarModalOpciones = cerrarModalOpciones;

  window.ModalOpciones = {
    procesarSiguienteSobre: procesarSiguienteSobre,
    abrir: abrirModalOpciones,
  };
})();
