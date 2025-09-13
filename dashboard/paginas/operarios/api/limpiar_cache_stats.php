<?php

/**
 * 📄 limpiar_cache_stats.php
 * Limpia el cache de estadísticas de operarios
 */

function limpiarCacheEstadisticas()
{
    $cache_file = __DIR__ . '/../cache/stats_operarios.json';
    if (file_exists($cache_file)) {
        unlink($cache_file);
        error_log("🧹 Cache de estadísticas limpiado: $cache_file");
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $resultado = limpiarCacheEstadisticas();
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $resultado,
        'message' => $resultado ? 'Cache limpiado correctamente' : 'No había cache para limpiar'
    ]);
}