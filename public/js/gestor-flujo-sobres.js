(function () {
  "use strict";

  if (window.GestorFlujoSobres) {
    return;
  }

  function hayModalDeFlujoVisible() {
    const modales = document.querySelectorAll("[data-flujo-sobres]");

    for (let i = 0; i < modales.length; i++) {
      const estilo = window.getComputedStyle(modales[i]);
      if (estilo.display !== "none") {
        return true;
      }
    }

    return false;
  }

  function hayLotePendiente() {
    return !!(
      window.ModoEscaneo &&
      typeof window.ModoEscaneo.hayLotePendiente === "function" &&
      window.ModoEscaneo.hayLotePendiente()
    );
  }

  function notificarPosibleFinDeFlujo() {
    requestAnimationFrame(function () {
      if (hayModalDeFlujoVisible()) {
        return;
      }

      if (hayLotePendiente()) {
        return;
      }

      document.dispatchEvent(new CustomEvent("flujo-sobres:finalizado"));
    });
  }

  window.GestorFlujoSobres = {
    notificarPosibleFinDeFlujo: notificarPosibleFinDeFlujo,
    hayModalDeFlujoVisible: hayModalDeFlujoVisible,
  };

  document.addEventListener("flujo-sobres:finalizado", function () {
    location.reload();
  });
})();