<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

writeLog("actualizar_estado_envios.php", " INICIO - Request recibido");

$data = json_decode(file_get_contents('php://input'), true);
$id_cliente = intval($data['id_cliente'] ?? 0);
$saludo = intval($data['saludo'] ?? 0);
$catalogo = intval($data['catalogo'] ?? 0);

if (!$id_cliente) {
    writeLog("actualizar_estado_envios.php", " Falta ID cliente");
    echo json_encode(['ok' => false, 'error' => 'Falta ID cliente']);
    exit;
}

try {
    $sql = "UPDATE clientes SET saludo = ?, catalogo = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iii", $saludo, $catalogo, $id_cliente);
    $stmt->execute();
    $stmt->close();

    writeLog("actualizar_estado_envios.php", " Cliente $id_cliente actualizado: saludo=$saludo, catalogo=$catalogo");

    echo json_encode(['ok' => true, 'mensaje' => 'Estado actualizado']);
} catch (Exception $e) {
    writeLog("actualizar_estado_envios.php", " ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>