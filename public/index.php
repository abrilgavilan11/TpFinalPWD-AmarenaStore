<?php
/**
 * Punto de entrada de la aplicación
 * Este archivo actúa como un "Controlador Frontal" (Front Controller).
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar el autoloader de Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';
// Cargar el archivo de configuración principal
require_once dirname(__DIR__) . '/config/config.php';

use App\Utils\Session;
use App\Middleware\CompatibilityMiddleware;
Session::init(); // CRÍTICO: Sin esto, el carrito no funciona

// --- 1. OBTENER LA RUTA SOLICITADA ---
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Limpiar la URI para que funcione en subdirectorios
$basePath = dirname($_SERVER['SCRIPT_NAME']);
$basePath = str_replace('\\', '/', $basePath); // Normalizar para Windows
$basePath = rtrim($basePath, '/');

// Limpiamos la URL para obtener solo la parte de la ruta (ej. /catalog, /producto/12)
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestUri = substr($requestUri, strlen($basePath));
$requestUri = rtrim($requestUri, '/') ?: '/';

// --- 2. PROCESAR COMPATIBILIDAD Y PERMISOS ---
$requestUri = CompatibilityMiddleware::handle($requestUri, $requestMethod);

// --- 3. CARGAR LAS RUTAS DEFINIDAS ---
$routes = require BASE_PATH . '/config/routes.php';

// --- 4. BUSCAR Y MANEJAR LA RUTA ---
$routeFound = false;

// Función de ayuda para manejar la lógica de una ruta encontrada.
// Sigue el principio de "un solo retorno".
function handleRoute($handler, $params = []) {
    $wasHandled = false; // Variable para guardar el resultado.

    list($controllerName, $method) = explode('@', $handler);
    $controllerClass = "App\\Controllers\\{$controllerName}";

    if (class_exists($controllerClass)) {
        $controller = new $controllerClass();
        if (method_exists($controller, $method)) {
            // Llama al método del controlador, pasando los parámetros.
            call_user_func_array([$controller, $method], $params);
            $wasHandled = true; // La ruta se manejó con éxito.
        }
    }

    return $wasHandled; // Único punto de salida de la función.
}

// Antes de buscar la ruta, vamos a verificar si el controlador del carrito existe
if (!class_exists('App\\Controllers\\CartController')) {
    die("ERROR CRÍTICO: La clase 'App\\Controllers\\CartController' no se encuentra. Revisa el nombre del archivo y el namespace en 'app/Controllers/CartController.php'.");
}

// Primero, buscamos rutas estáticas (sin parámetros como {id}), que son las más comunes.
if (isset($routes[$requestMethod][$requestUri])) {
    $handler = $routes[$requestMethod][$requestUri];
    $routeFound = handleRoute($handler);
}

// Si no se encontró una ruta estática, buscamos rutas dinámicas
if (!$routeFound) {
    foreach ($routes[$requestMethod] ?? [] as $route => $handler) {
        // Solo intentamos procesar si aún no hemos encontrado una ruta.
        // Esto elimina la necesidad de usar 'break'.
        if (!$routeFound) {
            // Convertir la ruta en una expresión regular
            $pattern = '#^' . preg_replace('/\{[^}]+\}/', '([^/]+)', $route) . '$#';
            
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Quita la coincidencia completa, deja solo los parámetros.
                $routeFound = handleRoute($handler, $matches);
            }
        }
    }
}

// --- 5. MANEJAR ERROR 404 ---
if (!$routeFound) {
    http_response_code(404);
    // Podríamos tener una vista bonita para el 404, por ahora un mensaje simple.
    echo "Error 404: Página no encontrada.";
}
