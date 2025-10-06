<?php
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';
require_once __DIR__ . '/limpiar_cache_stats.php'; // ← AÑADIR ESTA LÍNEA

function send_json($success, $message = '')
{
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    writeLog("cambiar_estado_usuario.php", "POST recibido. Data: " . json_encode($data) . " | _POST: " . json_encode($_POST));

    if ($data && isset($data['id'], $data['nuevo_estado'])) {
        $id = $data['id'];
        $nuevo_estado = $data['nuevo_estado'];
    } else {
        $id = $_POST['id'] ?? null;
        $nuevo_estado = $_POST['status'] ?? null;
    }

    writeLog("cambiar_estado_usuario.php", "Procesando id: $id, nuevo_estado: $nuevo_estado");

    if ($id && $nuevo_estado) {
        $stmt = $conexion->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $id);

        if ($stmt->execute()) {
            writeLog("cambiar_estado_usuario.php", "Estado actualizado correctamente para id: $id a $nuevo_estado");

            // ✅ LIMPIAR CACHE DE ESTADÍSTICAS
            limpiarCacheEstadisticas();
            writeLog("cambiar_estado_usuario.php", "Cache de estadísticas limpiado");

            if ($data) {
                send_json(true, 'Estado actualizado correctamente.');
            } else {
                header("Location: /public_html/dashboard/dashboard.php#usuarios");
                exit;
            }
        } else {
            writeLog("cambiar_estado_usuario.php", "Error al actualizar estado: " . $stmt->error);
            if ($data) {
                send_json(false, 'Error al actualizar el estado del usuario.');
            } else {
                die("❌ Error al actualizar el estado del usuario.");
            }
        }
    } else {
        writeLog("cambiar_estado_usuario.php", "Datos incompletos. id: $id, nuevo_estado: $nuevo_estado");
        if ($data) {
            send_json(false, 'Datos incompletos.');
        } else {
            die("❌ Datos incompletos.");
        }
    }
} else {
    writeLog("cambiar_estado_usuario.php", "Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    die("❌ Método no permitido.");
}