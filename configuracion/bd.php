<?php
// =============================================================
// 🔹 CONFIGURACIÓN DE CONEXIÓN A LA BASE DE DATOS (MySQLi)
// =============================================================
define('DB_HOST', 'auth-db1853.hstgr.io');
define('DB_PORT', 3306);
define('DB_NAME', 'u152493769_copflow');
define('DB_USER', 'u152493769_soporteclangsm');
define('DB_PASS', 'Maitershe@2025');

// Crear conexión
$conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Verificar conexión
if ($conexion->connect_error) {
    die("❌ Error al conectar a la base de datos: " . $conexion->connect_error);
}

// Opcional: establecer charset
$conexion->set_charset("utf8mb4");