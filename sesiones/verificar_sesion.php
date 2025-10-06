<?php

/**
 * ===============================================================
 * 📄 verificar_sesion.php
 * ---------------------------------------------------------------
 * Devuelve JSON con info de sesión:
 *   - logged: true/false
 *   - user_id
 *   - user_name
 *   - role: admin | user | operador
 * ===============================================================
 */

session_start();
require_once __DIR__ . "/../logs/logger.php";

header("Content-Type: application/json");

try {
    if (isset($_SESSION["user_id"]) && isset($_SESSION["role"])) {
        // 🔹 Sesión activa
        writeLog("verificar_sesion.php", "Sesión activa: ID={$_SESSION['user_id']} ({$_SESSION['role']})");

        echo json_encode([
            "logged"    => true,
            "user_id"   => $_SESSION["user_id"],
            "user_name" => $_SESSION["user_name"] ?? "",
            "role"      => $_SESSION["role"]
        ]);
    } else {
        // 🔹 No hay sesión
        writeLog("verificar_sesion.php", "No hay sesión activa");

        echo json_encode([
            "logged" => false,
            "user_id" => null,
            "user_name" => null,
            "role" => null
        ]);
    }
} catch (Exception $e) {
    writeLog("verificar_sesion.php", "❌ Error: " . $e->getMessage());

    echo json_encode([
        "logged" => false,
        "error"  => true,
        "message" => "Error interno"
    ]);
}