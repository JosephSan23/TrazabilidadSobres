(function () {
  "use strict";

  let usuariosCache = null;

  function obtenerUsuarios() {
    if (usuariosCache) {
      return Promise.resolve(usuariosCache);
    }

    return fetch((window.APP_BASE || "") + "/usuarios/disponibles", {
      headers: {
        Accept: "application/json",
      },
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("No se pudo cargar la lista de usuarios.");
        }
        return response.json();
      })
      .then(function (usuarios) {
        usuariosCache = usuarios;
        return usuarios;
      });
  }

  function construirSelectHTML(id, usuarios, placeholder) {
    const opciones = usuarios
      .map(function (u) {
        return (
          '<option value="' +
          u.id_usuario +
          '">' +
          u.nombre_completo +
          " - " +
          u.nombre_rol +
          "</option>"
        );
      })
      .join("");

    return (
      '<select id="' +
      id +
      '">' +
      '<option value="">' +
      placeholder +
      "</option>" +
      opciones +
      "</select>"
    );
  }

  window.SelectorUsuarios = {
    obtenerUsuarios: obtenerUsuarios,
    construirSelectHTML: construirSelectHTML,
  };
})();
