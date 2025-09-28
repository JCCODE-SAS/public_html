<?php
// =============================================================
// 🔹 CONFIGURACIÓN DE CONEXIÓN A LA BASE DE DATOS (VPS/SSH TUNNEL)
// =============================================================
define('DB_HOST', '127.0.0.1'); // APUNTA AL TÚNEL SSH LOCAL
define('DB_PORT', 3306);
define('DB_NAME', 'copflow'); // NOMBRE REAL EN EL VPS
define('DB_USER', 'soporteclangsm'); // USUARIO REAL EN EL VPS
define('DB_PASS', 'HuX1fcV2wm9Di788F'); // CLAVE REAL EN EL VPS

// Crear conexión
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Verificar conexión
if ($conexion->connect_error) {
    die("❌ Error al conectar a la base de datos: " . $conexion->connect_error);
}

// Opcional: establecer charset
$conexion->set_charset("utf8mb4");