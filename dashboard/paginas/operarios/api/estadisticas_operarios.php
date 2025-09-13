<?php

/**
 * API para obtener estadísticas de operarios en formato JSON
 * Archivo: /public_html/dashboard/paginas/operarios/api/estadisticas_operarios.php
 */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

try {
    require_once __DIR__ . "/../../../../configuracion/bd.php";
    require_once __DIR__ . '/../../../../logs/logger.php';

    if (!isset($conexion) || $conexion->connect_error) {
        throw new Exception("Error de conexión a la base de datos: " . $conexion->connect_error);
    }

    $stats = [];

    // Total de operarios
    $result = $conexion->query("SELECT COUNT(*) as total FROM operadores");
    $stats['total'] = $result ? (int)$result->fetch_assoc()['total'] : 0;

    // Operarios disponibles (disponible = '1')
    $result = $conexion->query("SELECT COUNT(*) as disponibles FROM operadores WHERE disponible = '1'");
    $stats['available'] = $result ? (int)$result->fetch_assoc()['disponibles'] : 0;

    // No disponibles (disponible = '0')
    $result = $conexion->query("SELECT COUNT(*) as no_disponibles FROM operadores WHERE disponible = '0'");
    $stats['unavailable'] = $result ? (int)$result->fetch_assoc()['no_disponibles'] : 0;

    // Log de éxito
    if (function_exists('writeSuccess')) {
        writeSuccess("estadisticas_operarios.api", "Estadísticas obtenidas correctamente", $stats);
    }

    $response = array_merge(['success' => true], $stats);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    if (function_exists('writeError')) {
        writeError("estadisticas_operarios.api", "Error al obtener estadísticas: " . $e->getMessage());
    }
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor',
        'error' => $e->getMessage(),
        'total' => 0,
        'available' => 0,
        'unavailable' => 0
    ], JSON_UNESCAPED_UNICODE);
}

if (isset($conexion)) {
    $conexion->close();
}