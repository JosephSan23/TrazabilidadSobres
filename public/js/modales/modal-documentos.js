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
    modalElement.id = "modal-documento-sobre";
    modalElement.className = "modal-documento-overlay";
    modalElement.style.display = "none";

    modalElement.innerHTML = `
      <div class="modal-documento-caja" role="dialog" aria-modal="true">
        <button
          type="button"
          class="modal-documento-cerrar"
          aria-label="Cerrar">&times;</button>

        <div class="modal-documento-encabezado">
          <h2>Documentos del sobre</h2>
          <p class="modal-documento-sobre"></p>
        </div>

        <p
          class="modal-documento-mensaje"
          aria-live="polite"></p>

        <div class="modal-documento-contenido"></div>
      </div>
    `;

    document.body.appendChild(modalElement);

    modalElement
      .querySelector(".modal-documento-cerrar")
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

    sobreActual = sobre;
    alCerrar = callbackCerrar || null;

    modal.querySelector(".modal-documento-sobre").textContent =
      (sobre.codigo_sobre || "") +
      " · Trámite #" +
      (sobre.id_tramite || "-") +
      " · Placa " +
      (sobre.placa || "-");

    mostrarMensaje("");
    modal.style.display = "flex";

    cargarDocumentos();
  }

  function cargarDocumentos() {
    const contenedor = modalElement.querySelector(
      ".modal-documento-contenido"
    );

    contenedor.innerHTML = "<p>Cargando documentos...</p>";

    fetch(
      (window.APP_BASE || "") +
        "/api/sobres/" +
        encodeURIComponent(sobreActual.id_sobre) +
        "/documentos",
      {
        headers: {
          Accept: "application/json"
        }
      }
    )
      .then(function (response) {
        return response.json().then(function (respuesta) {
          if (!response.ok || !respuesta.ok) {
            throw new Error(
              respuesta.message ||
              "No fue posible consultar los documentos."
            );
          }

          return respuesta;
        });
      })
      .then(function (respuesta) {
        renderizarFormulario(respuesta.documentos || []);
      })
      .catch(function (error) {
        contenedor.innerHTML = "";
        mostrarMensaje(error.message, "error");
      });
  }

  function renderizarFormulario(documentos) {
    const contenedor = modalElement.querySelector(
      ".modal-documento-contenido"
    );

    if (documentos.length === 0) {
      contenedor.innerHTML = `
        <p>
          Este trámite no tiene documentos requeridos configurados.
        </p>

        <button
          type="button"
          class="btn btn-secondary"
          data-volver-opciones>
          Volver
        </button>
      `;

      contenedor
        .querySelector("[data-volver-opciones]")
        .addEventListener("click", volverAOpciones);

      return;
    }

    const filas = documentos.map(function (documento) {
      const idDocumento = Number(documento.Id_documento);
      const marcado = Number(documento.flagdb) > 0;

      return `
        <label class="modal-documento-item">
          <input
            type="checkbox"
            name="documentos_marcados[]"
            value="${idDocumento}"
            ${marcado ? "checked" : ""}>

          <span>
            ${escaparHTML(documento.Documento || "Documento sin nombre")}
          </span>

          <small>
            ${marcado ? "Disponible" : "Pendiente"}
          </small>
        </label>
      `;
    }).join("");

    contenedor.innerHTML = `
      <p class="modal-documento-ayuda">
        Marca los documentos físicos disponibles en el sobre.
      </p>

      <div class="modal-documento-lista">
        ${filas}
      </div>

      <div class="mb-3">
        <label for="select-usuario-documentos">
          Registrado por
        </label>

        <div data-select-usuario-documentos>
          Cargando usuarios...
        </div>
      </div>

      <div class="modal-documento-botones">
        <button
          type="button"
          class="btn btn-secondary"
          data-volver-opciones>
          Volver
        </button>

        <button
          type="button"
          class="btn btn-primary"
          data-guardar-documentos>
          Guardar documentos
        </button>
      </div>
    `;

    contenedor
      .querySelector("[data-volver-opciones]")
      .addEventListener("click", volverAOpciones);

    contenedor
      .querySelector("[data-guardar-documentos]")
      .addEventListener("click", guardarDocumentos);

    cargarSelectorUsuario();
  }

  function cargarSelectorUsuario() {
    const destino = modalElement.querySelector(
      "[data-select-usuario-documentos]"
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
          "select-usuario-documentos",
          usuarios,
          "Selecciona quién registra..."
        );
      })
      .catch(function () {
        destino.textContent = "No fue posible cargar los usuarios.";
      });
  }

  function guardarDocumentos() {
    const contenedor = modalElement.querySelector(
      ".modal-documento-contenido"
    );

    const usuario = contenedor.querySelector(
      "#select-usuario-documentos"
    );

    if (!usuario || !usuario.value) {
      mostrarMensaje(
        "Selecciona quién registra los documentos.",
        "error"
      );
      return;
    }

    const boton = contenedor.querySelector(
      "[data-guardar-documentos]"
    );

    boton.disabled = true;
    boton.textContent = "Guardando...";

    const datos = new URLSearchParams();

    datos.set("id_usuario_registra", usuario.value);

    contenedor
      .querySelectorAll('input[name="documentos_marcados[]"]:checked')
      .forEach(function (checkbox) {
        datos.append("documentos_marcados[]", checkbox.value);
      });

    fetch(
      (window.APP_BASE || "") +
        "/api/sobres/" +
        encodeURIComponent(sobreActual.id_sobre) +
        "/documentos",
      {
        method: "POST",
        headers: {
          "Content-Type":
            "application/x-www-form-urlencoded; charset=UTF-8",
          Accept: "application/json"
        },
        body: datos.toString()
      }
    )
      .then(function (response) {
        return response.json().then(function (respuesta) {
          if (!response.ok || !respuesta.ok) {
            throw new Error(
              respuesta.message ||
              "No fue posible guardar los documentos."
            );
          }

          return respuesta;
        });
      })
      .then(function (respuesta) {
        mostrarMensaje(respuesta.message, "success");
        boton.textContent = "Documentos guardados";
      })
      .catch(function (error) {
        mostrarMensaje(error.message, "error");
        boton.disabled = false;
        boton.textContent = "Guardar documentos";
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
      ".modal-documento-mensaje"
    );

    elemento.textContent = mensaje || "";
    elemento.dataset.tipo = tipo || "";
  }

  function escaparHTML(valor) {
    const elemento = document.createElement("span");

    elemento.textContent = String(valor);

    return elemento.innerHTML;
  }

  window.ModalDocumento = {
    abrir: abrir
  };
})();