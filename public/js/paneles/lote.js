(function () {
  "use strict";

  let loteActual = [];
  let panelElement = null;

  function crearPanelSiNoExiste() {
    if (panelElement) {
      return panelElement;
    }

    const panel = document.createElement("div");
    panel.id = "panel-lote-sobres";
    panel.className = "panel-lote";
    panel.style.display = "none";

    panel.innerHTML = `
    <div class="panel-lote__header">
        <strong>Sobres seleccionados: <span class="panel-lote__contador">0</span></strong>
    </div>
    <ul class="panel-lote__lista"></ul>

    <div class="panel-lote__form-asignar" style="display:none;"></div>

    <div class="panel-lote__acciones">
        <button type="button" class="panel-lote__guardar">Guardar / Asignar todos</button>
        <button type="button" class="panel-lote__vaciar">Vaciar</button>
        <button type="button" class="panel-lote__cancelar">Cancelar modo lote</button>
    </div>
`;

    document.body.appendChild(panel);

    panel
      .querySelector(".panel-lote__guardar")
      .addEventListener("click", guardarLote);
    panel
      .querySelector(".panel-lote__vaciar")
      .addEventListener("click", vaciarLote);
    panel
      .querySelector(".panel-lote__cancelar")
      .addEventListener("click", cancelarModoLote);

    panelElement = panel;
    return panel;
  }

  function agregarSobreALote(datosSobre) {
    const yaEsta = loteActual.some(function (s) {
      return String(s.id_sobre) === String(datosSobre.id_sobre);
    });

    if (yaEsta) {
      console.log("[lote] Sobre duplicado, se ignora:", datosSobre.id_sobre);
      return;
    }

    loteActual.push(datosSobre);
    renderizarPanel();
  }

  function renderizarPanel() {
    const panel = crearPanelSiNoExiste();

    panel.querySelector(".panel-lote__contador").textContent =
      loteActual.length;

    const lista = panel.querySelector(".panel-lote__lista");
    lista.innerHTML = loteActual
      .map(function (s) {
        return (
          "<li>Trámite #" +
          (s.id_tramite || "-") +
          " · Placa " +
          (s.placa || "-") +
          "</li>"
        );
      })
      .join("");

    panel.style.display =
      loteActual.length > 0 || window.ModoEscaneo.obtenerModo() === "lote"
        ? "block"
        : "none";
  }

  function vaciarLote() {
    loteActual = [];
    renderizarPanel();
  }

  function cancelarModoLote() {
    loteActual = [];
    window.ModoEscaneo.activarModoIndividual();
    if (panelElement) {
      panelElement.style.display = "none";
    }
  }

  function guardarLote() {
    if (loteActual.length === 0) {
      alert("No hay sobres en la lista para asignar.");
      return;
    }

    const panel = crearPanelSiNoExiste();
    const contenedorForm = panel.querySelector(".panel-lote__form-asignar");

    window.SelectorUsuarios.obtenerUsuarios().then(function (usuarios) {
      const htmlResponsable = window.SelectorUsuarios.construirSelectHTML(
        "select-lote-responsable",
        usuarios,
        "Selecciona responsable...",
      );
      const htmlRegistra = window.SelectorUsuarios.construirSelectHTML(
        "select-lote-registra",
        usuarios,
        "¿Quién registra?",
      );

      contenedorForm.innerHTML = `
            <p>Asignar estos ${loteActual.length} sobres a:</p>
            ${htmlResponsable}
            ${htmlRegistra}
            <button type="button" id="btn-confirmar-lote">Confirmar asignación</button>
        `;
      contenedorForm.style.display = "block";

      document
        .getElementById("btn-confirmar-lote")
        .addEventListener("click", confirmarAsignacionLote);
    });
  }

  function confirmarAsignacionLote() {
    const idResponsable = document.getElementById(
      "select-lote-responsable",
    ).value;
    const idRegistra = document.getElementById("select-lote-registra").value;

    if (!idResponsable || !idRegistra) {
      alert("Selecciona ambos usuarios.");
      return;
    }

    const idsSobre = loteActual.map(function (s) {
      return s.id_sobre;
    });

    fetch((window.APP_BASE || "") + "/sobres/asignar-lote", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-Requested-With": "XMLHttpRequest",
      },
      body: JSON.stringify({
        ids_sobre: idsSobre,
        id_usuario_responsable_destino: idResponsable,
        id_usuario_registra: idRegistra,
      }),
    })
      .then(async function (response) {
        const contentType = response.headers.get("content-type") || "";

        if (!response.ok) {
          if (contentType.includes("application/json")) {
            const data = await response.json();
            throw new Error(data.error || "Error al asignar el lote.");
          }
          const texto = await response.text();
          console.error("[lote] Respuesta no JSON:", response.status, texto);
          throw new Error(
            "El servidor respondió " + response.status + " en " + response.url,
          );
        }

        return response.json();
      })
      .then(function (data) {
        console.log("[lote] Asignación exitosa:", data);
        loteActual = [];

        const panel = crearPanelSiNoExiste();
        panel.querySelector(".panel-lote__form-asignar").style.display = "none";
        panel.querySelector(".panel-lote__form-asignar").innerHTML = "";

        renderizarPanel();
      })
      .catch(function (error) {
        console.error("[lote] Error:", error);
        alert(error.message || "Ocurrió un error al asignar los sobres.");
      });
  }

  // si no quedan sobres pendientes en el lote se oculta el panel y cambia a individual.
  document.addEventListener("modo-escaneo:cambiado", function (event) {
    if (event.detail.modo === "lote") {
      renderizarPanel();
    } else if (loteActual.length === 0 && panelElement) {
      panelElement.style.display = "none";
    }
  });

  window.agregarSobreALote = agregarSobreALote;
})();
