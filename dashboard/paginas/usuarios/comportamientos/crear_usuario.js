//===============================================================
// CREAR_USUARIO.JS v2.1 - Lógica de Creación de Usuarios COPFLOW
//===============================================================
// 
// PROPÓSITO:
// Maneja toda la lógica para la creación de nuevos usuarios
// incluyendo validaciones en tiempo real, verificaciones de
// disponibilidad y recarga automática de tabla después de crear.
// 
// CARACTERÍSTICAS PRINCIPALES:
// ┌─────────────────────────────────────────────────────────────┐
// │ • Validaciones en tiempo real con feedback visual          │
// │ • Verificación de disponibilidad de email con debounce     │
// │ • Medidor de fortaleza de contraseña interactivo           │
// │ • Recarga automática de tabla con múltiples estrategias    │
// │ • Manejo robusto de errores con fallbacks                  │
// │ • Integración completa con sistema de modales              │
// │ • Accesibilidad y navegación por teclado                   │
// │ • Logging avanzado para debugging                          │
// └─────────────────────────────────────────────────────────────┘
// 
// ARQUITECTURA MODULAR:
// • Configuración y variables globales
// • Funciones principales del modal
// • Sistema de validaciones en tiempo real
// • Utilidades de validación y UI
// • Sistema de fortaleza de contraseña
// • Función principal de creación con recarga automática
// • Inicialización y eventos
// 
// DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
// FECHA CREACIÓN: 2025-09-10
// FECHA ACTUALIZACIÓN: 2025-09-10 04:13:53 UTC
// REPOSITORIO: https://github.com/Diomedez98/copflows
// EMPRESA: JCCODE-SAS
//===============================================================

