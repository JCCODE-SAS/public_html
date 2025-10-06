<?php

/**
 * ===============================================================
 * 📄 limpiar_cache_stats.php
 * ---------------------------------------------------------------
 * Limpia el cache de estadísticas cuando se actualizan usuarios.
 * Se debe llamar desde cambiar_estado_usuario.php
 * ===============================================================
 */

function limpiarCacheEstadisticas()
{
    $cache_file = __DIR__ . '/../cache/stats_usuarios.json';

    if (file_exists($cache_file)) {
        unlink($cache_file);
        error_log("🧹 Cache de estadísticas limpiado: $cache_file");
        return true;
    }

    return false;
}

// Si se llama directamente
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') {
    $resultado = limpiarCacheEstadisticas();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => $resultado,
        'message' => $resultado ? 'Cache limpiado correctamente' : 'No había cache para limpiar'
    ]);
}