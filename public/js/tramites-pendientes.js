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
            selected.set(numericId, {
              id: numericId,
              placa: "",
            });
          }
        });

        return selected;
      }

      Object.values(saved).forEach((tramite) => {
        const id = Number(tramite.id);

        if (id > 0) {
          selected.set(id, {
            id,
            placa: String(tramite.placa || ""),
          });
        }
      });

      return selected;
    } catch {
      return new Map();
    }
  };

  const saveSelectedTramites = (selectedTramites) => {
    const data = Object.fromEntries(selectedTramites);

    sessionStorage.setItem(storageKey, JSON.stringify(data));
  };

  const selectedTramites = getSelectedTramites();

  const checkboxes = [...document.querySelectorAll("[data-tramite-selection]")];

  const selectPageCheckbox = document.querySelector(
    "[data-select-current-page]",
  );

  const selectedCount = document.querySelector("[data-selected-count]");

  const openModalButton = document.querySelector(
    "[data-open-generation-modal]",
  );

  const generationModal = document.querySelector("[data-generation-modal]");

  const selectedPlates = document.querySelector("[data-selected-plates]");

  const closeModalButtons = document.querySelectorAll(
    "[data-close-generation-modal]",
  );

  const bulkForm = document.querySelector("[data-bulk-fichas-form]");

  const addTramite = (id, placa) => {
    selectedTramites.set(id, {
      id,
      placa,
    });
  };

  const updateSelectedPlates = () => {
    if (!selectedPlates) {
      return;
    }

    const template = document.querySelector("[data-location-select-template]");

    if (!(template instanceof HTMLTemplateElement)) {
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

        const locationCell = document.createElement("td");

        const locationTemplate = template.content.cloneNode(true);
        const select = locationTemplate.querySelector("select");

        if (select) {
          select.name = `ubicaciones[${tramite.id}]`;
          select.setAttribute(
            "aria-label",
            `Ubicación inicial para trámite ${tramite.id}`,
          );
        }

        locationCell.appendChild(locationTemplate);

        row.append(plateCell, procedureCell, locationCell);

        selectedPlates.appendChild(row);
      });
  };

  const updateInterface = () => {
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

    updateSelectedPlates();
  };

  checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
      const idTramite = Number(checkbox.value);
      const placa = String(checkbox.dataset.tramitePlaca || "");

      if (checkbox.checked) {
        addTramite(idTramite, placa);
      } else {
        selectedTramites.delete(idTramite);
      }

      saveSelectedTramites(selectedTramites);
      updateInterface();
    });
  });

  if (selectPageCheckbox) {
    selectPageCheckbox.addEventListener("change", () => {
      checkboxes.forEach((checkbox) => {
        const idTramite = Number(checkbox.value);
        const placa = String(checkbox.dataset.tramitePlaca || "");

        if (selectPageCheckbox.checked) {
          addTramite(idTramite, placa);
        } else {
          selectedTramites.delete(idTramite);
        }
      });

      saveSelectedTramites(selectedTramites);
      updateInterface();
    });
  }

  if (openModalButton && generationModal) {
    openModalButton.addEventListener("click", () => {
      updateSelectedPlates();

      if (typeof generationModal.showModal === "function") {
        generationModal.showModal();
      }
    });
  }

  closeModalButtons.forEach((button) => {
    button.addEventListener("click", () => {
      generationModal?.close();
    });
  });

  if (generationModal) {
    generationModal.addEventListener("cancel", (event) => {
      event.preventDefault();
      generationModal.close();
    });
  }

  if (bulkForm) {
    bulkForm.addEventListener("submit", (event) => {
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
  }

  const filterForm = document.querySelector("[data-pending-filter-form]");

  if (filterForm) {
    let filterTimer;

    filterForm.querySelectorAll('input[type="search"]').forEach((input) => {
      input.addEventListener("input", () => {
        window.clearTimeout(filterTimer);

        filterTimer = window.setTimeout(() => {
          filterForm.submit();
        }, 400);
      });
    });
  }

  updateInterface();
})();
