<?php
//===============================================================
// CREAR_USUARIO_MODAL.PHP - Modal de Creación de Usuarios
//===============================================================
// 
// PROPÓSITO:
// Modal con posicionamiento fijo absoluto, centrado perfecto
// y visualización completa sin interferencias de navegación.
// 
// DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
// FECHA ACTUALIZACIÓN: 2025-09-11 03:12:00 UTC
// REPOSITORIO: https://github.com/Diomedez98/copflows
// EMPRESA: JCCODE-SAS
//===============================================================
?>

<!-- =============================================================== -->
<!-- MODAL FIJO CENTRADO - PREDOMINA SOBRE TODO -->
<!-- =============================================================== -->

<div id="crearUsuarioModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center" style="position: fixed !important; 
           top: 0 !important; 
           left: 0 !important; 
           right: 0 !important; 
           bottom: 0 !important; 
           width: 100vw !important; 
           height: 100vh !important; 
           z-index: 99999 !important; 
           backdrop-filter: blur(12px) !important;
           -webkit-backdrop-filter: blur(12px) !important;
           padding: 1rem !important;">

    <!-- Contenedor del Modal - Centrado Perfecto -->
    <div class="w-full max-w-lg mx-auto" style="position: relative !important; 
                z-index: 100000 !important; 
                max-height: 90vh !important;
                min-height: auto !important;
                display: flex !important;
                flex-direction: column !important;">

        <!-- Modal Content - Glassmorphism Optimizado -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col" style="background: rgba(255, 255, 255, 0.98) !important; 
                    backdrop-filter: blur(30px) !important; 
                    -webkit-backdrop-filter: blur(30px) !important; 
                    border: 2px solid rgba(255, 255, 255, 0.5) !important; 
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 
                               0 8px 32px 0 rgba(31, 38, 135, 0.6) !important;
                    max-height: 90vh !important;
                    width: 100% !important;">

            <!-- Header Compacto -->
            <div class="bg-gradient-to-r from-blue-600 to-purple-600 text-white p-4 flex-shrink-0">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                            <i class="ri-user-add-line text-lg"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold">Crear Nuevo Usuario</h2>
                            <p class="text-sm text-blue-100">Complete la información del usuario</p>
                        </div>
                    </div>
                    <button onclick="cerrarModalCrearUsuario()"
                        class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition-all">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Contenido con Scroll Optimizado -->
            <div class="flex-1 overflow-y-auto p-4" style="max-height: calc(90vh - 160px) !important;">

                <form id="formCrearUsuario" class="space-y-4">

                    <!-- Información Personal -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2 border-b border-gray-200">
                            <i class="ri-user-line text-blue-600"></i>
                            <h3 class="font-semibold text-gray-800">Información Personal</h3>
                        </div>

                        <!-- Nombre Completo -->
                        <div>
                            <label for="nombreUsuario" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre Completo *
                            </label>
                            <input type="text" id="nombreUsuario" name="nombre" placeholder="Ingrese el nombre completo"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                required>

                            <div id="errorNombre"
                                class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-600">
                                <i class="ri-error-warning-line mr-1"></i>
                                <span></span>
                            </div>
                            <div id="validNombre"
                                class="hidden mt-1 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-600">
                                <i class="ri-check-line mr-1"></i>
                                <span>Nombre válido</span>
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="emailUsuario" class="block text-sm font-medium text-gray-700 mb-1">
                                Correo Electrónico *
                            </label>
                            <input type="email" id="emailUsuario" name="email" placeholder="usuario@ejemplo.com"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                required>

                            <div id="loadingEmail"
                                class="hidden mt-1 p-2 bg-blue-50 border border-blue-200 rounded text-xs text-blue-600">
                                <i class="ri-loader-4-line animate-spin mr-1"></i>
                                <span>Verificando disponibilidad...</span>
                            </div>
                            <div id="errorEmail"
                                class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-600">
                                <i class="ri-error-warning-line mr-1"></i>
                                <span></span>
                            </div>
                            <div id="validEmail"
                                class="hidden mt-1 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-600">
                                <i class="ri-check-line mr-1"></i>
                                <span>Email disponible</span>
                            </div>
                        </div>
                    </div>

                    <!-- Configuración de Cuenta -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2 border-b border-gray-200">
                            <i class="ri-lock-line text-blue-600"></i>
                            <h3 class="font-semibold text-gray-800">Configuración de Cuenta</h3>
                        </div>

                        <!-- Contraseña -->
                        <div>
                            <label for="passwordUsuario" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña *
                            </label>
                            <div class="relative">
                                <input type="password" id="passwordUsuario" name="password"
                                    placeholder="Mínimo 8 caracteres"
                                    class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                    required>
                                <button type="button"
                                    onclick="togglePasswordVisibility('passwordUsuario', 'eyeIconPassword')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="eyeIconPassword" class="ri-eye-off-line"></i>
                                </button>
                            </div>

                            <!-- Medidor de Fortaleza Compacto -->
                            <div id="passwordStrength" class="mt-2">
                                <div class="flex space-x-1">
                                    <div id="strength1"
                                        class="h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300"></div>
                                    <div id="strength2"
                                        class="h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300"></div>
                                    <div id="strength3"
                                        class="h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300"></div>
                                    <div id="strength4"
                                        class="h-1 w-1/4 bg-gray-200 rounded-full transition-all duration-300"></div>
                                </div>
                                <p id="strengthText" class="text-xs text-gray-500 mt-1">Ingrese una contraseña</p>
                            </div>

                            <div id="errorPassword"
                                class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-600">
                                <i class="ri-error-warning-line mr-1"></i>
                                <span></span>
                            </div>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="confirmarPassword" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar Contraseña *
                            </label>
                            <div class="relative">
                                <input type="password" id="confirmarPassword" name="confirmar_password"
                                    placeholder="Repita la contraseña"
                                    class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                    required>
                                <button type="button"
                                    onclick="togglePasswordVisibility('confirmarPassword', 'eyeIconConfirm')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="eyeIconConfirm" class="ri-eye-off-line"></i>
                                </button>
                            </div>

                            <div id="errorConfirmarPassword"
                                class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-600">
                                <i class="ri-error-warning-line mr-1"></i>
                                <span></span>
                            </div>
                            <div id="validConfirmarPassword"
                                class="hidden mt-1 p-2 bg-green-50 border border-green-200 rounded text-xs text-green-600">
                                <i class="ri-check-line mr-1"></i>
                                <span>Las contraseñas coinciden</span>
                            </div>
                        </div>
                    </div>

                    <!-- Permisos y Estado -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2 border-b border-gray-200">
                            <i class="ri-shield-user-line text-blue-600"></i>
                            <h3 class="font-semibold text-gray-800">Permisos y Estado</h3>
                        </div>

                        <!-- Rol del Usuario -->
                        <div>
                            <label for="rolUsuario" class="block text-sm font-medium text-gray-700 mb-1">
                                Rol del Usuario *
                            </label>
                            <select id="rolUsuario" name="rol"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                required>
                                <option value="">Seleccione un rol</option>
                                <option value="user">Usuario Estándar</option>
                                <option value="admin">Administrador</option>
                            </select>

                            <div id="rolDescription"
                                class="hidden mt-2 p-2 bg-blue-50 border border-blue-200 rounded text-xs text-blue-700">
                                <i class="ri-information-line mr-1"></i>
                                <span id="rolDescriptionText"></span>
                            </div>

                            <div id="errorRol"
                                class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-600">
                                <i class="ri-error-warning-line mr-1"></i>
                                <span></span>
                            </div>
                        </div>

                        <!-- Estado Inicial -->
                        <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-sm font-medium text-gray-700">Estado inicial</span>
                                    <p class="text-xs text-gray-500">El usuario será creado activo</p>
                                </div>
                                <span
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <span class="w-1.5 h-1.5 mr-1 bg-green-600 rounded-full"></span>
                                    Activo
                                </span>
                            </div>
                            <input type="hidden" name="estado" value="activo">
                        </div>
                    </div>

                </form>
            </div>

            <!-- Footer Fijo -->
            <div class="bg-gray-50 p-4 border-t flex justify-between items-center flex-shrink-0">
                <div class="text-xs text-gray-500 flex items-center">
                    <i class="ri-information-line mr-1"></i>
                    <span>Los campos con * son obligatorios</span>
                </div>

                <div class="flex space-x-3">
                    <button type="button" onclick="cerrarModalCrearUsuario()"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm transition-all">
                        Cancelar
                    </button>

                    <button type="button" onclick="limpiarFormularioCrear()"
                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm transition-all">
                        Limpiar
                    </button>

                    <button type="button" id="btnCrearUsuario" onclick="crearUsuario()"
                        class="px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i id="iconCrearUsuario" class="ri-user-add-line mr-1"></i>
                        <span id="textCrearUsuario">Crear Usuario</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- =============================================================== -->
