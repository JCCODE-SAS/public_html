<?php

/**
 * ===============================================================
 * 📄 editar_usuario_modal.php v2.1
 * ---------------------------------------------------------------
 * Modal de edición de usuario con diseño responsivo optimizado
 * Mantiene consistencia con otros modales del sistema
 * ===============================================================
 * 
 * DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
 * FECHA ACTUALIZACIÓN: 2025-09-11 02:35:15 UTC
 * REPOSITORIO: https://github.com/Diomedez98/copflows
 * EMPRESA: JCCODE-SAS
 * ===============================================================
 */
?>

<!-- Modal Editar Usuario - Responsivo con Glassmorphism -->
<div id="editarUsuarioModal"
    class="fixed inset-0 flex items-center justify-center p-2 transition-all duration-300 hidden" style="background-color: rgba(0, 0, 0, 0.65) !important; 
            backdrop-filter: blur(12px) !important; 
            -webkit-backdrop-filter: blur(12px) !important; 
            z-index: 1050 !important;">

    <!-- Contenedor Principal Responsivo -->
    <div class="w-full max-w-2xl mx-auto" style="max-height: 95vh !important; 
                display: flex !important; 
                flex-direction: column !important;">

        <!-- Modal Content con Glassmorphism -->
        <div class="rounded-3xl shadow-2xl transform transition-all duration-300 scale-95" style="background: rgba(255, 255, 255, 0.98) !important;
                    backdrop-filter: blur(30px) !important;
                    -webkit-backdrop-filter: blur(30px) !important;
                    border: 2px solid rgba(255, 255, 255, 0.5) !important;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 
                               0 8px 32px 0 rgba(31, 38, 135, 0.6) !important;
                    max-height: 95vh !important;
                    display: flex !important;
                    flex-direction: column !important;">

            <!-- Header Fijo del Modal -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200/50 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                                box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                        <i class="ri-user-settings-line text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Editar Usuario</h2>
                        <p class="text-sm text-gray-600">Modifica la información del usuario seleccionado</p>
                    </div>
                </div>
                <button onclick="cerrarModalEditarUsuario()"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>

            <!-- Contenido con Scroll Optimizado -->
            <div class="flex-1 overflow-y-auto px-6 py-4" style="scrollbar-width: thin; 
                        scrollbar-color: rgba(156, 163, 175, 0.5) transparent;">

                <!-- Loading State -->
                <div id="loadingEditarUsuario" class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                        <div class="animate-spin w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full">
                        </div>
                    </div>
                    <p class="text-gray-600">Cargando datos del usuario...</p>
                </div>

                <!-- Formulario de Edición -->
                <form id="formEditarUsuario" class="hidden space-y-4">
                    <input type="hidden" id="editUserId" name="id">

                    <!-- Información Básica -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2">
                            <i class="ri-user-line text-amber-600"></i>
                            <h3 class="text-base font-semibold text-gray-800">Información Básica</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nombre -->
                            <div>
                                <label for="editUserName" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre Completo *
                                </label>
                                <input type="text" id="editUserName" name="name" required
                                    placeholder="Ej: Juan Pérez García"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                                <div id="errorName" class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded-lg">
                                    <span class="text-red-600 text-xs"></span>
                                </div>
                            </div>

                            <!-- Email -->
                            <div>
                                <label for="editUserEmail" class="block text-sm font-medium text-gray-700 mb-1">
                                    Correo Electrónico *
                                </label>
                                <input type="email" id="editUserEmail" name="email" required
                                    placeholder="usuario@ejemplo.com"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                                <div id="errorEmail" class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded-lg">
                                    <span class="text-red-600 text-xs"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador -->
                    <div class="border-t border-gray-200/70 my-4"></div>

                    <!-- Configuración de Cuenta -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2">
                            <i class="ri-settings-3-line text-amber-600"></i>
                            <h3 class="text-base font-semibold text-gray-800">Configuración de Cuenta</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Rol -->
                            <div>
                                <label for="editUserRole" class="block text-sm font-medium text-gray-700 mb-1">
                                    Rol del Usuario *
                                </label>
                                <select id="editUserRole" name="role" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                                    <option value="">Seleccionar rol...</option>
                                    <option value="admin">Administrador</option>
                                    <option value="user">Usuario</option>
                                </select>
                                <div id="errorRole" class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded-lg">
                                    <span class="text-red-600 text-xs"></span>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div>
                                <label for="editUserStatus" class="block text-sm font-medium text-gray-700 mb-1">
                                    Estado del Usuario *
                                </label>
                                <select id="editUserStatus" name="status" required
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                                <div id="errorStatus"
                                    class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded-lg">
                                    <span class="text-red-600 text-xs"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Separador -->
                    <div class="border-t border-gray-200/70 my-4"></div>

                    <!-- Información Adicional -->
                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2">
                            <i class="ri-information-line text-amber-600"></i>
                            <h3 class="text-base font-semibold text-gray-800">Información del Sistema</h3>
                        </div>

                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200/50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 font-medium">ID del Usuario:</span>
                                    <span id="editUserIdDisplay" class="font-bold text-gray-800">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 font-medium">Fecha de Registro:</span>
                                    <span id="editUserCreatedAt" class="font-bold text-gray-800">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error General -->
                    <div id="errorGeneral" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                        <span class="text-red-600 text-sm"></span>
                    </div>
                </form>
            </div>

            <!-- Footer Fijo con Botones -->
            <div
                class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200/50 bg-gray-50/50 rounded-b-3xl flex-shrink-0">
                <button type="button" onclick="cerrarModalEditarUsuario()"
                    class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors font-medium text-sm">
                    <i class="ri-close-line mr-1"></i>
                    Cancelar
                </button>

                <button type="button" id="btnGuardarUsuario" onclick="guardarCambiosUsuario()" disabled
                    class="px-6 py-2 rounded-xl font-medium transition-all duration-200 opacity-50 cursor-not-allowed text-sm"
                    style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
                               color: white;
                               box-shadow: 0 4px 15px rgba(245, 158, 11, 0.3);">
                    <i class="ri-save-line mr-1"></i>
                    <span id="btnGuardarTexto">Guardar Cambios</span>
                    <div id="btnGuardarSpinner"
                        class="hidden ml-2 w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin">
                    </div>
                </button>
            </div>

        </div>
    </div>
