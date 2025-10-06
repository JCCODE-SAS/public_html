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
    
    let usuarioVisualizando = null;

    // ===============================================================
    // FUNCIONES PRINCIPALES
    // ===============================================================

    /**
     * Abrir modal de visualización de usuario
     * @param {number} userId - ID del usuario a visualizar
     */
    window.abrirModalVerUsuario = function(userId) {
        console.log("🚀 Abriendo modal de visualización para usuario ID:", userId);

        resetearModalVer();
        
        const modal = document.getElementById('verUsuarioModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        cargarDatosUsuarioVer(userId);
    };

    /**
     * Cerrar modal de visualización
     */
    window.cerrarModalVerUsuario = function() {
        const modal = document.getElementById('verUsuarioModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        
        usuarioVisualizando = null;
        console.log("✅ Modal de visualización cerrado");
    };

    /**
     * Resetear modal a estado inicial
     */
    function resetearModalVer() {
        document.getElementById('loadingVerUsuario').classList.remove('hidden');
        document.getElementById('contenidoVerUsuario').classList.add('hidden');
        document.getElementById('errorVerUsuario').classList.add('hidden');
        
        usuarioVisualizando = null;
        console.log("🔄 Modal de visualización reseteado");
    }

    // ===============================================================
    // CARGA DE DATOS
    // ===============================================================

    /**
     * Cargar datos del usuario para visualización
     * @param {number} userId - ID del usuario
     */
    function cargarDatosUsuarioVer(userId) {
        console.log("📡 Cargando datos del usuario ID:", userId);

        fetch("/public_html/dashboard/paginas/usuarios/api/obtener_usuario.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id: userId })
        })
        .then(response => response.text())
        .then(text => {
            try {
                const data = JSON.parse(text);
                
                if (data.success && data.user) {
                    usuarioVisualizando = data.user;
                    mostrarDatosUsuario(data.user);
                    mostrarContenido();
                    console.log("✅ Datos del usuario cargados correctamente");
                } else {
                    throw new Error(data.message || "Error al obtener datos del usuario");
                }
            } catch (e) {
                console.error("❌ Error al parsear respuesta:", text);
                mostrarError("El servidor no respondió correctamente");
            }
        })
        .catch(error => {
            console.error("❌ Error al cargar usuario:", error);
            mostrarError("Error de conexión al cargar datos del usuario");
        });
    }

    /**
     * Mostrar datos del usuario en el modal
     * @param {Object} usuario - Datos del usuario
     */
    function mostrarDatosUsuario(usuario) {
        console.log("📋 Mostrando datos del usuario:", usuario);

        // Avatar e iniciales
        const iniciales = generarIniciales(usuario.name);
        document.getElementById('userInitials').textContent = iniciales;

        // Información principal
        document.getElementById('userName').textContent = usuario.name;
        document.getElementById('userEmail').textContent = usuario.email;

        // Badges de rol y estado
        actualizarBadges(usuario.role, usuario.status);

        // Información detallada
        document.getElementById('userIdDisplay').textContent = usuario.id;
        document.getElementById('userNameDisplay').textContent = usuario.name;
        document.getElementById('userEmailDisplay').textContent = usuario.email;
        document.getElementById('userRoleDisplay').textContent = usuario.role.toUpperCase();
        document.getElementById('userStatusDisplay').textContent = 
            usuario.status.charAt(0).toUpperCase() + usuario.status.slice(1);

        // Permisos basados en rol
        const permisos = obtenerPermisos(usuario.role);
        document.getElementById('userPermissions').textContent = permisos;

        // Fechas
        document.getElementById('userCreatedAt').textContent = usuario.created_at;
        document.getElementById('userUpdatedAt').textContent = usuario.updated_at;

        // Calcular tiempo en sistema
        const tiempoSistema = calcularTiempoEnSistema(usuario.created_at_raw);
        document.getElementById('userTimeInSystem').textContent = tiempoSistema;

        // Estadísticas adicionales
        const diasActivo = calcularDiasActivo(usuario.created_at_raw);
        document.getElementById('userActiveDays').textContent = diasActivo + " días";

        const tipoUsuario = obtenerTipoUsuario(usuario.role);
        document.getElementById('userType').textContent = tipoUsuario;
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
     * @param {string} role - Rol del usuario
     * @param {string} status - Estado del usuario
     */
    function actualizarBadges(role, status) {
        // Badge de rol
        const roleBadge = document.getElementById('userRoleBadge');
        roleBadge.textContent = role.toUpperCase();
        roleBadge.className = role === 'admin' 
            ? 'px-3 py-1 bg-purple-600 bg-opacity-90 rounded-full text-xs font-bold uppercase tracking-wide text-white'
            : 'px-3 py-1 bg-blue-600 bg-opacity-90 rounded-full text-xs font-bold uppercase tracking-wide text-white';

        // Badge de estado
        const statusBadge = document.getElementById('userStatusBadge');
        statusBadge.textContent = status.toUpperCase();
        statusBadge.className = status === 'activo' 
            ? 'px-3 py-1 bg-green-500 rounded-full text-xs font-bold uppercase tracking-wide text-white'
            : 'px-3 py-1 bg-red-500 rounded-full text-xs font-bold uppercase tracking-wide text-white';
    }

    /**
     * Obtener permisos basados en el rol
     * @param {string} role - Rol del usuario
     * @returns {string} Descripción de permisos
     */
    function obtenerPermisos(role) {
        const permisos = {
            'admin': 'Acceso completo al sistema, gestión de usuarios, configuraciones',
            'user': 'Acceso básico, funciones estándar del sistema'
        };
        return permisos[role] || 'Permisos básicos';
    }

    /**
     * Calcular tiempo en el sistema
     * @param {string} fechaRegistro - Fecha de registro del usuario
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
     * Obtener tipo de usuario basado en rol
     * @param {string} role - Rol del usuario
     * @returns {string} Tipo de usuario
     */
    function obtenerTipoUsuario(role) {
        const tipos = {
            'admin': 'Administrador del Sistema',
            'user': 'Usuario Estándar'
        };
        return tipos[role] || 'Usuario Básico';
    }

    /**
     * Mostrar contenido y ocultar loading
     */
    function mostrarContenido() {
        document.getElementById('loadingVerUsuario').classList.add('hidden');
        document.getElementById('contenidoVerUsuario').classList.remove('hidden');
    }

    /**
     * Mostrar error
     * @param {string} mensaje - Mensaje de error
     */
    function mostrarError(mensaje) {
        document.getElementById('loadingVerUsuario').classList.add('hidden');
        document.getElementById('contenidoVerUsuario').classList.add('hidden');
        
        const errorDiv = document.getElementById('errorVerUsuario');
        const errorMessage = document.getElementById('errorMessage');
        
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
            const modal = document.getElementById('verUsuarioModal');
            if (modal && !modal.classList.contains('hidden')) {
                cerrarModalVerUsuario();
            }
        }
    });

    // Cerrar modal al hacer clic en el overlay
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('verUsuarioModal');
        if (e.target === modal) {
            cerrarModalVerUsuario();
        }
    });

    // Override de función verUsuario para usar modal avanzado
    window.verUsuario = function(userId) {
        console.log("🎯 Redirigiendo a modal avanzado para usuario ID:", userId);
        window.abrirModalVerUsuario(userId);
    };

    console.log("📁 Módulo ver_usuario.js cargado - COPFLOW v1.0");

})();

/**
 * ===============================================================
 * FIN DEL MÓDULO VER_USUARIO.JS
 * ===============================================================
 * 
 * CARACTERÍSTICAS IMPLEMENTADAS:
 * • Visualización completa de información de usuario
 * • Interfaz compacta y optimizada
 * • Cálculos automáticos de estadísticas
 * • Navegación fluida
 * • Responsive design
 * • Manejo robusto de errores
 * ===============================================================
 */