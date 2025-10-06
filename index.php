<?php

/**
 * ===============================================================
 * 📄 index.php
 * ---------------------------------------------------------------
 * Página principal de login.
 * Muestra el formulario de inicio de sesión.
 * Se comunica con:
 *   - activos/js/index.js → Manejo del envío de formulario.
 *   - sesiones/validar_login.php → Validación de credenciales.
 *   - dashboard/dashboard.php → Redirección tras login exitoso.
 * ===============================================================
 */
session_start();

// Si ya está logueado redirige al dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COPFLOW - Iniciar Sesión</title>
    <link rel="stylesheet" href="activos/css/output.css">
    <link rel="stylesheet" href="activos/css/index_login.css">
    <script src="activos/js/ubicacion_paginas.js"></script>
    <script src="activos/js/index.js" defer></script>
</head>

<body class="gradient-bg min-h-screen flex items-center justify-center p-4 relative overflow-hidden floating-elements">

    <!-- Additional Floating Orbs -->
    <div class="orb-1"></div>
    <div class="orb-2"></div>
    <div class="orb-3"></div>

    <!-- Login Container -->
    <div class="login-animation login-container w-full max-w-md">
        <div class="glass-effect rounded-3xl shadow-2xl p-8 backdrop-blur-lg">

            <!-- Enhanced Header -->
            <div class="text-center mb-10">
                <div
                    class="w-20 h-20 bg-gradient-to-br from-white/25 to-white/10 rounded-2xl flex items-center justify-center mx-auto mb-6 backdrop-blur-sm border border-white/20 icon-glow">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                <h1 class="brand-title text-4xl mb-3 tracking-tight">COPFLOW</h1>
                <p class="text-white/70 text-sm leading-relaxed">Ingresa tus credenciales para acceder al sistema</p>
                <div class="w-16 h-0.5 bg-gradient-to-r from-transparent via-white/30 to-transparent mx-auto mt-4">
                </div>
            </div>

            <!-- Enhanced Form -->
            <form id="loginForm" class="space-y-8">

                <!-- Email Field -->
                <div class="space-y-3">
                    <label for="email" class="form-label block text-sm font-medium text-white/90">Usuario /
                        Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-white/50 group-hover:text-white/70 transition-colors duration-300"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input type="text" id="email" name="email" required
                            class="input-focus w-full pl-12 pr-4 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-white/40 focus:outline-none transition-all duration-300"
                            placeholder="Ingresa tu email">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="space-y-3">
                    <label for="password" class="form-label block text-sm font-medium text-white/90">Contraseña</label>
                    <div class="relative group input-group">
                        <input type="password" id="password" name="password" required
                            class="input-focus w-full pl-12 pr-14 py-4 bg-white/10 border border-white/20 rounded-2xl text-white placeholder-white/40 focus:outline-none transition-all duration-300"
                            placeholder="Ingresa tu contraseña">
                        <div
                            class="icon-container absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-white/60 transition-colors duration-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <button type="button" onclick="togglePassword()" id="toggleBtn"
                            class="absolute inset-y-0 right-0 pr-4 flex items-center text-white/50 hover:text-white/80 transition-all duration-300 hover:scale-110 focus:outline-none">
                            <svg id="eye-open" class="h-5 w-5 transition-all duration-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg id="eye-closed" class="h-5 w-5 hidden transition-all duration-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Forgot Password Link -->
                <div class="flex justify-end">
                    <a href="#"
                        class="text-sm text-white/70 hover:text-white transition-colors duration-300 font-medium">
                        ¿Olvidaste tu contraseña?
                    </a>
                </div>

                <!-- Enhanced Submit Button -->
                <button type="submit" id="submitBtn"
                    class="btn-primary w-full font-semibold py-4 px-6 rounded-2xl focus:outline-none focus:ring-4 focus:ring-white/30 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98] relative overflow-hidden">
                    <span id="btnText">Iniciar Sesión</span>
                </button>

                <!-- Enhanced Message Area -->
                <div id="mensaje" class="text-center text-sm rounded-xl p-3 hidden transition-all duration-300"></div>

            </form>

            <!-- Enhanced Footer -->
            <div class="mt-10 text-center space-y-4">
                <div class="w-full h-px bg-gradient-to-r from-transparent via-white/20 to-transparent"></div>
                <p class="text-white/60 text-sm">
                    ¿Necesitas ayuda?
                    <a href="#"
                        class="text-white hover:text-white/80 font-medium transition-colors duration-300 ml-1 underline decoration-white/30 hover:decoration-white/60">
                        Contacta soporte
                    </a>
                </p>
                <div class="flex items-center justify-center space-x-4 text-white/50">
                    <span class="text-xs">Desarrollado por <strong class="text-white/70">JCODE.SAS</strong></span>
                    <div class="w-1 h-1 bg-white/40 rounded-full"></div>
                    <span class="text-xs">© 2025</span>
                </div>
            </div>

        </div>
    </div>

    <script>
    // Enhanced Password Toggle
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');
        const toggleBtn = document.getElementById('toggleBtn');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
            toggleBtn.classList.add('text-white/80');
        } else {
            passwordInput.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
            toggleBtn.classList.remove('text-white/80');
        }
    }

    // Enhanced Form Submit with Loading State
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const mensaje = document.getElementById('mensaje');

        // Add loading state
        submitBtn.classList.add('btn-loading');
        btnText.style.opacity = '0';
        submitBtn.disabled = true;

        // Hide any previous messages
        mensaje.classList.add('hidden');

        // Simulate processing (remove this in production)
        setTimeout(() => {
            submitBtn.classList.remove('btn-loading');
            btnText.style.opacity = '1';
            submitBtn.disabled = false;
        }, 2000);
    });

    // Enhanced Input Focus Effects
    document.querySelectorAll('.input-focus').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
            this.parentElement.style.transition = 'transform 0.3s ease';
        });

        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });

    // Message Display Function
    function showMessage(text, type = 'error') {
        const mensaje = document.getElementById('mensaje');
        mensaje.textContent = text;
        mensaje.className = `text-center text-sm rounded-xl p-3 transition-all duration-300 message-${type}`;
        mensaje.classList.remove('hidden');

        // Auto hide after 5 seconds
        setTimeout(() => {
            mensaje.classList.add('hidden');
        }, 5000);
    }

    // Add subtle parallax effect to floating elements
    document.addEventListener('mousemove', (e) => {
        const orbs = document.querySelectorAll('.orb-1, .orb-2, .orb-3');
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;

        orbs.forEach((orb, index) => {
            const speed = (index + 1) * 0.5;
            const xPos = (x - 0.5) * speed;
            const yPos = (y - 0.5) * speed;
            orb.style.transform = `translate(${xPos}px, ${yPos}px)`;
        });
    });
    </script>
    <?php include __DIR__ . '/modales/notificacion_exito.php'; ?>
    <?php include __DIR__ . '/modales/notificacion_error.php'; ?>
</body>

</html>