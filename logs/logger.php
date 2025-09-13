<?php

/**
 * ===============================================================
 * 📄 SISTEMA DE LOGGING CENTRALIZADO - COPFLOW v3.0
 * ===============================================================
 * 
 * 🎯 PROPÓSITO:
 * Centraliza el sistema de logs en archivos .log diarios organizados
 * por fecha para facilitar el debugging y monitoreo del sistema.
 * 
 * 👨‍💻 DESARROLLADO POR: Diomedez98
 * 📅 FECHA CREACIÓN: 2025-01-09
 * 🔗 REPOSITORIO: https://github.com/Diomedez98/copflows
 * 🏢 EMPRESA: JCCODE-SAS
 * 
 * ===============================================================
 */

/**
 * 📝 FUNCIÓN PRINCIPAL DE LOGGING - MEJORADA
 * 
 * Escribe un mensaje en el archivo de log diario correspondiente.
 * Crea automáticamente el directorio y archivo si no existen.
 * 
 * @param string $archivo - Nombre del archivo que genera el log (ej: "usuarios.php")
 * @param string $mensaje - Mensaje a registrar en el log
 * @param string $nivel   - Nivel de log: INFO, ERROR, DEBUG, WARNING (opcional)
 * 
 * @return bool - true si se escribió correctamente, false en caso de error
 */
function writeLog($archivo, $mensaje, $nivel = "INFO")
{
    try {
        // 📁 Configuración de directorios - CORREGIDO
        $baseDir = dirname(__DIR__); // Un nivel arriba del directorio logs
        $logDir = $baseDir . '/logs/files'; // Subcarpeta dedicada para archivos .log
        $fechaHoy = date("Y-m-d");
        $logFile = $logDir . "/" . $fechaHoy . ".log";

        // 🔒 Crear directorio si no existe (con permisos seguros)
        if (!is_dir($logDir)) {
            if (!mkdir($logDir, 0755, true)) {
                // 🚨 Fallback: usar directorio temporal si no se puede crear
                $logDir = sys_get_temp_dir() . '/copflow_logs';
                if (!is_dir($logDir)) {
                    mkdir($logDir, 0755, true);
                }
                $logFile = $logDir . "/" . $fechaHoy . ".log";
            }
        }

        // ⏰ Timestamp completo con milisegundos
        $timestamp = date("H:i:s");
        $microseconds = sprintf("%03d", (microtime(true) - floor(microtime(true))) * 1000);
        $fullTimestamp = $timestamp . "." . $microseconds;

        // 🎯 Formatear mensaje con contexto
        $nivelFormatted = str_pad($nivel, 7, " ", STR_PAD_RIGHT);
        $archivoFormatted = str_pad($archivo, 20, " ", STR_PAD_RIGHT);

        // 📝 Construir entrada del log
        $entry = sprintf(
            "[%s] [%s] [%s] %s%s",
            $fullTimestamp,
            $nivelFormatted,
            $archivoFormatted,
            $mensaje,
            PHP_EOL
        );

        // 💾 Escribir al archivo (con bloqueo para concurrencia)
        $result = file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);

        // 🔒 Establecer permisos seguros del archivo
        if (file_exists($logFile)) {
            chmod($logFile, 0644);
        }

        return $result !== false;
    } catch (Exception $e) {
        // 🚨 Fallback: escribir error en error_log de PHP
        error_log("CopFlow Logger Error: " . $e->getMessage());
        return false;
    }
}

/**
 * 🆘 FUNCIÓN DE LOGGING DE ERRORES
 */
