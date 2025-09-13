<?php

/**
 * ===============================================================
 * API ACTUALIZAR OPERARIO - COPFLOW v4.0 OPTIMIZADA
 * ===============================================================
 * 
 * PROPÓSITO:
 * Actualiza los datos de un operario existente y limpia el cache
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
    writeLog("actualizar_operarios.api", "Request recibido: " . json_encode($data, JSON_UNESCAPED_UNICODE));

    // Validar campos requeridos
    $camposRequeridos = ['id', 'nombre', 'email', 'usuario', 'disponible'];
    foreach ($camposRequeridos as $campo) {
        if (!isset($data[$campo]) || empty(trim($data[$campo]))) {
            throw new Exception("Campo requerido faltante: " . $campo);
        }
    }

    // Sanitizar y validar datos
    $operarioId = (int)$data['id'];
    $nombre = trim($data['nombre']);
    $email = trim($data['email']);
    $usuario = trim($data['usuario']);
    $disponible = trim($data['disponible']);

    // Validaciones específicas
    if ($operarioId <= 0) {
        throw new Exception("ID de operario inválido");
    }

    if (strlen($nombre) < 3) {
        throw new Exception("El nombre debe tener al menos 3 caracteres");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Formato de email inválido");
    }

    // VALIDACIÓN: Solo admin y user
    if (!in_array($usuario, ['admin', 'user'])) {
        throw new Exception("Usuario inválido. Debe ser: admin o user");
    }

    if (!in_array($disponible, ['si', 'no'])) {
        throw new Exception("Disponibilidad inválida. Debe ser: si o no");
    }

    // Verificar conexión a la base de datos
    if (!isset($conexion) || $conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos");
    }

    // OPTIMIZACIÓN 1: Consulta combinada compatible con MariaDB
    $checkStmt = $conexion->prepare("
        SELECT 
            o1.id, 
            o1.usuario, 
            o1.disponible,
            o1.nombre,
            o1.email,
            (SELECT COUNT(*) FROM operadores o2 WHERE o2.email = ? AND o2.id != ?) as email_exists
        FROM operadores o1 
        WHERE o1.id = ?
    ");

    $checkStmt->bind_param("sii", $email, $operarioId, $operarioId);
    $checkStmt->execute();
    $operarioActual = $checkStmt->get_result()->fetch_assoc();

    if (!$operarioActual) {
        throw new Exception("Operario no encontrado con ID: " . $operarioId);
    }

    if ($operarioActual['email_exists'] > 0) {
        throw new Exception("El email ya está en uso por otro operario");
    }

    // Detectar cambios para cache
    $usuarioCambio = $operarioActual['usuario'] !== $usuario;
    $disponibleCambio = $operarioActual['disponible'] !== $disponible;
    $nombreCambio = $operarioActual['nombre'] !== $nombre;
    $emailCambio = $operarioActual['email'] !== $email;
    $limpiarCache = $usuarioCambio || $disponibleCambio;

    // Verificar si realmente hay cambios
    $hayCambios = $usuarioCambio || $disponibleCambio || $nombreCambio || $emailCambio;

    if (!$hayCambios) {
        // No hay cambios, devolver respuesta sin hacer UPDATE
        $response = [
            'success' => true,
            'message' => 'No se realizaron cambios (datos idénticos)',
            'operario' => [
                'id' => $operarioId,
                'nombre' => $nombre,
                'email' => $email,
                'usuario' => $usuario,
                'disponible' => $disponible
            ],
            'changes' => [
                'usuario_changed' => false,
                'disponible_changed' => false,
                'cache_cleared' => false,
                'no_changes' => true
            ]
        ];

        writeLog("actualizar_operarios.api", "Operario ID {$operarioId}: No hay cambios para aplicar");
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // OPTIMIZACIÓN 2: Transacción con actualización + estadísticas
    $conexion->begin_transaction();

    try {
        // Actualizar operario
        $updateStmt = $conexion->prepare("
            UPDATE operadores 
            SET 
                nombre = ?, 
                email = ?, 
                usuario = ?, 
                disponible = ?, 
                actualizado = NOW()
            WHERE id = ?
        ");

        if (!$updateStmt) {
            throw new Exception("Error al preparar la consulta de actualización: " . $conexion->error);
        }

        $updateStmt->bind_param("ssssi", $nombre, $email, $usuario, $disponible, $operarioId);

        if (!$updateStmt->execute()) {
            throw new Exception("Error al actualizar operario: " . $updateStmt->error);
        }

        // OPTIMIZACIÓN 3: Estadísticas actualizadas para roles admin/user
        $statsStmt = $conexion->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN disponible = 'si' THEN 1 ELSE 0 END) as disponibles,
                SUM(CASE WHEN disponible = 'no' THEN 1 ELSE 0 END) as no_disponibles,
                SUM(CASE WHEN usuario = 'admin' THEN 1 ELSE 0 END) as usuarios,
                SUM(CASE WHEN usuario = 'user' THEN 1 ELSE 0 END) as users
            FROM operadores
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
                    writeLog("actualizar_operarios.api", "Cache de estadísticas limpiado correctamente");
                }
            } catch (Exception $cacheError) {
                writeWarning("actualizar_operarios.api", "No se pudo limpiar cache: " . $cacheError->getMessage());
                $cacheCleared = false;
            }
        }

        // Commit de la transacción
        $conexion->commit();

        // Preparar respuesta exitosa CON estadísticas
        $response = [
            'success' => true,
            'message' => 'Operario actualizado correctamente',
            'operario' => [
                'id' => $operarioId,
                'nombre' => $nombre,
                'email' => $email,
                'usuario' => $usuario,
                'disponible' => $disponible
            ],
            'changes' => [
                'usuario_changed' => $usuarioCambio,
                'disponible_changed' => $disponibleCambio,
                'nombre_changed' => $nombreCambio,
                'email_changed' => $emailCambio,
                'cache_cleared' => $cacheCleared
            ],
            'stats' => [
                'total' => (int)$stats['total'],
                'disponibles' => (int)$stats['disponibles'],
                'no_disponibles' => (int)$stats['no_disponibles'],
                'usuarios' => (int)$stats['usuarios'],
                'users' => (int)$stats['users']
            ]
        ];

        // Log de éxito con métricas de optimización
        writeSuccess("actualizar_operarios.api", "Operario ID {$operarioId} actualizado con 3 consultas SQL (MariaDB optimized - admin/user)", [
            'operario_nombre' => $nombre,
            'operario_email' => $email,
            'operario_usuario' => $usuario,
            'operario_disponible' => $disponible,
            'changes_made' => [
                'usuario_changed' => $usuarioCambio,
                'disponible_changed' => $disponibleCambio,
                'nombre_changed' => $nombreCambio,
                'email_changed' => $emailCambio
            ],
            'optimization' => [
                'queries_used' => 3,
                'transaction_used' => true,
                'stats_included' => true,
                'database' => 'MariaDB',
                'usuarios_supported' => ['admin', 'user']
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
    writeError("actualizar_operarios.api", "Error al actualizar operario: " . $e->getMessage(), [
        'request_data' => $data ?? null,
        'operario_id' => $operarioId ?? null,
        'database' => 'MariaDB',
        'usuarios_supported' => ['admin', 'user']
    ]);

    // Respuesta de error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'operario' => null
    ], JSON_UNESCAPED_UNICODE);
}

// Cerrar conexión si existe
if (isset($conexion)) {
    $conexion->close();
}

// Terminar ejecución para evitar outputs adicionales
exit;