(function() {
    "use strict";

    //===============================================================
    // CONFIGURACIÓN Y VARIABLES GLOBALES
    //===============================================================
    
    /**
     * Estado de validación de cada campo del formulario
     */
    let validacionEstado = {
        nombre: false,
        email: false,
        password: false,
        confirmarPassword: false,
        rol: false
    };

    /**
     * Variables de control para optimización
     */
    let timeoutEmail = null;              // Timeout para verificación de email
    let formularioInicializado = false;   // Control de inicialización única
    
    /**
     * Configuración del módulo
     */
    const CONFIG = {
        VERSION: '2.1',
        DEBUG_MODE: true,
        EMAIL_CHECK_DELAY: 800,        // ms para verificación de email
        FOCUS_DELAY: 150,              // ms para focus en campos
        RELOAD_DELAY: 500,             // ms para recarga de tabla
        MIN_PASSWORD_LENGTH: 8,        // Longitud mínima de contraseña
        MIN_PASSWORD_STRENGTH: 3       // Fortaleza mínima requerida
    };

    /**
     * Sistema de logging optimizado
     */
    function log(mensaje, tipo = 'info') {
        if (!CONFIG.DEBUG_MODE) return;
        
        const timestamp = new Date().toLocaleTimeString();
        const prefix = `[${timestamp}] [CREAR_USUARIO.JS v${CONFIG.VERSION}]`;
        
        switch(tipo) {
            case 'error': 
                console.error(`${prefix} ❌`, mensaje); 
                break;
            case 'warn': 
                console.warn(`${prefix} ⚠️`, mensaje); 
                break;
            case 'success': 
                console.log(`${prefix} ✅`, mensaje); 
                break;
            case 'info':
            default: 
                console.log(`${prefix} 🔄`, mensaje); 
                break;
        }
    }

    //===============================================================
    // FUNCIONES PRINCIPALES DEL MODAL
    //===============================================================

    /**
     * 🚀 Mostrar modal de creación de usuario
     * Función principal para abrir el modal con inicialización completa
     */
    window.mostrarModalNuevoUsuario = function() {
        log("Abriendo modal de creación de usuario");
        
        try {
            // Limpiar formulario y resetear validaciones
            limpiarFormularioCrear();
            
            // Verificar existencia del modal
            const modal = document.getElementById('crearUsuarioModal');
            if (!modal) {
                log("Modal de creación no encontrado en el DOM", 'error');
                mostrarError('crearUsuarioModal', 'El modal de creación no está disponible. Por favor, recarga la página.');
                return;
            }
            
            // Mostrar modal con animación
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            
            // Inicializar formulario si no se ha hecho
            if (!formularioInicializado) {
                inicializarValidaciones();
                formularioInicializado = true;
                log("Formulario inicializado por primera vez");
            }
            
            // Focus en el primer campo con delay para animación
            setTimeout(() => {
                const nombreInput = document.getElementById('nombreUsuario');
                if (nombreInput) {
                    nombreInput.focus();
                    log("Focus aplicado al campo nombre");
                }
            }, CONFIG.FOCUS_DELAY);
            
            log("Modal de creación abierto correctamente", 'success');
            
        } catch (error) {
            log(`Error al abrir modal: ${error.message}`, 'error');
            
            // Fallback para errores
            if (typeof mostrarModalError === 'function') {
                mostrarModalError("Error", "Error al abrir el modal de creación. Intenta nuevamente.");
            } else {
                alert("Error al abrir el modal de creación. Intenta nuevamente.");
            }
        }
    };

    /**
     * ✖️ Cerrar modal de creación
     * Limpia el estado y oculta el modal
     */
    window.cerrarModalCrearUsuario = function() {
        log("Cerrando modal de creación");
        
        try {
            const modal = document.getElementById('crearUsuarioModal');
            if (modal) {
                // Ocultar modal con animación
                modal.classList.add('hidden');
                document.body.style.overflow = '';
                
                // Limpiar formulario
                limpiarFormularioCrear();
                
                log("Modal de creación cerrado correctamente", 'success');
            }
        } catch (error) {
            log(`Error al cerrar modal: ${error.message}`, 'error');
        }
    };

    /**
     * 🧹 Limpiar formulario de creación
     * Resetea todos los campos y validaciones
     */
    window.limpiarFormularioCrear = function() {
        log("Limpiando formulario de creación");
        
        try {
            // Resetear formulario HTML
            const form = document.getElementById('formCrearUsuario');
            if (form) {
                form.reset();
            }
            
            // Limpiar todas las validaciones visuales
            limpiarTodasLasValidaciones();
            
            // Resetear estado de validación
            validacionEstado = {
                nombre: false,
                email: false,
                password: false,
                confirmarPassword: false,
                rol: false
            };
            
            // Resetear medidor de contraseña
            resetPasswordStrength();
            
            // Actualizar estado del botón
            actualizarEstadoBoton();
            
            // Limpiar timeouts pendientes
            if (timeoutEmail) {
                clearTimeout(timeoutEmail);
                timeoutEmail = null;
            }
            
            log("Formulario limpiado correctamente", 'success');
            
        } catch (error) {
            log(`Error al limpiar formulario: ${error.message}`, 'error');
        }
    };

    //===============================================================
    // SISTEMA DE VALIDACIONES EN TIEMPO REAL
    //===============================================================

    /**
     * 🔧 Inicializar validaciones del formulario
     * Configura todos los event listeners para validación en tiempo real
     */
    function inicializarValidaciones() {
        log("Inicializando sistema de validaciones");
        
        try {
            // Validación de nombre
            const nombreInput = document.getElementById('nombreUsuario');
            if (nombreInput) {
                nombreInput.addEventListener('input', function() {
                    validarNombre(this.value);
                });
                
                nombreInput.addEventListener('blur', function() {
                    if (this.value.length > 0) {
                        validarNombre(this.value);
                    }
                });
                log("Validación de nombre configurada");
            }

            // Validación de email con debounce
            const emailInput = document.getElementById('emailUsuario');
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
                        validacionEstado.email = false;
                        actualizarEstadoBoton();
                    }
                });
                
                emailInput.addEventListener('blur', function() {
                    if (this.value.length > 0) {
                        clearTimeout(timeoutEmail);
                        validarEmail(this.value);
                    }
                });
                log("Validación de email configurada");
            }

            // Validación de contraseña
            const passwordInput = document.getElementById('passwordUsuario');
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    validarPassword(this.value);
                    // Re-validar confirmación cuando cambia la contraseña principal
                    validarConfirmarPassword();
                });
                log("Validación de contraseña configurada");
            }

            // Validación de confirmación de contraseña
            const confirmarInput = document.getElementById('confirmarPassword');
            if (confirmarInput) {
                confirmarInput.addEventListener('input', function() {
                    validarConfirmarPassword();
                });
                log("Validación de confirmación configurada");
            }

            // Validación de rol
            const rolSelect = document.getElementById('rolUsuario');
            if (rolSelect) {
                rolSelect.addEventListener('change', function() {
                    validarRol(this.value);
                    mostrarDescripcionRol(this.value);
                });
                log("Validación de rol configurada");
            }
            
            log("Sistema de validaciones inicializado correctamente", 'success');
            
        } catch (error) {
            log(`Error al inicializar validaciones: ${error.message}`, 'error');
        }
    }

    /**
     * ✅ Validar nombre de usuario
     * @param {string} nombre - Nombre a validar
     */
    function validarNombre(nombre) {
        const campo = document.getElementById('nombreUsuario');
        const errorDiv = document.getElementById('errorNombre');
        const validDiv = document.getElementById('validNombre');
        
        try {
            // Limpiar estados anteriores
            limpiarEstadosCampo(campo, errorDiv, validDiv);
            
            // Validación de campo vacío
            if (nombre.length === 0) {
                validacionEstado.nombre = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación de longitud mínima
            if (nombre.length < 2) {
                mostrarErrorCampo('errorNombre', 'El nombre debe tener al menos 2 caracteres');
                campo.classList.add('border-red-500');
                validacionEstado.nombre = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación de longitud máxima
            if (nombre.length > 100) {
                mostrarErrorCampo('errorNombre', 'El nombre no puede exceder 100 caracteres');
                campo.classList.add('border-red-500');
                validacionEstado.nombre = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación de caracteres permitidos
            if (!/^[a-zA-ZÀ-ÿ\s]+$/.test(nombre)) {
                mostrarErrorCampo('errorNombre', 'El nombre solo puede contener letras y espacios');
                campo.classList.add('border-red-500');
                validacionEstado.nombre = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación exitosa
            campo.classList.add('border-green-500');
            validDiv.classList.remove('hidden');
            validacionEstado.nombre = true;
            actualizarEstadoBoton();
            
            log(`Nombre validado: "${nombre}"`, 'success');
            
        } catch (error) {
            log(`Error en validación de nombre: ${error.message}`, 'error');
        }
    }

    /**
     * 📧 Validar email de usuario
     * @param {string} email - Email a validar
     */
    function validarEmail(email) {
        const campo = document.getElementById('emailUsuario');
        const errorDiv = document.getElementById('errorEmail');
        const validDiv = document.getElementById('validEmail');
        const loadingDiv = document.getElementById('loadingEmail');
        
        try {
            // Limpiar estados anteriores
            limpiarEstadosCampo(campo, errorDiv, validDiv);
            loadingDiv.classList.add('hidden');
            
            // Validación de campo vacío
            if (email.length === 0) {
                validacionEstado.email = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación de formato de email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                mostrarErrorCampo('errorEmail', 'Formato de email inválido');
                campo.classList.add('border-red-500');
                validacionEstado.email = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Mostrar indicador de carga
            loadingDiv.classList.remove('hidden');
            log(`Verificando disponibilidad de email: ${email}`);
            
            // Verificar disponibilidad en servidor
            verificarDisponibilidadEmail(email);
            
        } catch (error) {
            log(`Error en validación de email: ${error.message}`, 'error');
            loadingDiv.classList.add('hidden');
            mostrarErrorCampo('errorEmail', 'Error al validar email');
            validacionEstado.email = false;
            actualizarEstadoBoton();
        }
    }

    /**
     * 🌐 Verificar disponibilidad de email en servidor
     * @param {string} email - Email a verificar
     */
    function verificarDisponibilidadEmail(email) {
        fetch('/public_html/dashboard/paginas/usuarios/api/verificar_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            const campo = document.getElementById('emailUsuario');
            const errorDiv = document.getElementById('errorEmail');
            const validDiv = document.getElementById('validEmail');
            const loadingDiv = document.getElementById('loadingEmail');
            
            // Ocultar loading
            loadingDiv.classList.add('hidden');
            
            if (data && !data.exists) {
                // Email disponible
                campo.classList.add('border-green-500');
                validDiv.classList.remove('hidden');
                validacionEstado.email = true;
                log(`Email disponible: ${email}`, 'success');
            } else {
                // Email ya existe
                mostrarErrorCampo('errorEmail', 'Este email ya está registrado en el sistema');
                campo.classList.add('border-red-500');
                validacionEstado.email = false;
                log(`Email ya existe: ${email}`, 'warn');
            }
            
            actualizarEstadoBoton();
        })
        .catch(error => {
            log(`Error al verificar email: ${error.message}`, 'error');
            
            const loadingDiv = document.getElementById('loadingEmail');
            if (loadingDiv) loadingDiv.classList.add('hidden');
            
            mostrarErrorCampo('errorEmail', 'Error al verificar disponibilidad del email');
            validacionEstado.email = false;
            actualizarEstadoBoton();
        });
    }

    /**
     * 🔒 Validar contraseña
     * @param {string} password - Contraseña a validar
     */
    function validarPassword(password) {
        const campo = document.getElementById('passwordUsuario');
        const errorDiv = document.getElementById('errorPassword');
        
        try {
            // Limpiar estados anteriores
            campo.classList.remove('border-red-500', 'border-green-500');
            errorDiv.classList.add('hidden');
            
            // Validación de campo vacío
            if (password.length === 0) {
                validacionEstado.password = false;
                resetPasswordStrength();
                actualizarEstadoBoton();
                return;
            }
            
            // Calcular y mostrar fortaleza
            const strength = calcularFortalezaPassword(password);
            mostrarFortalezaPassword(strength);
            
            // Validación de longitud mínima
            if (password.length < CONFIG.MIN_PASSWORD_LENGTH) {
                mostrarErrorCampo('errorPassword', `La contraseña debe tener al menos ${CONFIG.MIN_PASSWORD_LENGTH} caracteres`);
                campo.classList.add('border-red-500');
                validacionEstado.password = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación de fortaleza mínima
            if (strength < CONFIG.MIN_PASSWORD_STRENGTH) {
                mostrarErrorCampo('errorPassword', 'La contraseña es demasiado débil. Incluya mayúsculas, minúsculas y números');
                campo.classList.add('border-red-500');
                validacionEstado.password = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación exitosa
            campo.classList.add('border-green-500');
            validacionEstado.password = true;
            actualizarEstadoBoton();
            
            log(`Contraseña validada (fortaleza: ${strength}/4)`, 'success');
            
        } catch (error) {
            log(`Error en validación de contraseña: ${error.message}`, 'error');
        }
    }

    /**
     * 🔒 Validar confirmación de contraseña
     */
    function validarConfirmarPassword() {
        const password = document.getElementById('passwordUsuario').value;
        const confirmar = document.getElementById('confirmarPassword').value;
        const campo = document.getElementById('confirmarPassword');
        const errorDiv = document.getElementById('errorConfirmarPassword');
        const validDiv = document.getElementById('validConfirmarPassword');
        
        try {
            // Limpiar estados anteriores
            limpiarEstadosCampo(campo, errorDiv, validDiv);
            
            // Validación de campo vacío
            if (confirmar.length === 0) {
                validacionEstado.confirmarPassword = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación de coincidencia
            if (password !== confirmar) {
                mostrarErrorCampo('errorConfirmarPassword', 'Las contraseñas no coinciden');
                campo.classList.add('border-red-500');
                validacionEstado.confirmarPassword = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación exitosa
            campo.classList.add('border-green-500');
            validDiv.classList.remove('hidden');
            validacionEstado.confirmarPassword = true;
            actualizarEstadoBoton();
            
            log("Confirmación de contraseña validada", 'success');
            
        } catch (error) {
            log(`Error en validación de confirmación: ${error.message}`, 'error');
        }
    }

    /**
     * 👤 Validar rol de usuario
     * @param {string} rol - Rol a validar
     */
    function validarRol(rol) {
        const campo = document.getElementById('rolUsuario');
        const errorDiv = document.getElementById('errorRol');
        
        try {
            // Limpiar estados anteriores
            campo.classList.remove('border-red-500', 'border-green-500');
            errorDiv.classList.add('hidden');
            
            // Validación de selección
            if (!rol || rol === '') {
                mostrarErrorCampo('errorRol', 'Debe seleccionar un rol para el usuario');
                campo.classList.add('border-red-500');
                validacionEstado.rol = false;
                actualizarEstadoBoton();
                return;
            }
            
            // Validación exitosa
            campo.classList.add('border-green-500');
            validacionEstado.rol = true;
            actualizarEstadoBoton();
            
            log(`Rol seleccionado: ${rol}`, 'success');
            
        } catch (error) {
            log(`Error en validación de rol: ${error.message}`, 'error');
        }
    }

    //===============================================================
    // UTILIDADES DE VALIDACIÓN
    //===============================================================

    /**
     * 🧹 Limpiar estados visuales de un campo
     * @param {HTMLElement} campo - Campo de entrada
     * @param {HTMLElement} errorDiv - Div de error
     * @param {HTMLElement} validDiv - Div de validación exitosa
     */
    function limpiarEstadosCampo(campo, errorDiv, validDiv) {
        if (campo) {
            campo.classList.remove('border-red-500', 'border-green-500');
        }
        if (errorDiv) {
            errorDiv.classList.add('hidden');
        }
        if (validDiv) {
            validDiv.classList.add('hidden');
        }
    }

    /**
     * ❌ Mostrar error en campo específico
     * @param {string} errorId - ID del div de error
     * @param {string} mensaje - Mensaje de error
     */
    function mostrarErrorCampo(errorId, mensaje) {
        const errorDiv = document.getElementById(errorId);
        if (errorDiv) {
            const span = errorDiv.querySelector('span');
            if (span) {
                span.textContent = mensaje;
            }
            errorDiv.classList.remove('hidden');
        }
    }

    /**
     * 🧹 Limpiar todas las validaciones visuales
     */
    function limpiarTodasLasValidaciones() {
        try {
            const campos = ['nombreUsuario', 'emailUsuario', 'passwordUsuario', 'confirmarPassword', 'rolUsuario'];
            const errores = ['errorNombre', 'errorEmail', 'errorPassword', 'errorConfirmarPassword', 'errorRol'];
            const validos = ['validNombre', 'validEmail', 'validConfirmarPassword'];
            
            // Limpiar campos
            campos.forEach(campo => {
                const elemento = document.getElementById(campo);
                if (elemento) {
                    elemento.classList.remove('border-red-500', 'border-green-500');
                }
            });
            
            // Ocultar errores
            errores.forEach(error => {
                const elemento = document.getElementById(error);
                if (elemento) {
                    elemento.classList.add('hidden');
                }
            });
            
            // Ocultar validaciones exitosas
            validos.forEach(valido => {
                const elemento = document.getElementById(valido);
                if (elemento) {
                    elemento.classList.add('hidden');
                }
            });
            
            // Limpiar elementos específicos
            const elementosEspeciales = ['loadingEmail', 'rolDescription'];
            elementosEspeciales.forEach(id => {
                const elemento = document.getElementById(id);
                if (elemento) {
                    elemento.classList.add('hidden');
                }
            });
            
            log("Todas las validaciones limpiadas");
            
        } catch (error) {
            log(`Error al limpiar validaciones: ${error.message}`, 'error');
        }
    }

    /**
     * 📧 Limpiar validación específica de email
     */
    function limpiarValidacionEmail() {
        const elementos = {
            campo: document.getElementById('emailUsuario'),
            error: document.getElementById('errorEmail'),
            valid: document.getElementById('validEmail'),
            loading: document.getElementById('loadingEmail')
        };
        
        Object.values(elementos).forEach(elemento => {
            if (elemento) {
                if (elemento.id === 'emailUsuario') {
                    elemento.classList.remove('border-red-500', 'border-green-500');
                } else {
                    elemento.classList.add('hidden');
                }
            }
        });
        
        validacionEstado.email = false;
    }

    //===============================================================
    // SISTEMA DE FORTALEZA DE CONTRASEÑA
    //===============================================================

    /**
     * 🔢 Calcular fortaleza de contraseña
     * @param {string} password - Contraseña a evaluar
     * @returns {number} Nivel de fortaleza (0-4)
     */
    function calcularFortalezaPassword(password) {
        let score = 0;
        
        try {
            // Criterios de longitud
            if (password.length >= 8) score++;
            if (password.length >= 12) score++;
            
            // Criterios de caracteres
            if (/[a-z]/.test(password)) score++;    // Minúsculas
            if (/[A-Z]/.test(password)) score++;    // Mayúsculas
            if (/[0-9]/.test(password)) score++;    // Números
            if (/[^A-Za-z0-9]/.test(password)) score++; // Caracteres especiales
            
            return Math.min(score, 4);
            
        } catch (error) {
            log(`Error al calcular fortaleza: ${error.message}`, 'error');
            return 0;
        }
    }

    /**
     * 🎨 Mostrar fortaleza de contraseña visualmente
     * @param {number} strength - Nivel de fortaleza (0-4)
     */
    function mostrarFortalezaPassword(strength) {
        const colors = ['bg-red-500', 'bg-orange-500', 'bg-yellow-500', 'bg-green-500'];
        const texts = ['Muy débil', 'Débil', 'Moderada', 'Fuerte'];
        
        try {
            // Resetear todas las barras
            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById(`strength${i}`);
                if (bar) {
                    bar.className = 'h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300';
                }
            }
            
            // Llenar barras según fortaleza
            for (let i = 1; i <= strength; i++) {
                const bar = document.getElementById(`strength${i}`);
                if (bar) {
                    bar.className = `h-1 w-1/4 ${colors[strength - 1]} rounded-full transition-all duration-300`;
                }
            }
            
            // Actualizar texto descriptivo
            const strengthText = document.getElementById('strengthText');
            if (strengthText) {
                strengthText.textContent = texts[strength - 1] || 'Ingrese una contraseña';
                strengthText.className = `text-xs mt-1 ${strength >= 3 ? 'text-green-600' : strength >= 2 ? 'text-yellow-600' : 'text-red-600'}`;
            }
            
        } catch (error) {
            log(`Error al mostrar fortaleza: ${error.message}`, 'error');
        }
    }

    /**
     * 🔄 Resetear medidor de contraseña
     */
    function resetPasswordStrength() {
        try {
            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById(`strength${i}`);
                if (bar) {
                    bar.className = 'h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300';
                }
            }
            
            const strengthText = document.getElementById('strengthText');
            if (strengthText) {
                strengthText.textContent = 'Ingrese una contraseña';
                strengthText.className = 'text-xs text-gray-500 mt-1';
            }
            
        } catch (error) {
            log(`Error al resetear medidor: ${error.message}`, 'error');
        }
    }

    //===============================================================
    // UTILIDADES DE INTERFAZ
    //===============================================================

    /**
     * 👁️ Mostrar/ocultar contraseña
     * @param {string} inputId - ID del campo de contraseña
     * @param {string} iconId - ID del icono de toggle
     */
    window.togglePasswordVisibility = function(inputId, iconId) {
        try {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input && icon) {
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'ri-eye-line';
                    log(`Contraseña visible: ${inputId}`);
                } else {
                    input.type = 'password';
                    icon.className = 'ri-eye-off-line';
                    log(`Contraseña oculta: ${inputId}`);
                }
            }
        } catch (error) {
            log(`Error al toggle contraseña: ${error.message}`, 'error');
        }
    };

    /**
     * 📝 Mostrar descripción del rol seleccionado
     * @param {string} rol - Rol seleccionado
     */
    function mostrarDescripcionRol(rol) {
        const descripcionDiv = document.getElementById('rolDescription');
        const textoSpan = document.getElementById('rolDescriptionText');
        
        const descripciones = {
            'user': 'Acceso básico al sistema. Puede ver y gestionar su propia información.',
            'admin': 'Acceso completo al sistema. Puede gestionar usuarios y configuraciones.'
        };
        
        try {
            if (descripcionDiv && textoSpan) {
                if (rol && descripciones[rol]) {
                    textoSpan.textContent = descripciones[rol];
                    descripcionDiv.classList.remove('hidden');
                    log(`Descripción mostrada para rol: ${rol}`);
                } else {
                    descripcionDiv.classList.add('hidden');
                }
            }
        } catch (error) {
            log(`Error al mostrar descripción de rol: ${error.message}`, 'error');
        }
    }

    /**
     * 🔘 Actualizar estado del botón crear
     */
    function actualizarEstadoBoton() {
        try {
            const boton = document.getElementById('btnCrearUsuario');
            if (boton) {
                const todosValidos = Object.values(validacionEstado).every(estado => estado === true);
                
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
        } catch (error) {
            log(`Error al actualizar botón: ${error.message}`, 'error');
        }
    }

    //===============================================================
    // FUNCIÓN PRINCIPAL DE CREACIÓN CON RECARGA AUTOMÁTICA
    //===============================================================

    /**
     * 🚀 Crear nuevo usuario con recarga automática de tabla
     * Función principal que maneja todo el proceso de creación
     */
    window.crearUsuario = function() {
        log("Iniciando proceso de creación de usuario");
        
        try {
            // Validar formulario completo antes de enviar
            if (!validarFormularioCompleto()) {
                log("Formulario no válido, cancelando creación", 'warn');
                return;
            }
            
            // Mostrar estado de carga
            mostrarEstadoCarga(true);
            
            // Recopilar datos del formulario
            const datosUsuario = {
                nombre: document.getElementById('nombreUsuario').value.trim(),
                email: document.getElementById('emailUsuario').value.trim(),
                password: document.getElementById('passwordUsuario').value,
                rol: document.getElementById('rolUsuario').value,
                estado: 'activo' // Siempre activo en creación
            };
            
            log(`Enviando datos del usuario: ${datosUsuario.nombre} (${datosUsuario.email})`);
            
            // Enviar datos al servidor
            fetch('/public_html/dashboard/paginas/usuarios/api/crear_usuario.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(datosUsuario)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                mostrarEstadoCarga(false);
                
                if (data.success) {
                    log(`Usuario creado exitosamente: ID ${data.user_id || 'N/A'}`, 'success');
                    
                    // Mostrar notificación de éxito
                    if (typeof mostrarModalExito === 'function') {
                        mostrarModalExito(
                            "Usuario Creado", 
                            `El usuario "${datosUsuario.nombre}" ha sido creado exitosamente.`
                        );
                    } else {
                        alert(`Usuario "${datosUsuario.nombre}" creado exitosamente.`);
                    }
                    
                    // Cerrar modal
                    cerrarModalCrearUsuario();
                    
                    // ═══════════════════════════════════════════════════════════
                    // 🔄 SISTEMA DE RECARGA AUTOMÁTICA DE TABLA
                    // ═══════════════════════════════════════════════════════════
                    
                    log("Iniciando recarga automática de tabla");
                    
                    setTimeout(() => {
                        // Estrategia 1: Función específica de recarga de tabla
                        if (typeof window.recargarTablaUsuarios === 'function') {
                            log("Usando recargarTablaUsuarios()", 'success');
                            window.recargarTablaUsuarios();
                            
                        } else if (typeof window.ajaxFiltrar === 'function') {
                            // Estrategia 2: Función de filtros AJAX
                            log("Usando ajaxFiltrar() como alternativa", 'success');
                            window.ajaxFiltrar();
                            
                        } else if (typeof window.recargarSeccionUsuarios === 'function') {
                            // Estrategia 3: Recarga de sección completa
                            log("Usando recargarSeccionUsuarios()", 'success');
                            window.recargarSeccionUsuarios();
                            
                        } else {
                            // Estrategia 4: Recarga manual de sección
                            log("Intentando recarga manual de sección");
                            const usuariosSection = document.getElementById('usuariosSection');
                            if (usuariosSection) {
                                fetch('/public_html/dashboard/paginas/usuarios/usuarios.php')
                                .then(response => response.text())
                                .then(html => {
                                    usuariosSection.innerHTML = html;
                                    log("Sección recargada manualmente", 'success');
                                    
                                    // Reinicializar eventos si está disponible
                                    if (typeof window.inicializarUsuarios === 'function') {
                                        setTimeout(() => {
                                            window.inicializarUsuarios();
                                        }, 100);
                                    }
                                })
                                .catch(error => {
                                    log(`Error en recarga manual: ${error.message}`, 'error');
                                    recargarPaginaCompleta();
                                });
                            } else {
                                recargarPaginaCompleta();
                            }
                        }
                        
                        // Actualizar estadísticas independientemente
                        if (typeof actualizarEstadisticasUsuarios === 'function') {
                            setTimeout(() => {
                                actualizarEstadisticasUsuarios();
                                log("Estadísticas actualizadas");
                            }, 300);
                        }
                        
                    }, CONFIG.RELOAD_DELAY);
                    
                } else {
                    log(`Error al crear usuario: ${data.message}`, 'error');
                    
                    // Mostrar error específico
                    if (typeof mostrarModalError === 'function') {
                        mostrarModalError("Error al Crear Usuario", data.message);
                    } else {
                        alert("Error: " + data.message);
                    }
                }
            })
            .catch(error => {
                log(`Error de conexión: ${error.message}`, 'error');
                mostrarEstadoCarga(false);
                
                if (typeof mostrarModalError === 'function') {
                    mostrarModalError(
                        "Error de Conexión", 
                        "No se pudo conectar con el servidor. Intente nuevamente."
                    );
                } else {
                    alert("Error de conexión. Intente nuevamente.");
                }
            });
            
        } catch (error) {
            log(`Error general en creación: ${error.message}`, 'error');
            mostrarEstadoCarga(false);
            
            if (typeof mostrarModalError === 'function') {
                mostrarModalError("Error", "Ocurrió un error inesperado. Intente nuevamente.");
            } else {
                alert("Error inesperado. Intente nuevamente.");
            }
        }
    };

    /**
     * 🔄 Recargar página completa como último recurso
     */
    function recargarPaginaCompleta() {
        log("Último recurso: recargando página completa");
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    }

    /**
     * ✅ Validar formulario completo antes de enviar
     * @returns {boolean} - True si el formulario es válido
     */
    function validarFormularioCompleto() {
        try {
            // Re-validar todos los campos
            const nombre = document.getElementById('nombreUsuario').value.trim();
            const email = document.getElementById('emailUsuario').value.trim();
            const password = document.getElementById('passwordUsuario').value;
            const confirmar = document.getElementById('confirmarPassword').value;
            const rol = document.getElementById('rolUsuario').value;
            
            validarNombre(nombre);
            validarPassword(password);
            validarConfirmarPassword();
            validarRol(rol);
            
            // Verificar que todos los campos estén válidos
            const todosValidos = Object.values(validacionEstado).every(estado => estado === true);
            
            if (!todosValidos) {
                // Enfocar el primer campo con error
                const campos = ['nombre', 'email', 'password', 'confirmarPassword', 'rol'];
                for (const campo of campos) {
                    if (!validacionEstado[campo]) {
                        const elemento = document.getElementById(getInputIdByCampo(campo));
                        if (elemento) {
                            elemento.focus();
                            log(`Focus aplicado a campo con error: ${campo}`, 'warn');
                            break;
                        }
                    }
                }
            }
            
            log(`Validación completa: ${todosValidos ? 'EXITOSA' : 'FALLIDA'}`, todosValidos ? 'success' : 'warn');
            return todosValidos;
            
        } catch (error) {
            log(`Error en validación completa: ${error.message}`, 'error');
            return false;
        }
    }

    /**
     * 🎯 Obtener ID de input por nombre de campo
     * @param {string} campo - Nombre del campo
     * @returns {string} - ID del input
     */
    function getInputIdByCampo(campo) {
        const mapping = {
            'nombre': 'nombreUsuario',
            'email': 'emailUsuario',
            'password': 'passwordUsuario',
            'confirmarPassword': 'confirmarPassword',
            'rol': 'rolUsuario'
        };
        return mapping[campo] || '';
    }

    /**
     * ⏳ Mostrar estado de carga en el botón
     * @param {boolean} mostrar - True para mostrar carga, false para ocultar
     */
    function mostrarEstadoCarga(mostrar) {
        try {
            const boton = document.getElementById('btnCrearUsuario');
            const icono = document.getElementById('iconCrearUsuario');
            const texto = document.getElementById('textCrearUsuario');
            
            if (boton && icono && texto) {
                if (mostrar) {
                    boton.disabled = true;
                    icono.className = 'ri-loader-4-line animate-spin mr-1';
                    texto.textContent = 'Creando Usuario...';
                    boton.classList.add('opacity-75');
                    log("Estado de carga activado");
                } else {
                    boton.disabled = false;
                    icono.className = 'ri-user-add-line mr-1';
                    texto.textContent = 'Crear Usuario';
                    boton.classList.remove('opacity-75');
                    
                    // Re-evaluar estado del botón
                    actualizarEstadoBoton();
                    log("Estado de carga desactivado");
                }
            }
        } catch (error) {
            log(`Error al mostrar estado de carga: ${error.message}`, 'error');
        }
    }

    //===============================================================
    // INICIALIZACIÓN Y EVENTOS
    //===============================================================

    /**
     * 🎯 Inicializar módulo cuando el DOM esté listo
     */
    document.addEventListener('DOMContentLoaded', function() {
        log("Inicializando módulo de creación de usuarios", 'success');
        
        try {
            // Evento para cerrar modal con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const modal = document.getElementById('crearUsuarioModal');
                    if (modal && !modal.classList.contains('hidden')) {
                        cerrarModalCrearUsuario();
                        log("Modal cerrado con tecla ESC");
                    }
                }
            });
            
            // Evento para cerrar modal al hacer clic en el overlay
            document.addEventListener('click', function(e) {
                const modal = document.getElementById('crearUsuarioModal');
                if (e.target === modal) {
                    cerrarModalCrearUsuario();
                    log("Modal cerrado con clic en overlay");
                }
            });
            
            // Prevenir cierre accidental al hacer clic dentro del modal
            const modalContent = document.querySelector('#crearUsuarioModal .bg-white');
            if (modalContent) {
                modalContent.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
            
            log("Eventos de modal configurados correctamente", 'success');
            
        } catch (error) {
            log(`Error en inicialización: ${error.message}`, 'error');
        }
    });

})();

