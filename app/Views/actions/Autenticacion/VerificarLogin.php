<?php

namespace App\Views\Actions\Autenticacion;

use App\Models\User;
use App\Utils\Session;

/**
 * Action específico para verificar el login de usuario
 */
class VerificarLogin
{
    /**
     * Realiza verificación de login de un usuario
     */
    public function execute($email, $password)
    {
        $email = trim($email);
        $password = trim($password);

        if (empty($email) || empty($password)) {
            return [
                'success' => false,
                'message' => 'Email y contraseña son requeridos.'
            ];
        }

        $userModel = new User();
        $user = $userModel->authenticate($email, $password);

        if ($user) {
            Session::set('user_id', $user['idusuario']);
            Session::set('user_name', $user['usnombre']);
            Session::set('user_email', $user['usmail']);
            
            $role = $userModel->getRole($user['idusuario']);
            Session::set('user_role', $role);

            return [
                'success' => true,
                'message' => 'Ha ingresado correctamente',
                'role' => $role,
                'redirect' => $role === 'Administrador' ? '/admin' : '/'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Credenciales incorrectas.'
            ];
        }
    }
}