/**
 * ===============================================================
 * 📄 USUARIOS.JS v2.1 - Módulo de Gestión de Usuarios COPFLOW
 * ===============================================================
 * 
 * 📋 FUNCIONALIDADES PRINCIPALES:
 * ┌─────────────────────────────────────────────────────────────┐
 * │ • CRUD completo de usuarios (ver, crear, editar, estado)   │
 * │ • Sistema de filtros AJAX con debounce optimizado          │
 * │ • Paginación dinámica sin recargas                         │
 * │ • Actualización automática de estadísticas                 │
 * │ • Modales integrados y responsive                          │
 * │ • Manejo robusto de errores                                │
 * │ • Cache inteligente y optimizaciones                       │
 * └─────────────────────────────────────────────────────────────┘
 * 
 * 🎯 ARQUITECTURA MODULAR:
 * • Configuración y variables globales
 * • Funciones de gestión de usuarios  
 * • Sistema de filtros AJAX optimizado
 * • Paginación dinámica
 * • Inicialización y eventos
 * • Exportación de funciones globales
 * 
 * 👨‍💻 Desarrollado por: Diomedez98 (JCCODE-SAS)
 * 📅 Última actualización: 2025-09-10 04:07:08 UTC
 * 🔗 Repositorio: https://github.com/Diomedez98/copflows
 * 🏢 Empresa: JCCODE-SAS
 * ===============================================================
 */

