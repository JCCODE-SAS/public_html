<?php
//===============================================================
// CREAR_OPERARIO.PHP - API de creación de operario COPFLOW
//===============================================================
// Recibe: nombre, usuario, email (opcional), password, disponible
//===============================================================

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

require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    send_json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
}

try {
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);
    if (!$datos) throw new Exception('Datos JSON inválidos o vacíos');

    // Log de inicio
    writeLog("crear_operario", "Intento de creación por user_id: " . $_SESSION['user_id']);

    // Validación básica
    $camposRequeridos = ['nombre', 'usuario', 'password', 'disponible'];
    $faltantes = [];
    foreach ($camposRequeridos as $campo) {
        if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) $faltantes[] = $campo;
    }
    if ($faltantes) throw new Exception('Campos requeridos faltantes: ' . implode(', ', $faltantes));

    // Sanitizar
    $nombre = trim($datos['nombre']);
    $usuario = trim($datos['usuario']);
    $email = isset($datos['email']) ? trim(strtolower($datos['email'])) : null;
    $password = $datos['password'];
    $disponible = trim($datos['disponible']);

    if (strlen($nombre) < 2 || strlen($nombre) > 100) throw new Exception('El nombre debe tener 2-100 caracteres');
    if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nombre)) throw new Exception('El nombre solo puede contener letras y espacios');
    if (strlen($usuario) < 4 || strlen($usuario) > 50) throw new Exception('El usuario debe tener 4-50 caracteres');
    if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)) throw new Exception('Usuario solo puede contener letras, números, punto, guion, guion bajo');
    if ($email && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255)) throw new Exception('Email inválido');
    if (strlen($password) < 8 || strlen($password) > 255) throw new Exception('La contraseña debe tener 8-255 caracteres');
    if (!in_array($disponible, ['1', '0'])) throw new Exception('Estado disponible inválido');

    // Verificar usuario único
    $stmt = $conexion->prepare("SELECT id FROM operadores WHERE usuario = ?");
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) throw new Exception('Usuario ya existente');
    $stmt->close();

    // Verificar email único (si se envía)
    if ($email) {
        $stmt = $conexion->prepare("SELECT id FROM operadores WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) throw new Exception('Email ya existe');
        $stmt->close();
    }

    // Hash de contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if (!$passwordHash) throw new Exception('Error al encriptar la contraseña');

    // Insertar operario (agregar actualizado = NOW())
    $stmt = $conexion->prepare("INSERT INTO operadores (nombre, usuario, email, password, disponible, creado, actualizado) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->bind_param('ssssi', $nombre, $usuario, $email, $passwordHash, $disponible);
    if (!$stmt->execute()) throw new Exception('Error al crear operario: ' . $stmt->error);
    $operarioId = $conexion->insert_id;
    $stmt->close();

    // Obtener el operario recién creado para respuesta instantánea en la tabla
    $stmt = $conexion->prepare("SELECT id, nombre, usuario, email, disponible, creado, actualizado FROM operadores WHERE id = ?");
    $stmt->bind_param('i', $operarioId);
    $stmt->execute();
    $nuevoOperario = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Log y respuesta
    writeLog("crear_operario", "Operario creado exitosamente - ID: $operarioId, Usuario: $usuario");
    send_json(['success' => true, 'operario' => $nuevoOperario, 'message' => 'Operario creado exitosamente']);
} catch (Exception $e) {
    writeLog("crear_operario_error", "Error: " . $e->getMessage());
    send_json(['success' => false, 'message' => $e->getMessage()], 400);
} finally {
    if (isset($conexion)) $conexion->close();
}