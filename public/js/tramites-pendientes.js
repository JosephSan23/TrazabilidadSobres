"use strict";

(() => {
  const storageKey = "custodia.tramitesPendientesSeleccionados";

  const getSelectedTramites = () => {
    try {
      const saved = JSON.parse(sessionStorage.getItem(storageKey) || "{}");
      const selected = new Map();

      if (Array.isArray(saved)) {
        saved.forEach((id) => {
          const numericId = Number(id);
          if (numericId > 0) {
            selected.set(numericId, { id: numericId, placa: "" });
          }
        });
        return selected;
      }

      Object.values(saved).forEach((tramite) => {
        const id = Number(tramite.id);
        if (id > 0) {
          selected.set(id, { id, placa: String(tramite.placa || "") });
        }
      });

      return selected;
    } catch {
      return new Map();
    }
  };

  const saveSelectedTramites = (selectedTramites) => {
    sessionStorage.setItem(
      storageKey,
      JSON.stringify(Object.fromEntries(selectedTramites)),
    );
  };

  const selectedTramites = getSelectedTramites();

  const addTramite = (id, placa) => selectedTramites.set(id, { id, placa });
  const selectedPlates = document.querySelector(
    "[data-selected-plates]",
  )

  const updateSelectedPlates = () => {
    if (!selectedPlates) {
      return;
    }

    selectedPlates.replaceChildren();

    [...selectedTramites.values()]
      .sort((first, second) => first.placa.localeCompare(second.placa))
      .forEach((tramite) => {
        const row = document.createElement("tr");

        const plateCell = document.createElement("td");
        plateCell.textContent = tramite.placa || "-";

        const procedureCell = document.createElement("td");
        procedureCell.textContent = String(tramite.id);

        row.append(plateCell, procedureCell);

        selectedPlates.appendChild(row);
      });
  };

  const updateInterface = () => {
    const checkboxes = [
      ...document.querySelectorAll("[data-tramite-selection]"),
    ];
    const selectPageCheckbox = document.querySelector(
      "[data-select-current-page]",
    );
    const selectedCount = document.querySelector("[data-selected-count]");
    const openModalButton = document.querySelector(
      "[data-open-generation-modal]",
    );
    const clearSelectionButton = document.querySelector(
      "[data-clear-selected-tramites]",
    );

    checkboxes.forEach((checkbox) => {
      checkbox.checked = selectedTramites.has(Number(checkbox.value));
    });

    if (selectPageCheckbox) {
      selectPageCheckbox.checked =
        checkboxes.length > 0 &&
        checkboxes.every((checkbox) =>
          selectedTramites.has(Number(checkbox.value)),
        );
    }

    if (selectedCount) {
      selectedCount.textContent = String(selectedTramites.size);
    }

    if (openModalButton) {
      openModalButton.disabled = selectedTramites.size === 0;
    }

    if (clearSelectionButton) {
      clearSelectionButton.disabled = selectedTramites.size === 0;
    }

    updateSelectedPlates();
  };

  document.addEventListener("change", (event) => {
    const checkbox = event.target.closest("[data-tramite-selection]");

    if (checkbox) {
      const idTramite = Number(checkbox.value);
      const placa = String(checkbox.dataset.tramitePlaca || "");

      if (checkbox.checked) {
        addTramite(idTramite, placa);
      } else {
        selectedTramites.delete(idTramite);
      }

      saveSelectedTramites(selectedTramites);
      updateInterface();
      return;
    }

    const selectPageCheckbox = event.target.closest(
      "[data-select-current-page]",
    );

    if (selectPageCheckbox) {
      const checkboxes = [
        ...document.querySelectorAll("[data-tramite-selection]"),
      ];

      checkboxes.forEach((cb) => {
        const idTramite = Number(cb.value);
        const placa = String(cb.dataset.tramitePlaca || "");

        if (selectPageCheckbox.checked) {
          addTramite(idTramite, placa);
        } else {
          selectedTramites.delete(idTramite);
        }
      });

      saveSelectedTramites(selectedTramites);
      updateInterface();
    }
  });

  document.addEventListener("click", (event) => {
    if (event.target.closest("[data-open-generation-modal]")) {
      updateSelectedPlates();
      document.querySelector("[data-generation-modal]")?.showModal?.();
      return;
    }

    if (event.target.closest("[data-close-generation-modal]")) {
      document.querySelector("[data-generation-modal]")?.close();
      return;
    }

    if (event.target.closest("[data-clear-selected-tramites]")) {
      selectedTramites.clear();
      sessionStorage.removeItem(storageKey);
      updateInterface();
    }
  });

  document.addEventListener(
    "cancel",
    (event) => {
      if (event.target.matches("[data-generation-modal]")) {
        event.preventDefault();
        event.target.close();
      }
    },
    true,
  );

  document.addEventListener("submit", (event) => {
    const bulkForm = event.target.closest("[data-bulk-fichas-form]");

    if (!bulkForm) return;

    if (selectedTramites.size === 0) {
      event.preventDefault();
      return;
    }

    bulkForm
      .querySelectorAll("[data-selected-tramite-input]")
      .forEach((input) => input.remove());

    selectedTramites.forEach((tramite) => {
      const input = document.createElement("input");
      input.type = "hidden";
      input.name = "tramites[]";
      input.value = String(tramite.id);
      input.dataset.selectedTramiteInput = "true";
      bulkForm.appendChild(input);
    });
  });

  updateInterface();

  document.addEventListener("auto-filter:updated", updateInterface);
})();
