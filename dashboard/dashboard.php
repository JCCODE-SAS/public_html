<?php
session_start();

// Verificación de autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Framework CSS Principal -->
    <link rel="stylesheet" href="../activos/css/output.css" />
    <link rel="stylesheet" href="../activos/css/dashboard.css" />
    <!-- RemixIcon - Iconografía -->
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet" />
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap"
        rel="stylesheet" />
    <!-- Rutas centralizadas: debe ir primero -->
    <script src="../activos/js/ubicacion_paginas.js"></script>
    <!-- WhatsApp JS GLOBAL para integración SPA (carga solo una vez) -->
    <script src="/public_html/dashboard/paginas/whatsapp/comportamientos/whatsapp.js"></script>
</head>

<body class="font-inter antialiased">
    <div class="app-layout">
        <!-- SIDEBAR NAVIGATION -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">C</div>
                    <div class="logo-text">COPFLOW</div>
                </div>
            </div>
            <div class="user-section">
                <div class="user-card">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_name'], 0, 1)); ?>
                    </div>
                    <div class="user-name">
                        <?php echo "Bienvenido, " . htmlspecialchars($_SESSION['user_name']); ?>
                    </div>
                </div>
            </div>
            <nav class="nav-section">
                <div class="nav-group">
                    <h3 class="nav-group-title">Gestión</h3>
                    <ul class="nav-menu">
                        <?php $role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'usuario'; ?>
                        <?php if ($role === 'admin'): ?>
                        <li class="nav-item">
                            <button class="nav-link" data-section="usuariosSection">
                                <i class="nav-icon ri-user-settings-line"></i>
                                <span class="nav-text">Usuarios</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-section="operariosSection">
                                <i class="nav-icon ri-team-line"></i>
                                <span class="nav-text">Operarios</span>
                            </button>
                        </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <button class="nav-link" data-section="whatsappSection">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg"
                                    alt="whatsapp" class="nav-icon" width="20" height="20">
                                <span class="nav-text">whatsapp</span>
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="nav-group">
                    <h3 class="nav-group-title">Configuración</h3>
                    <ul class="nav-menu">
                        <li class="nav-item">
                            <button class="nav-link" data-section="perfilSection">
                                <i class="nav-icon ri-user-line"></i>
                                <span class="nav-text">Mi Perfil</span>
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-section="configuracionSection">
                                <i class="nav-icon ri-settings-line"></i>
                                <span class="nav-text">Configuración</span>
                            </button>
                        </li>
                    </ul>
                </div>
            </nav>
            <div class="sidebar-footer">
                <button class="logout-btn" id="logoutBtn">
                    <i class="ri-logout-box-line"></i>
                    <span class="logout-btn-text">Cerrar sesión</span>
                </button>
            </div>
        </aside>
        <div class="main-wrapper">
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="ri-menu-line"></i>
            </button>
            <header class="main-header">
                <div class="header-content">
                    <h1 class="page-title">Dashboard de Gestión COPFLOW</h1>
                    <div class="header-info">
                        <span class="version-badge"></span>
                        <span class="user-role"><?= ucfirst($_SESSION['user_role'] ?? 'usuario') ?></span>
                    </div>
                </div>
            </header>
            <main class="main-content">
                <?php $role = $_SESSION['user_role'] ?? $_SESSION['role'] ?? 'usuario'; ?>
                <?php if ($role === 'admin'): ?>
                <section id="usuariosSection" class="content-section"></section>
                <section id="operariosSection" class="content-section"></section>
                <?php endif; ?>
                <section id="whatsappSection" class="content-section active"></section>
                <section id="perfilSection" class="content-section"></section>
                <section id="configuracionSection" class="content-section"></section>
            </main>
        </div>
        <div class="mobile-overlay" id="mobileOverlay"></div>
    </div>

    <!-- MODALES BASE DEL SISTEMA -->
    <?php
    $modales_base = [
        'notificacion_confirmacion.php',
        'notificacion_error.php',
        'notificacion_exito.php'
    ];
    foreach ($modales_base as $modal) {
        $p = __DIR__ . '/../modales/' . $modal;
        if (file_exists($p)) include_once $p;
    }
    ?>

    <!-- MODALES ESPECÍFICOS DE USUARIOS -->
    <?php
    $modales_usuarios = [
        'ver_usuario_modal.php'   => 'Visualización usuarios',
        'crear_usuario_modal.php' => 'Creación usuarios',
        'editar_usuario_modal.php' => 'Edición usuarios'
    ];
    foreach ($modales_usuarios as $archivo => $desc) {
        $p = __DIR__ . '/paginas/usuarios/modales/' . $archivo;
        if (file_exists($p)) {
            echo "<!-- $desc -->\n";
            include_once $p;
            echo "\n";
        }
    }
    ?>

    <!-- MODALES ESPECÍFICOS DE OPERARIOS -->
    <?php
    // AJUSTE: Incluidos igual que usuarios, así SIEMPRE están en el DOM global.
    $modales_operarios = [
        'ver_operarios_modal.php'   => 'Visualización operarios',
        'crear_operarios_modal.php' => 'Creación operarios',
        'editar_operarios_modal.php' => 'Edición operarios'
    ];
    foreach ($modales_operarios as $archivo => $desc) {
        $p = __DIR__ . '/paginas/operarios/modales/' . $archivo;
        if (file_exists($p)) {
            echo "<!-- $desc -->\n";
            include_once $p;
            echo "\n";
        } else {
            echo "<!-- Modal no encontrado: $archivo ($desc) -->\n";
        }
    }
    ?>

    <!-- SCRIPT PRINCIPAL DEL DASHBOARD -->
    <script src="../activos/js/dashboard.js"></script>

    <!-- SCRIPTS DE GESTIÓN DE USUARIOS -->
    <?php
    $scripts = [
        'usuarios.js',
        'ver_usuario.js',
        'crear_usuario.js',
        'editar_usuario.js'
    ];
    $base = '/public_html/dashboard/paginas/usuarios/comportamientos/';
    foreach ($scripts as $fichero) {
        $ruta = $_SERVER['DOCUMENT_ROOT'] . $base . $fichero;
        if (file_exists($ruta)) {
            echo "    <script src=\"$base$fichero\"></script>\n";
        }
    }
    ?>

    <!-- SCRIPTS DE GESTIÓN DE OPERARIOS -->
    <?php
    $scripts_operarios = [
        'operarios.js',
        'ver_operarios.js',
        'crear_operarios.js',
        'editar_operarios.js'
    ];
    $base_operarios = '/public_html/dashboard/paginas/operarios/comportamientos/';
    foreach ($scripts_operarios as $fichero) {
        $ruta = $_SERVER['DOCUMENT_ROOT'] . $base_operarios . $fichero;
        if (file_exists($ruta)) {
            echo "    <script src=\"$base_operarios$fichero\"></script>\n";
        }
    }
    ?>

    <script>
    // UTILIDAD: Notificaciones temporales
    window.mostrarNotificacionTemporal = function(mensaje, tipo = 'info', duracion = 3000) {
        const colores = {
            'success': 'bg-green-500',
            'error': 'bg-red-500',
            'warning': 'bg-yellow-500',
            'info': 'bg-blue-500'
        };

        const notificacion = document.createElement('div');
        notificacion.className =
            `fixed top-4 right-4 ${colores[tipo]} text-white px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full`;
        notificacion.textContent = mensaje;

        document.body.appendChild(notificacion);

        setTimeout(() => {
            notificacion.classList.remove('translate-x-full');
        }, 100);

        setTimeout(() => {
            notificacion.classList.add('translate-x-full');
            setTimeout(() => {
                if (notificacion.parentNode) {
                    notificacion.parentNode.removeChild(notificacion);
                }
            }, 300);
        }, duracion);
    };


    // =========== RECARGA DINÁMICA DE SECCIONES PRINCIPALES ===========

    window.recargarSeccionUsuarios = function() {
        const usuariosSection = document.getElementById('usuariosSection');
        if (!usuariosSection) {
            console.error("❌ Sección de usuarios no encontrada");
            return;
        }
        usuariosSection.innerHTML = `<div class="flex items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
            <span class="ml-2 text-gray-600">Actualizando lista de usuarios...</span>
        </div>`;
        fetch(window.rutas.usuarios)
            .then(response => response.text())
            .then(html => {
                usuariosSection.innerHTML = html;
                if (typeof window.inicializarUsuarios === 'function') {
                    setTimeout(() => {
                        window.inicializarUsuarios();
                    }, 100);
                }
                document.dispatchEvent(new CustomEvent('tablaUsuariosActualizada', {
                    detail: {
                        method: 'seccionCompleta',
                        timestamp: new Date()
                    }
                }));
            })
            .catch(error => {
                usuariosSection.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <i class="ri-error-warning-line text-red-500 text-2xl mb-2"></i>
                    <p class="text-red-700 mb-2">Error al cargar la lista de usuarios</p>
                    <button onclick="window.location.reload()" 
                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
                        Recargar página
                    </button>
                </div>`;
            });
    };

    window.recargarSeccionOperarios = function() {
        const operariosSection = document.getElementById('operariosSection');
        if (!operariosSection) {
            console.error("❌ Sección de operarios no encontrada");
            return;
        }
        operariosSection.innerHTML = `<div class="flex items-center justify-center p-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
            <span class="ml-2 text-gray-600">Actualizando lista de operarios...</span>
        </div>`;
        fetch(window.rutas.operarios)
            .then(response => response.text())
            .then(html => {
                operariosSection.innerHTML = html;
                if (typeof window.inicializarOperarios === 'function') {
                    setTimeout(() => {
                        window.inicializarOperarios();
                    }, 100);
                }
                document.dispatchEvent(new CustomEvent('tablaOperariosActualizada', {
                    detail: {
                        method: 'seccionCompleta',
                        timestamp: new Date()
                    }
                }));
            })
            .catch(error => {
                operariosSection.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                    <i class="ri-error-warning-line text-red-500 text-2xl mb-2"></i>
                    <p class="text-red-700 mb-2">Error al cargar la lista de operarios</p>
                    <button onclick="window.location.reload()" 
                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
                        Recargar página
                    </button>
                </div>`;
            });
    };

    // =========== MODULO WHATSAPP: CARGA DINÁMICA Y JS ===========

    window.recargarSeccionwhatsapp = function() {
        const whatsappSection = document.getElementById('whatsappSection');
        if (!whatsappSection) {
            console.error("❌ Sección de whatsapp no encontrada");
            return;
        }
        whatsappSection.innerHTML = `<div class="flex items-center justify-center p-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
        <span class="ml-2 text-gray-600">Cargando whatsapp Web...</span>
    </div>`;

        if (!window.rutas || !window.rutas.whatsapp) {
            console.error("❌ La ruta para WhatsApp no está definida. Revisa ubicacion_paginas.js");
            whatsappSection.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <i class="ri-error-warning-line text-red-500 text-2xl mb-2"></i>
            <p class="text-red-700 mb-2">Error de configuración: ruta de WhatsApp no encontrada.</p>
            <button onclick="window.location.reload()" 
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
                Recargar página
            </button>
        </div>`;
            return;
        }

        fetch(window.rutas.whatsapp)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
            .then(html => {
                whatsappSection.innerHTML = html;

                // Limpia intervalos antes de iniciar el módulo (para evitar duplicados)
                if (typeof window.limpiarWhatsappIntervals === 'function') {
                    window.limpiarWhatsappIntervals();
                }

                // Inicializa WhatsApp al cargar el HTML
                setTimeout(() => {
                    if (typeof window.inicializarWhatsapp === 'function') {
                        window.inicializarWhatsapp();
                    }
                }, 100);
            })
            .catch(error => {
                whatsappSection.innerHTML = `<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <i class="ri-error-warning-line text-red-500 text-2xl mb-2"></i>
            <p class="text-red-700 mb-2">Error al cargar la sección whatsapp</p>
            <p class="text-red-500">${error.message}</p>
            <button onclick="window.location.reload()" 
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors">
                Recargar página
            </button>
        </div>`;
            });
    };

    // =========== CAMBIO DE SECCIÓN EN DASHBOARD ===========

    document.addEventListener('DOMContentLoaded', () => {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(btn => {
            btn.addEventListener('click', function() {
                const section = btn.getAttribute('data-section');
                // Si vas a otra sección que NO es whatsapp, limpia intervalos
                if (section !== 'whatsappSection' && typeof window.limpiarWhatsappIntervals ===
                    'function') {
                    window.limpiarWhatsappIntervals();
                }

                // Oculta todas las secciones
                document.querySelectorAll('.content-section').forEach(sec => sec.classList
                    .remove('active'));
                // Muestra solo la sección seleccionada
                document.getElementById(section).classList.add('active');

                // Carga contenido dinámico según la sección
                if (section === 'usuariosSection') window.recargarSeccionUsuarios();
                if (section === 'operariosSection') window.recargarSeccionOperarios();
                if (section === 'whatsappSection') window.recargarSeccionwhatsapp();
                if (section === 'perfilSection') {
                    // Si tienes función para perfil, agrégala aquí
                }
                if (section === 'configuracionSection') {
                    // Si tienes función para configuración, agrégala aquí
                }
            });
        });

        // Carga la sección inicial activa
        setTimeout(() => {
            const activeLink = document.querySelector('.nav-link.active') || navLinks[0];
            if (activeLink) activeLink.click();
        }, 100);
    });
    </script>
</body>

</html>
<?php
if (function_exists('writeLog')) {
    writeLog("dashboard.php", "Dashboard cargado exitosamente para usuario: " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ")");
}
?>