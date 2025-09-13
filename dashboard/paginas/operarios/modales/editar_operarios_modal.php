<?php
//===============================================================
// EDITAR_OPERARIO_MODAL.PHP - Modal de Edición de Operarios
//===============================================================
// Adaptado de usuarios, para tabla operadores.
//===============================================================
?>

<div id="editarOperarioModal"
    class="fixed inset-0 flex items-center justify-center p-2 transition-all duration-300 hidden" style="background-color: rgba(0, 0, 0, 0.65) !important; 
            backdrop-filter: blur(12px) !important; 
            -webkit-backdrop-filter: blur(12px) !important; 
            z-index: 1050 !important;">

    <div class="w-full max-w-2xl mx-auto"
        style="max-height: 95vh !important; display: flex !important; flex-direction: column !important;">

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
                    <div class="w-10 h-10 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);">
                        <i class="ri-user-settings-line text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Editar Operario</h2>
                        <p class="text-sm text-gray-600">Modifica la información del operario seleccionado</p>
                    </div>
                </div>
                <button onclick="cerrarModalEditarOperario()"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-4"
                style="scrollbar-width: thin; scrollbar-color: rgba(156, 163, 175, 0.5) transparent;">
                <div id="loadingEditarOperario" class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                        <div class="animate-spin w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full">
                        </div>
                    </div>
                    <p class="text-gray-600">Cargando datos del operario...</p>
                </div>

                <form id="formEditarOperario" class="hidden space-y-4">
                    <input type="hidden" id="editOperarioId" name="id">

                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2">
                            <i class="ri-user-line text-sky-600"></i>
                            <h3 class="text-base font-semibold text-gray-800">Información Básica</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Nombre -->
                            <div>
                                <label for="editOperarioNombre" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre Completo *
                                </label>
                                <input type="text" id="editOperarioNombre" name="nombre" required
                                    placeholder="Ej: Juan Pérez García"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                                <div id="errorNombre"
                                    class="hidden mt-1 p-2 bg-red-50 border border-red-200 rounded-lg">
                                    <span class="text-red-600 text-xs"></span>
                                </div>
                            </div>
                            <!-- Usuario -->
                            <div>
                                <label for="editOperarioUsuario" class="block text-sm font-medium text-gray-700 mb-1">
                                    Usuario *
                                </label>
                                <input type="text" id="editOperarioUsuario" name="usuario" required
                                    placeholder="Ej: mvillarreal"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                            </div>
                            <!-- Email -->
                            <div>
                                <label for="editOperarioEmail" class="block text-sm font-medium text-gray-700 mb-1">
                                    Correo Electrónico
                                </label>
                                <input type="email" id="editOperarioEmail" name="email"
                                    placeholder="operario@ejemplo.com"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                            </div>
                            <!-- Estado disponible -->
                            <div>
                                <label for="editOperarioDisponible"
                                    class="block text-sm font-medium text-gray-700 mb-1">
                                    Disponible
                                </label>
                                <select id="editOperarioDisponible" name="disponible"
                                    class="w-full px-3 py-2.5 rounded-xl border border-gray-300 focus:ring-2 focus:ring-sky-500/20 focus:border-sky-500 transition-all duration-200 text-sm"
                                    style="background: rgba(255, 255, 255, 0.9) !important;">
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200/70 my-4"></div>

                    <div class="space-y-3">
                        <div class="flex items-center space-x-2 pb-2">
                            <i class="ri-information-line text-sky-600"></i>
                            <h3 class="text-base font-semibold text-gray-800">Información del Sistema</h3>
                        </div>

                        <div class="bg-gray-50/80 p-4 rounded-xl border border-gray-200/50">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 font-medium">ID del Operario:</span>
                                    <span id="editOperarioIdDisplay" class="font-bold text-gray-800">-</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-600 font-medium">Fecha de Registro:</span>
                                    <span id="editOperarioCreado" class="font-bold text-gray-800">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="errorGeneral" class="hidden mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                        <span class="text-red-600 text-sm"></span>
                    </div>
                </form>
            </div>

            <div
                class="flex items-center justify-end space-x-3 px-6 py-4 border-t border-gray-200/50 bg-gray-50/50 rounded-b-3xl flex-shrink-0">
                <button type="button" onclick="cerrarModalEditarOperario()"
                    class="px-4 py-2 rounded-xl border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors font-medium text-sm">
                    <i class="ri-close-line mr-1"></i>
                    Cancelar
                </button>
                <button type="button" id="btnGuardarOperario" onclick="guardarCambiosOperario()" disabled
                    class="px-6 py-2 rounded-xl font-medium transition-all duration-200 opacity-50 cursor-not-allowed text-sm"
                    style="background: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 100%);
                               color: white;
                               box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);">
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

<!-- CSS igual que el de usuario, solo cambia ids a operario -->
<style>
#editarOperarioModal {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

#editarOperarioModal .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

#editarOperarioModal .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

#editarOperarioModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

#editarOperarioModal:not(.hidden) .scale-95 {
    transform: scale(1) !important;
}

#editarOperarioModal.hidden .scale-95 {
    transform: scale(0.95) !important;
}

@media (max-width: 768px) {
    #editarOperarioModal .md\:grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }

    #editarOperarioModal .max-w-2xl {
        max-width: calc(100vw - 1rem) !important;
    }
}

#editarOperarioModal #btnGuardarOperario:not(:disabled) {
    opacity: 1 !important;
    cursor: pointer !important;
}

#editarOperarioModal #btnGuardarOperario:not(:disabled):hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.3) !important;
}
</style>
<script>
window.cerrarModalEditarOperario = function() {
    const modal = document.getElementById('editarOperarioModal');
    if (modal) {
        modal.classList.add('hidden');
    }
};
</script>