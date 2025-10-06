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

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$src  = is_array($data) ? $data : $_POST;

$usuario  = isset($src['usuario']) ? trim($src['usuario']) : '';
$operarioId = isset($src['operario_id']) ? (int)$src['operario_id'] : 0;

if ($usuario === '' || !preg_match('/^[a-zA-Z0-9_.-]+$/', $usuario)) {
    send_json(['exists' => false]);
}

if ($operarioId > 0) {
    $stmt = $conexion->prepare("SELECT 1 FROM operadores WHERE usuario = ? AND id != ? LIMIT 1");
    $stmt->bind_param('si', $usuario, $operarioId);
} else {
    $stmt = $conexion->prepare("SELECT 1 FROM operadores WHERE usuario = ? LIMIT 1");
    $stmt->bind_param('s', $usuario);
}
$stmt->execute();
$exists = (bool)$stmt->get_result()->fetch_row();

writeLog("verificar_usuario.php", "usuario=$usuario exists=" . ($exists ? '1' : '0') . " exclude_id=$operarioId");

send_json(['exists' => $exists]);