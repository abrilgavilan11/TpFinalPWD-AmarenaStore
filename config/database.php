<?php
/**
 * Configuración de base de datos - Única fuente de verdad
 * Todos los modelos deben usar app/Utils/Database.php que lee este archivo
 */

return [
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'amarena_store',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
