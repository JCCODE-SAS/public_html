<?php
// api/apagar_mia_global.php
session_start();
require_once __DIR__ . '/../../../../configuracion/bd.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$user_id = intval($_SESSION['user_id']);
$role = $_SESSION['role'];

if ($role === 'admin') {
    // Admin puede apagar MIA en todos los chats activos
    $sql = "UPDATE chats SET mia_activa = 0 WHERE estado = 'activo' AND mia_activa = 1";
    $ok = $conexion->query($sql);
} else {
    // Operador solo en sus chats activos
    $sql = "UPDATE chats SET mia_activa = 0 WHERE estado = 'activo' AND operador_asignado = ? AND mia_activa = 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $ok = $stmt->execute();
    $stmt->close();
}

if ($ok) {
    echo json_encode(['success' => true, 'message' => 'MIA desactivada en todos los chats correspondientes']);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar los chats']);
}
