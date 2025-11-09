<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

function wa_columna_nombre_operador_disponible(mysqli $conexion): bool
{
    static $cache = null;
    if ($cache !== null) {
        return $cache;
    }

    try {
        $sql = "SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mensajes' AND COLUMN_NAME = 'nombre_operador'";
        $res = $conexion->query($sql);
        if ($res && ($row = $res->fetch_assoc())) {
            $cache = ((int)$row['total'] > 0);
            return $cache;
        }
    } catch (Throwable $e) {
        if (function_exists('writeLog')) {
            writeLog('obtener_mensajes.php', 'Error verificando columna nombre_operador: ' . $e->getMessage());
        }
    }

    $cache = false;
    return false;
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    if (function_exists('writeLog')) writeLog("obtener_mensajes.php", "❌ Acceso denegado: usuario no autenticado");
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}
$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

$id_chat = isset($_GET['id_chat']) ? intval($_GET['id_chat']) : 0;

if ($id_chat < 1) {
    echo json_encode(['ok' => false, 'error' => 'Parámetro id_chat inválido']);
    exit;
}

try {
    $columnaNombreOperador = wa_columna_nombre_operador_disponible($conexion);

    if ($columnaNombreOperador) {
        $sql = "SELECT id_mensaje, texto, remitente, nombre_operador, DATE(timestamp) as fecha, TIME(timestamp) as hora
                FROM mensajes
                WHERE id_chat = ?
                ORDER BY timestamp ASC";
    } else {
        $sql = "SELECT id_mensaje, texto, remitente, DATE(timestamp) as fecha, TIME(timestamp) as hora
                FROM mensajes
                WHERE id_chat = ?
                ORDER BY timestamp ASC";
    }

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id_chat);
    $stmt->execute();
    $result = $stmt->get_result();

    $mensajes = [];
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = [
            'id' => $row['id_mensaje'],
            'texto' => $row['texto'],
            'enviado_por' => $row['remitente'],
            'nombre_operador' => $columnaNombreOperador ? ($row['nombre_operador'] ?? null) : null,
            'fecha' => $row['fecha'],
            'hora' => substr($row['hora'], 0, 5)
        ];
    }
    $stmt->close();

    echo json_encode(['ok' => true, 'mensajes' => $mensajes]);
    if (function_exists('writeLog')) writeLog("obtener_mensajes.php", "Consultados " . count($mensajes) . " mensajes para chat $id_chat por $user_name (id=$user_id)");
} catch (Exception $e) {
    if (function_exists('writeLog')) writeLog("obtener_mensajes.php", "ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al consultar mensajes', 'exception' => $e->getMessage()]);
    exit;
}