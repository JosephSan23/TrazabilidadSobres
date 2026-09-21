(function () {
  "use strict";

  let modalElement = null;
  let sobreActual = null;
  let alCerrar = null;

  function crearModal() {
    if (modalElement) {
      return modalElement;
    }

    modalElement = document.createElement("div");

    modalElement.id = "modal-asignar-sobre";
    modalElement.className = "modal-asignar-overlay";
    modalElement.style.display = "none";

    // Requisito del árbitro de flujos
    modalElement.setAttribute("data-flujo-sobres", "true");

    modalElement.innerHTML = `
      <div class="modal-asignar-caja" role="dialog" aria-modal="true">
        <button
          type="button"
          class="modal-asignar-cerrar"
          aria-label="Cerrar">&times;</button>

        <div class="modal-asignar-encabezado">
          <h2>Asignar responsable</h2>
          <p class="modal-asignar-sobre"></p>
        </div>

        <div class="modal-asignar-contenido">
          <p
            class="modal-asignar-mensaje"
            aria-live="polite"></p>

          <div class="modal-asignar-campos"></div>
        </div>
      </div>
    `;

    document.body.appendChild(modalElement);

    modalElement
      .querySelector(".modal-asignar-cerrar")
      .addEventListener("click", function () {
        cerrar(true);
      });

    modalElement.addEventListener("click", function (event) {
      if (event.target === modalElement) {
        cerrar(true);
      }
    });

    return modalElement;
  }

  function abrir(sobre, callbackCerrar) {
    const modal = crearModal();

    if (
      !window.SelectorUsuarios ||
      typeof window.SelectorUsuarios.obtenerUsuarios !== "function" ||
      typeof window.SelectorUsuarios.construirSelectHTML !== "function"
    ) {
      alert("No está disponible el componente de selección de usuarios.");
      return;
    }

    sobreActual = sobre;
    alCerrar = callbackCerrar || null;

    modal.querySelector(".modal-asignar-sobre").textContent =
      (sobre.codigo_sobre || "") +
      " · Trámite #" +
      (sobre.id_tramite || "-") +
      " · Placa " +
      (sobre.placa || "-");

    mostrarMensaje("");
    modal.style.display = "flex";

    cargarFormulario();
  }

  function cargarFormulario() {
    const contenedor = modalElement.querySelector(".modal-asignar-campos");

    contenedor.innerHTML = "<p>Cargando usuarios...</p>";

    window.SelectorUsuarios.obtenerUsuarios()
      .then(function (usuarios) {
        const selectResponsable = window.SelectorUsuarios.construirSelectHTML(
          "select-responsable",
          usuarios,
          "Selecciona responsable...",
        );

        const selectRegistra = window.SelectorUsuarios.construirSelectHTML(
          "select-usuario-registra",
          usuarios,
          "¿Quién registra?",
        );

        contenedor.innerHTML = `
          <div class="mb-3">
            <label for="select-responsable">
              Responsable del sobre
            </label>
            ${selectResponsable}
          </div>

          <div class="mb-3">
            <label for="select-usuario-registra">
              Registrado por
            </label>
            ${selectRegistra}
          </div>

          <div class="modal-asignar-botones">
            <button
              type="button"
              class="btn btn-secondary"
              data-volver-opciones>
              Volver
            </button>

            <button
              type="button"
              class="btn btn-primary"
              data-confirmar-asignacion>
              Guardar asignación
            </button>
          </div>
        `;

        // Preseleccionar el responsable actual si ya existe en el sobre
        const selectRespEl = contenedor.querySelector("#select-responsable");
        if (selectRespEl && sobreActual) {
          if (sobreActual.id_responsable) {
            selectRespEl.value = sobreActual.id_responsable;
          } else if (sobreActual.nombre_responsable) {
            for (let i = 0; i < selectRespEl.options.length; i++) {
              if (selectRespEl.options[i].text.trim() === sobreActual.nombre_responsable.trim()) {
                selectRespEl.selectedIndex = i;
                break;
              }
            }
          }
        }

        contenedor
          .querySelector("[data-volver-opciones]")
          .addEventListener("click", volverAOpciones);

        contenedor
          .querySelector("[data-confirmar-asignacion]")
          .addEventListener("click", guardarAsignacion);
      })
      .catch(function () {
        contenedor.innerHTML = "";

        mostrarMensaje(
          "No fue posible cargar los usuarios disponibles.",
          "error",
        );
      });
  }

  function guardarAsignacion() {
    const contenedor = modalElement.querySelector(".modal-asignar-campos");

    const selectResponsable = contenedor.querySelector("#select-responsable");
    const idResponsable = selectResponsable ? selectResponsable.value : "";

    const idUsuarioRegistra = contenedor.querySelector(
      "#select-usuario-registra",
    ).value;

    if (!idResponsable || !idUsuarioRegistra) {
      mostrarMensaje(
        "Selecciona el responsable y quién registra la asignación.",
        "error",
      );

      return;
    }

    const boton = contenedor.querySelector("[data-confirmar-asignacion]");

    boton.disabled = true;
    boton.textContent = "Guardando...";

    const datos = new URLSearchParams();

    datos.set("id_usuario_responsable", idResponsable);
    datos.set("id_usuario_registra", idUsuarioRegistra);

    fetch(
      (window.APP_BASE || "") +
        "/api/sobres/" +
        encodeURIComponent(sobreActual.id_sobre) +
        "/asignar",
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
              respuesta.message || "No fue posible guardar la asignación.",
            );
          }

          return respuesta;
        });
      })
      .then(function (respuesta) {
        const nombreNuevo = respuesta.sobre.responsable;
        sobreActual.nombre_responsable = nombreNuevo;
        if (respuesta.sobre.id_responsable) {
          sobreActual.id_responsable = respuesta.sobre.id_responsable;
        }

        mostrarMensaje(respuesta.message, "success");

        boton.textContent = "Asignación registrada";

        actualizarResponsableEnTarjeta(sobreActual.id_sobre, nombreNuevo);

        setTimeout(function () {
          boton.disabled = false;
          boton.textContent = "Actualizar asignación";
        }, 1500);
      })
      .catch(function (error) {
        mostrarMensaje(error.message, "error");

        boton.disabled = false;
        boton.textContent = "Guardar asignación";
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

    // Notificar al árbitro global de flujos si cerramos del todo
    if (notificar && window.GestorFlujoSobres) {
      window.GestorFlujoSobres.notificarPosibleFinDeFlujo();
    }
  }

  function mostrarMensaje(mensaje, tipo) {
    const elemento = modalElement.querySelector(".modal-asignar-mensaje");

    elemento.textContent = mensaje || "";
    elemento.dataset.tipo = tipo || "";
  }

  function actualizarResponsableEnTarjeta(idSobre, nombreResponsable) {
    const boton = document.querySelector(
      '[data-abrir-modal-opciones][data-id-sobre="' + idSobre + '"]',
    );
    if (!boton) return;

    boton.dataset.responsable = nombreResponsable;

    const tarjeta =
      boton.closest("li, article, .card, tr") || boton.parentElement;
    const campo = tarjeta.querySelector("[data-campo-responsable]");
    if (campo) {
      campo.textContent = nombreResponsable;
      campo.classList.remove("text-muted-italic");
    }
  }

  window.ModalAsignar = {
    abrir: abrir,
  };
})();