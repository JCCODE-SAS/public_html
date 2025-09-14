<?php
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);
    if (!isset($datos['id'])) throw new Exception('ID de operario requerido');

    $id = (int)$datos['id'];
    if ($id <= 0) throw new Exception('ID inválido');

    // Consulta usando bind_result (más compatible)
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, email, disponible, creado FROM operadores WHERE id = ?");
    if (!$stmt) throw new Exception("Error en prepare: " . $conexion->error);

    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        $stmt->close();
        throw new Exception('Operario no encontrado');
    }

    $stmt->bind_result($idDB, $nombre, $usuario, $email, $disponible, $creado);
    $stmt->fetch();
    $stmt->close();

    $operario = [
        'id' => $idDB,
        'nombre' => $nombre,
        'usuario' => $usuario,
        'email' => $email,
        'disponible' => $disponible == 1 ? 1 : 0,
        'creado' => $creado,
    ];

    echo json_encode(['success' => true, 'operario' => $operario]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}