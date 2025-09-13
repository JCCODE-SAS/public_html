<?php
//===============================================================
// VER_OPERARIO_MODAL.PHP - Modal de Visualización de Operario
//===============================================================
// Adaptado de usuarios, para tabla operadores.
//===============================================================
?>

<div id="verOperarioModal" class="fixed inset-0 flex items-center justify-center p-2 transition-all duration-300 hidden"
    style="background-color: rgba(0, 0, 0, 0.65) !important; 
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

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200/50 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #22c55e 0%, #06b6d4 100%);
                                box-shadow: 0 4px 15px rgba(34,197,94,0.3);">
                        <i class="ri-user-3-line text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Información del Operario</h2>
                        <p class="text-sm text-gray-600">Detalles completos del operario seleccionado</p>
                    </div>
                </div>
                <button onclick="cerrarModalVerOperario()"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto"
                style="scrollbar-width: thin; scrollbar-color: rgba(156, 163, 175, 0.5) transparent;">
                <div id="loadingVerOperario" class="text-center py-8 px-6">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                        <div class="animate-spin w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full">
                        </div>
                    </div>
                    <p class="text-gray-600">Cargando información del operario...</p>
                </div>

                <div id="contenidoVerOperario" class="hidden">
                    <div class="px-6 py-4" style="background: linear-gradient(135deg, #22d3ee 0%, #818cf8 100%);">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center border-2 border-white/30"
                                style="background: rgba(255, 255, 255, 0.2);">
                                <span id="operarioInitials" class="text-xl font-bold text-white">OP</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 id="operarioNombre" class="text-xl font-bold text-white mb-1 truncate">Nombre
                                    Operario</h3>
                                <p id="operarioUsuario" class="text-blue-100 text-sm mb-2 truncate">usuario</p>
                                <p id="operarioEmail" class="text-blue-100 text-sm mb-2 truncate">email@ejemplo.com</p>
                                <div class="flex gap-2 flex-wrap">
                                    <span id="operarioDisponibleBadge"
                                        class="px-3 py-1 bg-green-500 rounded-full text-xs font-bold uppercase text-white">DISPONIBLE</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 rounded-xl border border-blue-200/50"
                                style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                                <h4 class="font-bold text-blue-800 mb-3 flex items-center text-sm">
                                    <i class="ri-user-line text-blue-600 mr-2"></i>
                                    Información Personal
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-700 font-medium text-xs">ID del Operario:</span>
                                        <span id="operarioIdDisplay" class="font-bold text-blue-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-700 font-medium text-xs">Nombre Completo:</span>
                                        <span id="operarioNombreDisplay"
                                            class="font-bold text-blue-900 text-xs text-right truncate ml-2 max-w-32">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-700 font-medium text-xs">Usuario:</span>
                                        <span id="operarioUsuarioDisplay"
                                            class="font-bold text-blue-900 text-xs text-right truncate ml-2 max-w-32">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-700 font-medium text-xs">Correo Electrónico:</span>
                                        <span id="operarioEmailDisplay"
                                            class="font-bold text-blue-900 text-xs text-right truncate ml-2 max-w-32">-</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-4 rounded-xl border border-green-200/50"
                                style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);">
                                <h4 class="font-bold text-green-800 mb-3 flex items-center text-sm">
                                    <i class="ri-settings-3-line text-green-600 mr-2"></i>
                                    Estado en Sistema
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-700 font-medium text-xs">Disponible:</span>
                                        <span id="operarioDisponibleDisplay"
                                            class="font-bold text-green-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-700 font-medium text-xs">Fecha de Registro:</span>
                                        <span id="operarioCreadoDisplay"
                                            class="font-bold text-green-900 text-xs">-</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="errorVerOperario" class="hidden p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
                        <i class="ri-error-warning-line text-2xl text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-red-600 mb-2">Error al cargar operario</h3>
                    <p id="errorMessage" class="text-gray-600 mb-4 text-sm">No se pudo cargar la información del
                        operario.</p>
                    <button onclick="cerrarModalVerOperario()"
                        class="px-4 py-2 rounded-xl text-white font-medium text-sm transition-all duration-200"
                        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        Cerrar Modal
                    </button>
                </div>
            </div>

            <div
                class="flex items-center justify-center px-6 py-4 border-t border-gray-200/50 bg-gray-50/50 rounded-b-3xl flex-shrink-0">
                <button onclick="cerrarModalVerOperario()"
                    class="px-6 py-2 rounded-xl font-medium transition-all duration-200 text-sm" style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
                               color: white;
                               box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);">
                    <i class="ri-close-line mr-1"></i>
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
<!-- CSS igual que el de usuario, solo cambia ids a operario -->
<style>
#verOperarioModal {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

#verOperarioModal .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

#verOperarioModal .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

#verOperarioModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

#verOperarioModal:not(.hidden) .scale-95 {
    transform: scale(1) !important;
}

#verOperarioModal.hidden .scale-95 {
    transform: scale(0.95) !important;
}

@media (max-width: 768px) {
    #verOperarioModal .md\:grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }

    #verOperarioModal .max-w-2xl {
        max-width: calc(100vw - 1rem) !important;
    }
}
</style>
<script>
window.cerrarModalVerOperario = function() {
    const modal = document.getElementById('verOperarioModal');
    if (modal) {
        modal.classList.add('hidden');
    }
};
</script>