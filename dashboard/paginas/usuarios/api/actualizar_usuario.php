<?php

/**
 * ===============================================================
 * API ACTUALIZAR USUARIO - COPFLOW v4.0 OPTIMIZADA
 * ===============================================================
 * 
 * PROPÓSITO:
 * Actualiza los datos de un usuario existente y limpia el cache
 * de estadísticas si es necesario.
 * 
 * OPTIMIZACIÓN v4.0:
 * • Reducido de 8 a 3 consultas SQL (-62% consultas)
 * • Transacciones atómicas para consistencia
 * • Estadísticas incluidas en respuesta
 * • Compatible con MariaDB/MySQL
 * • Roles: admin y user únicamente
 * 
 * DESARROLLADO POR: Diomedez98
 * FECHA CREACIÓN: 2025-01-09
 * FECHA OPTIMIZACIÓN: 2025-01-09
 * REPOSITORIO: https://github.com/Diomedez98/copflows
 * EMPRESA: JCCODE-SAS
 * 
 * ===============================================================
 */

// IMPORTANTE: Limpiar cualquier output buffer previo
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

    // Log del request recibido
    writeLog("actualizar_usuario.api", "Request recibido: " . json_encode($data, JSON_UNESCAPED_UNICODE));

    // Validar campos requeridos
    $camposRequeridos = ['id', 'name', 'email', 'role', 'status'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($data[$campo]) || empty(trim($data[$campo]))) {
            throw new Exception("Campo requerido faltante: " . $campo);
        }
    }

    // Sanitizar y validar datos
    $userId = (int)$data['id'];
    $name = trim($data['name']);
    $email = trim($data['email']);
    $role = trim($data['role']);
    $status = trim($data['status']);

    // Validaciones específicas
    if ($userId <= 0) {
        throw new Exception("ID de usuario inválido");
    }

    if (strlen($name) < 3) {
        throw new Exception("El nombre debe tener al menos 3 caracteres");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Formato de email inválido");
    }

    // VALIDACIÓN: Solo admin y user
    if (!in_array($role, ['admin', 'user'])) {
        throw new Exception("Rol inválido. Debe ser: admin o user");
    }

    if (!in_array($status, ['activo', 'inactivo'])) {
        throw new Exception("Estado inválido. Debe ser: activo o inactivo");
    }

    // Verificar conexión a la base de datos
    if (!isset($conexion) || $conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos");
    }

    // OPTIMIZACIÓN 1: Consulta combinada compatible con MariaDB
    $checkStmt = $conexion->prepare("
        SELECT 
            u1.id, 
            u1.role, 
            u1.status,
            u1.name,
            u1.email,
            (SELECT COUNT(*) FROM users u2 WHERE u2.email = ? AND u2.id != ?) as email_exists
        FROM users u1 
        WHERE u1.id = ?
    ");

    $checkStmt->bind_param("sii", $email, $userId, $userId);
    $checkStmt->execute();
    $usuarioActual = $checkStmt->get_result()->fetch_assoc();

    if (!$usuarioActual) {
        throw new Exception("Usuario no encontrado con ID: " . $userId);
    }

    if ($usuarioActual['email_exists'] > 0) {
        throw new Exception("El email ya está en uso por otro usuario");
    }

    // Detectar cambios para cache
    $rolCambio = $usuarioActual['role'] !== $role;
    $estadoCambio = $usuarioActual['status'] !== $status;
    $nombreCambio = $usuarioActual['name'] !== $name;
    $emailCambio = $usuarioActual['email'] !== $email;
    $limpiarCache = $rolCambio || $estadoCambio;

    // Verificar si realmente hay cambios
    $hayCambios = $rolCambio || $estadoCambio || $nombreCambio || $emailCambio;

    if (!$hayCambios) {
        // No hay cambios, devolver respuesta sin hacer UPDATE
        $response = [
            'success' => true,
            'message' => 'No se realizaron cambios (datos idénticos)',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status
            ],
            'changes' => [
                'role_changed' => false,
                'status_changed' => false,
                'cache_cleared' => false,
                'no_changes' => true
            ]
        ];

        writeLog("actualizar_usuario.api", "Usuario ID {$userId}: No hay cambios para aplicar");
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // OPTIMIZACIÓN 2: Transacción con actualización + estadísticas
    $conexion->begin_transaction();

    try {
        // Actualizar usuario
        $updateStmt = $conexion->prepare("
            UPDATE users 
            SET 
                name = ?, 
                email = ?, 
                role = ?, 
                status = ?, 
                updated_at = NOW()
            WHERE id = ?
        ");

        if (!$updateStmt) {
            throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
        }

        $updateStmt->bind_param("ssssi", $name, $email, $role, $status, $userId);

        if (!$updateStmt->execute()) {
            throw new Exception("Error al actualizar usuario: " . $updateStmt->error);
        }

        // OPTIMIZACIÓN 3: Estadísticas actualizadas para roles admin/user
        $statsStmt = $conexion->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'activo' THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'inactivo' THEN 1 ELSE 0 END) as inactive,
                SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as users
            FROM users
        ");

        if (!$statsStmt) {
            throw new Exception("Error al preparar consulta de estadísticas: " . $conexion->error);
        }

        $statsStmt->execute();
        $stats = $statsStmt->get_result()->fetch_assoc();

        // OPTIMIZACIÓN 4: Limpiar cache si es necesario
        $cacheCleared = false;
        if ($limpiarCache) {
            try {
                $cacheDir = __DIR__ . '/cache';
                if (is_dir($cacheDir)) {
                    $files = glob($cacheDir . '/*.json');
                    foreach ($files as $file) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                    $cacheCleared = true;
                    writeLog("actualizar_usuario.api", "Cache de estadísticas limpiado correctamente");
                }
            } catch (Exception $cacheError) {
                writeWarning("actualizar_usuario.api", "No se pudo limpiar cache: " . $cacheError->getMessage());
                $cacheCleared = false;
            }
        }

        // Commit de la transacción
        $conexion->commit();

        // Preparar respuesta exitosa CON estadísticas
        $response = [
            'success' => true,
            'message' => 'Usuario actualizado correctamente',
            'user' => [
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'status' => $status
            ],
            'changes' => [
                'role_changed' => $rolCambio,
                'status_changed' => $estadoCambio,
                'name_changed' => $nombreCambio,
                'email_changed' => $emailCambio,
                'cache_cleared' => $cacheCleared
            ],
            'stats' => [
                'total' => (int)$stats['total'],
                'active' => (int)$stats['active'],
                'inactive' => (int)$stats['inactive'],
                'admins' => (int)$stats['admins'],
                'users' => (int)$stats['users']
            ]
        ];

        // Log de éxito con métricas de optimización
        writeSuccess("actualizar_usuario.api", "Usuario ID {$userId} actualizado con 3 consultas SQL (MariaDB optimized - admin/user)", [
            'user_name' => $name,
            'user_email' => $email,
            'user_role' => $role,
            'user_status' => $status,
            'changes_made' => [
                'role_changed' => $rolCambio,
                'status_changed' => $estadoCambio,
                'name_changed' => $nombreCambio,
                'email_changed' => $emailCambio
            ],
            'optimization' => [
                'queries_used' => 3,
                'transaction_used' => true,
                'stats_included' => true,
                'database' => 'MariaDB',
                'roles_supported' => ['admin', 'user']
            ]
        ]);

        echo json_encode($response, JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        // Rollback en caso de error
        $conexion->rollback();
        throw $e;
    }
} catch (Exception $e) {
    // Log del error
    writeError("actualizar_usuario.api", "Error al actualizar usuario: " . $e->getMessage(), [
        'request_data' => $data ?? null,
        'user_id' => $userId ?? null,
        'database' => 'MariaDB',
        'roles_supported' => ['admin', 'user']
    ]);

    // Respuesta de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'user' => null
    ], JSON_UNESCAPED_UNICODE);
}

// Cerrar conexión si existe
if (isset($conexion)) {
    $conexion->close();
}

// Terminar ejecución para evitar outputs adicionales
exit;