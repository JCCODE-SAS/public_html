<?php
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

function send_json($payload = [], $code = 200)
{
    if (ob_get_length()) {
        @ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Método no permitido'], 405);
}

// Aceptar JSON o form-data
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$src  = is_array($data) ? $data : $_POST;

$email  = isset($src['email']) ? trim($src['email']) : '';
$userId = isset($src['user_id']) ? (int)$src['user_id'] : 0;

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // El front ya valida formato; aquí devolvemos exists=false sin bloquear
    send_json(['exists' => false]);
}

if ($userId > 0) {
    $stmt = $conexion->prepare("SELECT 1 FROM users WHERE email = ? AND id != ? LIMIT 1");
    $stmt->bind_param('si', $email, $userId);
} else {
    $stmt = $conexion->prepare("SELECT 1 FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param('s', $email);
}

$stmt->execute();
$exists = (bool)$stmt->get_result()->fetch_row();

writeLog("verificar_email.php", "email=$email exists=" . ($exists ? '1' : '0') . " exclude_id=$userId");

send_json(['exists' => $exists]);