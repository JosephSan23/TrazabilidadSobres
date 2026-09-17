"use strict";

(() => {
  sessionStorage.removeItem("custodia.tramitesPendientesSeleccionados");
  const barcodeElements = document.querySelectorAll("[data-barcode-value]");

  barcodeElements.forEach((element) => {
    const value = element.dataset.barcodeValue;

    if (!value || typeof window.JsBarcode !== "function") {
      return;
    }

    window.JsBarcode(element, value, {
      format: "CODE128",
      displayValue: false,
      height: 52,
      margin: 0,
      width: 1.5,
    });
  });

  const printButton = document.querySelector("[data-print-fichas]");

  if (printButton) {
    printButton.addEventListener("click", () => {
      window.print();
    });
  }
})();