<!-- CSS CRÍTICO PARA POSICIONAMIENTO ABSOLUTO -->
<!-- =============================================================== -->

<style>
/* POSICIONAMIENTO FIJO ABSOLUTO - PREDOMINA SOBRE TODO */
#crearUsuarioModal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 99999 !important;
    margin: 0 !important;
    box-sizing: border-box !important;
    overflow: hidden !important;
}

/* Cuando está visible, fuerza la visualización */
#crearUsuarioModal:not(.hidden) {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
}

/* Centrado perfecto y responsivo */
#crearUsuarioModal .max-w-lg {
    max-width: 32rem !important;
    width: calc(100% - 2rem) !important;
    margin: 0 auto !important;
}

/* Contenedor del modal con altura controlada */
#crearUsuarioModal .bg-white {
    max-height: 90vh !important;
    min-height: auto !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
}

/* Área de scroll del contenido */
#crearUsuarioModal .overflow-y-auto {
    flex: 1 !important;
    overflow-y: auto !important;
    max-height: none !important;
}

/* Scrollbar personalizado */
#crearUsuarioModal .overflow-y-auto::-webkit-scrollbar {
    width: 6px !important;
}

#crearUsuarioModal .overflow-y-auto::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1) !important;
    border-radius: 3px !important;
}

#crearUsuarioModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.3) !important;
    border-radius: 3px !important;
}

#crearUsuarioModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.5) !important;
}

/* Responsive optimizado */
@media (max-width: 768px) {
    #crearUsuarioModal {
        padding: 0.5rem !important;
    }

    #crearUsuarioModal .max-w-lg {
        width: calc(100% - 1rem) !important;
    }
}

@media (max-width: 480px) {
    #crearUsuarioModal {
        padding: 0.25rem !important;
    }

    #crearUsuarioModal .max-w-lg {
        width: calc(100% - 0.5rem) !important;
    }

    #crearUsuarioModal .p-4 {
        padding: 0.75rem !important;
    }

    #crearUsuarioModal .space-x-3 {
        gap: 0.5rem !important;
    }

    #crearUsuarioModal .space-x-3>* {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.75rem !important;
    }
}

/* Prevenir zoom en móviles */
@media (max-width: 640px) {

    #crearUsuarioModal input,
    #crearUsuarioModal select {
        font-size: 16px !important;
    }
}

/* Animaciones mejoradas */
#crearUsuarioModal .transition-all {
    transition: all 0.2s ease-in-out !important;
}

/* Medidor de contraseña */
#passwordStrength .bg-red-500 {
    background-color: #ef4444 !important;
}

#passwordStrength .bg-orange-500 {
    background-color: #f97316 !important;
}

#passwordStrength .bg-yellow-500 {
    background-color: #eab308 !important;
}

#passwordStrength .bg-green-500 {
    background-color: #22c55e !important;
}

/* Focus mejorado */
#crearUsuarioModal input:focus,
#crearUsuarioModal select:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}

/* Prevenir scroll del body */
body.modal-crear-usuario-abierto {
    overflow: hidden !important;
    height: 100vh !important;
}
</style>

<!-- Script para gestión perfecta del modal -->
<script>
// Gestión del scroll del body
function gestionarScrollModalCrearUsuario(abrir) {
    if (abrir) {
        document.body.classList.add('modal-crear-usuario-abierto');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    } else {
        document.body.classList.remove('modal-crear-usuario-abierto');
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }
}

// Override de las funciones originales
const mostrarModalOriginalCrear = window.mostrarModalNuevoUsuario;
const cerrarModalOriginalCrear = window.cerrarModalCrearUsuario;

window.mostrarModalNuevoUsuario = function() {
    const modal = document.getElementById('crearUsuarioModal');
    if (modal) {
        modal.classList.remove('hidden');
        gestionarScrollModalCrearUsuario(true);

        // Forzar el foco en el modal
        setTimeout(() => {
            const primerInput = modal.querySelector('input');
            if (primerInput) {
                primerInput.focus();
            }
        }, 100);
    }

    if (mostrarModalOriginalCrear && typeof mostrarModalOriginalCrear === 'function') {
        mostrarModalOriginalCrear();
    }

    console.log('📌 Modal crear usuario mostrado con posicionamiento fijo absoluto');
};

window.cerrarModalCrearUsuario = function() {
    const modal = document.getElementById('crearUsuarioModal');
    if (modal) {
        modal.classList.add('hidden');
        gestionarScrollModalCrearUsuario(false);
    }

    if (cerrarModalOriginalCrear && typeof cerrarModalOriginalCrear === 'function') {
        cerrarModalOriginalCrear();
    }

    console.log('📌 Modal crear usuario cerrado');
};

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('crearUsuarioModal');
        if (modal && !modal.classList.contains('hidden')) {
            cerrarModalCrearUsuario();
        }
    }
});

console.log('🎯 Modal crear usuario con posicionamiento fijo absoluto configurado');
</script>