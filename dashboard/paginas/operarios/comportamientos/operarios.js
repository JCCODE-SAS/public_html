/**
 * ===============================================================
 * 📄 OPERARIOS.JS v2.4 - Módulo de Gestión de Operarios COPFLOW
 * ===============================================================
 * 
 * FUNCIONALIDADES PRINCIPALES:
 * • CRUD completo de operarios (ver, crear, editar, estado)
 * • Sistema de filtros AJAX igual a usuarios (filtros, paginación y per_page)
 * • Paginación dinámica sin recargas
 * • Actualización automática de estadísticas
 * • Modales integrados y responsive (REUTILIZADOS desde dashboard)
 * • Manejo robusto de errores
 * 
 * 👨‍💻 Desarrollado por: Diomedez98 (JCCODE-SAS)
 * ===============================================================
 */
(function() {
    "use strict";

    console.log('%c[operarios.js] Script cargado', 'color: #22c55e; font-weight: bold; font-size: 16px');

    let searchTimeout = null;
    let filterTimeout = null;
    let filtroFormListenersIniciados = false;

    const CONFIG = {
        VERSION: '2.4',
        DEBUG_MODE: true,
        DEBOUNCE_SEARCH: 600,
        DEBOUNCE_FILTER: 400,
        STATS_UPDATE_INTERVAL: 180000
    };

    function log(mensaje, tipo = 'info') {
        if (!CONFIG.DEBUG_MODE) return;
        const timestamp = new Date().toLocaleTimeString();
        const prefix  = `[${timestamp}] [OPERARIOS.JS v${CONFIG.VERSION}]`;
        switch(tipo) {
            case 'error':   console.error(`${prefix} ❌`, mensaje); break;
            case 'warn':    console.warn(  `${prefix} ⚠️`, mensaje); break;
            case 'success': console.log(  `${prefix} ✅`, mensaje); break;
            default:        console.log(  `${prefix} 🔄`, mensaje); break;
        }
    }

    // =========================
    // CRUD FUNCIONES
    // =========================

    function verOperario(id) {
        log(`Iniciando visualización de operario ID: ${id}`);
        if (typeof window.abrirModalVerOperario === 'function') {
            window.abrirModalVerOperario(id);
        } else {
            log("Modal de visualización no disponible, usando fallback", 'warn');
            fetch("/dashboard/paginas/operarios/api/obtener_operarios.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success && data.operario) {
                        mostrarModalExito(
                            "Detalle de Operario",
                            `Operario: ${data.operario.nombre} (${data.operario.usuario})`
                        );
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
                fetch("/dashboard/paginas/operarios/api/cambiar_estado_operarios.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id, nuevo_estado: String(nuevoEstado) })
                })
                .then(res => res.json())
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
            const celdaDisp = fila.querySelector("td:nth-child(5) span");
            if (celdaDisp) {
                celdaDisp.textContent = nuevoEstado === 1 ? 'Disponible' : 'No disponible';
                celdaDisp.className = 
                    "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium " +
                    (nuevoEstado === 1 ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800");
            }
            const btn = fila.querySelector(".btn-toggle-disponibilidad");
            if (btn) {
                btn.innerHTML = `<i class="${nuevoEstado === 1 ? "ri-pause-circle-line" : "ri-play-circle-line"}"></i> ${nuevoEstado === 1 ? 'Deshabilitar' : 'Habilitar'}`;
                btn.setAttribute("data-id", operarioId);
                btn.setAttribute("data-disponible", nuevoEstado);
                btn.setAttribute("data-nombre", nombre);
                btn.title = nuevoEstado === 1 ? "Deshabilitar" : "Habilitar";
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
    fetch("/dashboard/paginas/operarios/api/estadisticas_operarios.php")
        .then(res => res.text())
        .then(text => {
            try {
                const stats = JSON.parse(text);
                const elementos = {
                    total:       document.getElementById("stats-total"),
                    available:   document.getElementById("stats-available"),
                    unavailable: document.getElementById("stats-unavailable")
                };
                if (elementos.total       && stats.total       !== undefined) elementos.total.textContent       = stats.total;
                if (elementos.available   && stats.available   !== undefined) elementos.available.textContent   = stats.available;
                if (elementos.unavailable && stats.unavailable !== undefined) elementos.unavailable.textContent = stats.unavailable;
                log("Estadísticas actualizadas", 'success');
            } catch (e) {
                log(`Respuesta inesperada en estadísticas: ${text.substring(0,200)}`, 'error');
            }
        })
        .catch(err => {
            log(`Error al cargar estadísticas: ${err.message}`, 'error');
        });
    }

    // =========================
    // SISTEMA DE FILTROS AJAX
    // =========================

    function filtrarOperariosAjax(extraParams) {
        const filtroForm = document.getElementById("filtroOperariosForm");
        if (!filtroForm) return;
        try {
            const params = new URLSearchParams(new FormData(filtroForm));
            if (extraParams) {
                Object.entries(extraParams).forEach(([k, v]) => params.set(k, v));
            }
            fetch("/dashboard/paginas/operarios/operarios.php?" + params.toString(), {
                method: "GET",
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(r => r.text())
            .then(html => {
                const tempDiv = document.createElement("div");
                tempDiv.innerHTML = html;
                const nuevoWrapper  = tempDiv.querySelector("#operarios-wrapper");
                const actualWrapper = document.getElementById("operarios-wrapper");
                if (nuevoWrapper && actualWrapper) {
                    actualWrapper.replaceWith(nuevoWrapper);
                    filtroFormListenersIniciados = false;
                    bindFiltroFormEvents();
                    bindPaginacionAjax();
                    log("Operarios filtrados y recargados por AJAX", 'success');
                }
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

        const searchInput = filtroForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.oninput = () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(filtrarOperariosAjax, CONFIG.DEBOUNCE_SEARCH);
            };
        }

        const selects = filtroForm.querySelectorAll("select");
        selects.forEach(sel => {
            sel.onchange = () => {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(filtrarOperariosAjax, CONFIG.DEBOUNCE_FILTER);
            };
        });

        filtroForm.onsubmit = e => {
            e.preventDefault();
            filtrarOperariosAjax();
        };

        const limpiarBtn = document.getElementById("limpiarFiltrosBtn");
        if (limpiarBtn) limpiarBtn.onclick = e => {
            e.preventDefault();
            limpiarTodosFiltros();
        };
    }

    function limpiarTodosFiltros() {
        clearTimeout(searchTimeout);
        clearTimeout(filterTimeout);
        const filtroForm = document.getElementById("filtroOperariosForm");
        if (filtroForm) filtroForm.reset();
        filtrarOperariosAjax();
    }

    function bindPaginacionAjax() {
        document.querySelectorAll("a.pagination-btn").forEach(enlace => {
            if (enlace.__bound) return;
            enlace.addEventListener("click", e => {
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
                    if (page) filtrarOperariosAjax({ page });
                }
            });
            enlace.__bound = true;
        });

        document.querySelectorAll("form#formPerPage select[name='per_page']").forEach(sel => {
            sel.onchange = () => filtrarOperariosAjax({ per_page: sel.value, page: 1 });
        });

        const goToForm = document.getElementById("formGoTo");
        if (goToForm) {
            goToForm.onsubmit = e => {
                e.preventDefault();
                const input = goToForm.querySelector('[name=page]');
                if (input && input.value) filtrarOperariosAjax({ page: input.value });
            };
        }
    }

    // =========================
    // INICIALIZACIÓN
    // =========================

    window.inicializarOperarios = function() {
        log(`Inicializando módulo de operarios COPFLOW v${CONFIG.VERSION}`, 'success');
        filtroFormListenersIniciados = false;
        bindFiltroFormEvents();
        bindPaginacionAjax();
        actualizarEstadisticasOperarios();

        // ← SOLO UN setInterval (persistente en window)
        if (!window._copflow_statsIntervalOperarios) {
            window._copflow_statsIntervalOperarios = setInterval(
                actualizarEstadisticasOperarios,
                CONFIG.STATS_UPDATE_INTERVAL
            );
        }

        log("Módulo de operarios inicializado correctamente", 'success');
    };

    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => {
                if (window.inicializarOperarios) window.inicializarOperarios();
            }, 100);
        });
    } else {
        setTimeout(() => {
            if (window.inicializarOperarios) window.inicializarOperarios();
        }, 100);
    }

    // Delegación de eventos para acciones dinámicas (fuera de DOMContentLoaded)
    document.body.addEventListener('click', function(e) {
        // Ver operario
        if (e.target.closest('.btn-ver-operario')) {
            const id = e.target.closest('.btn-ver-operario').dataset.id;
            if (window.abrirModalVerOperario) window.abrirModalVerOperario(id);
        }
        // Editar operario
        if (e.target.closest('.btn-editar-operario')) {
            const id = e.target.closest('.btn-editar-operario').dataset.id;
            if (window.editarOperario) window.editarOperario(id);
        }
        // Toggle disponibilidad
        if (e.target.closest('.btn-toggle-disponibilidad')) {
            const btn = e.target.closest('.btn-toggle-disponibilidad');
            const id = btn.dataset.id;
            const disponible = btn.dataset.disponible;
            const nombre = btn.dataset.nombre;
            if (window.toggleDisponibilidad) window.toggleDisponibilidad(id, disponible, nombre);
        }
    });

    // EXPORTACIÓN DE FUNCIONES GLOBALES
    window.verOperario                   = verOperario;
    window.editarOperario                = editarOperario;
    window.toggleDisponibilidad          = toggleDisponibilidad;
    window.mostrarModalNuevoOperario     = mostrarModalNuevoOperario;
    window.actualizarEstadisticasOperarios = actualizarEstadisticasOperarios;
    window.limpiarTodosFiltros           = limpiarTodosFiltros;
    window.filtrarOperariosAjax          = filtrarOperariosAjax;
    window.actualizarFilaOperarioEnTabla = actualizarFilaOperarioEnTabla;

    log(`Módulo operarios.js v${CONFIG.VERSION} cargado completamente`, 'success');
})();