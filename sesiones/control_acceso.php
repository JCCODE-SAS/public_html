<?php
// control_acceso.php
// Uso: include_once __DIR__ . '/../sesiones/control_acceso.php';
//
// Parámetros esperados antes de incluir:
//   $roles_permitidos = ['admin', 'operador'];
//

session_start();
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $roles_permitidos)) {
    // Si es petición AJAX, devolver JSON
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Acceso denegado']);
        exit;
    }
    // Si es acceso normal, redirigir o mostrar error
    header('Location: /public_html/sin_permiso.php');
    exit;
}