function writeError($archivo, $error, $context = [])
{
    $mensaje = "❌ ERROR: " . $error;

    if (!empty($context)) {
        $mensaje .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    return writeLog($archivo, $mensaje, "ERROR");
}

/**
 * 🐛 FUNCIÓN DE LOGGING DE DEBUG
 */
function writeDebug($archivo, $mensaje, $data = null)
{
    $logMessage = "🐛 DEBUG: " . $mensaje;

    if ($data !== null) {
        $logMessage .= " | Data: " . json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    return writeLog($archivo, $logMessage, "DEBUG");
}

/**
 * ✅ FUNCIÓN DE LOGGING DE ÉXITO
 */
function writeSuccess($archivo, $mensaje, $details = [])
{
    $logMessage = "✅ SUCCESS: " . $mensaje;

    if (!empty($details)) {
        $logMessage .= " | Details: " . json_encode($details, JSON_UNESCAPED_UNICODE);
    }

    return writeLog($archivo, $logMessage, "SUCCESS");
}

/**
 * ⚠️ FUNCIÓN DE LOGGING DE ADVERTENCIAS
 */
function writeWarning($archivo, $mensaje)
{
    return writeLog($archivo, "⚠️ WARNING: " . $mensaje, "WARNING");
}

/**
 * 📊 FUNCIÓN DE LOGGING DE ESTADÍSTICAS
 */
function writeStats($metrica, $valor, $context = [])
{
    $mensaje = "📊 STATS: {$metrica} = {$valor}";

    if (!empty($context)) {
        $mensaje .= " | Context: " . json_encode($context, JSON_UNESCAPED_UNICODE);
    }

    return writeLog("stats.system", $mensaje, "STATS");
}

/**
 * 🔐 FUNCIÓN DE LOGGING DE SEGURIDAD
 */
function writeSecurity($evento, $mensaje, $details = [])
{
    $logMessage = "🔐 SECURITY [{$evento}]: " . $mensaje;

    // Agregar IP automáticamente
    $details['ip'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $details['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $details['timestamp'] = date('Y-m-d H:i:s');

    $logMessage .= " | Details: " . json_encode($details, JSON_UNESCAPED_UNICODE);

    return writeLog("security.log", $logMessage, "SECURITY");
}

/**
 * 🧹 FUNCIÓN DE LIMPIEZA DE LOGS ANTIGUOS - ACTUALIZADA
 */
function cleanOldLogs($diasRetener = 30)
{
    // 📁 Usar el directorio correcto
    $baseDir = dirname(__DIR__);
    $logDir = $baseDir . '/logs/files';
    $archivosEliminados = 0;
    $fechaLimite = time() - ($diasRetener * 24 * 60 * 60);

    try {
        $files = glob($logDir . "/*.log");

        foreach ($files as $file) {
            if (filemtime($file) < $fechaLimite) {
                if (unlink($file)) {
                    $archivosEliminados++;
                    writeLog("logger.cleanup", "Archivo log eliminado: " . basename($file));
                }
            }
        }

        if ($archivosEliminados > 0) {
            writeLog("logger.cleanup", "Limpieza completada: {$archivosEliminados} archivos eliminados");
        }
    } catch (Exception $e) {
        writeError("logger.cleanup", "Error en limpieza de logs: " . $e->getMessage());
    }

    return $archivosEliminados;
}

/**
 * 📈 FUNCIÓN DE ANÁLISIS DE LOGS - ACTUALIZADA
 */
function analyzeLog($fecha = null)
{
    $fecha = $fecha ?: date("Y-m-d");
    $baseDir = dirname(__DIR__);
    $logFile = $baseDir . '/logs/files/' . $fecha . ".log";

    $stats = [
        'total_entries' => 0,
        'by_level' => [
            'INFO' => 0,
            'ERROR' => 0,
            'DEBUG' => 0,
            'WARNING' => 0,
            'SUCCESS' => 0,
            'SECURITY' => 0,
            'STATS' => 0
        ],
        'by_file' => [],
        'file_size' => 0,
        'fecha' => $fecha
    ];

    if (!file_exists($logFile)) {
        return $stats;
    }

    $stats['file_size'] = filesize($logFile);
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $stats['total_entries']++;

        // Extraer nivel
        if (preg_match('/\[([A-Z]+)\s*\]/', $line, $matches)) {
            $nivel = trim($matches[1]);
            if (isset($stats['by_level'][$nivel])) {
                $stats['by_level'][$nivel]++;
            }
        }

        // Extraer archivo
        if (preg_match('/\[([A-Z]+\s*)\]\s*\[([^\]]+)\]/', $line, $matches)) {
            $archivo = trim($matches[2]);
            $stats['by_file'][$archivo] = ($stats['by_file'][$archivo] ?? 0) + 1;
        }
    }

    return $stats;
}

// ===============================================================
// 🔧 CONFIGURACIÓN Y CONSTANTES
// ===============================================================

// Definir constantes para niveles de log
define('LOG_LEVEL_INFO', 'INFO');
define('LOG_LEVEL_ERROR', 'ERROR');
define('LOG_LEVEL_DEBUG', 'DEBUG');
define('LOG_LEVEL_WARNING', 'WARNING');
define('LOG_LEVEL_SUCCESS', 'SUCCESS');
define('LOG_LEVEL_SECURITY', 'SECURITY');
define('LOG_LEVEL_STATS', 'STATS');

// ===============================================================
// 🚀 INICIALIZACIÓN DEL SISTEMA
// ===============================================================

// Log de inicialización del sistema de logging
if (!defined('LOGGER_INITIALIZED')) {
    define('LOGGER_INITIALIZED', true);
    writeLog("logger.system", "🚀 Sistema de logging CopFlow v3.0 inicializado - by Diomedez98", "SYSTEM");
}

/**
 * ===============================================================
 * 📋 ESTRUCTURA DE ARCHIVOS MEJORADA:
 * ===============================================================
 * 
 * /public_html/
 * ├── logs/
 * │   ├── files/              ← Aquí se guardan los .log
 * │   │   ├── 2025-09-09.log
 * │   │   ├── 2025-09-10.log
 * │   │   └── ...
 * │   └── logger.php          ← Este archivo
 * └── dashboard/
 *     └── paginas/
 *         └── usuarios/
 *             └── api/
 *                 └── estadisticas_usuarios.php
 * 
 * ===============================================================
 */