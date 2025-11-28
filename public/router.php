<?php
/**
 * Router para el servidor PHP incorporado
 * Uso: php -S localhost:8000 -t public router.php
 */

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Log para debugging
error_log("Router: Requested URI = $requestUri");

// Si es un archivo estático existente, servirlo directamente
if ($requestUri !== '/' && file_exists(__DIR__ . $requestUri)) {
    error_log("Router: Serving static file: $requestUri");
    return false; // Servir el archivo estático
}

// Para todas las demás rutas, redirigir a index.php
error_log("Router: Redirecting to index.php for: $requestUri");

// Asegurar que el PATH_INFO esté configurado correctamente
$_SERVER['PATH_INFO'] = $requestUri;
$_SERVER['SCRIPT_NAME'] = '/index.php'; // Importante para el routing interno

require __DIR__ . '/index.php';