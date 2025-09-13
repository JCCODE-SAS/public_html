/**
 * ===============================================================
 * 📄 permisos.js
 * ---------------------------------------------------------------
 * Controla la visibilidad de menús según rol de sesión:
 *   - admin
 *   - user
 *   - operador
 * ===============================================================
 */

document.addEventListener("DOMContentLoaded", () => {

    // Validar que rutas esté definido
    if (typeof rutas === "undefined") {
        console.error("Error: rutas no está definido. Asegúrate de cargar ubicacion_paginas.js antes.");
        return;
    }

    // Consultar sesión activa
    fetch(rutas.verificarSesion)
        .then(res => res.json())
        .then(data => {
            if (data.logged) {
                // Mostrar menús según rol
                switch (data.role) {
                    case "admin":
                        console.log("Admin logueado");
                        document.getElementById("menuAdmin")?.classList.remove("hidden");
                        break;
                    case "user":
                        console.log("Usuario estándar logueado");
                        document.getElementById("menuUser")?.classList.remove("hidden");
                        break;
                    case "operador":
                        console.log("Operador logueado");
                        document.getElementById("menuOperador")?.classList.remove("hidden");
                        break;
                }
            } else {
                console.warn("No hay sesión activa, redirigiendo a login");
                window.location.href = rutas.login;
            }
        })
        .catch(err => console.error("Error en permisos.js:", err));
});