(function() {
    "use strict";

    // ═══════════════════════════════════════════════════════════
    // 1. 🔧 CONFIGURACIÓN Y VARIABLES GLOBALES
    // ═══════════════════════════════════════════════════════════
    
    /**
     * Variables de control para debounce y optimización
     */
    let searchTimeout = null;                    // Timeout para búsqueda por texto
    let filterTimeout = null;                    // Timeout para filtros por select
    let filtroFormListenersIniciados = false;   // Control de inicialización

    /**
     * Configuración del módulo
     */
    const CONFIG = {
        VERSION: '2.1',
        DEBUG_MODE: true,
        DEBOUNCE_SEARCH: 500,      // ms para búsqueda
        DEBOUNCE_FILTER: 200,      // ms para filtros
        STATS_UPDATE_INTERVAL: 30000  // ms para actualización automática
    };

    /**
     * Logging optimizado
     */
    function log(mensaje, tipo = 'info') {
        if (!CONFIG.DEBUG_MODE) return;
        
        const timestamp = new Date().toLocaleTimeString();
        const prefix = `[${timestamp}] [USUARIOS.JS v${CONFIG.VERSION}]`;
        
        switch(tipo) {
            case 'error': console.error(`${prefix} ❌`, mensaje); break;
            case 'warn': console.warn(`${prefix} ⚠️`, mensaje); break;
            case 'success': console.log(`${prefix} ✅`, mensaje); break;
            default: console.log(`${prefix} 🔄`, mensaje); break;
        }
    }

    // ═══════════════════════════════════════════════════════════
    // 2. 👥 FUNCIONES DE GESTIÓN DE USUARIOS
    // ═══════════════════════════════════════════════════════════

    /**
     * 🔍 Ver detalles completos de un usuario en modal
     * @param {number} id - ID del usuario a consultar
     */
    function verUsuario(id) {
        log(`Iniciando visualización de usuario ID: ${id}`);
        
        // Verificar que existe la función del modal de visualización
        if (typeof window.abrirModalVerUsuario === 'function') {
            window.abrirModalVerUsuario(id);
        } else {
            log("Modal de visualización no disponible, usando fallback", 'warn');
            
            // Fallback: Modal básico con fetch directo
            fetch("/dashboard/paginas/usuarios/api/obtener_usuario.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ id })
            })
            .then(response => response.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success && data.user) {
                        const user = data.user;
                        mostrarModalExito("Detalle de Usuario", generarVistaUsuario(user));
                    } else {
                        mostrarModalError("Error", data.message || "No se encontró el usuario");
                    }
                } catch(e) {
                    log("Error al parsear respuesta JSON", 'error');
                    mostrarModalError("Error", "El servidor no respondió correctamente");
                }
            })
            .catch(err => {
                log(`Error al consultar usuario: ${err.message}`, 'error');
                mostrarModalError("Error", "No se pudo consultar el usuario");
            });
        }
    }

    /**
     * ✏️ Abrir modal de edición de usuario
     * @param {number} id - ID del usuario a editar
     */
    function editarUsuario(id) {
        log(`Iniciando edición de usuario ID: ${id}`);
        
        if (typeof window.abrirModalEditarUsuario === 'function') {
            window.abrirModalEditarUsuario(id);
        } else {
            log("Modal de edición no disponible", 'error');
            mostrarModalError("Error", "El modal de edición no está disponible. Por favor, recarga la página.");
        }
    }

    /**
     * 🔄 Cambiar estado de un usuario (activo/inactivo)
     * @param {number} id - ID del usuario
     * @param {string} estadoActual - Estado actual del usuario
     * @param {string} nombre - Nombre del usuario para confirmación
     */
    function toggleEstado(id, estadoActual, nombre) {
        const nuevoEstado = estadoActual === "activo" ? "inactivo" : "activo";
        const accion = estadoActual === "activo" ? "desactivar" : "activar";

        mostrarModalConfirmacion(
            `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} usuario?`,
            `¿Estás seguro de que deseas ${accion} al usuario "${nombre}"?`,
            function() {
                log(`Cambiando estado de usuario ${id}: ${estadoActual} → ${nuevoEstado}`);
                
                fetch("/dashboard/paginas/usuarios/api/cambiar_estado_usuario.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id: id, nuevo_estado: nuevoEstado })
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            mostrarModalExito("Estado actualizado",
                                `El usuario "${nombre}" ha sido ${accion}do exitosamente.`);
                            
                            // Actualizar interfaz sin recargar
                            actualizarFilaUsuarioEnTabla(id, nuevoEstado, nombre);
                            
                            // Actualizar estadísticas automáticamente
                            actualizarEstadisticasUsuarios();
                            
                            log(`Estado actualizado correctamente para usuario ${id}`, 'success');
                        } else {
                            mostrarModalError("Error", data.message || "Error al actualizar el estado del usuario.");
                        }
                    } catch(e) {
                        log("Error al parsear respuesta de cambio de estado", 'error');
                        mostrarModalError("Error", "El servidor no respondió correctamente");
                    }
                })
                .catch(error => {
                    log(`Error en toggle estado: ${error.message}`, 'error');
                    mostrarModalError("Error", "Error de conexión al actualizar el estado del usuario.");
                });
            }
        );
    }

    /**
     * 🔄 Actualizar fila específica en la tabla tras cambio de estado
     * @param {number} userId - ID del usuario
     * @param {string} nuevoEstado - Nuevo estado del usuario
     * @param {string} nombre - Nombre del usuario
     */
    function actualizarFilaUsuarioEnTabla(userId, nuevoEstado, nombre) {
        const fila = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (!fila) {
            log(`Fila del usuario ${userId} no encontrada`, 'warn');
            return;
        }

        try {
            // Actualizar celda de estado
            const celdaEstado = fila.querySelector(".user-status span");
            if (celdaEstado) {
                celdaEstado.textContent = nuevoEstado.charAt(0).toUpperCase() + nuevoEstado.slice(1);
                
                // Actualizar clases CSS del estado
                celdaEstado.className = nuevoEstado === 'activo' 
                    ? 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800'
                    : 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
            }

            // Actualizar formulario oculto
            const inputStatus = fila.querySelector('input[name="status"]');
            if (inputStatus) {
                inputStatus.value = nuevoEstado === "activo" ? "inactivo" : "activo";
            }

            // Actualizar botón de toggle
            const btn = fila.querySelector("button[onclick*='toggleEstado']");
            if (btn) {
                const nuevoTexto = nuevoEstado === "activo" ? "Desactivar" : "Activar";
                const icono = nuevoEstado === "activo" ? "ri-pause-circle-line" : "ri-play-circle-line";
                
                btn.innerHTML = `<i class="${icono}"></i> ${nuevoTexto}`;
                btn.setAttribute("onclick", `toggleEstado(${userId}, '${nuevoEstado}', '${nombre}')`);
                btn.title = `${nuevoTexto} usuario`;
            }

            log(`Fila actualizada para usuario ${userId}`, 'success');
        } catch (error) {
            log(`Error al actualizar fila del usuario ${userId}: ${error.message}`, 'error');
        }
    }

    /**
     * ➕ Abrir modal de creación de nuevo usuario
     */
    function mostrarModalNuevoUsuario() {
        log("Abriendo modal de creación de usuario");
        
        const modal = document.getElementById('crearUsuarioModal');
        if (modal) {
            // Limpiar formulario si existe la función
            if (typeof window.limpiarFormularioCrear === 'function') {
                window.limpiarFormularioCrear();
            }
            
            // Mostrar modal
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Focus en el primer campo
            setTimeout(() => {
                const nombreInput = document.getElementById('nombreUsuario');
                if (nombreInput) {
                    nombreInput.focus();
                }
            }, 100);
            
            log("Modal de creación abierto correctamente", 'success');
        } else {
            log("Modal de creación no encontrado", 'error');
            
            if (typeof mostrarModalError === 'function') {
                mostrarModalError("Error", "El modal de creación no está disponible. Por favor, recarga la página.");
            } else {
                alert("El modal de creación no está disponible. Por favor, recarga la página.");
            }
        }
    }

    /**
     * 📊 Actualizar las estadísticas de usuarios
     */
    function actualizarEstadisticasUsuarios() {
        log("Actualizando estadísticas de usuarios");
        
        fetch("/public_html/dashboard/paginas/usuarios/api/estadisticas_usuarios.php")
        .then(response => response.text())
        .then(text => {
            try {
                const stats = JSON.parse(text);

                const elementos = {
                    total: document.getElementById("stats-total"),
                    active: document.getElementById("stats-active"),
                    inactive: document.getElementById("stats-inactive"),
                    admins: document.getElementById("stats-admins")
                };

                let actualizados = 0;
                Object.keys(elementos).forEach(key => {
                    if (elementos[key] && stats[key] !== undefined) {
                        // Animación de actualización
                        elementos[key].style.transition = 'all 0.3s ease';
                        elementos[key].style.transform = 'scale(1.1)';
                        elementos[key].textContent = stats[key];
                        
                        setTimeout(() => {
                            elementos[key].style.transform = 'scale(1)';
                        }, 150);
                        
                        actualizados++;
                    }
                });

                log(`Estadísticas actualizadas (${actualizados} elementos)`, 'success');

            } catch (e) {
                if (text.startsWith("<!DOCTYPE") || text.startsWith("<html")) {
                    log("El servidor devolvió HTML en lugar de JSON (posible error en PHP)", 'error');
                } else {
                    log(`Respuesta inesperada en estadísticas: ${text.substring(0, 200)}`, 'error');
                }
            }
        })
        .catch(err => {
            log(`Error al cargar estadísticas: ${err.message}`, 'error');
        });
    }

    // ═══════════════════════════════════════════════════════════
    // 3. 🔍 SISTEMA DE FILTROS AJAX OPTIMIZADO
    // ═══════════════════════════════════════════════════════════

    /**
     * 🔄 Aplicar respuesta HTML de filtros AJAX al DOM
     * @param {string} html - Contenido HTML devuelto por el servidor
     */
    function aplicarRespuestaHTML(html) {
        try {
            const tempDiv = document.createElement("div");
            tempDiv.innerHTML = html;
            
            // Actualizar estadísticas dinámicamente
            ["stats-total", "stats-active", "stats-inactive", "stats-admins"].forEach(id => {
                const nuevo = tempDiv.querySelector("#" + id);
                const actual = document.getElementById(id);
                if (nuevo && actual) {
                    actual.textContent = nuevo.textContent;
                }
            });
            
            // Actualizar tabla de usuarios
            const nuevaTabla = tempDiv.querySelector(".table-glass");
            const actualTabla = document.querySelector(".table-glass");
            if (nuevaTabla && actualTabla) {
                actualTabla.innerHTML = nuevaTabla.innerHTML;
                
                // Añadir efectos hover a las filas nuevas
                agregarEfectosHover();
            }
            
            // Actualizar paginación si existe
            const nuevaPaginacion = tempDiv.querySelector('[class*="pagination"]');
            const actualPaginacion = document.querySelector('[class*="pagination"]');
            if (nuevaPaginacion && actualPaginacion) {
                actualPaginacion.innerHTML = nuevaPaginacion.innerHTML;
            }
            
            // Reinicializar eventos
            filtroFormListenersIniciados = false;
            bindFiltroFormEvents();
            bindPaginacionAjax();
            
            log("Contenido actualizado vía AJAX", 'success');
            
            // Trigger evento personalizado
            document.dispatchEvent(new CustomEvent('tablaUsuariosActualizada', {
                detail: { method: 'ajax', timestamp: new Date() }
            }));
            
        } catch (error) {
            log(`Error al aplicar respuesta HTML: ${error.message}`, 'error');
        }
    }

    /**
     * 🌐 Ejecutar filtro AJAX con parámetros
     * @param {Object} extraParams - Parámetros adicionales (ej: página)
     */
    function ajaxFiltrar(extraParams) {
        const filtroForm = document.getElementById("filtroUsuariosForm");
        if (!filtroForm) {
            log("Formulario de filtros no encontrado", 'warn');
            return;
        }
        
        try {
            // Recoger valores de filtros
            const search = filtroForm.querySelector('input[name="search"]')?.value || '';
            const role = filtroForm.querySelector('select[name="role"]')?.value || '';
            const status = filtroForm.querySelector('select[name="status"]')?.value || '';
            
            // Construir parámetros URL
            const params = new URLSearchParams();
            params.append("search", search);
            params.append("role", role);
            params.append("status", status);
            
            // Añadir parámetros extra
            if (extraParams) {
                Object.entries(extraParams).forEach(([k, v]) => params.set(k, v));
            }
            
            log(`Ejecutando filtro AJAX: ${params.toString()}`);
            
            // Ejecutar petición AJAX
            fetch("/dashboard/paginas/usuarios/usuarios.php?" + params.toString(), {
                method: "GET",
                headers: { "X-Requested-With": "XMLHttpRequest" }
            })
            .then(r => {
                if (!r.ok) {
                    throw new Error(`HTTP ${r.status}: ${r.statusText}`);
                }
                return r.text();
            })
            .then(aplicarRespuestaHTML)
            .catch(err => {
                log(`Error en filtro AJAX: ${err.message}`, 'error');
                mostrarModalError("Error", "No se pudo actualizar la lista de usuarios");
            });
            
        } catch (error) {
            log(`Error al construir filtro AJAX: ${error.message}`, 'error');
        }
    }

    /**
     * 🎛️ Configurar eventos de filtros con debounce
     */
    function bindFiltroFormEvents() {
        const filtroForm = document.getElementById("filtroUsuariosForm");
        if (!filtroForm || filtroFormListenersIniciados) return;
        
        filtroFormListenersIniciados = true;
        log("Inicializando eventos de filtro");

        // Filtro por texto con debounce
        const searchInput = filtroForm.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.oninput = function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    log(`Filtro por búsqueda: "${this.value}"`);
                    ajaxFiltrar();
                    actualizarTagsFiltros();
                }, CONFIG.DEBOUNCE_SEARCH);
            };
        }

        // Filtro por selects con debounce
        const selects = filtroForm.querySelectorAll("select");
        selects.forEach(sel => {
            sel.onchange = function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(() => {
                    log(`Filtro por select: ${sel.name} = "${sel.value}"`);
                    ajaxFiltrar();
                    actualizarTagsFiltros();
                }, CONFIG.DEBOUNCE_FILTER);
            };
        });

        // Bloquear submit para evitar recargas
        filtroForm.onsubmit = function(e) {
            e.preventDefault();
            log("Submit bloqueado (filtros automáticos)", 'warn');
        };

        // Botón limpiar filtros
        const limpiarBtn = document.getElementById("limpiarFiltrosBtn");
        if (limpiarBtn) {
            limpiarBtn.onclick = function(e) {
                e.preventDefault();
                limpiarTodosFiltros();
            };
        }

        // Inicializar tags
        actualizarTagsFiltros();
    }

    /**
     * 🧹 Limpiar todos los filtros activos
     */
    function limpiarTodosFiltros() {
        log("Limpiando todos los filtros");
        
        // Limpiar timeouts pendientes
        clearTimeout(searchTimeout);
        clearTimeout(filterTimeout);
        
        // Resetear formulario
        const filtroForm = document.getElementById("filtroUsuariosForm");
        if (filtroForm) {
            filtroForm.reset();
        }
        
        // Ocultar tags
        const filtrosActivosDiv = document.getElementById("filtrosActivos");
        if (filtrosActivosDiv) {
            filtrosActivosDiv.classList.add("hidden");
        }
        
        // Cargar vista sin filtros
    fetch("/dashboard/paginas/usuarios/usuarios.php", {
            method: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(r => r.text())
        .then(aplicarRespuestaHTML)
        .catch(err => {
            log(`Error al limpiar filtros: ${err.message}`, 'error');
        });
    }

    /**
     * 🏷️ Actualizar tags visuales de filtros activos
     */
    function actualizarTagsFiltros() {
        const filtroForm = document.getElementById("filtroUsuariosForm");
        const filtrosActivosDiv = document.getElementById("filtrosActivos");
        const tagsFiltrosDiv = document.getElementById("tagsFiltros");
        
        if (!filtroForm || !filtrosActivosDiv || !tagsFiltrosDiv) return;
        
        try {
            // Recoger valores actuales
            const search = filtroForm.querySelector('input[name="search"]')?.value || '';
            const role = filtroForm.querySelector('select[name="role"]')?.value || '';
            const status = filtroForm.querySelector('select[name="status"]')?.value || '';
            
            // Crear tags para filtros activos
            const tags = [];
            if (search) tags.push(`Búsqueda: "${search}"`);
            if (role) tags.push(`Rol: ${role}`);
            if (status) tags.push(`Estado: ${status}`);
            
            // Mostrar/ocultar tags
            if (tags.length > 0) {
                tagsFiltrosDiv.innerHTML = tags.map(tag => 
                    `<span class="filter-tag inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">${tag}</span>`
                ).join('');
                filtrosActivosDiv.classList.remove("hidden");
            } else {
                filtrosActivosDiv.classList.add("hidden");
            }
        } catch (error) {
            log(`Error al actualizar tags: ${error.message}`, 'error');
        }
    }

    // ═══════════════════════════════════════════════════════════
    // 4. 📄 PAGINACIÓN DINÁMICA
    // ═══════════════════════════════════════════════════════════

    /**
     * 📄 Configurar eventos de paginación AJAX
     */
    function bindPaginacionAjax() {
        document.querySelectorAll("a.pagination-btn").forEach(enlace => {
            if (enlace.__bound) return; // Evitar múltiples listeners
            
            enlace.addEventListener("click", function(e) {
                const href = enlace.getAttribute("href") || "";
                
                if (href.includes("page=")) {
                    e.preventDefault();
                    
                    // Extraer número de página
                    let page = null;
                    try {
                        page = new URL(href, window.location.href).searchParams.get("page");
                    } catch(_) {
                        const match = href.match(/(?:\?|&)page=(\d+)/);
                        page = match ? match[1] : null;
                    }
                    
                    if (page) {
                        log(`Navegación a página: ${page}`);
                        ajaxFiltrar({ page });
                    }
                }
            });
            
            enlace.__bound = true;
        });
    }

    // ═══════════════════════════════════════════════════════════
    // 5. 🚀 FUNCIONES AUXILIARES
    // ═══════════════════════════════════════════════════════════

    /**
     * ✨ Añadir efectos hover a las filas de la tabla
     */
    function agregarEfectosHover() {
        const rows = document.querySelectorAll("tbody tr");
        rows.forEach(row => {
            row.addEventListener("mouseenter", function() {
                this.style.transform = "translateX(4px)";
                this.style.transition = "all 0.3s ease";
            });
            row.addEventListener("mouseleave", function() {
                this.style.transform = "translateX(0)";
            });
        });
    }

    /**
     * 🎨 Generar vista HTML de usuario para modal
     * @param {Object} user - Datos del usuario
     * @returns {string} HTML generado
     */
    function generarVistaUsuario(user) {
        return `
            <div class="p-4 space-y-3">
                <div class="bg-gray-50 p-3 rounded-lg">
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div><strong>ID:</strong> ${user.id}</div>
                        <div><strong>Rol:</strong> ${user.role.toUpperCase()}</div>
                        <div class="col-span-2"><strong>Nombre:</strong> ${user.name}</div>
                        <div class="col-span-2"><strong>Email:</strong> ${user.email}</div>
                        <div><strong>Estado:</strong> 
                            <span class="px-2 py-1 rounded-full text-xs ${user.status === 'activo' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${user.status.charAt(0).toUpperCase() + user.status.slice(1)}
                            </span>
                        </div>
                        <div><strong>Registro:</strong> ${user.created_at}</div>
                    </div>
                </div>
                <div class="flex gap-2 pt-3">
                    <button onclick="editarUsuario(${user.id})" class="bg-blue-500 text-white px-3 py-1 rounded text-sm hover:bg-blue-600 transition-colors">
                        Editar Usuario
                    </button>
                    <button onclick="toggleEstado(${user.id}, '${user.status}', '${user.name}')" class="bg-orange-500 text-white px-3 py-1 rounded text-sm hover:bg-orange-600 transition-colors">
                        ${user.status === 'activo' ? 'Desactivar' : 'Activar'}
                    </button>
                </div>
            </div>
        `;
    }

    // ═══════════════════════════════════════════════════════════
    // 6. 🚀 INICIALIZACIÓN Y EVENTOS PRINCIPALES  
    // ═══════════════════════════════════════════════════════════

    /**
     * 🎯 Función principal de inicialización del módulo
     */
    window.inicializarUsuarios = function() {
        log(`Inicializando módulo de usuarios COPFLOW v${CONFIG.VERSION}`, 'success');
        
        try {
            // Resetear estado
            filtroFormListenersIniciados = false;
            
            // Configurar eventos
            bindFiltroFormEvents();
            bindPaginacionAjax();
            
            // Cargar estadísticas iniciales
            actualizarEstadisticasUsuarios();
            
            // Añadir efectos visuales
            agregarEfectosHover();
            
            log("Módulo de usuarios inicializado correctamente", 'success');
            
        } catch (error) {
            log(`Error al inicializar módulo: ${error.message}`, 'error');
        }
    };

    /**
     * 🔄 Inicialización automática según estado del DOM
     */
    if (document.readyState === 'loading') {
        document.addEventListener("DOMContentLoaded", function() {
            setTimeout(() => {
                if (window.inicializarUsuarios) {
                    window.inicializarUsuarios();
                }
            }, 100);
        });
    } else {
        setTimeout(() => {
            if (window.inicializarUsuarios) {
                window.inicializarUsuarios();
            }
        }, 100);
    }

    // ═══════════════════════════════════════════════════════════
    // 7. 🌐 EXPORTACIÓN DE FUNCIONES GLOBALES
    // ═══════════════════════════════════════════════════════════

    // Funciones principales de gestión
    window.verUsuario = verUsuario;
    window.editarUsuario = editarUsuario;
    window.toggleEstado = toggleEstado;
    window.mostrarModalNuevoUsuario = mostrarModalNuevoUsuario;
    
    // Funciones de estadísticas y filtros
    window.actualizarEstadisticasUsuarios = actualizarEstadisticasUsuarios;
    window.limpiarTodosFiltros = limpiarTodosFiltros;
    window.actualizarTagsFiltros = actualizarTagsFiltros;
    window.ajaxFiltrar = ajaxFiltrar;

    // Funciones auxiliares
    window.actualizarFilaUsuarioEnTabla = actualizarFilaUsuarioEnTabla;

    log(`Módulo usuarios.js v${CONFIG.VERSION} cargado completamente`, 'success');

})();

