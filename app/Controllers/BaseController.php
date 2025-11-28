<?php

namespace App\Controllers;

class BaseController
{
    /**
     * Carga una vista y le pasa datos.
     *
     * @param string $view El nombre de la vista.
     * @param array $data Los datos que estarán disponibles en la vista.
     */
    protected function view($view, $data = [])
    {
        // Convierte la notación de puntos en una ruta de archivo real.
        $viewFile = VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
        
        // Verifica si el archivo de la vista realmente existe.
        if (file_exists($viewFile)) {
            // Si existe, lo incluye. El array $data estará disponible dentro de la vista.
            require $viewFile;
        } else {
            // Si no existe, detiene la aplicación y muestra un error claro.
            die("Error: La vista '{$view}' no fue encontrada en la ruta: {$viewFile}");
        }
    }

    /**
     * Redirige al usuario a una nueva URL.
     *
     * @param string $url La URL a la que se va a redirigir.
     */
    protected function redirect($url)
    {
        // Envía el encabezado HTTP para la redirección.
        header("Location: {$url}");
        // Detiene la ejecución del script para asegurar que la redirección ocurra inmediatamente.
        exit;
    }

    /**
     * Envía una respuesta en formato JSON. Útil para APIs o peticiones AJAX.
     *
     * @param mixed $data Los datos a convertir en JSON.
     * @param int $statusCode El código de estado HTTP.
     */
    protected function json($data, $statusCode = 200)
    {
        // Limpiar cualquier salida previa
        if (ob_get_level()) {
            ob_clean();
        }
        
        // Verificar si los headers ya fueron enviados
        if (!headers_sent()) {
            // Establece el código de estado HTTP adecuado.
            http_response_code($statusCode);
            
            // Headers CORS para evitar problemas de origen cruzado
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type, Authorization');
            
            // Indica al navegador que la respuesta es de tipo JSON.
            header('Content-Type: application/json');
        }
        
        // Convierte el array de PHP a una cadena de texto JSON y lo imprime.
        echo json_encode($data);
        // Detiene la ejecución del script.
        exit;
    }
}
