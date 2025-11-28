<?php

return;

// Parámetros para la conexión a la base de datos
$servidor = "localhost";
$usuario_db = "root";
$password_db = "";
$nombre_db = "amarena_store"; 

// Crear una nueva conexión a la base de datos usando la extensión MySQLi
$conexion = new mysqli($servidor, $usuario_db, $password_db, $nombre_db);

// Establecer el juego de caracteres a UTF-8 para soportar tildes y caracteres especiales
if (!$conexion->set_charset("utf8mb4")) {
    printf("Error al cargar el conjunto de caracteres utf8mb4: %s\n", $conexion->error);
    exit();
}

// Verificar si la conexión tuvo errores
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
