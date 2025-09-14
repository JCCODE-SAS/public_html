//===============================================================
// CREAR_OPERARIO.JS v2.1 - Lógica de Creación de Operarios COPFLOW
//===============================================================
// Adaptado para la tabla operadores. Usar campos: nombre, usuario, email, password, disponible.
// Ahora actualiza la tabla al instante con el nuevo operario y muestra el campo "actualizado".
//===============================================================

(function() {
    "use strict";

    let validacionEstado = {
        nombre: false,
        usuario: false,
        email: true,
        password: false,
        confirmarPassword: false
    };
    let timeoutEmail = null;
    let formularioInicializado = false;

    const CONFIG = {
        VERSION: '2.1',
        DEBUG_MODE: true,
        EMAIL_CHECK_DELAY: 800,
        FOCUS_DELAY: 150,
        RELOAD_DELAY: 500,
        MIN_PASSWORD_LENGTH: 8,
        MIN_PASSWORD_STRENGTH: 3
    };

    function log(mensaje, tipo = 'info') {
        if (!CONFIG.DEBUG_MODE) return;
        const timestamp = new Date().toLocaleTimeString();
        const prefix = `[${timestamp}] [CREAR_OPERARIO.JS v${CONFIG.VERSION}]`;
        switch(tipo) {
            case 'error':   console.error(`${prefix} ❌`, mensaje); break;
            case 'warn':    console.warn(`${prefix} ⚠️`, mensaje); break;
            case 'success': console.log(`${prefix} ✅`, mensaje); break;
            default:        console.log(`${prefix} 🔄`, mensaje); break;
        }
    }

    window.mostrarModalNuevoOperario = function() {
        log("Abriendo modal de creación de operario");
        limpiarFormularioCrearOperario();
        const modal = document.getElementById('crearOperarioModal');
        if (!modal) {
            log("Modal de creación no encontrado en el DOM", 'error');
            mostrarError('crearOperarioModal', 'El modal de creación no está disponible. Por favor, recarga la página.');
            return;
        }
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        if (!formularioInicializado) {
            inicializarValidaciones();
            formularioInicializado = true;
            log("Formulario inicializado por primera vez");
        }
        setTimeout(() => {
            const nombreInput = document.getElementById('nombreOperario');
            if (nombreInput) nombreInput.focus();
        }, CONFIG.FOCUS_DELAY);
        log("Modal de creación abierto correctamente", 'success');
    };

    window.cerrarModalCrearOperario = function() {
        log("Cerrando modal de creación");
        const modal = document.getElementById('crearOperarioModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            limpiarFormularioCrearOperario();
            log("Modal de creación cerrado correctamente", 'success');
        }
    };

    window.limpiarFormularioCrearOperario = function() {
        log("Limpiando formulario de creación");
        const form = document.getElementById('formCrearOperario');
        if (form) form.reset();
        limpiarTodasLasValidaciones();
        validacionEstado = {
            nombre: false,
            usuario: false,
            email: true,
            password: false,
            confirmarPassword: false
        };
        resetPasswordStrength();
        actualizarEstadoBoton();
        if (timeoutEmail) {
            clearTimeout(timeoutEmail);
            timeoutEmail = null;
        }
        log("Formulario limpiado correctamente", 'success');
    };

    function inicializarValidaciones() {
        log("Inicializando sistema de validaciones");

        const nombreInput = document.getElementById('nombreOperario');
        if (nombreInput) {
            nombreInput.addEventListener('input', function() { validarNombre(this.value); });
            nombreInput.addEventListener('blur', function() { if (this.value.length > 0) validarNombre(this.value); });
        }

        const usuarioInput = document.getElementById('usuarioOperario');
        if (usuarioInput) {
            usuarioInput.addEventListener('input', function() { validarUsuario(this.value); });
            usuarioInput.addEventListener('blur', function() { if (this.value.length > 0) validarUsuario(this.value); });
        }

        const emailInput = document.getElementById('emailOperario');
        if (emailInput) {
            emailInput.addEventListener('input', function() {
                clearTimeout(timeoutEmail);
                const email = this.value;
                limpiarValidacionEmail();
                if (email.length > 0) {
                    timeoutEmail = setTimeout(() => {
                        validarEmail(email);
                    }, CONFIG.EMAIL_CHECK_DELAY);
                } else {
                    validacionEstado.email = true;
                    actualizarEstadoBoton();
                }
            });
            emailInput.addEventListener('blur', function() {
                if (this.value.length > 0) {
                    clearTimeout(timeoutEmail);
                    validarEmail(this.value);
                }
            });
        }

        const passwordInput = document.getElementById('passwordOperario');
        if (passwordInput) {
            passwordInput.addEventListener('input', function() {
                validarPassword(this.value);
                validarConfirmarPassword();
            });
        }

        const confirmarInput = document.getElementById('confirmarPasswordOperario');
        if (confirmarInput) {
            confirmarInput.addEventListener('input', function() {
                validarConfirmarPassword();
            });
        }
        log("Sistema de validaciones inicializado correctamente", 'success');
    }

    function validarNombre(nombre) {
        const campo = document.getElementById('nombreOperario');
        const errorDiv = document.getElementById('errorNombre');
        const validDiv = document.getElementById('validNombre');
        limpiarEstadosCampo(campo, errorDiv, validDiv);
        if (nombre.length === 0) {
            validacionEstado.nombre = false;
            actualizarEstadoBoton();
            return;
        }
        if (nombre.length < 2) {
            mostrarErrorCampo('errorNombre', 'El nombre debe tener al menos 2 caracteres');
            campo.classList.add('border-red-500');
            validacionEstado.nombre = false;
            actualizarEstadoBoton();
            return;
        }
        if (nombre.length > 100) {
            mostrarErrorCampo('errorNombre', 'El nombre no puede exceder 100 caracteres');
            campo.classList.add('border-red-500');
            validacionEstado.nombre = false;
            actualizarEstadoBoton();
            return;
        }
        if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(nombre)) {
            mostrarErrorCampo('errorNombre', 'El nombre solo puede contener letras y espacios');
            campo.classList.add('border-red-500');
            validacionEstado.nombre = false;
            actualizarEstadoBoton();
            return;
        }
        campo.classList.add('border-green-500');
        validDiv.classList.remove('hidden');
        validacionEstado.nombre = true;
        actualizarEstadoBoton();
        log(`Nombre validado: "${nombre}"`, 'success');
    }

    function validarUsuario(usuario) {
        const campo = document.getElementById('usuarioOperario');
        campo.classList.remove('border-red-500', 'border-green-500');
        if (usuario.length < 4) {
            campo.classList.add('border-red-500');
            validacionEstado.usuario = false;
            actualizarEstadoBoton();
            return;
        }
        if (usuario.length > 50) {
            campo.classList.add('border-red-500');
            validacionEstado.usuario = false;
            actualizarEstadoBoton();
            return;
        }
        if (!/^[a-zA-Z0-9_.-]+$/.test(usuario)) {
            campo.classList.add('border-red-500');
            validacionEstado.usuario = false;
            actualizarEstadoBoton();
            return;
        }
        campo.classList.add('border-green-500');
        validacionEstado.usuario = true;
        actualizarEstadoBoton();
        log(`Usuario validado: "${usuario}"`, 'success');
    }

    function validarEmail(email) {
        const campo = document.getElementById('emailOperario');
        if (email.length === 0) {
            validacionEstado.email = true;
            actualizarEstadoBoton();
            return;
        }
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            campo.classList.add('border-red-500');
            validacionEstado.email = false;
            actualizarEstadoBoton();
            return;
        }
        campo.classList.add('border-green-500');
        validacionEstado.email = true;
        actualizarEstadoBoton();
        log(`Email validado: "${email}"`, 'success');
    }

    function validarPassword(password) {
        const campo = document.getElementById('passwordOperario');
        const errorDiv = document.getElementById('errorPassword');
        campo.classList.remove('border-red-500', 'border-green-500');
        errorDiv.classList.add('hidden');
        if (password.length === 0) {
            validacionEstado.password = false;
            resetPasswordStrength();
            actualizarEstadoBoton();
            return;
        }
        const strength = calcularFortalezaPassword(password);
        mostrarFortalezaPassword(strength);
        if (password.length < CONFIG.MIN_PASSWORD_LENGTH) {
            mostrarErrorCampo('errorPassword', `La contraseña debe tener al menos ${CONFIG.MIN_PASSWORD_LENGTH} caracteres`);
            campo.classList.add('border-red-500');
            validacionEstado.password = false;
            actualizarEstadoBoton();
            return;
        }
        if (strength < CONFIG.MIN_PASSWORD_STRENGTH) {
            mostrarErrorCampo('errorPassword', 'La contraseña es demasiado débil. Incluya mayúsculas, minúsculas y números');
            campo.classList.add('border-red-500');
            validacionEstado.password = false;
            actualizarEstadoBoton();
            return;
        }
        campo.classList.add('border-green-500');
        validacionEstado.password = true;
        actualizarEstadoBoton();
        log(`Contraseña validada (fortaleza: ${strength}/4)`, 'success');
    }

    function validarConfirmarPassword() {
        const password = document.getElementById('passwordOperario').value;
        const confirmar = document.getElementById('confirmarPasswordOperario').value;
        const campo = document.getElementById('confirmarPasswordOperario');
        const errorDiv = document.getElementById('errorConfirmarPassword');
        const validDiv = document.getElementById('validConfirmarPassword');
        limpiarEstadosCampo(campo, errorDiv, validDiv);
        if (confirmar.length === 0) {
            validacionEstado.confirmarPassword = false;
            actualizarEstadoBoton();
            return;
        }
        if (password !== confirmar) {
            mostrarErrorCampo('errorConfirmarPassword', 'Las contraseñas no coinciden');
            campo.classList.add('border-red-500');
            validacionEstado.confirmarPassword = false;
            actualizarEstadoBoton();
            return;
        }
        campo.classList.add('border-green-500');
        validDiv.classList.remove('hidden');
        validacionEstado.confirmarPassword = true;
        actualizarEstadoBoton();
        log("Confirmación de contraseña validada", 'success');
    }

    function limpiarEstadosCampo(campo, errorDiv, validDiv) {
        if (campo) campo.classList.remove('border-red-500', 'border-green-500');
        if (errorDiv) errorDiv.classList.add('hidden');
        if (validDiv) validDiv.classList.add('hidden');
    }

    function mostrarErrorCampo(errorId, mensaje) {
        const errorDiv = document.getElementById(errorId);
        if (errorDiv) {
            const span = errorDiv.querySelector('span');
            if (span) span.textContent = mensaje;
            errorDiv.classList.remove('hidden');
        }
    }

    function limpiarTodasLasValidaciones() {
        const campos = ['nombreOperario', 'usuarioOperario', 'emailOperario', 'passwordOperario', 'confirmarPasswordOperario'];
        campos.forEach(campo => {
            const elemento = document.getElementById(campo);
            if (elemento) elemento.classList.remove('border-red-500', 'border-green-500');
        });
        const errores = ['errorNombre', 'errorPassword', 'errorConfirmarPassword'];
        errores.forEach(error => {
            const elemento = document.getElementById(error);
            if (elemento) elemento.classList.add('hidden');
        });
        const validos = ['validNombre', 'validConfirmarPassword'];
        validos.forEach(valido => {
            const elemento = document.getElementById(valido);
            if (elemento) elemento.classList.add('hidden');
        });
    }

    function limpiarValidacionEmail() {
        const campo = document.getElementById('emailOperario');
        if (campo) campo.classList.remove('border-red-500', 'border-green-500');
        validacionEstado.email = true;
    }

    function calcularFortalezaPassword(password) {
        let score = 0;
        if (password.length >= 8) score++;
        if (password.length >= 12) score++;
        if (/[a-z]/.test(password)) score++;
        if (/[A-Z]/.test(password)) score++;
        if (/[0-9]/.test(password)) score++;
        if (/[^A-Za-z0-9]/.test(password)) score++;
        return Math.min(score, 4);
    }
    function mostrarFortalezaPassword(strength) {
        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
        const texts = ['Muy débil', 'Débil', 'Moderada', 'Fuerte'];
        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById(`strength${i}`);
            if (bar) bar.className = 'h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300';
        }
        for (let i = 1; i <= strength; i++) {
            const bar = document.getElementById(`strength${i}`);
            if (bar) bar.className = `h-1 w-1/4 ${colors[strength - 1]} rounded-full transition-all duration-300`;
        }
        const strengthText = document.getElementById('strengthText');
        if (strengthText) {
            strengthText.textContent = texts[strength - 1] || 'Ingrese una contraseña';
            strengthText.className = `text-xs mt-1 ${strength >= 3 ? 'text-green-600' : strength >= 2 ? 'text-yellow-600' : 'text-red-600'}`;
        }
    }
    function resetPasswordStrength() {
        for (let i = 1; i <= 4; i++) {
            const bar = document.getElementById(`strength${i}`);
            if (bar) bar.className = 'h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300';
        }
        const strengthText = document.getElementById('strengthText');
        if (strengthText) {
            strengthText.textContent = 'Ingrese una contraseña';
            strengthText.className = 'text-xs text-gray-500 mt-1';
        }
    }

    window.togglePasswordVisibility = function(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon = document.getElementById(iconId);
        if (input && icon) {
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'ri-eye-line';
            } else {
                input.type = 'password';
                icon.className = 'ri-eye-off-line';
            }
        }
    };

    function actualizarEstadoBoton() {
        const boton = document.getElementById('btnCrearOperario');
        if (boton) {
            const todosValidos = validacionEstado.nombre && validacionEstado.usuario && validacionEstado.password && validacionEstado.confirmarPassword && validacionEstado.email;
            if (todosValidos) {
                boton.disabled = false;
                boton.classList.remove('opacity-50', 'cursor-not-allowed');
                boton.classList.add('hover:bg-green-700', 'transition-colors');
            } else {
                boton.disabled = true;
                boton.classList.add('opacity-50', 'cursor-not-allowed');
                boton.classList.remove('hover:bg-green-700');
            }
        }
    }

    //===============================================================
    // CREACIÓN DE OPERARIO Y ACTUALIZACIÓN INSTANTÁNEA DE LA TABLA
    //===============================================================

    window.crearOperario = function() {
        log("Iniciando proceso de creación de operario");
        if (!validacionEstado.nombre || !validacionEstado.usuario || !validacionEstado.password || !validacionEstado.confirmarPassword || !validacionEstado.email) {
            log("Formulario no válido, cancelando creación", 'warn');
            return;
        }
        mostrarEstadoCarga(true);
        const datosOperario = {
            nombre: document.getElementById('nombreOperario').value.trim(),
            usuario: document.getElementById('usuarioOperario').value.trim(),
            email: document.getElementById('emailOperario').value.trim(),
            password: document.getElementById('passwordOperario').value,
            disponible: 1
        };
        log(`Enviando datos del operario: ${datosOperario.nombre} (${datosOperario.usuario})`);
        fetch('/public_html/dashboard/paginas/operarios/api/crear_operarios.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(datosOperario)
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            return response.json();
        })
        .then(data => {
            mostrarEstadoCarga(false);
            if (data.success && data.operario) {
                log(`Operario creado exitosamente: ID ${data.operario.id || 'N/A'}`, 'success');
                if (typeof mostrarModalExito === 'function') {
                    mostrarModalExito("Operario Creado", `El operario "${datosOperario.nombre}" ha sido creado exitosamente.`);
                } else {
                    alert(`Operario "${datosOperario.nombre}" creado exitosamente.`);
                }
                cerrarModalCrearOperario();
                // Insertar nuevo operario en la tabla al instante
                if (typeof window.insertarFilaOperarioEnTabla === 'function') {
                    window.insertarFilaOperarioEnTabla(data.operario);
                } else if (typeof window.recargarTablaOperarios === 'function') {
                    window.recargarTablaOperarios();
                } else {
                    window.location.reload();
                }
            } else {
                log(`Error al crear operario: ${data.message}`, 'error');
                if (typeof mostrarModalError === 'function') {
                    mostrarModalError("Error al Crear Operario", data.message);
                } else {
                    alert("Error: " + data.message);
                }
            }
        })
        .catch(error => {
            log(`Error de conexión: ${error.message}`, 'error');
            mostrarEstadoCarga(false);
            if (typeof mostrarModalError === 'function') {
                mostrarModalError("Error de Conexión", "No se pudo conectar con el servidor. Intente nuevamente.");
            } else {
                alert("Error de conexión. Intente nuevamente.");
            }
        });
    };

    // Inserta la fila del nuevo operario en la tabla
    window.insertarFilaOperarioEnTabla = function(operario) {
        const tbody = document.querySelector('.table-glass tbody');
        if (!tbody) return;
        function formateaFecha(fechaStr) {
            const f = new Date(fechaStr.replace(' ', 'T'));
            return f.toLocaleDateString() + ' ' + f.toLocaleTimeString().slice(0,5);
        }
        const fila = document.createElement('tr');
        fila.setAttribute('data-operario-id', operario.id);
        fila.className = "hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-purple-50/50 transition-all duration-300 group";
        fila.innerHTML = `
            <td class="px-6 py-4 text-sm text-gray-500 font-mono">#${String(operario.id).padStart(4,'0')}</td>
            <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-medium">
                        ${operario.nombre.charAt(0).toUpperCase()}
                    </div>
                    <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">${operario.nombre}</div>
                        <div class="text-xs text-gray-500">${operario.usuario}</div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <i class="ri-user-line mr-1"></i>
                    ${operario.usuario}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="text-xs text-gray-500">${operario.email || ''}</div>
            </td>
            <td class="px-6 py-4 user-status">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span class="w-1.5 h-1.5 mr-1.5 rounded-full bg-green-600"></span>
                    Disponible
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <div class="flex items-center">
                    <i class="ri-calendar-2-line mr-2 text-gray-400"></i>
                    ${formateaFecha(operario.creado)}
                </div>
                <div class="text-xs text-gray-400 mt-1">
                    ${formateaFecha(operario.creado).split(' ')[1]} hrs
                </div>
            </td>
            <td class="px-6 py-4 text-sm text-gray-500">
                <span class="bg-blue-50 text-blue-800 px-2 py-1 rounded text-xs">
                    <i class="ri-history-line mr-1"></i>
                    ${formateaFecha(operario.actualizado)}
                </span>
            </td>
            <td class="px-6 py-4">
                <div class="acciones-btn-group flex items-center justify-center space-x-2">
                    <button onclick="abrirModalVerOperario(${operario.id})" class="action-btn btn-view text-xs" title="Ver">
                        <i class="ri-eye-line"></i> Ver
                    </button>
                    <button onclick="editarOperario(${operario.id})" class="action-btn btn-edit text-xs" title="Editar">
                        <i class="ri-edit-line"></i> Editar
                    </button>
                    <button type="button" class="action-btn btn-toggle text-xs"
                        onclick="toggleDisponibilidad(${operario.id}, ${operario.disponible}, '${operario.nombre}')"
                        title="Deshabilitar">
                        <i class="ri-pause-circle-line"></i> Deshabilitar
                    </button>
                </div>
            </td>
        `;
        tbody.prepend(fila);
    };

    function mostrarEstadoCarga(mostrar) {
        const boton = document.getElementById('btnCrearOperario');
        const icono = document.getElementById('iconCrearOperario');
        const texto = document.getElementById('textCrearOperario');
        if (boton && icono && texto) {
            if (mostrar) {
                boton.disabled = true;
                icono.className = 'ri-loader-4-line animate-spin mr-1';
                texto.textContent = 'Creando Operario...';
                boton.classList.add('opacity-75');
            } else {
                boton.disabled = false;
                icono.className = 'ri-user-add-line mr-1';
                texto.textContent = 'Crear Operario';
                boton.classList.remove('opacity-75');
                actualizarEstadoBoton();
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        log("Inicializando módulo de creación de operarios", 'success');
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('crearOperarioModal');
                if (modal && !modal.classList.contains('hidden')) cerrarModalCrearOperario();
            }
        });
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('crearOperarioModal');
            if (e.target === modal) cerrarModalCrearOperario();
        });
        const modalContent = document.querySelector('#crearOperarioModal .bg-white');
        if (modalContent) modalContent.addEventListener('click', function(e) { e.stopPropagation(); });
        log("Eventos de modal configurados correctamente", 'success');
    });

})();

//===============================================================
// FIN DEL MÓDULO CREAR_OPERARIO.JS v2.1
//===============================================================