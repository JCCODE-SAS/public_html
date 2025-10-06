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

    // SOLO UNA CONSULTA para todas las estadísticas
    $result = $conexion->query("
        SELECT
            COUNT(*) as total,
            SUM(disponible = '1') as available,
            SUM(disponible = '0') as unavailable
        FROM operadores
    ");
    $stats = $result ? $result->fetch_assoc() : ['total' => 0, 'available' => 0, 'unavailable' => 0];

    if (function_exists('writeSuccess')) {
        writeSuccess("estadisticas_operarios.api", "Estadísticas obtenidas correctamente", $stats);
    }

    $response = array_merge(['success' => true], [
        'total' => (int)$stats['total'],
        'available' => (int)$stats['available'],
        'unavailable' => (int)$stats['unavailable'],
    ]);
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