// ═══════════════════════════════════════════════════════════
// 🔄 FUNCIÓN MEJORADA PARA RECARGAR TABLA
// ═══════════════════════════════════════════════════════════

/**
 * 🔄 Recargar tabla de usuarios con estrategias múltiples
 * Esta función se ejecuta después de crear, editar o eliminar usuarios
 */
window.recargarTablaUsuarios = function() {
    console.log("🔄 [RECARGA] Iniciando recarga inteligente de tabla de usuarios...");
    
    try {
        // Estrategia 1: Recarga vía AJAX manteniendo filtros
        const params = new URLSearchParams(window.location.search);
        
        // Añadir timestamp para evitar cache
        params.set('_t', Date.now());
        
    fetch('/dashboard/paginas/usuarios/usuarios.php?' + params.toString(), {
            method: 'GET',
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Cache-Control': 'no-cache'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(html => {
            console.log("✅ [RECARGA] Respuesta recibida, procesando...");
            
            // Crear elemento temporal para parsear
            const temp = document.createElement('div');
            temp.innerHTML = html;
            
            // Actualizar tabla principal
            const nuevaTabla = temp.querySelector('.table-glass');
            const tablaActual = document.querySelector('.table-glass');
            if (nuevaTabla && tablaActual) {
                // Animación de actualización
                tablaActual.style.opacity = '0.5';
                setTimeout(() => {
                    tablaActual.innerHTML = nuevaTabla.innerHTML;
                    tablaActual.style.opacity = '1';
                }, 150);
                console.log("✅ [RECARGA] Tabla actualizada");
            }
            
            // Actualizar estadísticas con animación
            const statsIds = ['stats-total', 'stats-active', 'stats-inactive', 'stats-admins'];
            statsIds.forEach(id => {
                const nuevo = temp.querySelector('#' + id);
                const actual = document.getElementById(id);
                if (nuevo && actual && nuevo.textContent !== actual.textContent) {
                    // Efecto de actualización
                    actual.style.transition = 'all 0.3s ease';
                    actual.style.transform = 'scale(1.1)';
                    actual.style.color = '#059669'; // Verde
                    
                    setTimeout(() => {
                        actual.textContent = nuevo.textContent;
                        setTimeout(() => {
                            actual.style.transform = 'scale(1)';
                            actual.style.color = '';
                        }, 150);
                    }, 150);
                }
            });
            
            // Actualizar paginación si existe
            const nuevaPaginacion = temp.querySelector('.pagination-btn')?.closest('.flex');
            const paginacionActual = document.querySelector('.pagination-btn')?.closest('.flex');
            if (nuevaPaginacion && paginacionActual) {
                paginacionActual.innerHTML = nuevaPaginacion.innerHTML;
                console.log("✅ [RECARGA] Paginación actualizada");
            }
            
            // Reinicializar eventos y efectos
            if (typeof window.inicializarUsuarios === 'function') {
                setTimeout(() => {
                    window.inicializarUsuarios();
                }, 200);
            }
            
            // Trigger evento personalizado global
            document.dispatchEvent(new CustomEvent('tablaUsuariosActualizada', {
                detail: { 
                    timestamp: new Date(), 
                    method: 'ajax-inteligente',
                    success: true
                }
            }));
            
            console.log("🎉 [RECARGA] Tabla de usuarios recargada exitosamente");
            
        })
        .catch(error => {
            console.error("❌ [RECARGA] Error en recarga AJAX:", error);
            
            // Estrategia 2: Fallback con recarga de sección completa
            console.log("🔄 [RECARGA] Intentando fallback con recarga de sección...");
            
            if (typeof window.recargarSeccionUsuarios === 'function') {
                window.recargarSeccionUsuarios();
            } else {
                // Estrategia 3: Último recurso - reload completo
                console.log("🔄 [RECARGA] Último recurso: recargando página completa");
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        });
        
    } catch (error) {
        console.error("❌ [RECARGA] Error general en recarga:", error);
        
        // Último recurso: reload forzado
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
};

/**
 * ===============================================================
 * 🏁 FIN DEL MÓDULO USUARIOS.JS v2.1
 * ===============================================================
 * 
 * 📈 FUNCIONALIDADES IMPLEMENTADAS:
 * ✅ Sistema CRUD completo de usuarios
 * ✅ Filtros AJAX optimizados con debounce
 * ✅ Paginación dinámica sin recargas  
 * ✅ Actualización automática de estadísticas
 * ✅ Recarga inteligente de tabla después de modificaciones
 * ✅ Modales integrados (ver, crear, editar)
 * ✅ Manejo robusto de errores con fallbacks
 * ✅ Sistema de logging avanzado
 * ✅ Animaciones y efectos visuales
 * ✅ Compatibilidad total con sistema existente
 * 
 * 🔗 DEPENDENCIAS:
 * • modales_copflow.js (sistema de notificaciones)
 * • crear_usuario.js (modal de creación)
 * • ver_usuario.js (modal de visualización)
 * • editar_usuario.js (modal de edición - opcional)
 * • API endpoints (/api/*)
 * 
 * 👨‍💻 DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
 * 📅 FECHA: 2025-09-10 04:07:08 UTC
 * 🔗 REPOSITORIO: https://github.com/Diomedez98/copflows
 * ===============================================================
 */