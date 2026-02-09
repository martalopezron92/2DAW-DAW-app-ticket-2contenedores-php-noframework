<?php
declare(strict_types=1);

/**
 * Archivo de configuración principal
 * 
 * Centraliza todas las variables de configuración de la aplicación.
 * Lee las variables de entorno definidas en Docker.
 */

// ====================================
// CONFIGURACIÓN DE BASE DE DATOS
// ====================================

define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'ticketing');
define('DB_USER', getenv('DB_USER') ?: 'ticket_user');
define('DB_PASS', getenv('DB_PASS') ?: 'ticket_pass');
define('DB_CHARSET', 'utf8mb4');

// ====================================
// CONFIGURACIÓN DE SESIONES
// ====================================

// Nombre de la sesión
define('SESSION_NAME', 'TICKETING_APP');

// Tiempo de expiración de la sesión (en segundos)
// 1800 segundos = 30 minutos
define('SESSION_LIFETIME', 1800);

// ====================================
// CONFIGURACIÓN DE LA APLICACIÓN
// ====================================

// Zona horaria
date_default_timezone_set('Europe/Madrid');

// Nivel de reporte de errores
// En producción, cambiar a 0 y registrar errores en logs
if (getenv('APP_ENV') === 'production') {
    error_reporting(0);
    ini_set('display_errors', '0');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
}

// Ruta base de la aplicación
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');

// URL base (ajustar según el entorno)
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
define('BASE_URL', $protocol . '://' . $host);
