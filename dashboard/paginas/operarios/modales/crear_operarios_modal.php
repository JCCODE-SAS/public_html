<?php
//===============================================================
// CREAR_OPERARIO_MODAL.PHP - Modal de Creación de Operarios
//===============================================================
// Adaptado de usuarios, para tabla operadores.
//===============================================================
?>

<div id="crearOperarioModal" class="fixed inset-0 bg-black bg-opacity-60 hidden flex items-center justify-center" style="position: fixed !important; 
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
                            <h2 class="text-lg font-bold">Crear Nuevo Operario</h2>
                            <p class="text-sm text-blue-100">Complete la información del operario</p>
                        </div>
                    </div>
                    <button onclick="cerrarModalCrearOperario()"
                        class="w-8 h-8 bg-white bg-opacity-20 rounded-full flex items-center justify-center hover:bg-opacity-30 transition-all">
                        <i class="ri-close-line text-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Contenido con Scroll Optimizado -->
            <div class="flex-1 overflow-y-auto p-4" style="max-height: calc(90vh - 160px) !important;">

                <form id="formCrearOperario" class="space-y-4">

                    <!-- Información Personal -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2 border-b border-gray-200">
                            <i class="ri-user-line text-blue-600"></i>
                            <h3 class="font-semibold text-gray-800">Información Personal</h3>
                        </div>

                        <!-- Nombre Completo -->
                        <div>
                            <label for="nombreOperario" class="block text-sm font-medium text-gray-700 mb-1">
                                Nombre Completo *
                            </label>
                            <input type="text" id="nombreOperario" name="nombre"
                                placeholder="Ingrese el nombre completo"
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

                        <!-- Usuario -->
                        <div>
                            <label for="usuarioOperario" class="block text-sm font-medium text-gray-700 mb-1">
                                Usuario *
                            </label>
                            <input type="text" id="usuarioOperario" name="usuario" placeholder="Ingrese el usuario"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                required>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="emailOperario" class="block text-sm font-medium text-gray-700 mb-1">
                                Correo Electrónico
                            </label>
                            <input type="email" id="emailOperario" name="email" placeholder="operario@ejemplo.com"
                                class="w-full px-3 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm">
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
                            <label for="passwordOperario" class="block text-sm font-medium text-gray-700 mb-1">
                                Contraseña *
                            </label>
                            <div class="relative">
                                <input type="password" id="passwordOperario" name="password"
                                    placeholder="Mínimo 8 caracteres"
                                    class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                    required>
                                <button type="button"
                                    onclick="togglePasswordVisibility('passwordOperario', 'eyeIconPasswordOperario')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="eyeIconPasswordOperario" class="ri-eye-off-line"></i>
                                </button>
                            </div>
                            <div id="errorPassword"
                                class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-600">
                                <i class="ri-error-warning-line mr-1"></i>
                                <span></span>
                            </div>
                        </div>

                        <!-- Confirmar Contraseña -->
                        <div>
                            <label for="confirmarPasswordOperario" class="block text-sm font-medium text-gray-700 mb-1">
                                Confirmar Contraseña *
                            </label>
                            <div class="relative">
                                <input type="password" id="confirmarPasswordOperario" name="confirmar_password"
                                    placeholder="Repita la contraseña"
                                    class="w-full px-3 py-2.5 pr-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-sm"
                                    required>
                                <button type="button"
                                    onclick="togglePasswordVisibility('confirmarPasswordOperario', 'eyeIconConfirmOperario')"
                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                    <i id="eyeIconConfirmOperario" class="ri-eye-off-line"></i>
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

                    <!-- Estado Inicial -->
                    <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-sm font-medium text-gray-700">Estado inicial</span>
                                <p class="text-xs text-gray-500">El operario será creado disponible</p>
                            </div>
                            <span
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1.5 h-1.5 mr-1 bg-green-600 rounded-full"></span>
                                Disponible
                            </span>
                        </div>
                        <input type="hidden" name="disponible" value="1">
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
                    <button type="button" onclick="cerrarModalCrearOperario()"
                        class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm transition-all">
                        Cancelar
                    </button>
                    <button type="button" onclick="limpiarFormularioCrearOperario()"
                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm transition-all">
                        Limpiar
                    </button>
                    <button type="button" id="btnCrearOperario" onclick="crearOperario()"
                        class="px-5 py-2 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white rounded-lg text-sm transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                        <i id="iconCrearOperario" class="ri-user-add-line mr-1"></i>
                        <span id="textCrearOperario">Crear Operario</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Igual al original, solo cambia el id a crearOperarioModal */
