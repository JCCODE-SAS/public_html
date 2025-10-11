<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

writeLog("registrar_mensaje_cliente.php", " INICIO - Request recibido");

// --- Leer body JSON ---
$data = json_decode(file_get_contents('php://input'), true);
$numero = trim($data['numero'] ?? '');
$nombre  = trim($data['nombre_cliente'] ?? '');
$texto   = trim($data['texto'] ?? '');
$id_chat = intval($data['id_chat'] ?? 0);

if (!$numero || !$texto) {
    writeLog("registrar_mensaje_cliente.php", " Datos incompletos: número o texto faltante");
    echo json_encode(['ok' => false, 'error' => 'Faltan datos']);
    exit;
}

try {
    //  Verificar o crear cliente
    $sql_check = "SELECT id FROM clientes WHERE numero = ?";
    $stmt = $conexion->prepare($sql_check);
    $stmt->bind_param("s", $numero);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $sql_insert = "INSERT INTO clientes (numero, nombre_cliente, saludo, catalogo)
                       VALUES (?, ?, 0, 0)";
        $stmt_ins = $conexion->prepare($sql_insert);
        $stmt_ins->bind_param("ss", $numero, $nombre);
        $stmt_ins->execute();
        $id_cliente = $stmt_ins->insert_id;
        writeLog("registrar_mensaje_cliente.php", " Cliente nuevo creado ID=$id_cliente");
        $stmt_ins->close();
    } else {
        $row = $result->fetch_assoc();
        $id_cliente = $row['id'];
        writeLog("registrar_mensaje_cliente.php", " Cliente existente ID=$id_cliente");
    }
    $stmt->close();

    //  Buscar operador disponible
    $sql_operador = "SELECT id FROM operadores WHERE disponible = 1 LIMIT 1";
    $result_op = $conexion->query($sql_operador);
    $id_operador = null;

    if ($result_op->num_rows > 0) {
        $row_op = $result_op->fetch_assoc();
        $id_operador = $row_op['id'];
        writeLog("registrar_mensaje_cliente.php", "Operador disponible ID=$id_operador");

        //  Asignar operador (tabla existente)
        // Aquí usamos un INSERT simple con fecha
        $sql_asg = "INSERT INTO asignacion_operador (ultimo_id) VALUES (?)";
        $stmt_asg = $conexion->prepare($sql_asg);
        $stmt_asg->bind_param("i", $id_operador); // guardamos solo el operador asignado
        $stmt_asg->execute();
        $stmt_asg->close();
    }

    //  Crear chat activo
    if ($id_chat === 0) {
        $sql_chat = "INSERT INTO chats (numero_cliente, operador_asignado, estado, pausado, nombre_cliente)
                     VALUES (?, ?, 'activo', 0, ?)";
        $stmt_chat = $conexion->prepare($sql_chat);
        $stmt_chat->bind_param("sis", $numero, $id_operador, $nombre);
        $stmt_chat->execute();
        $id_chat = $stmt_chat->insert_id;
        $stmt_chat->close();
    }

    // 5️⃣ Registrar mensaje del cliente
    $sql_msg = "INSERT INTO mensajes (id_chat, remitente, leido, texto)
                VALUES (?, 'cliente', 1, ?)";
    $stmt_msg = $conexion->prepare($sql_msg);
    $stmt_msg->bind_param("is", $id_chat, $texto);
    $stmt_msg->execute();
    $mensaje_id = $stmt_msg->insert_id;
    $stmt_msg->close();

    writeLog("registrar_mensaje_cliente.php", " Mensaje cliente registrado ID=$mensaje_id");

    //  Respuesta
    echo json_encode([
        'ok' => true,
        'cliente_id' => $id_cliente,
        'operador_id' => $id_operador,
        'chat_id' => $id_chat,
        'mensaje_id' => $mensaje_id
    ]);

    writeLog("registrar_mensaje_cliente.php", " COMPLETADO - Registro inicial correcto");

} catch (Exception $e) {
    writeLog("registrar_mensaje_cliente.php", " ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>