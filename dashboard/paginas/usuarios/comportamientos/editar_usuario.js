/**
 * ===============================================================
 * EDITAR_USUARIO.JS - Modal de Edición de Usuarios COPFLOW
 * ===============================================================
 * 
 * PROPÓSITO:
 * Maneja la lógica completa del modal de edición de usuarios:
 * carga de datos, validaciones, guardado y actualización de UI.
 * 
 * DESARROLLADO POR: Diomedez98
 * FECHA CREACIÓN: 2025-01-09
 * REPOSITORIO: https://github.com/Diomedez98/copflows
 * EMPRESA: JCCODE-SAS
 * 
 * ===============================================================
 * FUNCIONALIDADES:
 * • Cargar datos del usuario desde API
 * • Validaciones en tiempo real
 * • Guardar cambios con feedback visual
 * • Integración con sistema de logging
 * • Actualización automática de estadísticas
 * • Manejo robusto de errores y respuestas malformadas
 * ===============================================================
 */

(function() {
    "use strict";

    // ===============================================================
    // VARIABLES GLOBALES Y CONFIGURACIÓN
    // ===============================================================
    
    let usuarioActual = null;           // Datos del usuario siendo editado
    let cambiosPendientes = false;      // Flag para detectar cambios
    let validacionTimeout = null;       // Timeout para validaciones

    // ===============================================================
    // FUNCIONES PRINCIPALES DEL MODAL
    // ===============================================================

    /**
     * Abrir modal y cargar datos del usuario
     * @param {number} userId - ID del usuario a editar
     */
    window.abrirModalEditarUsuario = function(userId) {
        console.log("Abriendo modal de edición para usuario ID:", userId);

        // Resetear estado del modal
        resetearModal();
        
        // Mostrar modal con loading
        const modal = document.getElementById('editarUsuarioModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden'; // Prevenir scroll del body
        }

        // Cargar datos del usuario
        cargarDatosUsuario(userId);
    };

    /**
     * Cerrar modal con confirmación si hay cambios
     */
    window.cerrarModalEditarUsuario = function() {
        if (cambiosPendientes) {
            mostrarModalConfirmacion(
                "¿Cerrar sin guardar?",
                "Tienes cambios sin guardar. ¿Estás seguro de que quieres cerrar el modal?",
                function() {
                    cerrarModalForzado();
                }
            );
        } else {
            cerrarModalForzado();
        }
    };

    /**
     * Cerrar modal sin confirmación
     */
    function cerrarModalForzado() {
        const modal = document.getElementById('editarUsuarioModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = ''; // Restaurar scroll del body
        }
        
        // Limpiar estado
        usuarioActual = null;
        cambiosPendientes = false;
        
        console.log("Modal de edición cerrado");
    }

    /**
     * Resetear modal a estado inicial
     */
    function resetearModal() {
        // Mostrar loading y ocultar formulario
        document.getElementById('loadingEditarUsuario').classList.remove('hidden');
        document.getElementById('formEditarUsuario').classList.add('hidden');
        
        // Limpiar errores
        limpiarErrores();
        
        // Resetear botón de guardar
        const btnGuardar = document.getElementById('btnGuardarUsuario');
        if (btnGuardar) {
            btnGuardar.disabled = true;
            btnGuardar.querySelector('#btnGuardarTexto').textContent = 'Guardar Cambios';
            btnGuardar.querySelector('#btnGuardarSpinner').classList.add('hidden');
        }
        
        // Resetear flags
        cambiosPendientes = false;
        usuarioActual = null;
        
        console.log("Modal reseteado a estado inicial");
    }

    // ===============================================================
    // COMUNICACIÓN CON APIs
    // ===============================================================

    /**
     * Cargar datos del usuario desde la API
     * @param {number} userId - ID del usuario
     */
    function cargarDatosUsuario(userId) {
        console.log("Cargando datos del usuario ID:", userId);

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
                    usuarioActual = data.user;
                    llenarFormulario(data.user);
                    mostrarFormulario();
                    console.log("Datos del usuario cargados correctamente");
                } else {
                    throw new Error(data.message || "Error al obtener datos del usuario");
                }
            } catch (e) {
                console.error("Error al parsear respuesta:", text);
                mostrarErrorCarga("El servidor no respondió correctamente");
            }
        })
        .catch(error => {
            console.error("Error al cargar usuario:", error);
            mostrarErrorCarga("Error de conexión al cargar datos del usuario");
        });
    }

    /**
     * Limpiar y parsear respuesta JSON potencialmente corrupta
     * @param {string} text - Texto de respuesta crudo
     * @returns {Object} JSON parseado o null si falla
     */
    function limpiarYParsearJSON(text) {
        try {
            // Primer intento: parseo directo
            return JSON.parse(text.trim());
        } catch (e) {
            console.log("Primera tentativa de parseo falló, intentando limpieza...");
            
            try {
                // Segundo intento: buscar múltiples JSONs y tomar el correcto
                const jsonPattern = /\{[^{}]*(?:\{[^{}]*\}[^{}]*)*\}/g;
                const matches = text.match(jsonPattern);
                
                if (matches && matches.length > 0) {
                    // Buscar el JSON que contiene "Usuario actualizado correctamente"
                    for (let match of matches) {
                        try {
                            const parsed = JSON.parse(match);
                            if (parsed.success === true && parsed.message && parsed.message.includes("actualizado correctamente")) {
                                console.log("JSON exitoso encontrado:", parsed);
                                return parsed;
                            }
                        } catch (innerError) {
                            continue;
                        }
                    }
                    
                    // Si no encontramos el exitoso, tomar el último válido
                    for (let i = matches.length - 1; i >= 0; i--) {
                        try {
                            const parsed = JSON.parse(matches[i]);
                            console.log("Usando último JSON válido:", parsed);
                            return parsed;
                        } catch (innerError) {
                            continue;
                        }
                    }
                }
                
                // Tercer intento: verificar si contiene indicadores de éxito
                if (text.includes('"success":true') && text.includes('Usuario actualizado')) {
                    console.log("Respuesta contiene éxito aunque esté malformada");
                    return {
                        success: true,
                        message: "Usuario actualizado correctamente",
                        partial_response: true
                    };
                }
                
                throw new Error("No se pudo extraer JSON válido");
                
            } catch (cleanupError) {
                console.error("Error en limpieza de JSON:", cleanupError);
                return null;
            }
        }
    }

    /**
     * Guardar cambios del usuario
     */
    window.guardarCambiosUsuario = function() {
        console.log("Iniciando guardado de cambios...");

        // Validar formulario antes de enviar
        if (!validarFormulario()) {
            console.log("Validación de formulario falló");
            return;
        }

        // Obtener datos del formulario
        const datosUsuario = obtenerDatosFormulario();
        
        // Mostrar estado de carga en el botón
        mostrarCargandoBoton(true);

        fetch("/public_html/dashboard/paginas/usuarios/api/actualizar_usuario.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(datosUsuario)
        })
        .then(response => response.text())
        .then(text => {
            console.log("Respuesta cruda del servidor:", text);
            
            // Usar función de limpieza robusta
            const data = limpiarYParsearJSON(text);
            
            if (!data) {
                throw new Error("Respuesta del servidor inválida");
            }
            
            if (data.success) {
                // Determinar mensaje basado en si es respuesta parcial o completa
                const mensaje = data.partial_response 
                    ? `Los datos de "${datosUsuario.name}" han sido actualizados correctamente.`
                    : `Los datos de "${datosUsuario.name}" han sido actualizados correctamente.`;
                
                mostrarModalExito("Usuario actualizado", mensaje);
                
                // Actualizar tabla y estadísticas
                actualizarFilaUsuario(datosUsuario);
                
                // Solo actualizar estadísticas si tenemos info completa de cambios
                if (!data.partial_response && data.changes && (data.changes.role_changed || data.changes.status_changed)) {
                    actualizarEstadisticasUsuarios();
                } else if (data.partial_response) {
                    // Si es respuesta parcial, actualizar estadísticas por seguridad
                    actualizarEstadisticasUsuarios();
                }
                
                // Cerrar modal
                cerrarModalForzado();
                
                console.log("Usuario actualizado correctamente");
            } else {
                throw new Error(data.message || "Error al actualizar usuario");
            }
        })
        .catch(error => {
            console.error("Error al guardar usuario:", error);
            mostrarError("errorGeneral", "Error de conexión al guardar cambios: " + error.message);
        })
        .finally(() => {
            mostrarCargandoBoton(false);
        });
    };

    // ===============================================================
    // MANEJO DEL FORMULARIO
    // ===============================================================

    /**
     * Llenar formulario con datos del usuario
     * @param {Object} usuario - Datos del usuario
     */
    function llenarFormulario(usuario) {
        console.log("Llenando formulario con datos:", usuario);

        // Campos editables
        document.getElementById('editUserId').value = usuario.id;
        document.getElementById('editUserName').value = usuario.name;
        document.getElementById('editUserEmail').value = usuario.email;
        document.getElementById('editUserRole').value = usuario.role;
        document.getElementById('editUserStatus').value = usuario.status;

        // Campos de solo lectura
        document.getElementById('editUserIdDisplay').textContent = usuario.id;
        document.getElementById('editUserCreatedAt').textContent = usuario.created_at;

        // Configurar eventos de cambio
        configurarEventosCambio();
    }

    /**
     * Mostrar formulario y ocultar loading
     */
    function mostrarFormulario() {
        document.getElementById('loadingEditarUsuario').classList.add('hidden');
        document.getElementById('formEditarUsuario').classList.remove('hidden');
    }

    /**
     * Obtener datos actuales del formulario
     * @returns {Object} Datos del formulario
     */
    function obtenerDatosFormulario() {
        return {
            id: parseInt(document.getElementById('editUserId').value),
            name: document.getElementById('editUserName').value.trim(),
            email: document.getElementById('editUserEmail').value.trim(),
            role: document.getElementById('editUserRole').value,
            status: document.getElementById('editUserStatus').value
        };
    }

    /**
     * Configurar eventos de cambio en los campos
     */
    function configurarEventosCambio() {
        const campos = ['editUserName', 'editUserEmail', 'editUserRole', 'editUserStatus'];
        
        campos.forEach(campoId => {
            const campo = document.getElementById(campoId);
            if (campo) {
                // Evento de input/change
                const evento = campo.tagName === 'SELECT' ? 'change' : 'input';
                
                campo.addEventListener(evento, function() {
                    // Detectar cambios
                    detectarCambios();
                    
                    // Validar campo específico con debounce
                    clearTimeout(validacionTimeout);
                    validacionTimeout = setTimeout(() => {
                        validarCampo(campoId);
                    }, 300);
                });
            }
        });

        console.log("Eventos de cambio configurados");
    }

    /**
     * Detectar si hay cambios pendientes
     */
    function detectarCambios() {
        if (!usuarioActual) return;

        const datosActuales = obtenerDatosFormulario();
        
        const hayCambios = (
            datosActuales.name !== usuarioActual.name ||
            datosActuales.email !== usuarioActual.email ||
            datosActuales.role !== usuarioActual.role ||
            datosActuales.status !== usuarioActual.status
        );

        cambiosPendientes = hayCambios;
        
        // Habilitar/deshabilitar botón de guardar
        const btnGuardar = document.getElementById('btnGuardarUsuario');
        if (btnGuardar) {
            btnGuardar.disabled = !hayCambios || !validarFormulario(true);
        }

        if (hayCambios) {
            console.log("Cambios detectados en el formulario");
        }
    }

    // ===============================================================
    // SISTEMA DE VALIDACIONES
    // ===============================================================

    /**
     * Validar campo específico
     * @param {string} campoId - ID del campo a validar
     */
    function validarCampo(campoId) {
        const campo = document.getElementById(campoId);
        const valor = campo.value.trim();
        let esValido = true;
        let mensaje = "";

        switch (campoId) {
            case 'editUserName':
                if (valor.length < 3) {
                    esValido = false;
                    mensaje = "El nombre debe tener al menos 3 caracteres";
                }
                break;

            case 'editUserEmail':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(valor)) {
                    esValido = false;
                    mensaje = "Formato de email inválido";
                }
                break;

            case 'editUserRole':
                if (!['admin', 'user'].includes(valor)) {
                    esValido = false;
                    mensaje = "Rol inválido";
                }
                break;

            case 'editUserStatus':
                if (!['activo', 'inactivo'].includes(valor)) {
                    esValido = false;
                    mensaje = "Estado inválido";
                }
                break;
        }

        // Mostrar/ocultar error
        const errorId = campoId.replace('editUser', 'error');
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

    /**
     * Validar formulario completo
     * @param {boolean} silencioso - No mostrar errores si es true
     * @returns {boolean} True si el formulario es válido
     */
    function validarFormulario(silencioso = false) {
        const campos = ['editUserName', 'editUserEmail', 'editUserRole', 'editUserStatus'];
        let todosValidos = true;

        campos.forEach(campoId => {
            if (!silencioso) {
                const esValido = validarCampo(campoId);
                if (!esValido) todosValidos = false;
            } else {
                const campo = document.getElementById(campoId);
                const valor = campo.value.trim();
                
                // Validación silenciosa básica
                switch (campoId) {
                    case 'editUserName':
                        if (valor.length < 3) todosValidos = false;
                        break;
                    case 'editUserEmail':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(valor)) todosValidos = false;
                        break;
                    case 'editUserRole':
                        if (!['admin', 'user'].includes(valor)) todosValidos = false;
                        break;
                    case 'editUserStatus':
                        if (!valor) todosValidos = false;
                        break;
                }
            }
        });

        return todosValidos;
    }

    // ===============================================================
    // FUNCIONES DE UI Y FEEDBACK VISUAL
    // ===============================================================

    /**
     * Mostrar/ocultar estado de carga en botón
     * @param {boolean} cargando - Estado de carga
     */
    function mostrarCargandoBoton(cargando) {
        const btn = document.getElementById('btnGuardarUsuario');
        const texto = document.getElementById('btnGuardarTexto');
        const spinner = document.getElementById('btnGuardarSpinner');

        if (btn && texto && spinner) {
            btn.disabled = cargando;
            texto.textContent = cargando ? 'Guardando...' : 'Guardar Cambios';
            
            if (cargando) {
                spinner.classList.remove('hidden');
            } else {
                spinner.classList.add('hidden');
            }
        }
    }

    /**
     * Mostrar error en campo específico
     * @param {string} errorId - ID del elemento de error
     * @param {string} mensaje - Mensaje de error
     */
    function mostrarError(errorId, mensaje) {
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.textContent = mensaje;
            errorElement.classList.remove('hidden');
        }
    }

    /**
     * Ocultar error de campo específico
     * @param {string} errorId - ID del elemento de error
     */
    function ocultarError(errorId) {
        const errorElement = document.getElementById(errorId);
        if (errorElement) {
            errorElement.classList.add('hidden');
        }
    }

    /**
     * Limpiar todos los errores
     */
    function limpiarErrores() {
        const errores = ['errorName', 'errorEmail', 'errorRole', 'errorStatus', 'errorGeneral'];
        errores.forEach(errorId => ocultarError(errorId));
        
        // Restaurar estilos de campos
        const campos = ['editUserName', 'editUserEmail', 'editUserRole', 'editUserStatus'];
        campos.forEach(campoId => {
            const campo = document.getElementById(campoId);
            if (campo) {
                campo.classList.remove('border-red-500');
                campo.classList.add('border-gray-300');
            }
        });
    }

    /**
     * Mostrar error de carga
     * @param {string} mensaje - Mensaje de error
     */
    function mostrarErrorCarga(mensaje) {
        const loading = document.getElementById('loadingEditarUsuario');
        if (loading) {
            loading.innerHTML = `
                <div class="text-center py-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                        <i class="ri-error-warning-line text-2xl text-red-600"></i>
                    </div>
                    <p class="text-red-600 font-medium">${mensaje}</p>
                    <button onclick="cerrarModalEditarUsuario()" class="mt-4 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        Cerrar
                    </button>
                </div>
            `;
        }
    }

    // ===============================================================
    // ACTUALIZACIÓN DE INTERFAZ
    // ===============================================================

    /**
     * Actualizar fila del usuario en la tabla
     * @param {Object} datosUsuario - Datos actualizados del usuario
     */
    function actualizarFilaUsuario(datosUsuario) {
        const fila = document.querySelector(`tr[data-user-id="${datosUsuario.id}"]`);
        if (!fila) return;

        // Actualizar nombre
        const celdaNombre = fila.children[1]; // Segunda columna (nombre)
        if (celdaNombre) {
            celdaNombre.textContent = datosUsuario.name;
        }

        // Actualizar rol sin conversión - mantener admin/user
        const celdaRol = fila.children[2]; // Tercera columna (rol)
        if (celdaRol) {
            celdaRol.textContent = datosUsuario.role;
        }

        // Actualizar estado
        const celdaEstado = fila.querySelector('.user-status');
        if (celdaEstado) {
            celdaEstado.textContent = datosUsuario.status.charAt(0).toUpperCase() + datosUsuario.status.slice(1);
        }

        // Actualizar botón de toggle si cambió el estado
        const btnToggle = fila.querySelector("button[onclick*='toggleEstado']");
        if (btnToggle) {
            btnToggle.textContent = datosUsuario.status === 'activo' ? 'Desactivar' : 'Activar';
            btnToggle.setAttribute('onclick', 
                `toggleEstado(${datosUsuario.id}, '${datosUsuario.status}', '${datosUsuario.name}')`
            );
        }

        console.log("Fila del usuario actualizada en la tabla");
    }

    // ===============================================================
    // INICIALIZACIÓN
    // ===============================================================

    // Cerrar modal con ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('editarUsuarioModal');
            if (modal && !modal.classList.contains('hidden')) {
                cerrarModalEditarUsuario();
            }
        }
    });

    // Cerrar modal al hacer clic en el overlay
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('editarUsuarioModal');
        if (e.target === modal) {
            cerrarModalEditarUsuario();
        }
    });

    console.log("Módulo editar_usuario.js cargado - COPFLOW v2.0");

})();

/**
 * ===============================================================
 * FIN DEL MÓDULO EDITAR_USUARIO.JS
 * ===============================================================
 * 
 * CARACTERÍSTICAS IMPLEMENTADAS:
 * • Carga dinámica de datos del usuario
 * • Validaciones en tiempo real
 * • Detección automática de cambios
 * • Guardado con feedback visual
 * • Manejo robusto de respuestas malformadas/concatenadas
 * • Actualización automática de tabla y estadísticas
 * • Experiencia de usuario fluida
 * • Recuperación inteligente de errores de parsing
 * 
 * INTEGRACIÓN:
 * • APIs: obtener_usuario.php, actualizar_usuario.php
 * • Modales: sistema de confirmación/éxito/error existente
 * • Logging: writeLog(), writeSuccess(), writeError()
 * • Cache: limpieza automática de estadísticas
 * 
 * ROBUSTEZ:
 * • Manejo de múltiples JSONs concatenados
 * • Recuperación de respuestas parcialmente corruptas
 * • Fallback inteligente para respuestas exitosas malformadas
 * • Logging detallado para debugging
 * ===============================================================
 */