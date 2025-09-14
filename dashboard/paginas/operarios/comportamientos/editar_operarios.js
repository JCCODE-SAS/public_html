/**
 * ===============================================================
 * EDITAR_OPERARIO.JS - Modal de Edición de Operarios COPFLOW
 * ===============================================================
 * 
 * Módulo robusto con integración de modales universales.
 * Carga datos, valida, guarda y actualiza la UI con feedback visual.
 * Manejo robusto de errores y recarga la tabla tras guardar.
 * 
 * 👨‍💻 Diomedez98 (JCCODE-SAS)
 * ===============================================================
 */
(function() {
    "use strict";

    let operarioActual = null;
    let cambiosPendientes = false;
    let validacionTimeout = null;

    // Mostrar modal y cargar datos del operario
    window.mostrarModalEditarOperario = function(id) {
        resetearModal();
        const modal = document.getElementById('editarOperarioModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            cargarDatosOperario(id);
        } else {
            mostrarModalError('Error', 'Modal de editar operario no encontrado');
        }
    };
    window.abrirModalEditarOperario = window.mostrarModalEditarOperario;

    // Cerrar modal con confirmación si hay cambios
    window.cerrarModalEditarOperario = function() {
        if (cambiosPendientes) {
            mostrarModalConfirmacion(
                "¿Cerrar sin guardar cambios?",
                "Tienes cambios sin guardar. ¿Seguro que deseas cerrar el formulario?",
                cerrarModalForzado
            );
        } else {
            cerrarModalForzado();
        }
    };

    function cerrarModalForzado() {
        const modal = document.getElementById('editarOperarioModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
        operarioActual = null;
        cambiosPendientes = false;
        resetearModal();
    }

    function resetearModal() {
        const loading = document.getElementById('loadingEditarOperario');
        const form = document.getElementById('formEditarOperario');
        if (loading) loading.classList.remove('hidden');
        if (form) form.classList.add('hidden');
        limpiarErrores();
        const btnGuardar = document.getElementById('btnGuardarOperario');
        if (btnGuardar) {
            btnGuardar.disabled = true;
            document.getElementById('btnGuardarTexto').textContent = 'Guardar Cambios';
            document.getElementById('btnGuardarSpinner').classList.add('hidden');
        }
        cambiosPendientes = false;
        operarioActual = null;
    }

    // Cargar datos del operario desde la API
    function cargarDatosOperario(operarioId) {
        fetch("/public_html/dashboard/paginas/operarios/api/obtener_operarios.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: operarioId })
        })
        .then(async response => {
            const text = await response.text();
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (e) {
                if (text.trim().startsWith('<!DOCTYPE')) {
                    mostrarErrorCarga("La respuesta del servidor no es válida. Verifica la sesión o el endpoint.");
                    return;
                }
                mostrarErrorCarga("Error inesperado: " + text);
                return;
            }
            if (data.success && data.operario) {
                operarioActual = data.operario;
                llenarFormulario(data.operario);
                mostrarFormulario();
            } else {
                mostrarErrorCarga(data.message || "Error al obtener datos del operario");
            }
        })
        .catch(error => {
            mostrarErrorCarga("Error de conexión al cargar datos del operario: " + error.message);
        });
    }

    // Guardar cambios del operario y recargar tabla
    window.guardarCambiosOperario = function() {
        if (!validarFormulario()) return;
        const datosOperario = obtenerDatosFormulario();
        mostrarCargandoBoton(true);

        fetch("/public_html/dashboard/paginas/operarios/api/actualizar_operarios.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(datosOperario)
        })
        .then(async response => {
            const text = await response.text();
            let data = null;
            try {
                data = JSON.parse(text);
            } catch (e) {
                if (text.trim().startsWith('<!DOCTYPE')) {
                    mostrarModalError("Error", "La respuesta del servidor no es válida. Verifica la sesión o el endpoint.");
                    return;
                }
                mostrarModalError("Error", "Error inesperado: " + text);
                return;
            }
            if (!data.success) {
                mostrarModalError("Error", data.message || "Error al actualizar operario");
                return;
            }
            mostrarModalExito("¡Operario actualizado!", "Los cambios se guardaron correctamente.");
            cerrarModalForzado();
            // --- Recargar la tabla y estadísticas tras guardar ---
            setTimeout(function() {
                if (typeof window.recargarTablaOperarios === 'function') {
                    window.recargarTablaOperarios();
                } else if (typeof window.inicializarOperarios === 'function') {
                    window.inicializarOperarios(); // Fallback adicional
                } else {
                    window.location.reload();
                }
            }, 600); // Espera breve para que el modal se cierre visualmente
        })
        .catch(error => {
            mostrarModalError("Error", "Error de conexión al guardar cambios: " + error.message);
        })
        .finally(() => {
            mostrarCargandoBoton(false);
        });
    };

    // Manejo del formulario
    function llenarFormulario(operario) {
        document.getElementById('editOperarioId').value = operario.id;
        document.getElementById('editOperarioNombre').value = operario.nombre;
        document.getElementById('editOperarioUsuario').value = operario.usuario;
        document.getElementById('editOperarioEmail').value = operario.email;
        document.getElementById('editOperarioDisponible').value = operario.disponible;
        document.getElementById('editOperarioIdDisplay').textContent = operario.id;
        document.getElementById('editOperarioCreado').textContent = operario.creado;
        configurarEventosCambio();
    }

    function mostrarFormulario() {
        document.getElementById('loadingEditarOperario').classList.add('hidden');
        document.getElementById('formEditarOperario').classList.remove('hidden');
    }

    function obtenerDatosFormulario() {
        return {
            id: parseInt(document.getElementById('editOperarioId').value),
            nombre: document.getElementById('editOperarioNombre').value.trim(),
            usuario: document.getElementById('editOperarioUsuario').value.trim(),
            email: document.getElementById('editOperarioEmail').value.trim(),
            disponible: document.getElementById('editOperarioDisponible').value
        };
    }

    function configurarEventosCambio() {
        ['editOperarioNombre','editOperarioUsuario','editOperarioEmail','editOperarioDisponible'].forEach(campoId => {
            const campo = document.getElementById(campoId);
            if (campo) {
                const evento = campo.tagName === 'SELECT' ? 'change' : 'input';
                campo.addEventListener(evento, function() {
                    detectarCambios();
                    clearTimeout(validacionTimeout);
                    validacionTimeout = setTimeout(() => {
                        validarCampo(campoId);
                    }, 300);
                });
            }
        });
    }

    function detectarCambios() {
        if (!operarioActual) return;
        const datosActuales = obtenerDatosFormulario();
        const hayCambios = (
            datosActuales.nombre !== operarioActual.nombre ||
            datosActuales.usuario !== operarioActual.usuario ||
            datosActuales.email !== operarioActual.email ||
            datosActuales.disponible != operarioActual.disponible
        );
        cambiosPendientes = hayCambios;
        const btnGuardar = document.getElementById('btnGuardarOperario');
        if (btnGuardar) btnGuardar.disabled = !hayCambios || !validarFormulario(true);
    }

    // Validaciones
    function validarCampo(campoId) {
        const campo = document.getElementById(campoId);
        const valor = campo.value.trim();
        let esValido = true;
        let mensaje = "";
        switch (campoId) {
            case 'editOperarioNombre':
                if (valor.length < 2) { esValido = false; mensaje = "El nombre debe tener al menos 2 caracteres"; }
                break;
            case 'editOperarioUsuario':
                if (valor.length < 4) { esValido = false; mensaje = "Usuario muy corto"; }
                break;
            case 'editOperarioEmail':
                if (valor.length > 0) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(valor)) { esValido = false; mensaje = "Formato de email inválido"; }
                }
                break;
        }
        const errorId = campoId.replace('editOperario','error');
        if (esValido) {
            ocultarError(errorId);
            campo.classList.remove('border-red-500');
            campo.classList.add('border-gray-300');
        } else {
            mostrarError(errorId, mensaje);
            campo.classList.add('border-red-500');
            campo.classList.remove('border-gray-300');
        }
        return esValido;
    }

    function validarFormulario(silencioso = false) {
        const campos = ['editOperarioNombre','editOperarioUsuario','editOperarioEmail'];
        let todosValidos = true;
        campos.forEach(campoId => {
            const campo = document.getElementById(campoId);
            const valor = campo.value.trim();
            switch (campoId) {
                case 'editOperarioNombre':
                    if (valor.length < 2) todosValidos = false;
                    break;
                case 'editOperarioUsuario':
                    if (valor.length < 4) todosValidos = false;
                    break;
                case 'editOperarioEmail':
                    if (valor.length > 0) {
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(valor)) todosValidos = false;
                    }
                    break;
            }
        });
        return todosValidos;
    }

    // UI/feedback
    function mostrarCargandoBoton(cargando) {
        const btn = document.getElementById('btnGuardarOperario');
        const texto = document.getElementById('btnGuardarTexto');
        const spinner = document.getElementById('btnGuardarSpinner');
        if (btn && texto && spinner) {
            btn.disabled = cargando;
            texto.textContent = cargando ? 'Guardando...' : 'Guardar Cambios';
            if (cargando) spinner.classList.remove('hidden');
            else spinner.classList.add('hidden');
        }
    }

    function mostrarError(errorId, mensaje) {
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.textContent = mensaje;
            errorElement.classList.remove('hidden');
        }
    }
    function ocultarError(errorId) {
        const errorElement = document.getElementById(errorId);
        if (errorElement) errorElement.classList.add('hidden');
    }
    function limpiarErrores() {
        ['errorNombre','errorUsuario','errorEmail','errorGeneral'].forEach(errorId => ocultarError(errorId));
        ['editOperarioNombre','editOperarioUsuario','editOperarioEmail'].forEach(campoId => {
            const campo = document.getElementById(campoId);
            if (campo) {
                campo.classList.remove('border-red-500');
                campo.classList.add('border-gray-300');
            }
        });
    }
    function mostrarErrorCarga(mensaje) {
        const loading = document.getElementById('loadingEditarOperario');
        if (loading) {
            loading.innerHTML = `<div class="text-center py-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                    <i class="ri-error-warning-line text-2xl text-red-600"></i>
                </div>
                <p class="text-red-600 font-medium">${mensaje}</p>
                <button onclick="cerrarModalEditarOperario()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">Cerrar</button>
            </div>`;
        }
    }

    // Event listeners para cerrar modal desde overlay/esc
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('editarOperarioModal');
            if (modal && !modal.classList.contains('hidden')) {
                window.cerrarModalEditarOperario();
            }
        }
    });
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('editarOperarioModal');
        if (e.target === modal) window.cerrarModalEditarOperario();
    });

    function mostrarModalError(titulo, mensaje) {
        if (typeof window.mostrarModalError === 'function') {
            window.mostrarModalError(titulo, mensaje);
        } else {
            alert(`${titulo}: ${mensaje}`);
        }
    }
    function mostrarModalExito(titulo, mensaje) {
        if (typeof window.mostrarModalExito === 'function') {
            window.mostrarModalExito(titulo, mensaje);
        } else {
            alert(`${titulo}: ${mensaje}`);
        }
    }
    function mostrarModalConfirmacion(titulo, mensaje, callback) {
        if (typeof window.mostrarModalConfirmacion === 'function') {
            window.mostrarModalConfirmacion(titulo, mensaje, callback);
        } else {
            if (confirm(`${titulo}\n${mensaje}`)) callback && callback();
        }
    }

    console.log("Módulo editar_operario.js cargado - COPFLOW");

})();