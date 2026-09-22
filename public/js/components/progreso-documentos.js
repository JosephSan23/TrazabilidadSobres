(function () {
  "use strict";

  function renderizar(contenedor, disponibles, total) {
    if (!contenedor) {
      return;
    }

    const cantidadTotal = Math.max(0, Number(total) || 0);
    const cantidadDisponibles = Math.min(
      cantidadTotal,
      Math.max(0, Number(disponibles) || 0),
    );

    const porcentaje =
      cantidadTotal === 0
        ? 0
        : Math.round((cantidadDisponibles / cantidadTotal) * 100);

    contenedor.innerHTML = `
      <div class="d-flex justify-content-between align-items-center mb-1">
        <strong>Documentos disponibles</strong>

        <span>
          ${cantidadDisponibles} de ${cantidadTotal}
          (${porcentaje}%)
        </span>
      </div>

      <div
        class="progress"
        role="progressbar"
        aria-label="Progreso de documentos del sobre"
        aria-valuemin="0"
        aria-valuemax="${cantidadTotal}"
        aria-valuenow="${cantidadDisponibles}">
        <div
          class="progress-bar bg-success"
          style="width: ${porcentaje}%">
          ${porcentaje}%
        </div>
      </div>
    `;
  }

  window.ProgresoDocumentos = {
    renderizar: renderizar,
  };
})();
