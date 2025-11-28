<?php

namespace App\Models;

use App\Utils\Database;

class BaseModel
{
    /**
     * @var \PDO La instancia de la conexión PDO.
     */
    protected $pdo;

    public function __construct()
    {
        // Usamos el singleton de Database para obtener la conexión PDO una sola vez.
        $this->pdo = Database::getInstance()->getConnection();
    }
}