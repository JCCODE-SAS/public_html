<?php
// --- Encabezado de respuesta ---
header('Content-Type: application/json');

// --- Inclusión de dependencias ---
require_once __DIR__ . '/../../../../configuracion/bd.php'; // $mysqli ya disponible

// --- Lectura del cuerpo JSON ---
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// --- Validación defensiva ---
if (empty($data['chat_id']) || empty($data['texto']) || empty($data['cliente'])) {
    echo json_encode(['ok' => false, 'error' => 'Faltan datos requeridos']);
    exit;
}

// --- Sanitización ---
$id_chat = intval($data['chat_id']);
$texto = $mysqli->real_escape_string($data['texto']);
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
$archivo = $logPath . 'señal_actualizacion.json';

$fp = fopen($archivo, 'w');
if (flock($fp, LOCK_EX)) {
    fwrite($fp, json_encode([
        'chat_id' => $id_chat,
        'texto' => $texto,
        'cliente' => $numero,
        'accion' => $accion,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT));
    flock($fp, LOCK_UN);
}
fclose($fp);

// --- Respuesta final ---
echo json_encode([
    'ok' => true,
    'evento' => 'actualizar_chat',
    'mensaje' => 'Señal de actualización recibida correctamente'
]);