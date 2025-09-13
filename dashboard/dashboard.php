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
                        <li class="nav-item">
                            <button class="nav-link active" data-section="usuariosSection">
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
                        <li class="nav-item">
                            <button class="nav-link" data-section="reportesSection">
                                <i class="nav-icon ri-bar-chart-line"></i>
                                <span class="nav-text">Reportes</span>
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
                <section id="usuariosSection" class="content-section active"></section>
                <section id="operariosSection" class="content-section"></section>
                <section id="reportesSection" class="content-section"></section>
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
    $modales_operarios = [
        'ver_operario_modal.php'   => 'Visualización operarios',
        'crear_operario_modal.php' => 'Creación operarios',
        'editar_operario_modal.php' => 'Edición operarios'
    ];
    foreach ($modales_operarios as $archivo => $desc) {
        $p = __DIR__ . '/paginas/operarios/modales/' . $archivo;
        if (file_exists($p)) {
            echo "<!-- $desc -->\n";
            include_once $p;
            echo "\n";
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
    // INICIALIZACIÓN Y VERIFICACIÓN DEL DASHBOARD
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Inicializando Dashboard COPFLOW v2.1...');
        console.log('👤 Usuario:', '<?= htmlspecialchars($_SESSION['user_name']) ?>');
        console.log('🔐 Rol:', '<?= htmlspecialchars($_SESSION['user_role'] ?? 'usuario') ?>');

        const funcionesCriticas = [
            'mostrarModalNuevoUsuario',
            'cerrarModalCrearUsuario',
            'crearUsuario',
            'limpiarFormularioCrear',
            'recargarTablaUsuarios',
            'actualizarEstadisticasUsuarios',
            'mostrarModalNuevoOperario',
            'cerrarModalCrearOperario',
            'crearOperario',
            'limpiarFormularioCrearOperario',
            'recargarTablaOperarios',
            'actualizarEstadisticasOperarios'
        ];

        const funcionesFaltantes = [];
        const funcionesDisponibles = [];

        funcionesCriticas.forEach(funcion => {
            if (typeof window[funcion] === 'function') {
                funcionesDisponibles.push(funcion);
            } else {
                funcionesFaltantes.push(funcion);
            }
        });

        console.log('📋 Estado de funciones del sistema:');
        funcionesDisponibles.forEach(funcion => {
            console.log(`  ✅ ${funcion}: Disponible`);
        });

        if (funcionesFaltantes.length > 0) {
            console.warn('⚠️ Funciones faltantes:');
            funcionesFaltantes.forEach(funcion => {
                console.warn(`  ❌ ${funcion}: No disponible`);
            });
        }

        document.addEventListener('tablaUsuariosActualizada', function(e) {
            console.log('🎉 Tabla de usuarios actualizada exitosamente:', e.detail);
            if (typeof mostrarNotificacionTemporal === 'function') {
                mostrarNotificacionTemporal('Lista de usuarios actualizada', 'success', 2000);
            }
        });

        document.addEventListener('tablaOperariosActualizada', function(e) {
            console.log('🎉 Tabla de operarios actualizada exitosamente:', e.detail);
            if (typeof mostrarNotificacionTemporal === 'function') {
                mostrarNotificacionTemporal('Lista de operarios actualizada', 'success', 2000);
            }
        });

        window.addEventListener('error', function(e) {
            console.error('❌ Error global capturado:', e.error);
            console.error('📍 Archivo:', e.filename);
            console.error('📍 Línea:', e.lineno);
            console.error('📍 Columna:', e.colno);
        });

        setTimeout(() => {
            console.log('✅ Dashboard COPFLOW v2.1 inicializado correctamente');
            console.log('🔧 Funciones disponibles:', funcionesDisponibles.length + '/' +
                funcionesCriticas.length);
            document.dispatchEvent(new CustomEvent('dashboardReady', {
                detail: {
                    version: '2.1',
                    user: '<?= htmlspecialchars($_SESSION['user_name']) ?>',
                    role: '<?= htmlspecialchars($_SESSION['user_role'] ?? 'usuario') ?>',
                    timestamp: new Date(),
                    funcionesDisponibles: funcionesDisponibles,
                    funcionesFaltantes: funcionesFaltantes
                }
            }));
        }, 100);
    });

    // FUNCIÓN DE RESPALDO PARA RECARGAR SECCIÓN USUARIOS
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
        fetch('/public_html/dashboard/paginas/usuarios/usuarios.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
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

    // FUNCIÓN DE RESPALDO PARA RECARGAR SECCIÓN OPERARIOS
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
        fetch('/public_html/dashboard/paginas/operarios/operarios.php')
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.text();
            })
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
    </script>
</body>

</html>
<?php
if (function_exists('writeLog')) {
    writeLog("dashboard.php", "Dashboard cargado exitosamente para usuario: " . $_SESSION['user_name'] . " (ID: " . $_SESSION['user_id'] . ")");
}
?>