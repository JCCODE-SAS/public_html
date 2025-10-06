<?php
// --- Encabezado de respuesta ---
header('Content-Type: application/json');

// --- Inclusión de dependencias ---
// Esto define $conexion según tu archivo bd.php
require_once __DIR__ . '/../../../../configuracion/bd.php';

// 🟢 BLOQUE DE PARCHE CRÍTICO PARA LA API:
// 1. Unificar Variables: Hace que la variable principal $mysqli sea igual a $conexion.
$mysqli = $conexion;

// 2. Interceptar Fatal Error: Si $conexion falló (y activó die() o no),
// debemos interceptar la variable $mysqli antes de usarla en la línea 20.
if ($mysqli->connect_error) {
    // Si falla, devolvemos un JSON de error legible para n8n.
    echo json_encode(['ok' => false, 'error' => 'ERROR FATAL: La conexión a la base de datos falló.']);
    exit; // Detiene el script devolviendo JSON limpio.
}
// ----------------------------------------------------

// --- Lectura del cuerpo JSON ---
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Validación defensiva ---
if (empty($data['chat_id']) || empty($data['texto']) || empty($data['cliente'])) {
    echo json_encode(['ok' => false, 'error' => 'Faltan datos requeridos']);
    exit;
}

// --- Sanitización ---
// Esta sección ahora es segura gracias a la validación anterior.
$id_chat = intval($data['chat_id']);
$texto = $mysqli->real_escape_string($data['texto']); // Línea 20 (Ahora segura)
$numero = $mysqli->real_escape_string($data['cliente']);
$accion = isset($data['accion']) ? $mysqli->real_escape_string($data['accion']) : 'insertarMensaje';

// --- Inserción en tabla mensajes ---
$stmt = $mysqli->prepare("INSERT INTO mensajes (id_chat, remitente, texto) VALUES (?, 'cliente', ?)");
$stmt->bind_param("is", $id_chat, $texto);
if (!$stmt->execute()) {
    echo json_encode(['ok' => false, 'error' => 'Error al insertar mensaje']);
    exit;
}
$stmt->close();

// --- Activación del chat si está pausado o cerrado ---
$mysqli->query("UPDATE chats SET estado = 'activo', pausado = 0 WHERE id_chat = $id_chat");

// --- Señal para frontend ---
$logPath = __DIR__ . '/../cache/';
if (!is_dir($logPath)) mkdir($logPath, 0777, true);
// Escribir archivo con ñ (existente) y también versión sin ñ para evitar problemas en URLs
$archivo_con_ntilde = $logPath . 'señal_actualizacion.json';
$archivo_sin_ntilde = $logPath . 'senal_actualizacion.json';

$payload = json_encode([
    'chat_id' => $id_chat,
    'texto' => $texto,
    'cliente' => $numero,
    'accion' => $accion,
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);

// Intentamos escribir ambos archivos de forma segura
foreach ([$archivo_con_ntilde, $archivo_sin_ntilde] as $archivo) {
    $fp = fopen($archivo, 'w');
    if ($fp) {
        if (flock($fp, LOCK_EX)) {
            fwrite($fp, $payload);
            flock($fp, LOCK_UN);
        } else {
            // Si no se pudo bloquear, intentar escritura directa
            fwrite($fp, $payload);
        }
        fclose($fp);
    }
}

// --- Respuesta final ---
echo json_encode([
    'ok' => true,
    'evento' => 'actualizar_chat',
    'mensaje' => 'Señal de actualización recibida correctamente'
]);
