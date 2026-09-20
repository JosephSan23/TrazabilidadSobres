(function () {
  "use strict";

  const SCANNER_MAX_INTERVAL_MS = 40;
  const SCANNER_MIN_LENGTH = 4;
  const SCANNER_TIMEOUT_RESET_MS = 300;

  let buffer = "";
  let lastKeyTime = 0;
  let resetTimer = null;

  function resetBuffer() {
    buffer = "";
    lastKeyTime = 0;
  }

  function scheduleReset() {
    if (resetTimer) {
      clearTimeout(resetTimer);
    }
    resetTimer = setTimeout(resetBuffer, SCANNER_TIMEOUT_RESET_MS);
  }

  function isEditableTarget(target) {
    if (!target) return false;
    const tag = target.tagName ? target.tagName.toLowerCase() : "";
    return tag === "input" || tag === "textarea" || target.isContentEditable;
  }

  document.addEventListener(
    "keydown",
    function (event) {
      const now = Date.now();
      const interval = now - lastKeyTime;

      if (interval > SCANNER_MAX_INTERVAL_MS && buffer.length > 0) {
        resetBuffer();
      }

      if (event.key === "Enter") {
        if (buffer.length >= SCANNER_MIN_LENGTH) {
          const codigoEscaneado = buffer;
          resetBuffer();

          if (isEditableTarget(event.target)) {
            event.preventDefault();
          }

          onSobreEscaneado(codigoEscaneado);
        } else {
          resetBuffer();
        }
        return;
      }

      if (event.key.length === 1) {
        buffer += event.key;
        lastKeyTime = now;
        scheduleReset();
      }
    },
    true,
  );

  function onSobreEscaneado(codigo) {
    console.log("[scanner] Código detectado:", codigo);

    fetch("/sobres/buscar-por-codigo?codigo=" + encodeURIComponent(codigo), {
      method: "GET",
      headers: { "X-Requested-With": "XMLHttpRequest" },
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("No se encontró el sobre para el código " + codigo);
        }
        return response.json();
      })
      .then(function (data) {
        if (data && data.id_sobre) {
          const modo = window.ModoEscaneo
            ? window.ModoEscaneo.obtenerModo()
            : "individual";

          if (modo === "lote") {
            if (typeof window.agregarSobreALote === "function") {
              window.agregarSobreALote(data);
            } else {
              console.error(
                "[scanner] agregarSobreALote no está definido. ¿Está cargado lote.js?",
              );
            }
          } else {
            if (typeof window.abrirModalOpciones === "function") {
              window.abrirModalOpciones(data);
            } else {
              console.error(
                "[scanner] abrirModalOpciones no está definido. ¿Está cargado modal-opciones.js?",
              );
            }
          }
        } else {
          console.warn("[scanner] Respuesta sin id_sobre:", data);
          alert("No se encontró ningún sobre con el código: " + codigo);
        }
      })
      .catch(function (error) {
        console.error("[scanner] Error buscando el sobre:", error);
        alert("No se encontró ningún sobre con el código: " + codigo);
      });
  }

  window.onSobreEscaneado = onSobreEscaneado;
})();
