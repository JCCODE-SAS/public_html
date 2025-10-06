<?php
// api/activar_mia.php
require_once __DIR__ . '/../../../../configuracion/bd.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$id_chat = isset($_POST['id_chat']) ? intval($_POST['id_chat']) : 0;
if ($id_chat <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID de chat inválido']);
    exit;
}

global $conexion;
if (!isset($conexion) || !$conexion) {
    echo json_encode(['success' => false, 'message' => 'No hay conexión a la base de datos']);
    exit;
}

$stmt = $conexion->prepare('UPDATE chats SET mia_activa = 1 WHERE id_chat = ?');
if ($stmt) {
    $stmt->bind_param('i', $id_chat);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'MIA activada para este chat']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el chat o ya estaba activada']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
}
