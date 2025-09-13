<?php

/**
 * 📄 destruir_sesion.php
 * Cierra la sesión del usuario y redirige al index.
 */
session_start();
require_once __DIR__ . "/../logs/logger.php";

try {
    if (isset($_SESSION["user_id"])) {
        $userId   = $_SESSION["user_id"];
        $username = $_SESSION["user_name"];
        $role     = $_SESSION["role"];

        session_unset();
        session_destroy();

        writeLog("destruir_sesion.php", "Sesión cerrada: ID=$userId ($username, rol=$role)");
    } else {
        writeLog("destruir_sesion.php", "Intento de logout sin sesión activa");
    }
} catch (Exception $e) {
    writeLog("destruir_sesion.php", "Error: " . $e->getMessage());
}

// Redirigir siempre al login/index
header("Location: ../index.php");
exit;