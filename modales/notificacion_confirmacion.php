<?php

/**
 * ===============================================================
 * 📄 notificacion_confirmacion.php v2.1
 * ---------------------------------------------------------------
 * Modal de confirmación reutilizable con acciones
 * Diseño glassmorphism optimizado con estilos forzados
 * ===============================================================
 * 
 * DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
 * FECHA ACTUALIZACIÓN: 2025-09-11 02:21:21 UTC
 * REPOSITORIO: https://github.com/Diomedez98/copflows
 * EMPRESA: JCCODE-SAS
 * ===============================================================
 */
?>

<!-- Modal de Confirmación con Estilos Forzados -->
<div id="modalConfirmacion"
    class="modal-overlay fixed inset-0 flex items-center justify-center p-4 transition-all duration-300 opacity-0 invisible"
    style="background-color: rgba(0, 0, 0, 0.65) !important; 
           backdrop-filter: blur(12px) !important; 
           -webkit-backdrop-filter: blur(12px) !important; 
           z-index: 1070 !important;">

    <div class="modal-content rounded-3xl shadow-2xl p-8 w-full max-w-md mx-4 transform scale-90 transition-all duration-300"
        style="background: rgba(255, 255, 255, 0.98) !important;
                backdrop-filter: blur(30px) !important;
                -webkit-backdrop-filter: blur(30px) !important;
                border: 2px solid rgba(255, 255, 255, 0.5) !important;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 
                           0 8px 32px 0 rgba(31, 38, 135, 0.6),
                           inset 0 1px 0 rgba(255, 255, 255, 0.9) !important;
                position: relative !important;">

        <!-- Icono de Confirmación -->
        <div class="text-center mb-6">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4" style="background: radial-gradient(circle at 60% 40%, #fef3c7 0%, #fbbf24 30%, #f59e0b 100%);
                        border: 3px solid rgba(251, 191, 36, 0.3);
                        filter: drop-shadow(0 0 15px rgba(251, 191, 36, 0.4));
                        box-shadow: 0 8px 20px rgba(251, 191, 36, 0.3);">
                <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
            </div>

            <h3 class="modal-title" id="tituloConfirmacion" style="color: #1f2937 !important; 
                       font-size: 2rem !important; 
                       font-weight: 700 !important; 
                       margin-bottom: 0.5em !important;
                       text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;">
                ¿Estás seguro?
            </h3>

            <p class="leading-relaxed" id="mensajeConfirmacion" style="color: #4b5563 !important; 
                      font-size: 0.95rem !important; 
                      line-height: 1.6 !important;
                      opacity: 0.9 !important;">
                Esta acción no se puede deshacer.
            </p>
        </div>

        <!-- Botones -->
        <div class="flex gap-4 justify-center">
            <button onclick="cerrarModalConfirmacion()"
                class="btn-cancel px-6 py-3 rounded-2xl font-semibold transition-all duration-300 hover:scale-105 focus:outline-none"
                style="background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%) !important;
                       color: #374151 !important;
                       border: none !important;
                       font-weight: 600 !important;
                       padding: 0.75rem 1.5rem !important;
                       border-radius: 1rem !important;
                       box-shadow: 0 6px 20px rgba(107, 114, 128, 0.3) !important;
                       cursor: pointer !important;
                       position: relative !important;
                       overflow: hidden !important;">
                Cancelar
            </button>

            <button onclick="confirmarAccion()"
                class="btn-confirm px-6 py-3 rounded-2xl font-semibold transition-all duration-300 hover:scale-105 focus:outline-none"
                style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%) !important;
                       color: #92400e !important;
                       border: none !important;
                       font-weight: 600 !important;
                       padding: 0.75rem 1.5rem !important;
                       border-radius: 1rem !important;
                       box-shadow: 0 8px 25px rgba(251, 191, 36, 0.4) !important;
                       cursor: pointer !important;
                       position: relative !important;
                       overflow: hidden !important;">
                Confirmar
            </button>
        </div>

    </div>
</div>

<!-- Estilos CSS Adicionales -->
<style>
/* Efectos adicionales para el modal de confirmación */
#modalConfirmacion {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

#modalConfirmacion .btn-cancel:hover {
    background: linear-gradient(135deg, #d1d5db 0%, #9ca3af 100%) !important;
    transform: translateY(-2px) scale(1.05) !important;
    box-shadow: 0 10px 30px rgba(107, 114, 128, 0.4) !important;
}

#modalConfirmacion .btn-confirm:hover {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    transform: translateY(-2px) scale(1.05) !important;
    box-shadow: 0 12px 35px rgba(251, 191, 36, 0.5) !important;
}

#modalConfirmacion .btn-cancel:active,
#modalConfirmacion .btn-confirm:active {
    transform: translateY(0) scale(1.02) !important;
    transition: all 0.1s ease !important;
}

/* Animación de entrada suave */
#modalConfirmacion:not(.opacity-0) {
    animation: modalFadeInConfirm 0.3s ease-out forwards;
}

@keyframes modalFadeInConfirm {
    from {
        opacity: 0;
        backdrop-filter: blur(0px);
        -webkit-backdrop-filter: blur(0px);
    }

    to {
        opacity: 1;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
    }
}

/* Animación del contenido */
#modalConfirmacion .modal-content.scale-100 {
    animation: contentScaleInConfirm 0.3s ease-out forwards;
}

@keyframes contentScaleInConfirm {
    from {
        transform: scale(0.9);
        opacity: 0.8;
    }

    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* Efectos de brillo en los botones */
#modalConfirmacion .btn-cancel::before,
#modalConfirmacion .btn-confirm::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: all 0.5s ease;
}

