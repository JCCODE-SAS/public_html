<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$id_chat = intval($data['id_chat'] ?? 0);
$texto = trim($data['texto'] ?? '');

if (!$id_chat || !$texto) {
    echo json_encode(['ok' => false, 'error' => 'Datos incompletos']);
    exit;
}

try {
    $sql = "INSERT INTO mensajes (id_chat, remitente, texto) VALUES (?, 'mia', ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("is", $id_chat, $texto);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['ok' => true]);
    if (function_exists('writeLog')) {
        writeLog("registrar_mensaje.php", "📩 Mensaje guardado en chat $id_chat");
    }
} catch (Exception $e) {
    if (function_exists('writeLog')) {
        writeLog("registrar_mensaje.php", "ERROR: " . $e->getMessage());
    }
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al guardar mensaje']);
}