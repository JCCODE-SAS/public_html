<?php
session_start();
require_once __DIR__ . '/../../../configuracion/bd.php';
try {
    require_once __DIR__ . '/../../../logs/logger.php';
} catch (Throwable $e) {
    error_log("No se pudo cargar logger.php: " . $e->getMessage());
}

// Validación de usuario autenticado
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    if (function_exists('writeLog')) writeLog("whatsapp.php", "❌ Acceso denegado: usuario no autenticado");
    http_response_code(401);
    echo "<div style='padding:40px;color:red;'>Acceso denegado. Debe iniciar sesión.</div>";
    exit;
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['user_role'] ?? 'usuario';
$user_id   = $_SESSION['user_id'];
if (function_exists('writeLog')) writeLog("whatsapp.php", "Módulo WhatsApp cargado por usuario: $user_name (rol=$user_role, id=$user_id)");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>WhatsApp Web - Módulo Operador</title>
    <link rel="stylesheet" href="/public_html/dashboard/paginas/whatsapp/comportamientos/whatsapp.css">
</head>

<body>
    <div class="wa-container">
        <aside class="wa-chats">
            <h2 style="padding:18px 15px 0 15px; margin:0 0 10px 0;">Chats activos</h2>
            <ul class="wa-chatlist" id="wa-chatlist">
                <!-- Los chats se cargarán aquí vía JS -->
            </ul>
        </aside>
        <main class="wa-main">
            <section class="wa-messages" id="wa-messages">
                <div style="color:#bbb;text-align:center;margin-top:60px;">
                    Selecciona un chat para ver los mensajes
                </div>
            </section>
            <form class="wa-inputbox" id="wa-form" style="display:none;">
                <input type="text" id="wa-input" placeholder="Escribe un mensaje..." autocomplete="off" />
                <button type="submit">Enviar</button>
            </form>
        </main>
    </div>
    <script>
    window.WA_USER = {
        name: <?= json_encode($user_name) ?>,
        role: <?= json_encode($user_role) ?>,
        id: <?= json_encode($user_id) ?>
    };
    </script>
    <!-- Solo incluye el JS una vez -->
    <script id="wa-js" src="/public_html/dashboard/paginas/whatsapp/comportamientos/whatsapp.js"></script>
</body>

</html>