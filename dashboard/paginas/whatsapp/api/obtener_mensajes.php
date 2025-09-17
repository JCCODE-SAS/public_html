<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../configuracion/bd.php';
require_once __DIR__ . '/../../../logs/logger.php';

$id_chat = isset($_GET['id_chat']) ? intval($_GET['id_chat']) : 0;

if ($id_chat < 1) {
    echo json_encode(['ok' => false, 'error' => 'Parámetro id_chat inválido']);
    exit;
}

try {
    $sql = "SELECT id, texto, enviado_por, DATE(fecha_hora) as fecha, TIME(fecha_hora) as hora
            FROM mensajes
            WHERE id_chat = :id_chat
            ORDER BY fecha_hora ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id_chat', $id_chat, PDO::PARAM_INT);
    $stmt->execute();
    $mensajes = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $mensajes[] = [
            'id' => $row['id'],
            'texto' => $row['texto'],
            'enviado_por' => $row['enviado_por'],
            'fecha' => $row['fecha'],
            'hora' => substr($row['hora'], 0, 5)
        ];
    }
    echo json_encode(['ok' => true, 'mensajes' => $mensajes]);
    if (function_exists('writeLog')) writeLog("obtener_mensajes.php", "Consultados " . count($mensajes) . " mensajes para chat $id_chat");
} catch (Exception $e) {
    if (function_exists('writeLog')) writeLog("obtener_mensajes.php", "ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al consultar mensajes']);
}