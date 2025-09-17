<?php
// Asegura que solo usuarios autenticados pueden acceder (ajusta según tu lógica de login)
require_once __DIR__ . '/../../../configuracion/bd.php';
require_once __DIR__ . '/../../../logs/logger.php';
// Puedes agregar aquí verificaciones de rol, permisos, etc.
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>WhatsApp Web - Módulo Operador</title>
    <link rel="stylesheet" href="/public_html/dashboard/paginas/whatsapp/comportamientos/whatsapp.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        background: #f0f0f0;
    }

    .wa-container {
        display: flex;
        height: 94vh;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 1px 8px #bbb;
    }

    .wa-chats {
        width: 320px;
        border-right: 1px solid #eee;
        overflow-y: auto;
        background: #f8fafb;
    }

    .wa-chatlist {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .wa-chat {
        padding: 15px;
        border-bottom: 1px solid #eee;
        cursor: pointer;
    }

    .wa-chat.selected {
        background: #e1f5fe;
    }

    .wa-main {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .wa-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f6f7f9;
    }

    .wa-inputbox {
        display: flex;
        padding: 15px;
        border-top: 1px solid #eee;
        background: #fff;
    }

    .wa-inputbox input {
        flex: 1;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 30px;
    }

    .wa-inputbox button {
        margin-left: 10px;
        border: none;
        background: #25d366;
        color: #fff;
        padding: 10px 20px;
        border-radius: 30px;
    }

    .wa-bubble {
        margin-bottom: 10px;
        max-width: 60%;
        padding: 12px 18px;
        border-radius: 17px;
    }

    .wa-bubble.cliente {
        background: #fff;
        align-self: flex-start;
        border-bottom-left-radius: 0;
    }

    .wa-bubble.operador {
        background: #dcf8c6;
        align-self: flex-end;
        border-bottom-right-radius: 0;
    }

    .wa-bubble.mia {
        background: #ffe0b2;
        align-self: flex-start;
        border-bottom-left-radius: 0;
    }

    .wa-msg-meta {
        font-size: 11px;
        color: #888;
        margin-top: 5px;
    }

    @media (max-width: 900px) {
        .wa-container {
            flex-direction: column;
            height: auto;
            min-height: 100vh;
            border-radius: 0;
        }

        .wa-chats {
            width: 100%;
            border-right: none;
            border-bottom: 1px solid #eee;
            min-height: 160px;
        }

        .wa-main {
            min-height: 400px;
        }
    }
    </style>
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
    <script src="/public_html/dashboard/paginas/whatsapp/comportamientos/whatsapp.js"></script>
</body>

</html>