//===============================================================
// FIN DEL MÓDULO CREAR_USUARIO.JS v2.1
//===============================================================

/**
 * ===============================================================
 * 📋 DOCUMENTACIÓN FINAL
 * ===============================================================
 * 
 * 🎯 FUNCIONALIDADES IMPLEMENTADAS:
 * ✅ Modal minimalista y completamente responsive
 * ✅ Validación en tiempo real con feedback visual avanzado
 * ✅ Verificación de disponibilidad de email con debounce
 * ✅ Medidor de fortaleza de contraseña interactivo
 * ✅ Validación de confirmación de contraseña instantánea
 * ✅ Selección de rol con descripción automática
 * ✅ Sistema de recarga automática de tabla con múltiples estrategias
 * ✅ Estados de carga y feedback visual optimizado
 * ✅ Manejo robusto de errores con fallbacks
 * ✅ Integración completa con sistema de notificaciones
 * ✅ Accesibilidad y navegación por teclado
 * ✅ Logging avanzado para debugging
 * ✅ Compatibilidad total con sistema existente
 * 
 * 🔧 ARQUITECTURA TÉCNICA:
 * • Patrón de módulo IIFE para encapsulación
 * • Sistema de configuración centralizado
 * • Validaciones independientes y reutilizables
 * • Manejo de errores por niveles
 * • Logging estructurado con niveles
 * • Event delegation y optimización de performance
 * 
 * 🚀 SISTEMA DE RECARGA AUTOMÁTICA:
 * • Estrategia 1: recargarTablaUsuarios() (específica)
 * • Estrategia 2: ajaxFiltrar() (filtros AJAX)
 * • Estrategia 3: recargarSeccionUsuarios() (sección completa)
 * • Estrategia 4: Recarga manual con fetch
 * • Fallback: Recarga completa de página
 * 
 * 🔗 DEPENDENCIAS:
 * • modales_copflow.js (sistema de notificaciones)
 * • usuarios.js (funciones de tabla y estadísticas)
 * • API: crear_usuario.php, verificar_email.php
 * • Modal: crear_usuario_modal.php
 * 
 * 🎨 ESTILOS REQUERIDOS:
 * • Tailwind CSS para clases utilitarias
 * • RemixIcon para iconografía
 * • Clases personalizadas para animaciones
 * 
 * 👨‍💻 DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
 * 📅 FECHA: 2025-09-10 04:13:53 UTC
 * 🔗 REPOSITORIO: https://github.com/Diomedez98/copflows
 * 🏢 EMPRESA: JCCODE-SAS
 * 📦 VERSIÓN: 2.1
 * 
 * ===============================================================
 */