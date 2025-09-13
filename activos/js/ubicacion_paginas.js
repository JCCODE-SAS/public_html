/**
 * ===============================================================
 * 📄 ubicacion_paginas.js
 * ---------------------------------------------------------------
 * Centraliza las rutas de backend para evitar problemas al mover
 * entre localhost y servidor en producción.
 * ===============================================================
 */

// Detecta si estamos en localhost
const isLocalhost = ["localhost", "127.0.0.1"].includes(window.location.hostname);

// Ajusta la base según el entorno
const baseURL = isLocalhost
    ? `${window.location.origin}/public_html/`   // si accedes por http://localhost/public_html
    : `${window.location.origin}/`;              // si tu vhost apunta directo a public_html

// Listado de rutas absolutas
window.rutas = {
    login: baseURL + "index.php",
    validarLogin: baseURL + "sesiones/validar_login.php",
    verificarSesion: baseURL + "sesiones/verificar_sesion.php",
    logout: baseURL + "sesiones/destruir_sesion.php",
    dashboard: baseURL + "dashboard/dashboard.php",
    usuarios: baseURL + "dashboard/paginas/usuarios/usuarios.php",
    operarios: baseURL + "dashboard/paginas/operarios/operarios.php",
    reportes: baseURL + "dashboard/paginas/reportes/reportes.php",
    perfil: baseURL + "dashboard/paginas/perfil/perfil.php",
    configuracion: baseURL + "dashboard/paginas/configuracion/configuracion.php",

    // Rutas a los scripts JS
    indexJS: baseURL + "activos/js/index.js",
    dashboardJS: baseURL + "activos/js/dashboard.js",
    permisosJS: baseURL + "activos/js/permisos.js",
    ubicacionPaginasJS: baseURL + "activos/js/ubicacion_paginas.js",

    // Nuevas rutas a CSS
    cssOutput: baseURL + "activos/css/output.css",
    cssIndexLogin: baseURL + "activos/css/index_login.css",
    cssDashboard: baseURL + "activos/css/dashboard.css",
};

// Debug en consola (opcional)
console.log("🌐 Rutas cargadas:", window.rutas);

// Marcar que las rutas están disponibles
window.__RUTAS_LOADED__ = true;