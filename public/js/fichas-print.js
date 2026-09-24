"use strict";

(function () {
  function renderizarCodigos() {
    const elementos = document.querySelectorAll("[data-barcode-value]");

    let todosGenerados = elementos.length > 0;

    elementos.forEach(function (elemento) {
      const valor = (elemento.dataset.barcodeValue || "").trim();

      if (!valor || typeof window.JsBarcode !== "function") {
        todosGenerados = false;
        return;
      }

      try {
        elemento.replaceChildren();

        window.JsBarcode(elemento, valor, {
          format: "CODE128",
          displayValue: false,
          height: 52,
          margin: 0,
          width: 1.5,
        });
      } catch (error) {
        todosGenerados = false;

        console.error("No se pudo generar el código de barras:", valor, error);
      }
    });

    return todosGenerados;
  }

  function imprimirFichas() {
    const codigosListos = renderizarCodigos();

    if (!codigosListos) {
      alert(
        "No fue posible preparar todos los códigos de barras. " +
          "No imprimas todavía; revisa la consola.",
      );

      return;
    }

    requestAnimationFrame(function () {
      requestAnimationFrame(function () {
        window.print();
      });
    });
  }

  const printButton = document.querySelector("[data-print-fichas]");

  renderizarCodigos();

  if (printButton) {
    printButton.addEventListener("click", imprimirFichas);
  }

  window.addEventListener("beforeprint", renderizarCodigos);
})();
