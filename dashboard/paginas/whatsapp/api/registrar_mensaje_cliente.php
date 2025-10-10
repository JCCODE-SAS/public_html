<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

require_once __DIR__ . '/../../../../configuracion/bd.php';  // tu conexión a BD
require_once __DIR__ . '/../../../../logs/logger.php';      // para logs

writeLog("registrar_mensaje_cliente.php", " INICIO - Request recibido");

$data = json_decode(file_get_contents('php://input'), true);

// Variables principales
$numero = trim($data['numero'] ?? '');
$nombre = trim($data['nombre_cliente'] ?? '');
$texto  = trim($data['texto'] ?? '');
$id_chat = intval($data['id_chat'] ?? 0);

if (!$numero || !$texto) {
    writeLog("registrar_mensaje_cliente.php", " Datos incompletos");
    echo json_encode(['ok' => false, 'error' => 'Faltan datos']);
    exit;
}

try {
    //  Verificar si el cliente ya existe
    $sql_check = "SELECT id FROM clientes WHERE numero = ?";
    $stmt = $conexion->prepare($sql_check);
    $stmt->bind_param("s", $numero);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        //  Insertar nuevo cliente
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

    // Registrar mensaje
    $sql_mensaje = "INSERT INTO mensajes (id_chat, remitente, leido, texto)
                    VALUES (?, 'cliente', 1, ?)";
    $stmt_msg = $conexion->prepare($sql_mensaje);
    $stmt_msg->bind_param("is", $id_chat, $texto);
    $stmt_msg->execute();
    $mensaje_id = $stmt_msg->insert_id;
    $stmt_msg->close();
    writeLog("registrar_mensaje_cliente.php", " Mensaje registrado ID=$mensaje_id");

    //  Actualizar saludo y catálogo en clientes
    $sql_update = "UPDATE clientes SET saludo = 1, catalogo = 1 WHERE id = ?";
    $stmt_upd = $conexion->prepare($sql_update);
    $stmt_upd->bind_param("i", $id_cliente);
    $stmt_upd->execute();
    $stmt_upd->close();
    writeLog("registrar_mensaje_cliente.php", " Estados actualizados en cliente ID=$id_cliente");

    //  Respuesta final
    echo json_encode([
        'ok' => true,
        'mensaje_id' => $mensaje_id,
        'cliente_id' => $id_cliente
    ]);

    writeLog("registrar_mensaje_cliente.php", " COMPLETADO - Todo correcto");

} catch (Exception $e) {
    writeLog("registrar_mensaje_cliente.php", " ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>