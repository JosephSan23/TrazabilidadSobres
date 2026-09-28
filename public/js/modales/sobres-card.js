(function () {
  "use strict";

  document.addEventListener("click", function (event) {
    const boton = event.target.closest("[data-abrir-modal-opciones]");

    if (!boton) {
      return;
    }

    const datosSobre = {
      id_sobre: boton.dataset.idSobre,
      id_tramite: boton.dataset.idTramite,
      codigo_sobre: boton.dataset.codigoSobre,
      placa: boton.dataset.placa,
      estado: boton.dataset.estado,
      responsable: boton.dataset.responsable,
    };

    if (typeof window.abrirModalOpciones === "function") {
      window.abrirModalOpciones(datosSobre);
    } else {
      console.error(
        "[sobres-card] abrirModalOpciones no está definido. ¿Está cargado modal-opciones.js?",
      );
    }
  });

  function iniciarProgresoCards() {
    document
      .querySelectorAll("[data-sobre-card]")
      .forEach(cargarProgresoDocumentos);

    observarNuevasCards();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", iniciarProgresoCards);
  } else {
    iniciarProgresoCards();
  }

  function cargarProgresoDocumentos(card) {
    const idSobre = card.dataset.idSobre;

    const barra = card.querySelector("[data-progreso-barra]");

    if (!idSobre || !barra) {
      return;
    }

    if (card.dataset.progresoCargado === "1") {
      return;
    }

    card.dataset.progresoCargado = "1";

    fetch(
      (window.APP_BASE || "") +
        "/api/sobres/" +
        encodeURIComponent(idSobre) +
        "/documentos",
      {
        headers: {
          Accept: "application/json",
        },
      },
    )
      .then(function (response) {
        return response.json().then(function (respuesta) {
          if (!response.ok || !respuesta.ok) {
            throw new Error();
          }

          return respuesta;
        });
      })
      .then(function (respuesta) {
        const documentos = respuesta.documentos || [];

        const disponibles = documentos.filter(function (documento) {
          return (
            documento.validado === true && documento.estado_custodia === null
          );
        }).length;

        renderizarProgreso(barra, disponibles, documentos.length);
        renderizarEstadoDocumentos(card, disponibles, documentos.length);
      })
      .catch(function () {
        renderizarProgreso(barra, 0, 0);

        const badge = card.querySelector("[data-estado-documentos]");
        if (badge) {
          badge.className = "badge-documentacion badge-documentacion--error";
          badge.textContent = "Documentación no disponible";
        }
      });
  }

  function renderizarProgreso(contenedor, disponibles, total) {
    const porcentaje =
      total === 0 ? 0 : Math.round((disponibles / total) * 100);

    const estadoProgreso = obtenerEstadoProgreso(porcentaje);

    contenedor.innerHTML = `
        <div
            class="custodia-progreso-mini custodia-progreso-mini--${estadoProgreso}"
            role="progressbar"
            aria-label="Progreso de documentos"
            aria-valuemin="0"
            aria-valuemax="${total}"
            aria-valuenow="${disponibles}">

            <div
                class="custodia-progreso-mini__avance"
                style="width: calc(${porcentaje}%)"
            </div>

            <span class="custodia-progreso-mini__texto">
                ${porcentaje}%
            </span>
        </div>
    `;
  }

  function obtenerEstadoDocumentacion(disponibles, total) {
    if (total === 0) {
      return {
        clase: "sin-configuracion",
        texto: "Sin documentos configurados",
      };
    }

    if (disponibles === 0) {
      return {
        clase: "pendiente",
        texto: "Pendiente de verificación",
      };
    }

    if (disponibles < total) {
      return {
        clase: "incompleta",
        texto: "Documentación incompleta",
      };
    }

    return {
      clase: "completa",
      texto: "Documentación completa",
    };
  }

  function renderizarEstadoDocumentos(card, disponibles, total) {
    const badge = card.querySelector("[data-estado-documentos]");

    if (!badge) {
      return;
    }

    const estado = obtenerEstadoDocumentacion(disponibles, total);

    badge.className =
      "badge-documentacion badge-documentacion--" + estado.clase;

    badge.textContent = estado.texto;
  }

  function cargarProgresosEn(contenedor) {
    if (!(contenedor instanceof Element)) {
      return;
    }

    if (contenedor.matches("[data-sobre-card]")) {
      cargarProgresoDocumentos(contenedor);
    }

    contenedor
      .querySelectorAll("[data-sobre-card]")
      .forEach(cargarProgresoDocumentos);
  }

  function observarNuevasCards() {
    const observador = new MutationObserver(function (mutaciones) {
      mutaciones.forEach(function (mutacion) {
        mutacion.addedNodes.forEach(function (nodo) {
          cargarProgresosEn(nodo);
        });
      });
    });

    observador.observe(document.body, {
      childList: true,
      subtree: true,
    });
  }

  function obtenerEstadoProgreso(porcentaje) {
    if (porcentaje === 100) {
      return "completo";
    }

    if (porcentaje >= 51) {
      return "advertencia";
    }

    if (porcentaje >= 1) {
      return "pendiente";
    }

    return "sin-avance";
  }
})();
