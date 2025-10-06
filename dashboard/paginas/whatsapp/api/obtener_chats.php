<?php
// --- Configuración de errores ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Inicio de sesión y cabecera JSON ---
session_start();
header('Content-Type: application/json');

// --- Inclusión de dependencias ---
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

// --- Validación de sesión ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    if (function_exists('writeLog')) {
        writeLog("obtener_chats.php", "❌ Acceso denegado: usuario no autenticado");
    }
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$user_name = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

try {
    // --- Consulta principal: chats activos ---

    // Filtrar por operador asignado si no es admin
    $role = $_SESSION['role'] ?? '';
    if ($role === 'admin') {
        $sql = "SELECT id_chat, nombre_cliente, numero_cliente, estado, creado, operador_asignado
            FROM chats
            WHERE estado = 'activo'
            ORDER BY creado DESC";
        $result = $conexion->query($sql);
    } else {
        $sql = "SELECT id_chat, nombre_cliente, numero_cliente, estado, creado, operador_asignado
            FROM chats
            WHERE estado = 'activo' AND operador_asignado = ?
            ORDER BY creado DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $chats = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // --- Subconsulta: último mensaje ---
            $subsql = "SELECT texto FROM mensajes WHERE id_chat = ? ORDER BY timestamp DESC LIMIT 1";
            $stmt = $conexion->prepare($subsql);
            $stmt->bind_param("i", $row['id_chat']);
            $stmt->execute();
            $stmt->bind_result($ultimo_mensaje);
            $stmt->fetch();
            $stmt->close();

            // --- Subconsulta: mensajes no leídos ---
            $subsql2 = "SELECT COUNT(*) FROM mensajes WHERE id_chat = ? AND remitente = 'cliente' AND leido = 0";
            $stmt2 = $conexion->prepare($subsql2);
            $stmt2->bind_param("i", $row['id_chat']);
            $stmt2->execute();
            $stmt2->bind_result($no_leidos);
            $stmt2->fetch();
            $stmt2->close();

            // --- Construcción del objeto chat ---
            $chats[] = [
                'id' => $row['id_chat'],
                'cliente' => $row['nombre_cliente'],
                'numero' => $row['numero_cliente'],
                'estado' => $row['estado'],
                'ultimo_mensaje' => $ultimo_mensaje ?: '',
                'creado' => $row['creado'],
                'no_leidos' => (int)$no_leidos
            ];
        }
        $result->free();
    }

    // --- Respuesta JSON ---
    echo json_encode(['ok' => true, 'chats' => $chats]);

    // --- Logging exitoso ---
    if (function_exists('writeLog')) {
        writeLog("obtener_chats.php", "Consultados " . count($chats) . " chats por $user_name (id=$user_id)");
    }
} catch (Exception $e) {
    // --- Manejo de errores ---
    if (function_exists('writeLog')) {
        writeLog("obtener_chats.php", "ERROR: " . $e->getMessage());
    }
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Error al consultar chats',
        'exception' => $e->getMessage()
    ]);
    exit;
}