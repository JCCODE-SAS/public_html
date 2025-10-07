<?php
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';
require_once __DIR__ . '/limpiar_cache_stats.php'; // ← Limpia cache de stats

function send_json($success, $message = '')
{
    if (ob_get_length()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => $success, 'message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    writeLog("cambiar_estado_operarios.php", "POST recibido. Data: " . json_encode($data) . " | _POST: " . json_encode($_POST));

    if ($data && isset($data['id'], $data['nuevo_estado'])) {
        $id = (int)$data['id'];
        $nuevo_estado = $data['nuevo_estado'];
    } else {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
        $nuevo_estado = $_POST['disponible'] ?? null;
    }

    writeLog("cambiar_estado_operarios.php", "Procesando id: $id, nuevo_estado: $nuevo_estado");

    // CORREGIDO: compara como string
    if ($id && (strval($nuevo_estado) === '1' || strval($nuevo_estado) === '0')) {
        $stmt = $conexion->prepare("UPDATE operadores SET disponible = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $id);

        if ($stmt->execute()) {
            writeLog("cambiar_estado_operarios.php", "Disponibilidad actualizada correctamente para id: $id a $nuevo_estado");

            limpiarCacheEstadisticas();
            writeLog("cambiar_estado_operarios.php", "Cache de estadísticas limpiado");

            if ($data) {
                send_json(true, 'Disponibilidad actualizada correctamente.');
            } else {
                header("Location: /dashboard.php#operarios");
                exit;
            }
        } else {
            writeLog("cambiar_estado_operarios.php", "Error al actualizar disponibilidad: " . $stmt->error);
            if ($data) {
                send_json(false, 'Error al actualizar la disponibilidad del operario.');
            } else {
                die("❌ Error al actualizar la disponibilidad del operario.");
            }
        }
    } else {
        writeLog("cambiar_estado_operarios.php", "Datos incompletos. id: $id, nuevo_estado: $nuevo_estado");
        if ($data) {
            send_json(false, 'Datos incompletos o estado inválido.');
        } else {
            die("❌ Datos incompletos o estado inválido.");
        }
    }
} else {
    writeLog("cambiar_estado_operarios.php", "Método no permitido: " . $_SERVER['REQUEST_METHOD']);
    die("❌ Método no permitido.");
}

// Eliminar el cierre PHP y cualquier espacio/línea en blanco al final para evitar salida extra