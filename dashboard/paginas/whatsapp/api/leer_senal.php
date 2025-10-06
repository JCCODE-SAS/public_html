<?php
header('Content-Type: application/json');

// Endpoint simple que devuelve el contenido del archivo de señal (si existe).
// Usa session/cabeceras como las otras APIs para mantener coherencia.
session_start();
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$archivo_con_ntilde = __DIR__ . '/../cache/señal_actualizacion.json';
$archivo_sin_ntilde = __DIR__ . '/../cache/senal_actualizacion.json';

$contenido = null;
if (is_file($archivo_sin_ntilde)) {
    $contenido = file_get_contents($archivo_sin_ntilde);
} elseif (is_file($archivo_con_ntilde)) {
    $contenido = file_get_contents($archivo_con_ntilde);
}

if (!$contenido) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'No signal file']);
    exit;
}

// Intentar decodificar JSON para asegurar formato
$json = json_decode($contenido, true);
if ($json === null) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Malformed JSON in signal file']);
    exit;
}

echo json_encode($json);
