<?php

/**
 * ===============================================================
 * 📄 API OBTENER USUARIO - COPFLOW v4.0 OPTIMIZADA
 * ===============================================================
 * 
 * 🎯 PROPÓSITO:
 * Obtiene los datos completos de un usuario específico para 
 * cargar en el modal de edición. Optimizada para roles admin/user.
 * 
 * 🚀 OPTIMIZACIÓN v4.0:
 * • Solo maneja roles admin y user
 * • Sin mapeos innecesarios
 * • Validación robusta
 * • Logging optimizado
 * • Compatible con MariaDB/MySQL
 * 
 * 👨‍💻 DESARROLLADO POR: Diomedez98
 * 📅 FECHA CREACIÓN: 2025-01-09
 * 📅 FECHA OPTIMIZACIÓN: 2025-09-09
 * 🔗 REPOSITORIO: https://github.com/Diomedez98/copflows
 * 🏢 EMPRESA: JCCODE-SAS
 * 
 * ===============================================================
 */

// ⚠️ IMPORTANTE: Limpiar cualquier output buffer previo
while (ob_get_level()) {
    ob_end_clean();
}

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

try {
    // Incluir configuración
    require_once __DIR__ . '/../../../../configuracion/bd.php';
    require_once __DIR__ . '/../../../../logs/logger.php';

    // Verificar que sea POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido. Solo se acepta POST.");
    }

    // Leer datos JSON del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Validar que el JSON sea válido
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON inválido en el request: " . json_last_error_msg());
    }

    // Log del request recibido
    writeLog("obtener_usuario.api", "Request recibido: " . json_encode($data, JSON_UNESCAPED_UNICODE));

    // Validar que se envió el ID
    if (!isset($data['id']) || empty($data['id'])) {
        throw new Exception("ID de usuario requerido");
    }

    $userId = (int)$data['id'];

    // Validar que el ID sea válido
    if ($userId <= 0) {
        throw new Exception("ID de usuario inválido: " . $userId);
    }

    // Verificar conexión a la base de datos
    if (!isset($conexion) || $conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . ($conexion->connect_error ?? 'Conexión no disponible'));
    }

    // ✅ CONSULTA OPTIMIZADA: Obtener datos del usuario
    $stmt = $conexion->prepare("
        SELECT 
            id, 
            name, 
            email, 
            role, 
            status, 
            created_at,
            updated_at
        FROM users 
        WHERE id = ? 
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("i", $userId);

    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (!$usuario) {
        writeWarning("obtener_usuario.api", "Usuario no encontrado con ID: " . $userId);
        throw new Exception("Usuario no encontrado con ID: " . $userId);
    }

    // ✅ VALIDACIÓN DE ROL: Solo admin y user permitidos
    $roleFromDb = trim($usuario['role']);

    // Verificar que el rol en BD sea válido
    if (!in_array($roleFromDb, ['admin', 'user'])) {
        writeWarning("obtener_usuario.api", "Rol inválido en BD para usuario {$userId}: '{$roleFromDb}' - Convirtiendo a 'user'");
        $roleFromDb = 'user'; // Por defecto convertir a user
    }

    // ✅ FORMATEO DE FECHAS
    $fechaCreacion = 'No disponible';
    $fechaActualizacion = 'Nunca';

    try {
        if ($usuario['created_at']) {
            $fechaCreacion = date('d/m/Y H:i', strtotime($usuario['created_at']));
        }
        if ($usuario['updated_at']) {
            $fechaActualizacion = date('d/m/Y H:i', strtotime($usuario['updated_at']));
        }
    } catch (Exception $dateError) {
        writeWarning("obtener_usuario.api", "Error al formatear fechas para usuario {$userId}: " . $dateError->getMessage());
    }

    // ✅ PREPARAR RESPUESTA EXITOSA
    $response = [
        'success' => true,
        'message' => 'Usuario obtenido correctamente',
        'user' => [
            'id' => (int)$usuario['id'],
            'name' => $usuario['name'],
            'email' => $usuario['email'],
            'role' => $roleFromDb, // admin o user directo
            'status' => $usuario['status'],
            'created_at' => $fechaCreacion,
            'updated_at' => $fechaActualizacion,
            'created_at_raw' => $usuario['created_at'],
            'updated_at_raw' => $usuario['updated_at']
        ],
        'meta' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'roles_supported' => ['admin', 'user'],
            'database' => 'MariaDB'
        ]
    ];

    // ✅ LOG DE ÉXITO CON INFORMACIÓN COMPLETA
    writeSuccess("obtener_usuario.api", "Usuario ID {$userId} obtenido correctamente", [
        'user_id' => (int)$usuario['id'],
        'user_name' => $usuario['name'],
        'user_email' => $usuario['email'],
        'user_role' => $roleFromDb,
        'user_status' => $usuario['status'],
        'response_size' => strlen(json_encode($response)),
        'execution_time' => microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true))
    ]);

    // Enviar respuesta JSON
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // ✅ LOG DETALLADO DEL ERROR
    writeError("obtener_usuario.api", "Error al obtener usuario: " . $e->getMessage(), [
        'request_data' => $data ?? null,
        'user_id' => $userId ?? null,
        'error_line' => $e->getLine(),
        'error_file' => basename($e->getFile()),
        'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
    ]);

    // Limpiar cualquier output previo en caso de error
    if (ob_get_level()) {
        ob_end_clean();
    }

    // ✅ RESPUESTA DE ERROR ESTRUCTURADA
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'user' => null,
        'error' => [
            'type' => 'obtener_usuario_error',
            'timestamp' => date('Y-m-d H:i:s'),
            'request_id' => uniqid('req_')
        ]
    ], JSON_UNESCAPED_UNICODE);
} finally {
    // ✅ LIMPIEZA DE RECURSOS
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }

    if (isset($conexion) && $conexion instanceof mysqli) {
        $conexion->close();
    }
}

// Terminar ejecución para evitar outputs adicionales
exit;