<?php
$roles_permitidos = ['admin', 'operador'];
include_once __DIR__ . '/../../../sesiones/control_acceso.php';
require_once __DIR__ . '/../../../configuracion/bd.php';
try {
    require_once __DIR__ . '/../../../logs/logger.php';
} catch (Throwable $e) {
    error_log("No se pudo cargar logger.php: " . $e->getMessage());
}

$user_name = $_SESSION['user_name'];
$user_role = $_SESSION['role'] ?? 'usuario';
$user_id   = $_SESSION['user_id'];
if (function_exists('writeLog')) writeLog("whatsapp.php", "Módulo WhatsApp cargado por usuario: $user_name (rol=$user_role, id=$user_id)");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Web - Módulo Operador</title>
    <link rel="stylesheet" href="/dashboard/paginas/whatsapp/comportamientos/whatsapp.css">
</head>

<body>
    <div class="wa-container">
        <aside class="wa-chats">
            <h2>Chats activos</h2>
            <ul class="wa-chatlist" id="wa-chatlist">
                <!-- Los chats se cargarán aquí vía JS -->
            </ul>
        </aside>
        <main class="wa-main">
         <div id="wa-header" class="wa-header" style="display:none;">
                <div class="wa-header-avatar"></div>
                 <div class="wa-header-info">
                     <div class="wa-header-name"></div>
                            <div class="wa-header-status"></div>
                            </div>
                        </div>


            
            <section class="wa-messages" id="wa-messages">
                <div style="color:#8696a0;text-align:center;padding:60px 20px;">
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
    <script id="wa-js" src="/dashboard/paginas/whatsapp/comportamientos/whatsapp.js"></script>
</body>

</html>