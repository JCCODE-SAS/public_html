<?php

/**
 * ===============================================================
 * 📄 ver_usuario_modal.php v2.1
 * ---------------------------------------------------------------
 * Modal de visualización de usuario con diseño responsivo optimizado
 * Información completa en formato compacto y elegante
 * ===============================================================
 * 
 * DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
 * FECHA ACTUALIZACIÓN: 2025-09-11 02:35:15 UTC
 * REPOSITORIO: https://github.com/Diomedez98/copflows
 * EMPRESA: JCCODE-SAS
 * ===============================================================
 */
?>

<!-- Modal Ver Usuario - Responsivo con Glassmorphism -->
<div id="verUsuarioModal" class="fixed inset-0 flex items-center justify-center p-2 transition-all duration-300 hidden"
    style="background-color: rgba(0, 0, 0, 0.65) !important; 
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
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
                                box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);">
                        <i class="ri-user-3-line text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Información del Usuario</h2>
                        <p class="text-sm text-gray-600">Detalles completos del usuario seleccionado</p>
                    </div>
                </div>
                <button onclick="cerrarModalVerUsuario()"
                    class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="ri-close-line text-lg"></i>
                </button>
            </div>

            <!-- Contenido con Scroll Optimizado -->
            <div class="flex-1 overflow-y-auto" style="scrollbar-width: thin; 
                        scrollbar-color: rgba(156, 163, 175, 0.5) transparent;">

                <!-- Loading State -->
                <div id="loadingVerUsuario" class="text-center py-8 px-6">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                        <div class="animate-spin w-8 h-8 border-3 border-blue-500 border-t-transparent rounded-full">
                        </div>
                    </div>
                    <p class="text-gray-600">Cargando información del usuario...</p>
                </div>

                <!-- Contenido Principal -->
                <div id="contenidoVerUsuario" class="hidden">

                    <!-- Perfil Principal con Avatar -->
                    <div class="px-6 py-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-full flex items-center justify-center border-2 border-white/30"
                                style="background: rgba(255, 255, 255, 0.2);">
                                <span id="userInitials" class="text-xl font-bold text-white">US</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 id="userName" class="text-xl font-bold text-white mb-1 truncate">Nombre Usuario</h3>
                                <p id="userEmail" class="text-blue-100 text-sm mb-2 truncate">email@ejemplo.com</p>
                                <div class="flex gap-2 flex-wrap">
                                    <span id="userRoleBadge"
                                        class="px-3 py-1 bg-white/25 rounded-full text-xs font-bold uppercase text-white">ADMIN</span>
                                    <span id="userStatusBadge"
                                        class="px-3 py-1 bg-green-500 rounded-full text-xs font-bold uppercase text-white">ACTIVO</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid de Información Detallada -->
                    <div class="p-6 space-y-4">

                        <!-- Fila 1: Información Personal + Sistema -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- Información Personal -->
                            <div class="p-4 rounded-xl border border-blue-200/50"
                                style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                                <h4 class="font-bold text-blue-800 mb-3 flex items-center text-sm">
                                    <i class="ri-user-line text-blue-600 mr-2"></i>
                                    Información Personal
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-700 font-medium text-xs">ID del Usuario:</span>
                                        <span id="userIdDisplay" class="font-bold text-blue-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-700 font-medium text-xs">Nombre Completo:</span>
                                        <span id="userNameDisplay"
                                            class="font-bold text-blue-900 text-xs text-right truncate ml-2 max-w-32">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-blue-700 font-medium text-xs">Correo Electrónico:</span>
                                        <span id="userEmailDisplay"
                                            class="font-bold text-blue-900 text-xs text-right truncate ml-2 max-w-32">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Información del Sistema -->
                            <div class="p-4 rounded-xl border border-green-200/50"
                                style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);">
                                <h4 class="font-bold text-green-800 mb-3 flex items-center text-sm">
                                    <i class="ri-settings-3-line text-green-600 mr-2"></i>
                                    Configuración del Sistema
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-700 font-medium text-xs">Rol Asignado:</span>
                                        <span id="userRoleDisplay" class="font-bold text-green-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-700 font-medium text-xs">Estado Actual:</span>
                                        <span id="userStatusDisplay" class="font-bold text-green-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-green-700 font-medium text-xs">Tipo de Cuenta:</span>
                                        <span id="userType" class="font-bold text-green-900 text-xs">Estándar</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Fila 2: Fechas + Estadísticas -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            <!-- Fechas y Actividad -->
                            <div class="p-4 rounded-xl border border-purple-200/50"
                                style="background: linear-gradient(135deg, #e9d5ff 0%, #d8b4fe 100%);">
                                <h4 class="font-bold text-purple-800 mb-3 flex items-center text-sm">
                                    <i class="ri-calendar-line text-purple-600 mr-2"></i>
                                    Fechas Importantes
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-purple-700 font-medium text-xs">Fecha de Registro:</span>
                                        <span id="userCreatedAt" class="font-bold text-purple-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-purple-700 font-medium text-xs">Última Actualización:</span>
                                        <span id="userUpdatedAt" class="font-bold text-purple-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-purple-700 font-medium text-xs">Tiempo en Sistema:</span>
                                        <span id="userTimeInSystem" class="font-bold text-purple-900 text-xs">-</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Estadísticas y Métricas -->
                            <div class="p-4 rounded-xl border border-orange-200/50"
                                style="background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);">
                                <h4 class="font-bold text-orange-800 mb-3 flex items-center text-sm">
                                    <i class="ri-bar-chart-line text-orange-600 mr-2"></i>
                                    Estadísticas de Uso
                                </h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between items-center">
                                        <span class="text-orange-700 font-medium text-xs">Días Activo:</span>
                                        <span id="userActiveDays" class="font-bold text-orange-900 text-xs">-</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-orange-700 font-medium text-xs">Estado de Verificación:</span>
                                        <span class="font-bold text-green-600 text-xs">✓ Verificado</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-orange-700 font-medium text-xs">Nivel de Permisos:</span>
                                        <span id="userPermissions"
                                            class="font-bold text-orange-900 text-xs">Completo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error State -->
                <div id="errorVerUsuario" class="hidden p-6 text-center">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                        style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
                        <i class="ri-error-warning-line text-2xl text-red-500"></i>
                    </div>
                    <h3 class="text-lg font-bold text-red-600 mb-2">Error al cargar usuario</h3>
                    <p id="errorMessage" class="text-gray-600 mb-4 text-sm">No se pudo cargar la información del
                        usuario.</p>
                    <button onclick="cerrarModalVerUsuario()"
                        class="px-4 py-2 rounded-xl text-white font-medium text-sm transition-all duration-200"
                        style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);">
                        Cerrar Modal
                    </button>
                </div>
            </div>

            <!-- Footer Fijo con Botón -->
            <div
                class="flex items-center justify-center px-6 py-4 border-t border-gray-200/50 bg-gray-50/50 rounded-b-3xl flex-shrink-0">
                <button onclick="cerrarModalVerUsuario()"
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

