<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$id_chat = isset($_POST['id_chat']) ? intval($_POST['id_chat']) : 0;
if ($id_chat < 1) {
    echo json_encode(['ok' => false, 'error' => 'Parámetro id_chat inválido']);
    exit;
}

try {
    $sql = "UPDATE mensajes SET leido = 1 WHERE id_chat = ? AND remitente = 'cliente' AND leido = 0";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_chat);
    $stmt->execute();
    $afectados = $stmt->affected_rows;
    $stmt->close();
    echo json_encode(['ok' => true, 'marcados' => $afectados]);
    if (function_exists('writeLog')) writeLog("marcar_leido.php", "Marcados como leídos $afectados mensajes en chat $id_chat");
} catch (Exception $e) {
    if (function_exists('writeLog')) writeLog("marcar_leido.php", "ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al marcar como leídos', 'exception' => $e->getMessage()]);
    exit;
}
