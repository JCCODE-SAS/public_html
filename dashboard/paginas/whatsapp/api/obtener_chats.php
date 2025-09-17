<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../configuracion/bd.php';
require_once __DIR__ . '/../../../logs/logger.php';

// Ejemplo de consulta, ajusta los nombres de tabla/campo según tu base de datos
try {
    $sql = "SELECT c.id, c.cliente, c.estado, m.texto AS ultimo_mensaje
            FROM chats c
            LEFT JOIN (
                SELECT id_chat, texto, MAX(fecha_hora) AS max_fecha
                FROM mensajes
                GROUP BY id_chat
            ) m ON c.id = m.id_chat
            ORDER BY c.actualizado DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $chats = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $chats[] = [
            'id' => $row['id'],
            'cliente' => $row['cliente'],
            'estado' => $row['estado'],
            'ultimo_mensaje' => $row['ultimo_mensaje']
        ];
    }
    echo json_encode(['ok' => true, 'chats' => $chats]);
    if (function_exists('writeLog')) writeLog("obtener_chats.php", "Consultados " . count($chats) . " chats");
} catch (Exception $e) {
    if (function_exists('writeLog')) writeLog("obtener_chats.php", "ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Error al consultar chats']);
}