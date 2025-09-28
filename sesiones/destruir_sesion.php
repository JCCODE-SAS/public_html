<?php

/**
 * ===============================================================
 * 📄 destruir_sesion.php
 * ---------------------------------------------------------------
 * Cierra la sesión del usuario y devuelve JSON al cliente JS.
 * ===============================================================
 */
session_start();
// ¡CRUCIAL! Asegúrate que logger.php NO imprima NADA a la salida.
require_once __DIR__ . "/../logs/logger.php";

// Establece el encabezado JSON antes de cualquier salida
header("Content-Type: application/json");

try {
    $sessionWasActive = isset($_SESSION["user_id"]);
    $userId = $sessionWasActive ? $_SESSION["user_id"] : null;
    $username = $sessionWasActive ? $_SESSION["user_name"] : "N/A";
    $role = $sessionWasActive ? $_SESSION["role"] : "N/A";

    session_unset();
    session_destroy();

    // Si la sesión estaba activa, loguea el cierre
    if ($sessionWasActive) {
        writeLog("destruir_sesion.php", "✅ Sesión cerrada: ID=$userId ($username, rol=$role)");
        $message = "Sesión cerrada correctamente.";
    } else {
        writeLog("destruir_sesion.php", "Intento de logout sin sesión activa (OK).");
        $message = "No había sesión activa, pero se cerró correctamente.";
    }

    // Devuelve una respuesta JSON de éxito
    echo json_encode([
        "success" => true,
        "message" => $message
    ]);
} catch (Exception $e) {
    writeLog("destruir_sesion.php", "❌ Error al cerrar sesión: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Error interno del servidor al cerrar sesión."
    ]);
}
exit;