<?php
//===============================================================
// CREAR_USUARIO.PHP - API de Creación de Usuarios COPFLOW
//===============================================================
// 
// PROPÓSITO:
// API endpoint para crear nuevos usuarios con validación completa,
// encriptación de contraseñas y manejo de errores robusto.
// 
// DESARROLLADO POR: Diomedez98 (JCCODE-SAS)
// FECHA CREACIÓN: 2025-09-10
// REPOSITORIO: https://github.com/Diomedez98/copflows
// EMPRESA: JCCODE-SAS
//===============================================================

// Función auxiliar para respuestas JSON
function send_json($payload = [], $code = 200)
{
    if (ob_get_length()) {
        @ob_end_clean();
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// Solo permitir método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['success' => false, 'message' => 'Método no permitido'], 405);
}

// Incluir archivos necesarios
require_once __DIR__ . '/../../../../configuracion/bd.php';
require_once __DIR__ . '/../../../../logs/logger.php';

// Iniciar sesión para verificar autenticación
session_start();

// Verificar autenticación del usuario
if (!isset($_SESSION['user_id'])) {
    send_json(['success' => false, 'message' => 'Usuario no autenticado'], 401);
}

try {
    //===============================================================
    // RECIBIR Y VALIDAR DATOS DE ENTRADA
    //===============================================================

    // Obtener datos JSON del cuerpo de la petición
    $input = file_get_contents('php://input');
    $datos = json_decode($input, true);

    // Verificar que se recibieron datos válidos
    if (!$datos) {
        throw new Exception('Datos JSON inválidos o vacíos');
    }

    // Log de inicio de creación
    writeLog("crear_usuario", "Intento de creación de usuario por user_id: " . $_SESSION['user_id']);

    //===============================================================
    // VALIDACIÓN DE CAMPOS REQUERIDOS
    //===============================================================

    $camposRequeridos = ['nombre', 'email', 'password', 'rol', 'estado'];
    $camposFaltantes = [];

    foreach ($camposRequeridos as $campo) {
        if (!isset($datos[$campo]) || empty(trim($datos[$campo]))) {
            $camposFaltantes[] = $campo;
        }
    }

    if (!empty($camposFaltantes)) {
        throw new Exception('Campos requeridos faltantes: ' . implode(', ', $camposFaltantes));
    }

    // Sanitizar datos de entrada
    $nombre = trim($datos['nombre']);
    $email = trim(strtolower($datos['email']));
    $password = $datos['password'];
    $rol = trim($datos['rol']);
    $estado = trim($datos['estado']);

    //===============================================================
    // VALIDACIONES DE FORMATO Y CONTENIDO
    //===============================================================

    // Validar nombre
    if (strlen($nombre) < 2) {
        throw new Exception('El nombre debe tener al menos 2 caracteres');
    }

    if (strlen($nombre) > 100) {
        throw new Exception('El nombre no puede exceder 100 caracteres');
    }

    if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $nombre)) {
        throw new Exception('El nombre solo puede contener letras y espacios');
    }

    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Formato de email inválido');
    }

    if (strlen($email) > 255) {
        throw new Exception('El email no puede exceder 255 caracteres');
    }

    // Validar contraseña
    if (strlen($password) < 8) {
        throw new Exception('La contraseña debe tener al menos 8 caracteres');
    }

    if (strlen($password) > 255) {
        throw new Exception('La contraseña no puede exceder 255 caracteres');
    }

    // Validar fortaleza de contraseña
    $fortaleza = calcularFortalezaPassword($password);
    if ($fortaleza < 3) {
        throw new Exception('La contraseña es demasiado débil. Debe incluir mayúsculas, minúsculas y números');
    }

    // Validar rol
    $rolesPermitidos = ['user', 'admin'];
    if (!in_array($rol, $rolesPermitidos)) {
        throw new Exception('Rol inválido. Roles permitidos: ' . implode(', ', $rolesPermitidos));
    }

    // Validar estado
    $estadosPermitidos = ['activo', 'inactivo'];
    if (!in_array($estado, $estadosPermitidos)) {
        throw new Exception('Estado inválido. Estados permitidos: ' . implode(', ', $estadosPermitidos));
    }

    //===============================================================
    // VERIFICAR DISPONIBILIDAD DE EMAIL
    //===============================================================

    $sqlVerificar = "SELECT id FROM users WHERE email = ?";
    $stmtVerificar = $conexion->prepare($sqlVerificar);

    if (!$stmtVerificar) {
        throw new Exception('Error en la consulta de verificación: ' . $conexion->error);
    }

    $stmtVerificar->bind_param('s', $email);
    $stmtVerificar->execute();
    $resultado = $stmtVerificar->get_result();

    if ($resultado->num_rows > 0) {
        throw new Exception('El email ya está registrado en el sistema');
    }

    $stmtVerificar->close();

    //===============================================================
    // ENCRIPTAR CONTRASEÑA
    //===============================================================

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if (!$passwordHash) {
        throw new Exception('Error al encriptar la contraseña');
    }

    //===============================================================
    // INSERTAR NUEVO USUARIO
    //===============================================================

    $sqlInsertar = "INSERT INTO users (name, email, password, role, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

    $stmtInsertar = $conexion->prepare($sqlInsertar);

    if (!$stmtInsertar) {
        throw new Exception('Error en la consulta de inserción: ' . $conexion->error);
    }

    $stmtInsertar->bind_param('sssss', $nombre, $email, $passwordHash, $rol, $estado);

    if (!$stmtInsertar->execute()) {
        throw new Exception('Error al insertar usuario: ' . $stmtInsertar->error);
    }

    // Obtener ID del usuario creado
    $usuarioId = $conexion->insert_id;

    $stmtInsertar->close();

    //===============================================================
    // OBTENER DATOS DEL USUARIO CREADO
    //===============================================================

    $sqlObtener = "SELECT id, name, email, role, status, 
                          DATE_FORMAT(created_at, '%d/%m/%Y %H:%i') as created_at_formatted,
                          created_at as created_at_raw
                   FROM users WHERE id = ?";

    $stmtObtener = $conexion->prepare($sqlObtener);
    $stmtObtener->bind_param('i', $usuarioId);
    $stmtObtener->execute();
    $resultadoUsuario = $stmtObtener->get_result();
    $usuarioCreado = $resultadoUsuario->fetch_assoc();

    $stmtObtener->close();

    //===============================================================
    // LOG DE ÉXITO Y RESPUESTA
    //===============================================================

    writeLog("crear_usuario", "Usuario creado exitosamente - ID: $usuarioId, Email: $email, Creado por: " . $_SESSION['user_id']);

    // Respuesta exitosa
    send_json([
        'success' => true,
        'message' => 'Usuario creado exitosamente',
        'data' => [
            'usuario' => $usuarioCreado,
            'mensaje' => "El usuario '$nombre' ha sido creado con éxito",
            'timestamp' => date('Y-m-d H:i:s')
        ]
    ]);
} catch (Exception $e) {
    //===============================================================
    // MANEJO DE ERRORES
    //===============================================================

    // Log del error
    writeLog("crear_usuario_error", "Error al crear usuario: " . $e->getMessage() . " - User ID: " . ($_SESSION['user_id'] ?? 'No autenticado'));

    // Determinar código de estado HTTP
    $httpCode = 400; // Bad Request por defecto

    if (strpos($e->getMessage(), 'no autenticado') !== false) {
        $httpCode = 401; // Unauthorized
    } elseif (strpos($e->getMessage(), 'ya está registrado') !== false) {
        $httpCode = 409; // Conflict
    } elseif (
        strpos($e->getMessage(), 'Error en la consulta') !== false ||
        strpos($e->getMessage(), 'Error al insertar') !== false
    ) {
        $httpCode = 500; // Internal Server Error
    }

    // Respuesta de error
    send_json([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => $httpCode,
        'timestamp' => date('Y-m-d H:i:s')
    ], $httpCode);
} finally {
    //===============================================================
    // LIMPIEZA DE RECURSOS
    //===============================================================

    // Cerrar conexión a la base de datos
    if (isset($conexion)) {
        $conexion->close();
    }
}

//===============================================================
// FUNCIONES AUXILIARES
//===============================================================

/**
 * Calcular fortaleza de contraseña
 * @param string $password Contraseña a evaluar
 * @return int Puntuación de fortaleza (0-6)
 */
function calcularFortalezaPassword($password)
{
    $score = 0;

    // Longitud
    if (strlen($password) >= 8) $score++;
    if (strlen($password) >= 12) $score++;

    // Tipos de caracteres
    if (preg_match('/[a-z]/', $password)) $score++; // Minúsculas
    if (preg_match('/[A-Z]/', $password)) $score++; // Mayúsculas
    if (preg_match('/[0-9]/', $password)) $score++; // Números
    if (preg_match('/[^A-Za-z0-9]/', $password)) $score++; // Caracteres especiales

    return $score;
}

//===============================================================
// FIN DEL ARCHIVO CREAR_USUARIO.PHP
//===============================================================