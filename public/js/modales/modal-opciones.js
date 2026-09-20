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

    // Escaneo por lotes
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

  function cerrarModalOpciones() {
    if (!modalElement) return;

    modalElement.style.display = "none";
    sobreActual = null;

    if (colaIndividual.length > 0) {
      const siguiente = colaIndividual.shift();
      mostrarSobreEnModal(siguiente);
    }
  }

  function manejarAccion(accion, sobre) {
    const idSobre = sobre ? sobre.id_sobre : null;

    switch (accion) {
      case "marcar-documentos":
        console.log("[modal-opciones] Marcar documentos ->", idSobre);
        break;
      case "cambiar-estado":
        console.log("[modal-opciones] Cambiar estado ->", idSobre);
        break;
      case "asignar-persona":
        console.log("[modal-opciones] Asignar a persona ->", idSobre);
        break;
    }
  }

  window.abrirModalOpciones = abrirModalOpciones;
  window.cerrarModalOpciones = cerrarModalOpciones;
})();
