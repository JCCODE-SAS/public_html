<?php

/**
 * ===============================================================
 * 📄 destruir_sesion.php
 * ---------------------------------------------------------------
 * Cierra la sesión del usuario y devuelve JSON al cliente JS.
 * ===============================================================
 */
session_start();
require_once __DIR__ . "/../logs/logger.php";

// Detecta si es AJAX/fetch
$isAjax = false;
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    $isAjax = true;
}
if (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/json') !== false) {
    $isAjax = true;
}

try {
    $sessionWasActive = isset($_SESSION["user_id"]);
    $userId = $sessionWasActive ? $_SESSION["user_id"] : null;
    $username = $sessionWasActive ? $_SESSION["user_name"] : "N/A";
    $role = $sessionWasActive ? $_SESSION["role"] : "N/A";

    session_unset();
    session_destroy();

    if ($sessionWasActive) {
        writeLog("destruir_sesion.php", "✅ Sesión cerrada: ID=$userId ($username, rol=$role)");
        $message = "Sesión cerrada correctamente.";
    } else {
        writeLog("destruir_sesion.php", "Intento de logout sin sesión activa (OK).");
        $message = "No había sesión activa, pero se cerró correctamente.";
    }

    if ($isAjax) {
        header("Content-Type: application/json");
        echo json_encode([
            "success" => true,
            "message" => $message
        ]);
    } else {
        // Redirige al login si NO es AJAX
        header("Location: /public_html/index.php");
        exit;
    }
} catch (Exception $e) {
    writeLog("destruir_sesion.php", "❌ Error al cerrar sesión: " . $e->getMessage());
    if ($isAjax) {
        http_response_code(500);
        header("Content-Type: application/json");
        echo json_encode([
            "success" => false,
            "message" => "Error interno del servidor al cerrar sesión."
        ]);
    } else {
        header("Location: /public_html/index.php");
        exit;
    }
}
exit;