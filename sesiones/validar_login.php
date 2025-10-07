<?php

/**
 * ===============================================================
 * 📄 validar_login.php
 * ---------------------------------------------------------------
 * Valida credenciales contra:
 *   - Tabla `users` (admin/user, login por email).
 *   - Tabla `operadores` (login por email o usuario).
 * 
 * Si login exitoso, crea la sesión.
 * ===============================================================
 */

session_start();
require_once __DIR__ . "/../configuracion/bd.php";
require_once __DIR__ . "/../logs/logger.php";

header("Content-Type: application/json");

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        writeLog("validar_login.php", "Acceso no permitido (no POST)");
        echo json_encode(["success" => false, "message" => "Método no permitido"]);
        exit;
    }

    $login    = trim($_POST["username"] ?? $_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");

    // 🔎 Log para depuración
    writeLog("validar_login.php", "POST recibido: " . print_r($_POST, true));

    if (empty($login) || empty($password)) {
        writeLog("validar_login.php", "Campos vacíos");
        echo json_encode(["success" => false, "message" => "Usuario y contraseña requeridos"]);
        exit;
    }

    // ===========================================================
    // 🔹 1. Buscar en tabla users (admins/users, login con email)
    // ===========================================================
    $sqlUsers = "SELECT id, name, email, password, role 
                 FROM users 
                 WHERE email = ? AND status = 'activo' 
                 LIMIT 1";
    $stmtUsers = $conexion->prepare($sqlUsers);
    $stmtUsers->bind_param("s", $login);
    $stmtUsers->execute();
    $resultUsers = $stmtUsers->get_result();
    $user = $resultUsers->fetch_assoc();

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["user_name"] = $user["name"];
        $_SESSION["role"]      = $user["role"];

        writeLog("validar_login.php", "✅ Login exitoso en users: {$user['email']} ({$user['role']})");

        echo json_encode([
            "success"  => true,
            "role"     => $user["role"],
            "redirect" => "dashboard/dashboard.php"
        ]);
        exit;
    }

    // ===========================================================
    // 🔹 2. Buscar en tabla operadores (login con usuario o email)
    // ===========================================================
    $sqlOps = "SELECT id, nombre, usuario, email, password 
               FROM operadores 
               WHERE (usuario = ? OR email = ?) 
               AND disponible = 1 
               LIMIT 1";
    $stmtOps = $conexion->prepare($sqlOps);
    $stmtOps->bind_param("ss", $login, $login);
    $stmtOps->execute();
    $resultOps = $stmtOps->get_result();
    $operador = $resultOps->fetch_assoc();

    if ($operador && password_verify($password, $operador["password"])) {
        $_SESSION["user_id"]   = $operador["id"];
        $_SESSION["user_name"] = $operador["nombre"];
        $_SESSION["role"]      = "operador";

        writeLog("validar_login.php", "✅ Login exitoso en operadores: {$operador['usuario']}");

        echo json_encode([
            "success"  => true,
            "role"     => "operador",
            "redirect" => "/dashboard.php"
        ]);
        exit;
    }

    // ❌ Ningún login válido
    writeLog("validar_login.php", "Login fallido para '$login'");
    echo json_encode(["success" => false, "message" => "Credenciales inválidas"]);
} catch (Exception $e) {
    writeLog("validar_login.php", "❌ Error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Error interno"]);
}