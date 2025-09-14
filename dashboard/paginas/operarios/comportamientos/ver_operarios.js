/**
 * ===============================================================
 * VER_OPERARIO.JS - Modal de Visualización de Operarios COPFLOW
 * ===============================================================
 * 
 * PROPÓSITO:
 * Visualiza información completa del operario en el modal.
 * 
 * Adaptado para la tabla operadores.
 * ===============================================================
 */
(function() {
    "use strict";
    let operarioVisualizando = null;

    // FUNCIÓN GLOBAL PARA ABRIR EL MODAL DE VER OPERARIO
    window.abrirModalVerOperario = function(id) {
        // Diagnóstico: log del ID recibido
        console.log('[ver_operarios.js] abrirModalVerOperario() ID recibido:', id);
        if (typeof window.mostrarModalVerOperario === 'function') {
            window.mostrarModalVerOperario(id);
        } else {
            alert('Función mostrarModalVerOperario no disponible');
        }
    };

    // FUNCIÓN GLOBAL PARA CERRAR EL MODAL DE VER OPERARIO
    window.cerrarModalVerOperario = function() {
        const modal = document.getElementById('verOperarioModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        operarioVisualizando = null;
    };

    // LIMPIAR EL MODAL ANTES DE MOSTRARLO
    function resetearModalVer() {
        const loading = document.getElementById('loadingVerOperario');
        const contenido = document.getElementById('contenidoVerOperario');
        const error = document.getElementById('errorVerOperario');
        if (loading) loading.classList.remove('hidden');
        if (contenido) contenido.classList.add('hidden');
        if (error) error.classList.add('hidden');
        operarioVisualizando = null;
    }

    // CARGAR DATOS DEL OPERARIO POR AJAX
    function cargarDatosOperarioVer(operarioId) {
        // Log para depuración
        console.log('[ver_operarios.js] cargarDatosOperarioVer() ID enviado:', operarioId);

        // Validación de ID
        if (!operarioId || operarioId <= 0) {
            mostrarError("ID de operario inválido");
            return;
        }

        fetch("/public_html/dashboard/paginas/operarios/api/obtener_operarios.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: operarioId })
        })
        .then(response => response.text())
        .then(text => {
            let data;
            try { data = JSON.parse(text); } catch(e) { data = null; }
            // Diagnóstico: log de la respuesta
            console.log('[ver_operarios.js] Respuesta AJAX:', data);

            if (data && data.success && typeof data.operario === 'object' && data.operario) {
                operarioVisualizando = data.operario;
                mostrarDatosOperario(data.operario);
                mostrarContenido();
            } else {
                // Mostrar error más detallado
                mostrarError(data && data.message ? data.message : "No se encontró el operario");
            }
        })
        .catch(error => {
            mostrarError("Error de conexión al cargar datos del operario");
        });
    }

    // LLENAR EL MODAL CON DATOS DEL OPERARIO
    function mostrarDatosOperario(operario) {
        const setText = (id, value) => { const el = document.getElementById(id); if (el) el.textContent = value; };
        setText('operarioInitials', generarIniciales(operario.nombre));
        setText('operarioNombre', operario.nombre);
        setText('operarioEmail', operario.email);
        setText('operarioUsuario', operario.usuario);
        const badge = document.getElementById('operarioDisponibleBadge');
        if (badge) {
            badge.textContent = operario.disponible == 1 ? "DISPONIBLE" : "NO DISPONIBLE";
            badge.className = operario.disponible == 1
                ? 'px-3 py-1 bg-green-500 rounded-full text-xs font-bold uppercase text-white'
                : 'px-3 py-1 bg-red-500 rounded-full text-xs font-bold uppercase text-white';
        }
        setText('operarioIdDisplay', operario.id);
        setText('operarioNombreDisplay', operario.nombre);
        setText('operarioUsuarioDisplay', operario.usuario);
        setText('operarioEmailDisplay', operario.email);
        setText('operarioDisponibleDisplay', operario.disponible == 1 ? "Disponible" : "No disponible");
        setText('operarioCreadoDisplay', operario.creado);
    }

    // GENERAR INICIALES DEL NOMBRE
    function generarIniciales(nombre) {
        if (!nombre) return '';
        return nombre.split(' ').map(word => word.charAt(0).toUpperCase()).slice(0,2).join('');
    }

    // MOSTRAR CONTENIDO DEL MODAL
    function mostrarContenido() {
        const loading = document.getElementById('loadingVerOperario');
        const contenido = document.getElementById('contenidoVerOperario');
        if (loading) loading.classList.add('hidden');
        if (contenido) contenido.classList.remove('hidden');
    }

    // MOSTRAR ERROR EN EL MODAL
    function mostrarError(mensaje) {
        const loading = document.getElementById('loadingVerOperario');
        const contenido = document.getElementById('contenidoVerOperario');
        const errorDiv = document.getElementById('errorVerOperario');
        if (loading) loading.classList.add('hidden');
        if (contenido) contenido.classList.add('hidden');
        if (errorDiv) errorDiv.classList.remove('hidden');
        // Ajuste de id para error (puede ser errorMessage u errorMessageOperario)
        const errorMessage = document.getElementById('errorMessageOperario') || document.getElementById('errorMessage');
        if (errorMessage) errorMessage.textContent = mensaje;
        // Diagnóstico: log del error
        console.error('[ver_operarios.js] mostrarError:', mensaje);
    }

    // FUNCIÓN GLOBAL QUE REALMENTE MUESTRA EL MODAL Y CARGA LOS DATOS
    window.mostrarModalVerOperario = function(id) {
        const modal = document.getElementById('verOperarioModal');
        console.log('[ver_operarios.js] mostrarModalVerOperario() - Abriendo modal para ID:', id);
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            resetearModalVer();
            cargarDatosOperarioVer(id);
        } else {
            alert("No se encontró el modal de visualización de operario.");
        }
    };

    // EVENTOS PARA CERRAR EL MODAL CON ESCAPE O CLICK FUERA DEL MODAL
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('verOperarioModal');
            if (modal && !modal.classList.contains('hidden')) {
                window.cerrarModalVerOperario();
            }
        }
    });
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('verOperarioModal');
        if (e.target === modal) window.cerrarModalVerOperario();
    });

    // FUNCIÓN GLOBAL PARA LLAMAR DESDE OTROS MÓDULOS
    window.verOperario = function(operarioId) {
        window.abrirModalVerOperario(operarioId);
    };

})();