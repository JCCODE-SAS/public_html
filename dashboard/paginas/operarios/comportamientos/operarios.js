/**
 * ===============================================================
 * 📄 OPERARIOS.JS v2.3 - Módulo de Gestión de Operarios COPFLOW
 * ===============================================================
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * • CRUD completo de operarios (ver, crear, editar, estado)
 * • Sistema de filtros AJAX con debounce optimizado
 * • Paginación dinámica sin recargas
 * • Actualización automática de estadísticas
 * • Modales integrados y responsive (REUTILIZADOS desde dashboard)
 * • Manejo robusto de errores
 * • Cache inteligente y optimizaciones
 * 
 * 👨‍💻 Desarrollado por: Diomedez98 (JCCODE-SAS)
 * ===============================================================
 */

(function() {
    "use strict";

    // CONFIGURACIÓN Y VARIABLES GLOBALES
    let searchTimeout = null;
    let filterTimeout = null;
    let filtroFormListenersIniciados = false;

    const CONFIG = {
        VERSION: '2.3',
        DEBUG_MODE: true,
        DEBOUNCE_SEARCH: 1200,
        DEBOUNCE_FILTER: 600,
        STATS_UPDATE_INTERVAL: 180000
    };

    function log(mensaje, tipo = 'info') {
        if (!CONFIG.DEBUG_MODE) return;
        const timestamp = new Date().toLocaleTimeString();
        const prefix = `[${timestamp}] [OPERARIOS.JS v${CONFIG.VERSION}]`;
        switch(tipo) {
            case 'error': console.error(`${prefix} ❌`, mensaje); break;
            case 'warn': console.warn(`${prefix} ⚠️`, mensaje); break;
            case 'success': console.log(`${prefix} ✅`, mensaje); break;
            default: console.log(`${prefix} 🔄`, mensaje); break;
        }
    }

    // =========================
    // MODAL UNIVERSAL - REUTILIZADO
    // =========================
    // Confirmación
    function mostrarModalConfirmacion(titulo, mensaje, callback) {
        if (typeof window.mostrarModalConfirmacion === 'function') {
            window.mostrarModalConfirmacion(titulo, mensaje, callback);
        } else {
            // Fallback
            if (confirm(`${titulo}\n${mensaje}`)) callback && callback();
        }
    }
    // Error
    function mostrarModalError(titulo, mensaje) {
        if (typeof window.mostrarModalError === 'function') {
            window.mostrarModalError(titulo, mensaje);
        } else {
            alert(`${titulo}: ${mensaje}`);
        }
    }
    // Éxito
    function mostrarModalExito(titulo, mensaje) {
        if (typeof window.mostrarModalExito === 'function') {
            window.mostrarModalExito(titulo, mensaje);
        } else {
            alert(`${titulo}: ${mensaje}`);
        }
    }

    // =========================
    // FUNCIONES DE GESTIÓN DE OPERARIOS
    // =========================

    function verOperario(id) {
        log(`Iniciando visualización de operario ID: ${id}`);
        if (typeof window.abrirModalVerOperario === 'function') {
            window.abrirModalVerOperario(id);
        } else {
            log("Modal de visualización no disponible, usando fallback", 'warn');
            fetch("/public_html/dashboard/paginas/operarios/api/obtener_operarios.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success && data.operario) {
                        mostrarModalExito("Detalle de Operario", `Operario: ${data.operario.nombre} (${data.operario.usuario})`);
                    } else {
                        mostrarModalError("Error", data.message || "No se encontró el operario");
                    }
                } catch(e) {
                    log("Error al parsear respuesta JSON", 'error');
                    mostrarModalError("Error", "El servidor no respondió correctamente");
                }
            })
            .catch(err => {
                log(`Error al consultar operario: ${err.message}`, 'error');
                mostrarModalError("Error", "No se pudo consultar el operario");
            });
        }
    }

    function editarOperario(id) {
        log(`Iniciando edición de operario ID: ${id}`);
        if (typeof window.abrirModalEditarOperario === 'function') {
            window.abrirModalEditarOperario(id);
        } else {
            log("Modal de edición no disponible", 'error');
            mostrarModalError("Error", "El modal de edición no está disponible. Por favor, recarga la página.");
        }
    }

    function toggleDisponibilidad(id, disponibleActual, nombre) {
        const nuevoEstado = disponibleActual === 1 ? 0 : 1;
        const accion = disponibleActual === 1 ? "desactivar" : "activar";

        mostrarModalConfirmacion(
            `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} operario?`,
            `¿Estás seguro de que deseas ${accion} al operario "${nombre}"?`,
            function() {
                fetch("/public_html/dashboard/paginas/operarios/api/cambiar_estado_operarios.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id: id, nuevo_estado: String(nuevoEstado) })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarModalExito(
                            "Estado actualizado",
                            `El operario "${nombre}" ha sido ${accion}do exitosamente.`
                        );
                        actualizarFilaOperarioEnTabla(id, nuevoEstado, nombre);
                        actualizarEstadisticasOperarios();
                    } else {
                        mostrarModalError("Error", data.message || "Error al actualizar la disponibilidad.");
                    }
                })
                .catch(error => {
                    log(`Error en toggle disponibilidad: ${error.message}`, 'error');
                    mostrarModalError("Error", "Error de conexión al actualizar la disponibilidad.");
                });
            }
        );
    }

    function actualizarFilaOperarioEnTabla(operarioId, nuevoEstado, nombre) {
        const fila = document.querySelector(`tr[data-operario-id="${operarioId}"]`);
        if (!fila) {
            log(`Fila del operario ${operarioId} no encontrada`, 'warn');
            return;
        }
        try {
            // Actualiza la celda de disponibilidad
            const celdaDisp = fila.querySelector("td:nth-child(5) span");
            if (celdaDisp) {
                celdaDisp.textContent = nuevoEstado === 1 ? 'Disponible' : 'No disponible';
                celdaDisp.className =
                    "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium " +
                    (nuevoEstado === 1 ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800");
            }
            // Actualiza el botón de toggle
            const btn = fila.querySelector("button[onclick*='toggleDisponibilidad']");
            if (btn) {
                btn.innerHTML = `<i class="${nuevoEstado === 1 ? "ri-pause-circle-line" : "ri-play-circle-line"}"></i> ${nuevoEstado === 1 ? 'Deshabilitar' : 'Habilitar'}`;
                btn.setAttribute("onclick", `toggleDisponibilidad(${operarioId}, ${nuevoEstado}, '${nombre}')`);
                btn.title = `${nuevoEstado === 1 ? "Deshabilitar" : "Habilitar"} operario`;
            }
            log(`Fila actualizada para operario ${operarioId}`, 'success');
        } catch (error) {
            log(`Error al actualizar fila del operario ${operarioId}: ${error.message}`, 'error');
        }
    }

    function mostrarModalNuevoOperario() {
        log("Abriendo modal de creación de operario");
        const modal = document.getElementById('crearOperarioModal');
        if (modal) {
            if (typeof window.limpiarFormularioCrearOperario === 'function') {
                window.limpiarFormularioCrearOperario();
            }
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                const nombreInput = document.getElementById('nombreOperario');
                if (nombreInput) nombreInput.focus();
            }, 100);
            log("Modal de creación de operario abierto correctamente", 'success');
        } else {
            mostrarModalError('Error', 'El modal de creación no está disponible.');
        }
    }
    window.mostrarModalNuevoOperario = mostrarModalNuevoOperario;

    function actualizarEstadisticasOperarios() {
        log("Actualizando estadísticas de operarios");
        fetch("/public_html/dashboard/paginas/operarios/api/estadisticas_operarios.php")
        .then(response => response.text())
        .then(text => {
            try {
                const stats = JSON.parse(text);
                const elementos = {
                    total: document.getElementById("stats-total"),
                    available: document.getElementById("stats-available"),
                    unavailable: document.getElementById("stats-unavailable")
                };
                if (elementos.total && stats.total !== undefined) elementos.total.textContent = stats.total;
                if (elementos.available && stats.available !== undefined) elementos.available.textContent = stats.available;
                if (elementos.unavailable && stats.unavailable !== undefined) elementos.unavailable.textContent = stats.unavailable;
                log(`Estadísticas actualizadas`, 'success');
            } catch (e) {
                log(`Respuesta inesperada en estadísticas: ${text.substring(0, 200)}`, 'error');
            }
        })
        .catch(err => {
            log(`Error al cargar estadísticas: ${err.message}`, 'error');
        });
    }

    // SISTEMA DE FILTROS AJAX OPTIMIZADO
    function ajaxFiltrar(extraParams) {
        const filtroForm = document.getElementById("filtroOperariosForm");
        if (!filtroForm) return;
        try {
            const search = filtroForm.querySelector('input[name="search"]')?.value || '';
            const status = filtroForm.querySelector('select[name="status"]')?.value || '';
            const params = new URLSearchParams();
            params.append("search", search);
            params.append("status", status);
            if (extraParams) {
                Object.entries(extraParams).forEach(([k, v]) => params.set(k, v));
            }
            fetch("/public_html/dashboard/paginas/operarios/operarios.php?" + params.toString(), {
                method: "GET",
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(r => r.text())
            .then(html => {
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = html;
                // Actualizar tabla
                const nuevaTabla = tempDiv.querySelector(".table-glass");
                const actualTabla = document.querySelector(".table-glass");
                if (nuevaTabla && actualTabla) actualTabla.innerHTML = nuevaTabla.innerHTML;
                // Actualizar paginación
                const nuevaPaginacion = tempDiv.querySelector('.pagination-btn')?.closest('.flex');
                const actualPaginacion = document.querySelector('.pagination-btn')?.closest('.flex');
                if (nuevaPaginacion && actualPaginacion) actualPaginacion.innerHTML = nuevaPaginacion.innerHTML;
                // Reinicializar eventos
                filtroFormListenersIniciados = false;
                bindFiltroFormEvents();
                bindPaginacionAjax();
                log("Contenido actualizado vía AJAX", 'success');
            })
            .catch(err => {
                log(`Error en filtro AJAX: ${err.message}`, 'error');
                mostrarModalError("Error", "No se pudo actualizar la lista de operarios");
            });
        } catch (error) {
            log(`Error al construir filtro AJAX: ${error.message}`, 'error');
        }
    }

    function bindFiltroFormEvents() {
        const filtroForm = document.getElementById("filtroOperariosForm");
        if (!filtroForm || filtroFormListenersIniciados) return;
        filtroFormListenersIniciados = true;
        // Filtro por texto con debounce
        const searchInput = filtroForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.oninput = function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    ajaxFiltrar();
                }, CONFIG.DEBOUNCE_SEARCH);
            };
        }
        // Filtro por select con debounce
        const selects = filtroForm.querySelectorAll("select");
        selects.forEach(sel => {
            sel.onchange = function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    ajaxFiltrar();
                }, CONFIG.DEBOUNCE_FILTER);
            };
        });
        filtroForm.onsubmit = function(e) {
            e.preventDefault();
        };
        // Botón limpiar filtros
        const limpiarBtn = document.getElementById("limpiarFiltrosBtn");
        if (limpiarBtn) limpiarBtn.onclick = function(e) {
            e.preventDefault();
            limpiarTodosFiltros();
        };
    }

    function limpiarTodosFiltros() {
        clearTimeout(searchTimeout);
        clearTimeout(filterTimeout);
        const filtroForm = document.getElementById("filtroOperariosForm");
        if (filtroForm) filtroForm.reset();
        fetch("/public_html/dashboard/paginas/operarios/operarios.php", {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(r => r.text())
        .then(html => {
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;
            const nuevaTabla = tempDiv.querySelector(".table-glass");
            const actualTabla = document.querySelector(".table-glass");
            if (nuevaTabla && actualTabla) actualTabla.innerHTML = nuevaTabla.innerHTML;
            filtroFormListenersIniciados = false;
            bindFiltroFormEvents();
            bindPaginacionAjax();
        })
        .catch(err => {
            log(`Error al limpiar filtros: ${err.message}`, 'error');
            mostrarModalError("Error", "No se pudo limpiar los filtros");
        });
    }

    function bindPaginacionAjax() {
        document.querySelectorAll("a.pagination-btn").forEach(enlace => {
            if (enlace.__bound) return;
            enlace.addEventListener("click", function(e) {
                const href = enlace.getAttribute("href") || "";
                if (href.includes("page=")) {
                    e.preventDefault();
                    let page = null;
                    try {
                        page = new URL(href, window.location.href).searchParams.get("page");
                    } catch(_) {
                        const match = href.match(/(?:\?|&)page=(\d+)/);
                        page = match ? match[1] : null;
                    }
                    if (page) ajaxFiltrar({ page });
                }
            });
            enlace.__bound = true;
        });
    }

    window.inicializarOperarios = function() {
        log(`Inicializando módulo de operarios COPFLOW v${CONFIG.VERSION}`, 'success');
        filtroFormListenersIniciados = false;
        bindFiltroFormEvents();
        bindPaginacionAjax();
        actualizarEstadisticasOperarios();
        setInterval(actualizarEstadisticasOperarios, CONFIG.STATS_UPDATE_INTERVAL);
        log("Módulo de operarios inicializado correctamente", 'success');
    };

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                if (window.inicializarOperarios) window.inicializarOperarios();
            }, 100);
        });
    } else {
        setTimeout(() => {
            if (window.inicializarOperarios) window.inicializarOperarios();
        }, 100);
    }

    // EXPORTACIÓN DE FUNCIONES GLOBALES
    window.verOperario = verOperario;
    window.editarOperario = editarOperario;
    window.toggleDisponibilidad = toggleDisponibilidad;
    window.mostrarModalNuevoOperario = mostrarModalNuevoOperario;
    window.actualizarEstadisticasOperarios = actualizarEstadisticasOperarios;
    window.limpiarTodosFiltros = limpiarTodosFiltros;
    window.ajaxFiltrar = ajaxFiltrar;
    window.actualizarFilaOperarioEnTabla = actualizarFilaOperarioEnTabla;

    log(`Módulo operarios.js v${CONFIG.VERSION} cargado completamente`, 'success');

})();