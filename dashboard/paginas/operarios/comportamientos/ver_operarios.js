/**
 * ===============================================================
 * VER_USUARIO.JS - Modal de Visualización de Usuarios COPFLOW
 * ===============================================================
 * 
 * PROPÓSITO:
 * Maneja la visualización completa de información de usuarios
 * en un modal optimizado y compacto.
 * 
 * DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
 * FECHA CREACIÓN: 2025-09-09
 * REPOSITORIO: https://github.com/Diomedez98/copflows
 * EMPRESA: JCCODE-SAS
 * 
 * ===============================================================
 */

(function() {
    "use strict";

    // ===============================================================
    // VARIABLES GLOBALES
    // ===============================================================
    
    let operarioVisualizando = null;

    // ===============================================================
    // FUNCIONES PRINCIPALES
    // ===============================================================

    /**
     * Abrir modal de visualización de operario
     * @param {number} operarioId - ID del operario a visualizar
     */
    window.abrirModalVerOperario = function(operarioId) {
        console.log("🚀 Abriendo modal de visualización para operario ID:", operarioId);

        resetearModalVer();
        
        const modal = document.getElementById('verOperarioModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        cargarDatosOperarioVer(operarioId);
    };

    /**
     * Cerrar modal de visualización
     */
    window.cerrarModalVerOperario = function() {
        const modal = document.getElementById('verOperarioModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        operarioVisualizando = null;
        console.log("✅ Modal de visualización cerrado");
    };

    /**
     * Resetear modal a estado inicial
     */
    function resetearModalVer() {
        document.getElementById('loadingVerOperario').classList.remove('hidden');
        document.getElementById('contenidoVerOperario').classList.add('hidden');
        document.getElementById('errorVerOperario').classList.add('hidden');
        
        operarioVisualizando = null;
        console.log("🔄 Modal de visualización reseteado");
    }

    // ===============================================================
    // CARGA DE DATOS
    // ===============================================================

    /**
     * Cargar datos del operario para visualización
     * @param {number} operarioId - ID del operario
     */
    function cargarDatosOperarioVer(operarioId) {
        console.log("📡 Cargando datos del operario ID:", operarioId);

        fetch("/public_html/dashboard/paginas/operarios/api/obtener_operarios.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: operarioId })
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                
                if (data.success && data.operario) {
                    operarioVisualizando = data.operario;
                    mostrarDatosOperario(data.operario);
                    mostrarContenido();
                    console.log("✅ Datos del operario cargados correctamente");
                } else {
                    throw new Error(data.message || "Error al obtener datos del operario");
                }
            } catch (e) {
                console.error("❌ Error al parsear respuesta:", text);
                mostrarError("El servidor no respondió correctamente");
            }
        })
        .catch(error => {
            console.error("❌ Error al cargar operario:", error);
            mostrarError("Error de conexión al cargar datos del operario");
        });
    }

    /**
     * Mostrar datos del operario en el modal
     * @param {Object} operario - Datos del operario
     */
    function mostrarDatosOperario(operario) {
        console.log("📋 Mostrando datos del operario:", operario);

        // Avatar e iniciales
        const iniciales = generarIniciales(operario.nombre);
        document.getElementById('operarioInitials').textContent = iniciales;

        // Información principal
        document.getElementById('operarioNombre').textContent = operario.nombre;
        document.getElementById('operarioEmail').textContent = operario.email;

        // Badges de rol y estado
        actualizarBadges(operario.usuario, operario.disponible);

        // Información detallada
        document.getElementById('operarioIdDisplay').textContent = operario.id;
        document.getElementById('operarioNombreDisplay').textContent = operario.nombre;
        document.getElementById('operarioEmailDisplay').textContent = operario.email;
        document.getElementById('operarioUsuarioDisplay').textContent = operario.usuario.toUpperCase();
        document.getElementById('operarioDisponibleDisplay').textContent = 
            operario.disponible.charAt(0).toUpperCase() + operario.disponible.slice(1);

        // Permisos basados en rol
        const permisos = obtenerPermisos(operario.usuario);
        document.getElementById('operarioPermissions').textContent = permisos;

        // Fechas
        document.getElementById('operarioCreatedAt').textContent = operario.creado;
        document.getElementById('operarioUpdatedAt').textContent = operario.actualizado;

        // Calcular tiempo en sistema
        const tiempoSistema = calcularTiempoEnSistema(operario.creado_raw);
        document.getElementById('operarioTimeInSystem').textContent = tiempoSistema;

        // Estadísticas adicionales
        const diasActivo = calcularDiasActivo(operario.creado_raw);
        document.getElementById('operarioActiveDays').textContent = diasActivo + " días";

        const tipoOperario = obtenerTipoOperario(operario.usuario);
        document.getElementById('operarioType').textContent = tipoOperario;
    }

    /**
     * Generar iniciales del nombre
     * @param {string} nombre - Nombre completo
     * @returns {string} Iniciales
     */
    function generarIniciales(nombre) {
        return nombre
            .split(' ')
            .map(word => word.charAt(0).toUpperCase())
            .slice(0, 2)
            .join('');
    }

    /**
     * Actualizar badges de rol y estado
     * @param {string} usuario - Rol del operario
     * @param {string} disponible - Estado del operario
     */
    function actualizarBadges(usuario, disponible) {
        // Badge de rol
        const usuarioBadge = document.getElementById('operarioUsuarioBadge');
        usuarioBadge.textContent = usuario.toUpperCase();
        usuarioBadge.className = usuario === 'admin' 
            ? 'px-3 py-1 bg-purple-600 bg-opacity-90 rounded-full text-xs font-bold uppercase tracking-wide text-white'
            : 'px-3 py-1 bg-blue-600 bg-opacity-90 rounded-full text-xs font-bold uppercase tracking-wide text-white';

        // Badge de estado
        const disponibleBadge = document.getElementById('operarioDisponibleBadge');
        disponibleBadge.textContent = disponible.toUpperCase();
        disponibleBadge.className = disponible === 'si' 
            ? 'px-3 py-1 bg-green-500 rounded-full text-xs font-bold uppercase tracking-wide text-white'
            : 'px-3 py-1 bg-red-500 rounded-full text-xs font-bold uppercase tracking-wide text-white';
    }

    /**
     * Obtener permisos basados en el rol
     * @param {string} usuario - Rol del operario
     * @returns {string} Descripción de permisos
     */
    function obtenerPermisos(usuario) {
        const permisos = {
            'admin': 'Acceso completo al sistema, gestión de operarios, configuraciones',
            'user': 'Acceso básico, funciones estándar del sistema'
        };
        return permisos[usuario] || 'Permisos básicos';
    }

    /**
     * Calcular tiempo en el sistema
     * @param {string} fechaRegistro - Fecha de registro del operario
     * @returns {string} Tiempo formateado
     */
    function calcularTiempoEnSistema(fechaRegistro) {
        if (!fechaRegistro) return 'No disponible';
        
        const registro = new Date(fechaRegistro);
        const ahora = new Date();
        const diff = ahora - registro;
        
        const dias = Math.floor(diff / (1000 * 60 * 60 * 24));
        const meses = Math.floor(dias / 30);
        const años = Math.floor(dias / 365);
        
        if (años > 0) {
            return `${años} año${años > 1 ? 's' : ''} y ${meses % 12} mes${meses % 12 !== 1 ? 'es' : ''}`;
        } else if (meses > 0) {
            return `${meses} mes${meses > 1 ? 'es' : ''} y ${dias % 30} día${dias % 30 !== 1 ? 's' : ''}`;
        } else {
            return `${dias} día${dias !== 1 ? 's' : ''}`;
        }
    }

    /**
     * Calcular días activo
     * @param {string} fechaRegistro - Fecha de registro
     * @returns {number} Número de días
     */
    function calcularDiasActivo(fechaRegistro) {
        if (!fechaRegistro) return 0;
        
        const registro = new Date(fechaRegistro);
        const ahora = new Date();
        const diff = ahora - registro;
        
        return Math.floor(diff / (1000 * 60 * 60 * 24));
    }

    /**
     * Obtener tipo de operario basado en rol
     * @param {string} usuario - Rol del operario
     * @returns {string} Tipo de operario
     */
    function obtenerTipoOperario(usuario) {
        const tipos = {
            'admin': 'Administrador del Sistema',
            'user': 'Operario Estándar'
        };
        return tipos[usuario] || 'Operario Básico';
    }

    /**
     * Mostrar contenido y ocultar loading
     */
    function mostrarContenido() {
        document.getElementById('loadingVerOperario').classList.add('hidden');
        document.getElementById('contenidoVerOperario').classList.remove('hidden');
    }

    /**
     * Mostrar error
     * @param {string} mensaje - Mensaje de error
     */
    function mostrarError(mensaje) {
        document.getElementById('loadingVerOperario').classList.add('hidden');
        document.getElementById('contenidoVerOperario').classList.add('hidden');
        
        const errorDiv = document.getElementById('errorVerOperario');
        const errorMessage = document.getElementById('errorMessageOperario');
        
        if (errorMessage) {
            errorMessage.textContent = mensaje;
        }
        
        errorDiv.classList.remove('hidden');
    }

    // ===============================================================
    // EVENT LISTENERS
    // ===============================================================

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('verOperarioModal');
            if (modal && !modal.classList.contains('hidden')) {
                cerrarModalVerOperario();
            }
        }
    });

    // Cerrar modal al hacer clic en el overlay
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('verOperarioModal');
        if (e.target === modal) {
            cerrarModalVerOperario();
        }
    });

    // Override de función verOperario para usar modal avanzado
    window.verOperario = function(operarioId) {
        console.log("🎯 Redirigiendo a modal avanzado para operario ID:", operarioId);
        window.abrirModalVerOperario(operarioId);
    };

    console.log("📁 Módulo ver_operario.js cargado - COPFLOW v1.0");

})();

/**
 * ===============================================================
 * FIN DEL MÓDULO VER_USUARIO.JS
 * ===============================================================
 * 
 * CARACTERÍSTICAS IMPLEMENTADAS:
 * • Visualización completa de información de operario
 * • Interfaz compacta y optimizada
 * • Cálculos automáticos de estadísticas
 * • Navegación fluida
 * • Responsive design
 * • Manejo robusto de errores
 * ===============================================================
 */