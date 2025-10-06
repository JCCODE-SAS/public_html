<?php

/**
 * ===============================================================
 * 📄 notificacion_error.php v2.1
 * ---------------------------------------------------------------
 * Modal de notificación de error reutilizable
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

<!-- Modal de Error con Estilos Forzados -->
<div id="modalError"
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

        <!-- Icono de Error SVG Moderno -->
        <div class="text-center mb-6">
            <div class="w-20 h-20 flex items-center justify-center mx-auto mb-4" style="background: radial-gradient(circle at 60% 40%, #fee2e2 0%, #fecaca 30%, #ef4444 100%); 
                        border-radius: 50%; 
                        border: 3px solid rgba(239, 68, 68, 0.3);
                        filter: drop-shadow(0 0 15px rgba(239, 68, 68, 0.4));
                        box-shadow: 0 8px 20px rgba(239, 68, 68, 0.3);">
                <svg width="56" height="56" viewBox="0 0 56 56" fill="none">
                    <circle cx="28" cy="28" r="26" stroke="rgba(239, 68, 68, 0.3)" stroke-width="4" fill="#fff" />
                    <line x1="18" y1="18" x2="38" y2="38" stroke="#ef4444" stroke-width="4" stroke-linecap="round" />
                    <line x1="38" y1="18" x2="18" y2="38" stroke="#ef4444" stroke-width="4" stroke-linecap="round" />
                </svg>
            </div>

            <h3 class="modal-title" id="tituloError" style="color: #1f2937 !important; 
                       font-size: 2rem !important; 
                       font-weight: 700 !important; 
                       margin-bottom: 0.5em !important;
                       text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1) !important;">
                ¡Error!
            </h3>

            <p class="leading-relaxed" id="mensajeError" style="color: #4b5563 !important; 
                      font-size: 0.95rem !important; 
                      line-height: 1.6 !important;
                      opacity: 0.9 !important;">
                Ha ocurrido un error inesperado.
            </p>
        </div>

        <!-- Botón -->
        <div class="flex justify-center">
            <button onclick="cerrarModalError()"
                class="btn-error px-8 py-3 rounded-2xl font-semibold transition-all duration-300 hover:scale-105 focus:outline-none"
                style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%) !important;
                       color: white !important;
                       border: none !important;
                       font-weight: 600 !important;
                       padding: 0.75rem 2rem !important;
                       border-radius: 1rem !important;
                       box-shadow: 0 8px 25px rgba(239, 68, 68, 0.4) !important;
                       cursor: pointer !important;
                       position: relative !important;
                       overflow: hidden !important;">
                Entendido
            </button>
        </div>

    </div>
</div>

<!-- Estilos CSS Adicionales -->
<style>
/* Efectos adicionales para el modal de error */
#modalError {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

#modalError .btn-error:hover {
    background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
    transform: translateY(-2px) scale(1.05) !important;
    box-shadow: 0 12px 35px rgba(239, 68, 68, 0.5) !important;
}

#modalError .btn-error:active {
    transform: translateY(0) scale(1.02) !important;
    transition: all 0.1s ease !important;
}

/* Animación de entrada suave */
#modalError:not(.opacity-0) {
    animation: modalFadeInError 0.3s ease-out forwards;
}

@keyframes modalFadeInError {
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
#modalError .modal-content.scale-100 {
    animation: contentScaleInError 0.3s ease-out forwards;
}

@keyframes contentScaleInError {
    from {
        transform: scale(0.9);
        opacity: 0.8;
    }

    to {
        transform: scale(1);
        opacity: 1;
    }
}

/* Efecto de brillo en el botón */
#modalError .btn-error::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: all 0.5s ease;
}

#modalError .btn-error:hover::before {
    left: 100%;
}

/* Compatibilidad cross-browser */
@supports not (backdrop-filter: blur(12px)) {
    #modalError {
        background-color: rgba(0, 0, 0, 0.8) !important;
    }

    #modalError .modal-content {
        background: rgba(255, 255, 255, 0.95) !important;
        border: 2px solid rgba(255, 255, 255, 0.7) !important;
    }
}

/* Responsive design */
@media (max-width: 480px) {
    #modalError .modal-content {
        margin: 1rem !important;
        padding: 1.5rem !important;
        max-width: calc(100vw - 2rem) !important;
    }

    #modalError .modal-title {
        font-size: 1.75rem !important;
    }

    #modalError .btn-error {
        width: 100% !important;
        padding: 1rem 2rem !important;
    }
}
</style>

<!-- JavaScript Mejorado -->
<script>
/**
 * Mostrar modal de error con animaciones optimizadas
 * @param {string} titulo - Título del modal
 * @param {string} mensaje - Mensaje del modal
 */
function mostrarModalError(titulo = '¡Error!', mensaje = 'Ha ocurrido un error inesperado.') {
    console.log('❌ Mostrando modal de error:', titulo);

    try {
        // Actualizar contenido
        const tituloElement = document.getElementById('tituloError');
        const mensajeElement = document.getElementById('mensajeError');

        if (tituloElement) tituloElement.textContent = titulo;
        if (mensajeElement) mensajeElement.textContent = mensaje;

        const modal = document.getElementById('modalError');
        const content = modal?.querySelector('.modal-content');

        if (!modal || !content) {
            console.error('❌ Modal de error no encontrado');
            alert(titulo + ': ' + mensaje); // Fallback
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

        console.log('✅ Modal de error mostrado correctamente');

    } catch (error) {
        console.error('❌ Error al mostrar modal de error:', error);
        alert(titulo + ': ' + mensaje); // Fallback
    }
}

/**
 * Cerrar modal de error con animaciones
 */
function cerrarModalError() {
    console.log('🔄 Cerrando modal de error');

    try {
        const modal = document.getElementById('modalError');
        const content = modal?.querySelector('.modal-content');

        if (!modal || !content) {
            console.error('❌ Modal de error no encontrado para cerrar');
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

            console.log('✅ Modal de error cerrado correctamente');
        }, 250);

    } catch (error) {
        console.error('❌ Error al cerrar modal de error:', error);
    }
}

// Event listeners cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalError');

    if (modal) {
        // Cerrar modal al hacer clic fuera
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalError();
            }
        });

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !modal.classList.contains('opacity-0')) {
                cerrarModalError();
            }
        });

        console.log('✅ Event listeners del modal de error configurados');
    }
});

// Función de prueba para debugging
window.probarModalError = function() {
    mostrarModalError("Prueba de Error", "Este es un modal de error de prueba para verificar el diseño.");
};

console.log('❌ Modal de error COPFLOW v2.1 cargado - by Diomedez98');
</script>

<!--
===============================================================
MODAL DE ERROR COPFLOW v2.1 - DOCUMENTACIÓN
===============================================================

✅ CARACTERÍSTICAS IMPLEMENTADAS:
• Estilos inline forzados para máxima visibilidad
• Efecto glassmorphism optimizado y robusto
• Animaciones suaves de entrada y salida
• Responsive design completo
• Compatibilidad cross-browser
• Event listeners mejorados
• Fallbacks para casos de error
• Función de prueba integrada
• Logging detallado para debugging

🎨 MEJORAS VISUALES:
• Fondo más oscuro y visible (65% opacidad)
• Blur más fuerte (30px en contenido, 12px en overlay)
• Bordes más definidos (2px blancos)
• Sombras más pronunciadas
• Gradientes optimizados en botones
• Efectos de hover mejorados
• Icono de error con gradiente rojo

===============================================================
-->