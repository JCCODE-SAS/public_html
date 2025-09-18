<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    if (function_exists('writeLog')) writeLog("obtener_chats.php", "❌ Acceso denegado: usuario no autenticado");
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}
$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

try {
    $sql = "SELECT id_chat, nombre_cliente, estado, creado
            FROM chats
            WHERE mia_activa = 1
            ORDER BY creado DESC";
    $result = $conexion->query($sql);

    $chats = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // Obtener el último mensaje de este chat
            $subsql = "SELECT texto FROM mensajes WHERE id_chat = ? ORDER BY timestamp DESC LIMIT 1";
            $stmt = $conexion->prepare($subsql);
            $stmt->bind_param("i", $row['id_chat']);
            $stmt->execute();
            $stmt->bind_result($ultimo_mensaje);
            $stmt->fetch();
            $stmt->close();

            $chats[] = [
                'id' => $row['id_chat'],
                'cliente' => $row['nombre_cliente'],
                'estado' => $row['estado'],
                'ultimo_mensaje' => $ultimo_mensaje ? $ultimo_mensaje : '',
                'creado' => $row['creado']
            ];
        }
        $result->free();
    }
    echo json_encode(['ok' => true, 'chats' => $chats]);
    if (function_exists('writeLog')) writeLog("obtener_chats.php", "Consultados " . count($chats) . " chats por $user_name (id=$user_id)");
} catch (Exception $e) {
    if (function_exists('writeLog')) writeLog("obtener_chats.php", "ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al consultar chats', 'exception' => $e->getMessage()]);
    exit;
}