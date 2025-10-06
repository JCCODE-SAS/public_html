<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

// LOG DE ENTRADA
if (function_exists('writeLog')) {
    writeLog("registrar_mensaje_mia.php", "🔵 INICIO - Request recibido");
}

$data = json_decode(file_get_contents('php://input'), true);

// Aceptar tanto 'id_chat' como 'chat_id' para compatibilidad
$id_chat = intval($data['id_chat'] ?? $data['chat_id'] ?? 0);
$texto = trim($data['texto'] ?? '');

if (function_exists('writeLog')) {
    writeLog("registrar_mensaje_mia.php", "📥 Datos: id_chat=$id_chat, texto_length=" . strlen($texto));
}

if (!$id_chat || !$texto) {
    if (function_exists('writeLog')) {
        writeLog("registrar_mensaje_mia.php", "❌ Datos incompletos");
    }
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $sql = "INSERT INTO mensajes (id_chat, remitente, texto) VALUES (?, 'mia', ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("is", $id_chat, $texto);
    $stmt->execute();
    $mensaje_id = $stmt->insert_id;
    $stmt->close();

    if (function_exists('writeLog')) {
        writeLog("registrar_mensaje_mia.php", "✅ Mensaje guardado en BD (id=$mensaje_id)");
    }

    // ✅ ESCRIBIR ARCHIVO DE SEÑAL PARA ACTUALIZACIÓN EN TIEMPO REAL
    $cache_dir = __DIR__ . '/../cache';

    if (function_exists('writeLog')) {
        writeLog("registrar_mensaje_mia.php", "📁 Cache dir: $cache_dir");
    }

    if (!is_dir($cache_dir)) {
        mkdir($cache_dir, 0755, true);
        if (function_exists('writeLog')) {
            writeLog("registrar_mensaje_mia.php", "📂 Directorio cache creado");
        }
    }

    $archivo_senal = $cache_dir . '/senal_actualizacion.json';
    $signal_data = [
        'timestamp' => microtime(true),
        'chat_id' => $id_chat,
        'remitente' => 'mia',
        'action' => 'nuevo_mensaje',
        'mensaje_id' => $mensaje_id
    ];

    $write_result = file_put_contents($archivo_senal, json_encode($signal_data));

    if (function_exists('writeLog')) {
        if ($write_result !== false) {
            writeLog("registrar_mensaje_mia.php", "✅ Señal escrita: $archivo_senal (bytes=$write_result)");
            writeLog("registrar_mensaje_mia.php", "📡 Señal: " . json_encode($signal_data));
        } else {
            writeLog("registrar_mensaje_mia.php", "❌ ERROR escribiendo señal en: $archivo_senal");
        }
    }

    // Verificar permisos del archivo
    if (file_exists($archivo_senal)) {
        $perms = substr(sprintf('%o', fileperms($archivo_senal)), -4);
        if (function_exists('writeLog')) {
            writeLog("registrar_mensaje_mia.php", "🔐 Permisos del archivo: $perms");
        }
    }

    echo json_encode(['ok' => true, 'mensaje_id' => $mensaje_id]);

    if (function_exists('writeLog')) {
        writeLog("registrar_mensaje_mia.php", "🟢 COMPLETADO - Chat $id_chat");
    }
} catch (Exception $e) {
    if (function_exists('writeLog')) {
        writeLog("registrar_mensaje_mia.php", "💥 ERROR: " . $e->getMessage());
        writeLog("registrar_mensaje_mia.php", "Stack trace: " . $e->getTraceAsString());
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar mensaje', 'details' => $e->getMessage()]);
}