#modalConfirmacion .btn-cancel:hover::before,
#modalConfirmacion .btn-confirm:hover::before {
    left: 100%;
}

/* Compatibilidad cross-browser */
@supports not (backdrop-filter: blur(12px)) {
    #modalConfirmacion {
        background-color: rgba(0, 0, 0, 0.8) !important;
    }

    #modalConfirmacion .modal-content {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 2px solid rgba(255, 255, 255, 0.7) !important;
    }
}

/* Responsive design */
@media (max-width: 480px) {
    #modalConfirmacion .modal-content {
        margin: 1rem !important;
        padding: 1.5rem !important;
        max-width: calc(100vw - 2rem) !important;
    }

    #modalConfirmacion .modal-title {
        font-size: 1.75rem !important;
    }

    #modalConfirmacion .btn-cancel,
    #modalConfirmacion .btn-confirm {
        width: 100% !important;
        padding: 1rem 1.5rem !important;
        margin-bottom: 0.5rem !important;
    }

    #modalConfirmacion .flex {
        flex-direction: column !important;
    }
}
</style>

<!-- JavaScript Mejorado -->
<script>
let accionConfirmacion = null;

/**
 * Mostrar modal de confirmación con animaciones optimizadas
 * @param {string} titulo - Título del modal
 * @param {string} mensaje - Mensaje del modal
 * @param {function} callback - Función a ejecutar al confirmar
 */
function mostrarModalConfirmacion(titulo = '¿Estás seguro?', mensaje = 'Esta acción no se puede deshacer.', callback =
    null) {
    console.log('⚠️ Mostrando modal de confirmación:', titulo);

    try {
        // Actualizar contenido
        const tituloElement = document.getElementById('tituloConfirmacion');
        const mensajeElement = document.getElementById('mensajeConfirmacion');

        if (tituloElement) tituloElement.textContent = titulo;
        if (mensajeElement) mensajeElement.textContent = mensaje;

        accionConfirmacion = callback;

        const modal = document.getElementById('modalConfirmacion');
        const content = modal?.querySelector('.modal-content');

        if (!modal || !content) {
            console.error('❌ Modal de confirmación no encontrado');
            if (confirm(titulo + '\n' + mensaje)) {
                if (typeof callback === 'function') callback();
            }
            return;
        }

        // Aplicar estilos forzados adicionales
        modal.style.display = 'flex';
        modal.style.backgroundColor = 'rgba(0, 0, 0, 0.65)';
        modal.style.backdropFilter = 'blur(12px)';
        modal.style.webkitBackdropFilter = 'blur(12px)';
        modal.style.zIndex = '1070';

        content.style.background = 'rgba(255, 255, 255, 0.98)';
        content.style.backdropFilter = 'blur(30px)';
        content.style.webkitBackdropFilter = 'blur(30px)';
        content.style.border = '2px solid rgba(255, 255, 255, 0.5)';
        content.style.boxShadow = '0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 8px 32px 0 rgba(31, 38, 135, 0.6)';

        // Mostrar modal con animación
        modal.classList.remove('opacity-0', 'invisible');

        // Prevenir scroll del body
        document.body.style.overflow = 'hidden';

        setTimeout(() => {
            content.classList.remove('scale-90');
            content.classList.add('scale-100');
        }, 50);

        console.log('✅ Modal de confirmación mostrado correctamente');

    } catch (error) {
        console.error('❌ Error al mostrar modal de confirmación:', error);
        if (confirm(titulo + '\n' + mensaje)) {
            if (typeof callback === 'function') callback();
        }
    }
}

/**
 * Cerrar modal de confirmación con animaciones
 */
function cerrarModalConfirmacion() {
    console.log('🔄 Cerrando modal de confirmación');

    try {
        const modal = document.getElementById('modalConfirmacion');
        const content = modal?.querySelector('.modal-content');

        if (!modal || !content) {
            console.error('❌ Modal de confirmación no encontrado para cerrar');
            return;
        }

        // Animación de salida
        content.classList.remove('scale-100');
        content.classList.add('scale-90');

        setTimeout(() => {
            modal.classList.add('opacity-0', 'invisible');
            modal.style.display = 'none';

            // Restaurar scroll del body
            document.body.style.overflow = '';

            // Limpiar acción
            accionConfirmacion = null;

            console.log('✅ Modal de confirmación cerrado correctamente');
        }, 250);

    } catch (error) {
        console.error('❌ Error al cerrar modal de confirmación:', error);
    }
}

/**
 * Confirmar acción y cerrar modal
 */
function confirmarAccion() {
    console.log('✅ Confirmando acción');

    try {
        if (typeof accionConfirmacion === 'function') {
            accionConfirmacion();
        }
        cerrarModalConfirmacion();
    } catch (error) {
        console.error('❌ Error al confirmar acción:', error);
        cerrarModalConfirmacion();
    }
}

// Event listeners cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalConfirmacion');

    if (modal) {
        // Cerrar modal al hacer clic fuera
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalConfirmacion();
            }
        });

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('opacity-0')) {
                cerrarModalConfirmacion();
            }
        });

        console.log('✅ Event listeners del modal de confirmación configurados');
    }
});

// Función de prueba para debugging
window.probarModalConfirmacion = function() {
    mostrarModalConfirmacion(
        "Prueba de Confirmación",
        "¿Deseas continuar con esta acción de prueba?",
        function() {
            alert("¡Acción confirmada!");
        }
    );
};

console.log('⚠️ Modal de confirmación COPFLOW v2.1 cargado - by Diomedez98');
</script>