#crearOperarioModal {
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

#crearOperarioModal:not(.hidden) {
    display: flex !important;
    visibility: visible !important;
    opacity: 1 !important;
}

#crearOperarioModal .max-w-lg {
    max-width: 32rem !important;
    width: calc(100% - 2rem) !important;
    margin: 0 auto !important;
}

#crearOperarioModal .bg-white {
    max-height: 90vh !important;
    min-height: auto !important;
    overflow: hidden !important;
    display: flex !important;
    flex-direction: column !important;
}

#crearOperarioModal .overflow-y-auto {
    flex: 1 !important;
    overflow-y: auto !important;
    max-height: none !important;
}

#crearOperarioModal .overflow-y-auto::-webkit-scrollbar {
    width: 6px !important;
}

#crearOperarioModal .overflow-y-auto::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1) !important;
    border-radius: 3px !important;
}

#crearOperarioModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.3) !important;
    border-radius: 3px !important;
}

#crearOperarioModal .overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.5) !important;
}

@media (max-width: 768px) {
    #crearOperarioModal {
        padding: 0.5rem !important;
    }

    #crearOperarioModal .max-w-lg {
        width: calc(100% - 1rem) !important;
    }
}

@media (max-width: 480px) {
    #crearOperarioModal {
        padding: 0.25rem !important;
    }

    #crearOperarioModal .max-w-lg {
        width: calc(100% - 0.5rem) !important;
    }

    #crearOperarioModal .p-4 {
        padding: 0.75rem !important;
    }

    #crearOperarioModal .space-x-3 {
        gap: 0.5rem !important;
    }

    #crearOperarioModal .space-x-3>* {
        padding: 0.5rem 0.75rem !important;
        font-size: 0.75rem !important;
    }
}

@media (max-width: 640px) {

    #crearOperarioModal input,
    #crearOperarioModal select {
        font-size: 16px !important;
    }
}

#crearOperarioModal .transition-all {
    transition: all 0.2s ease-in-out !important;
}

#crearOperarioModal input:focus,
#crearOperarioModal select:focus {
    outline: none !important;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
}

body.modal-crear-operario-abierto {
    overflow: hidden !important;
    height: 100vh !important;
}
</style>

<script>
// Gestión del scroll del body
function gestionarScrollModalCrearOperario(abrir) {
    if (abrir) {
        document.body.classList.add('modal-crear-operario-abierto');
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    } else {
        document.body.classList.remove('modal-crear-operario-abierto');
        document.documentElement.style.overflow = '';
        document.body.style.overflow = '';
    }
}

window.mostrarModalNuevoOperario = function() {
    const modal = document.getElementById('crearOperarioModal');
    if (modal) {
        modal.classList.remove('hidden');
        gestionarScrollModalCrearOperario(true);
        setTimeout(() => {
            const primerInput = modal.querySelector('input');
            if (primerInput) {
                primerInput.focus();
            }
        }, 100);
    }
};

window.cerrarModalCrearOperario = function() {
    const modal = document.getElementById('crearOperarioModal');
    if (modal) {
        modal.classList.add('hidden');
        gestionarScrollModalCrearOperario(false);
    }
};

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('crearOperarioModal');
        if (modal && !modal.classList.contains('hidden')) {
            cerrarModalCrearOperario();
        }
    }
});
</script>