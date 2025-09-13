<?php
//===============================================================
// ACTUALIZAR_OPERARIO.PHP - API de Edición de Operario COPFLOW
//===============================================================
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

function send_json($payload = [], $code = 200)
{
    if (ob_get_length()) {
        @ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Método no permitido'], 405);
}

session_start();
if (!isset($_SESSION['user_id'])) {
    send_json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
}

try {
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);
    if (!$datos) throw new Exception('Datos JSON inválidos o vacíos');
    if (!isset($datos['id'])) throw new Exception('ID de operario requerido');

    $id = (int)$datos['id'];
    $nombre = trim($datos['nombre'] ?? '');
    $usuario = trim($datos['usuario'] ?? '');
    $email = trim($datos['email'] ?? '');
    $disponible = trim($datos['disponible'] ?? '');

    if ($id <= 0) throw new Exception('ID inválido');
    if (strlen($nombre) < 2) throw new Exception('Nombre muy corto');
    if (strlen($usuario) < 4) throw new Exception('Usuario muy corto');
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) throw new Exception('Email inválido');
    if (!in_array($disponible, ['1', '0'])) throw new Exception('Estado disponible inválido');

    // Verificar email único (si cambia)
    if ($email) {
        $stmt = $conexion->prepare("SELECT id FROM operadores WHERE email = ? AND id != ?");
        $stmt->bind_param('si', $email, $id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) throw new Exception('Email ya existe');
        $stmt->close();
    }

    // Actualizar operario
    $stmt = $conexion->prepare("UPDATE operadores SET nombre = ?, usuario = ?, email = ?, disponible = ?, actualizado = NOW() WHERE id = ?");
    $stmt->bind_param('ssssi', $nombre, $usuario, $email, $disponible, $id);
    if (!$stmt->execute()) throw new Exception('Error al actualizar operario: ' . $stmt->error);
    $stmt->close();

    writeLog("actualizar_operario", "Operario actualizado - ID: $id, Usuario: $usuario");
    send_json(['success' => true, 'message' => 'Operario actualizado correctamente']);
} catch (Exception $e) {
    writeLog("actualizar_operario_error", "Error: " . $e->getMessage());
    send_json(['success' => false, 'message' => $e->getMessage()], 400);
} finally {
    if (isset($conexion)) $conexion->close();
}