<!-- CSS Responsivo para Modal Ver Usuario -->
<style>
/* Modal ver usuario responsivo */
#verUsuarioModal {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Scrollbar personalizado */
#verUsuarioModal .overflow-y-auto::-webkit-scrollbar {
    width: 4px;
}

#verUsuarioModal .overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

#verUsuarioModal .overflow-y-auto::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.5);
    border-radius: 2px;
}

/* Animaciones */
#verUsuarioModal:not(.hidden) .scale-95 {
    transform: scale(1) !important;
}

#verUsuarioModal.hidden .scale-95 {
    transform: scale(0.95) !important;
}

/* Estados del contenido */
#verUsuarioModal #contenidoVerUsuario:not(.hidden) {
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive breakpoints */
@media (max-height: 800px) {
    #verUsuarioModal .space-y-4>*+* {
        margin-top: 0.75rem !important;
    }

    #verUsuarioModal .p-6 {
        padding: 1rem !important;
    }
}

@media (max-height: 700px) {
    #verUsuarioModal .py-4 {
        padding-top: 0.75rem !important;
        padding-bottom: 0.75rem !important;
    }

    #verUsuarioModal .text-xl {
        font-size: 1.125rem !important;
    }
}

@media (max-width: 768px) {
    #verUsuarioModal .md\:grid-cols-2 {
        grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }

    #verUsuarioModal .max-w-2xl {
        max-width: calc(100vw - 1rem) !important;
    }

    #verUsuarioModal .space-x-4>*+* {
        margin-left: 0 !important;
        margin-top: 1rem !important;
    }

    #verUsuarioModal .flex-row {
        flex-direction: column !important;
    }
}

@media (max-width: 480px) {
    #verUsuarioModal .gap-4 {
        gap: 0.75rem !important;
    }

    #verUsuarioModal .p-4 {
        padding: 0.75rem !important;
    }

    #verUsuarioModal .max-w-32 {
        max-width: 6rem !important;
    }
}

/* Truncate text para móviles */
#verUsuarioModal .truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Estados de carga mejorados */
#verUsuarioModal .animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>

<script>
console.log('👁️ Modal ver usuario responsivo v2.1 - by Diomedez98');
</script>