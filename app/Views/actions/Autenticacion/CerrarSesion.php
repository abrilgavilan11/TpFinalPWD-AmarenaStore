<?php

namespace App\Views\Actions\Autenticacion;

use App\Utils\Session;

/**
 * Action para cerrar sesión de usuario
 */
class CerrarSesion
{
    public function execute()
    {
        Session::start();
        Session::destroy();
        
        return [
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ];
    }
}