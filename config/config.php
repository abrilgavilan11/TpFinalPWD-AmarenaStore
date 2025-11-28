<?php
/**
 * Configuración general de la aplicación
 */

// Configuración de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zona horaria
date_default_timezone_set('America/Argentina/Buenos_Aires');

// Rutas base
define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');
define('PUBLIC_PATH', BASE_PATH . '/public');
define('VIEWS_PATH', APP_PATH . '/Views');
define('CONFIG_PATH', BASE_PATH . '/config'); // Agregar ruta CONFIG_PATH

// URL base dinámica
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Detectar si estamos usando el servidor integrado de PHP
if (strpos($host, ':') !== false) {
    // Servidor integrado PHP (php -S localhost:8000)
    // Si estamos en una subcarpeta como amarena, agregarla a la URL base
    $currentDir = basename(dirname($_SERVER['SCRIPT_FILENAME'] ?? __DIR__));
    if ($currentDir === 'amarena' || strpos($_SERVER['REQUEST_URI'] ?? '', '/amarena') !== false) {
        $baseUrl = $protocol . $host . '/amarena';
    } else {
        $baseUrl = $protocol . $host;
    }
} else {
    // Apache normal o servidor con subdirectorios
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $basePath = dirname($scriptName);
    $basePath = str_replace('\\', '/', $basePath); // Normalizar para Windows
    $basePath = rtrim($basePath, '/');
    
    if ($basePath && $basePath !== '/') {
        $baseUrl = $protocol . $host . $basePath;
    } else {
        $baseUrl = $protocol . $host;
    }
}

define('BASE_URL', $baseUrl);

// Cargar autoloader de Composer primero
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

use App\Utils\Session;
// Configuración de sesión
Session::init();
