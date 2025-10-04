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

try {
    // Cuenta todos los mensajes de cliente no leídos en todos los chats activos
    $sql = "SELECT COUNT(*) as total FROM mensajes m INNER JOIN chats c ON m.id_chat = c.id_chat WHERE m.remitente = 'cliente' AND m.leido = 0 AND c.estado = 'activo'";
    $result = $conexion->query($sql);
    $row = $result ? $result->fetch_assoc() : ['total' => 0];
    $total = (int)($row['total'] ?? 0);
    echo json_encode(['ok' => true, 'total_no_leidos' => $total]);
    if (function_exists('writeLog')) writeLog("contar_no_leidos.php", "Total no leídos: $total");
} catch (Exception $e) {
    if (function_exists('writeLog')) writeLog("contar_no_leidos.php", "ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al contar no leídos', 'exception' => $e->getMessage()]);
    exit;
}
