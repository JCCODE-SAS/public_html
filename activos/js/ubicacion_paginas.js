/**
 * ===============================================================
 * ?? ubicacion_paginas.js
 * ---------------------------------------------------------------
 * Centraliza las rutas de backend y frontend para ambiente local y producci�n.
 * ===============================================================
 */


// Detecta si estamos en localhost o en un dominio real
const isLocalhost = ["localhost", "127.0.0.1"].includes(window.location.hostname);

const baseURL = isLocalhost
    ? `${window.location.origin}/public_html/`
    : `${window.location.origin}/dashboard/`;

// Listado de rutas absolutas (ajustadas para producci�n y local)
window.rutas = {
    // Páginas principales
    login:            isLocalhost ? baseURL + "index.php" : window.location.origin + "/index.php",
    validarLogin:     isLocalhost ? baseURL + "sesiones/validar_login.php" : window.location.origin + "/sesiones/validar_login.php",
    verificarSesion:  isLocalhost ? baseURL + "sesiones/verificar_sesion.php" : window.location.origin + "/sesiones/verificar_sesion.php",
    logout:           isLocalhost ? baseURL + "sesiones/destruir_sesion.php" : window.location.origin + "/sesiones/destruir_sesion.php",
    dashboard:        isLocalhost ? baseURL + "dashboard.php" : window.location.origin + "/dashboard.php",

    // Secciones del dashboard
    usuarios:         baseURL + "paginas/usuarios/usuarios.php",
    operarios:        baseURL + "paginas/operarios/operarios.php",
    whatsapp:         baseURL + "paginas/whatsapp/whatsapp.php",
    perfil:           baseURL + "paginas/perfil/perfil.php",
    configuracion:    baseURL + "paginas/configuracion/configuracion.php",

    // Scripts JS
    indexJS:          baseURL + "../activos/js/index.js",
    dashboardJS:      baseURL + "../activos/js/dashboard.js",
    permisosJS:       baseURL + "../activos/js/permisos.js",
    ubicacionPaginasJS: baseURL + "../activos/js/ubicacion_paginas.js",

    // WhatsApp módulo
    whatsappJS:       baseURL + "paginas/whatsapp/comportamientos/whatsapp.js",
    whatsappApi:      baseURL + "paginas/whatsapp/api/",

    // CSS
    cssOutput:        baseURL + "../activos/css/output.css",
    cssIndexLogin:    baseURL + "../activos/css/index_login.css",
    cssDashboard:     baseURL + "../activos/css/dashboard.css",
};

// Debug en consola (opcional)
console.log("?? Rutas cargadas:", window.rutas);

// Marca que las rutas est�n listas
window.__RUTAS_LOADED__ = true;