</div>

<!-- CSS Responsivo para Modal Editar -->
<style>
/* Modal editar usuario responsivo */
#editarUsuarioModal {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Scrollbar personalizado */
#editarUsuarioModal .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

#editarUsuarioModal .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

#editarUsuarioModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

/* Animaciones */
#editarUsuarioModal:not(.hidden) .scale-95 {
    transform: scale(1) !important;
}

#editarUsuarioModal.hidden .scale-95 {
    transform: scale(0.95) !important;
}

/* Estados del formulario */
#editarUsuarioModal #formEditarUsuario:not(.hidden) {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive breakpoints */
@media (max-height: 800px) {
    #editarUsuarioModal .space-y-4>*+* {
        margin-top: 0.75rem !important;
    }

    #editarUsuarioModal .py-4 {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }
}

@media (max-height: 700px) {
    #editarUsuarioModal .px-6 {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }

    #editarUsuarioModal .text-xl {
        font-size: 1.125rem !important;
    }
}

@media (max-width: 768px) {
    #editarUsuarioModal .md\:grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }

    #editarUsuarioModal .max-w-2xl {
        max-width: calc(100vw - 1rem) !important;
    }
}

/* Botón guardar habilitado */
#editarUsuarioModal #btnGuardarUsuario:not(:disabled) {
    opacity: 1 !important;
    cursor: pointer !important;
}

#editarUsuarioModal #btnGuardarUsuario:not(:disabled):hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4) !important;
}

/* Estados de validación */
#editarUsuarioModal .border-amber-500 {
    border-color: #f59e0b !important;
}

#editarUsuarioModal .border-red-500 {
    border-color: #ef4444 !important;
}
</style>

<script>
console.log('📝 Modal editar usuario responsivo v2.1 - by Diomedez98');
</script>