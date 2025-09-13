<?php
//===============================================================
// OBTENER_OPERARIOS.PHP - API para obtener datos de un operario
//===============================================================
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);
    if (!isset($datos['id'])) throw new Exception('ID de operario requerido');

    $id = (int)$datos['id'];
    if ($id <= 0) throw new Exception('ID inválido');

    $stmt = $conexion->prepare("SELECT id, nombre, usuario, email, disponible, creado, IFNULL(actualizado,'') as actualizado FROM operadores WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $operario = $result->fetch_assoc();
    $stmt->close();

    if (!$operario) throw new Exception('Operario no encontrado');

    // Formatear datos para el modal
    $operario['disponible'] = $operario['disponible'] == '1' ? 1 : 0;

    echo json_encode(['success' => true, 'operario' => $operario]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}