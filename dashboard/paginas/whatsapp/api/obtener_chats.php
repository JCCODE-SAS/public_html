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
        $sql = "SELECT c.id_chat, c.nombre_cliente, c.numero_cliente, c.estado, c.creado, c.operador_asignado, c.mia_activa,
                       u.user_name as nombre_operador_asignado
                FROM chats c
                LEFT JOIN usuarios u ON c.operador_asignado = u.id
                WHERE c.estado = 'activo'
                ORDER BY c.creado DESC";
        $result = $conexion->query($sql);
    } else {
        $sql = "SELECT c.id_chat, c.nombre_cliente, c.numero_cliente, c.estado, c.creado, c.operador_asignado, c.mia_activa,
                       u.user_name as nombre_operador_asignado
                FROM chats c
                LEFT JOIN usuarios u ON c.operador_asignado = u.id
                WHERE c.estado = 'activo' AND c.operador_asignado = ?
                ORDER BY c.creado DESC";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }

    $chats = [];

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            // --- Subconsulta: último mensaje y operador que lo envió ---
            $subsql = "SELECT m.texto, m.remitente, u2.user_name as nombre_operador_ultimo_mensaje
                       FROM mensajes m
                       LEFT JOIN usuarios u2 ON m.id_usuario = u2.id
                       WHERE m.id_chat = ? 
                       ORDER BY m.timestamp DESC 
                       LIMIT 1";
            $stmt_ultimo = $conexion->prepare($subsql);
            $stmt_ultimo->bind_param("i", $row['id_chat']);
            $stmt_ultimo->execute();
            $result_ultimo = $stmt_ultimo->get_result();
            $ultimo_datos = $result_ultimo->fetch_assoc();
            $stmt_ultimo->close();

            $ultimo_mensaje = $ultimo_datos['texto'] ?? '';
            $operador_ultimo_mensaje = 'Sin mensaje';

            if ($ultimo_datos) {
                if ($ultimo_datos['remitente'] === 'operador' && !empty($ultimo_datos['nombre_operador_ultimo_mensaje'])) {
                    $operador_ultimo_mensaje = $ultimo_datos['nombre_operador_ultimo_mensaje'];
                } elseif ($ultimo_datos['remitente'] === 'mia') {
                    $operador_ultimo_mensaje = 'MIA';
                } elseif ($ultimo_datos['remitente'] === 'cliente') {
                    $operador_ultimo_mensaje = 'Cliente';
                } else {
                    $operador_ultimo_mensaje = ucfirst($ultimo_datos['remitente']);
                }
            }

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
                'no_leidos' => (int)$no_leidos,
                'mia_activa' => isset($row['mia_activa']) ? (int)$row['mia_activa'] : 1,
                'operador_asignado' => $row['nombre_operador_asignado'] ?: 'Sin asignar',
                'operador_ultimo_mensaje' => $operador_ultimo_mensaje
            ];
        }
        $result->free();

        // Cerrar statement principal si fue usado (no admin)
        if ($role !== 'admin' && isset($stmt)) {
            $stmt->close();
        }
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
