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

    window.abrirModalVerOperario = function(id) {
        // Aquí va la lógica para abrir el modal de ver operario
        // Por ejemplo:
        if (typeof mostrarModalVerOperario === 'function') {
            mostrarModalVerOperario(id);
        } else {
            alert('Función mostrarModalVerOperario no disponible');
        }
    };

    window.cerrarModalVerOperario = function() {
        const modal = document.getElementById('verOperarioModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        operarioVisualizando = null;
    };

    function resetearModalVer() {
        const loading = document.getElementById('loadingVerOperario');
        const contenido = document.getElementById('contenidoVerOperario');
        const error = document.getElementById('errorVerOperario');
        if (loading) loading.classList.remove('hidden');
        if (contenido) contenido.classList.add('hidden');
        if (error) error.classList.add('hidden');
        operarioVisualizando = null;
    }

    function cargarDatosOperarioVer(operarioId) {
        fetch("/public_html/dashboard/paginas/operarios/api/obtener_operarios.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: operarioId })
        })
        .then(response => response.text())
        .then(text => {
            let data;
            try { data = JSON.parse(text); } catch(e) { data = null; }
            if (data && data.success && data.operario) {
                operarioVisualizando = data.operario;
                mostrarDatosOperario(data.operario);
                mostrarContenido();
            } else {
                mostrarError("No se encontró el operario");
            }
        })
        .catch(error => {
            mostrarError("Error de conexión al cargar datos del operario");
        });
    }

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

    function generarIniciales(nombre) {
        return nombre.split(' ').map(word => word.charAt(0).toUpperCase()).slice(0,2).join('');
    }

    function mostrarContenido() {
        const loading = document.getElementById('loadingVerOperario');
        const contenido = document.getElementById('contenidoVerOperario');
        if (loading) loading.classList.add('hidden');
        if (contenido) contenido.classList.remove('hidden');
    }

    function mostrarError(mensaje) {
        const loading = document.getElementById('loadingVerOperario');
        const contenido = document.getElementById('contenidoVerOperario');
        const errorDiv = document.getElementById('errorVerOperario');
        if (loading) loading.classList.add('hidden');
        if (contenido) contenido.classList.add('hidden');
        if (errorDiv) errorDiv.classList.remove('hidden');
        const errorMessage = document.getElementById('errorMessageOperario');
        if (errorMessage) errorMessage.textContent = mensaje;
    }

    // Event listeners
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

    window.verOperario = function(operarioId) {
        window.abrirModalVerOperario(operarioId);
    };

})();