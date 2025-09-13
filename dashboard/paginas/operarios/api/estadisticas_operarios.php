<?php

/**
 * API para obtener estadísticas de usuarios en formato JSON
 * Archivo: /public_html/dashboard/paginas/usuarios/api/estadisticas_usuarios.php
 */

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

try {
    // Incluir configuración de base de datos
    require_once __DIR__ . "/../../../../configuracion/bd.php";
    require_once __DIR__ . '/../../../../logs/logger.php';

    // Verificar conexión a la base de datos
    if (!isset($conexion) || $conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
    }

    // Consultas para obtener estadísticas
    $stats = [];

    // Total de operarios
    $result = $conexion->query("SELECT COUNT(*) as total FROM operadores");
    $stats['total'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

    // Operarios disponibles
    $result = $conexion->query("SELECT COUNT(*) as disponibles FROM operadores WHERE disponible = 'si'");
    $stats['disponibles'] = $result ? (int)$result->fetch_assoc()['disponibles'] : 0;

    // Operarios no disponibles
    $result = $conexion->query("SELECT COUNT(*) as no_disponibles FROM operadores WHERE disponible = 'no'");
    $stats['no_disponibles'] = $result ? (int)$result->fetch_assoc()['no_disponibles'] : 0;

    // Usuarios admin
    $result = $conexion->query("SELECT COUNT(*) as usuarios FROM operadores WHERE usuario = 'admin'");
    $stats['usuarios'] = $result ? (int)$result->fetch_assoc()['usuarios'] : 0;

    // Log de éxito - CORREGIDO: usar writeSuccess en lugar de escribir_log
    if (function_exists('writeSuccess')) {
        writeSuccess("estadisticas_usuarios.api", "Estadísticas obtenidas correctamente", $stats);
    }

    // Respuesta exitosa
    $response = array_merge(['success' => true], $stats);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    // Log del error - CORREGIDO: usar writeError en lugar de escribir_log
    if (function_exists('writeError')) {
        writeError("estadisticas_usuarios.api", "Error al obtener estadísticas: " . $e->getMessage());
    }

    // Respuesta de error en formato JSON
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage(),
        'total' => 0,
        'disponibles' => 0,
        'no_disponibles' => 0,
        'usuarios' => 0
    ], JSON_UNESCAPED_UNICODE);
}

// Cerrar conexión si existe
if (isset($conexion)) {
    $conexion->close();
}