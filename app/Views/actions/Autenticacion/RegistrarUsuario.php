<?php

namespace App\Views\Actions\Autenticacion;

use App\Models\User;
use App\Utils\Session;

/**
 * Action para registrar nuevos usuarios
 */
class RegistrarUsuario
{
    public function execute($userData)
    {
        // Validar campos requeridos
        $requiredFields = ['usnombre', 'usmail', 'uspass'];
        foreach ($requiredFields as $field) {
            if (empty($userData[$field])) {
                return [
                    'success' => false,
                    'message' => 'Todos los campos son requeridos'
                ];
            }
        }
        
        // Validar email
        if (!filter_var($userData['usmail'], FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'Email inválido'
            ];
        }
        
        try {
            $userModel = new User();
            
            // Verificar si el email ya existe
            if ($userModel->existsEmail($userData['usmail'])) {
                return [
                    'success' => false,
                    'message' => 'Ya existe un usuario con ese email'
                ];
            }
            
            // Crear el usuario
            $userId = $userModel->create($userData);
            
            if ($userId) {
                return [
                    'success' => true,
                    'message' => 'Usuario creado exitosamente',
                    'userId' => $userId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al crear el usuario'
                ];
            }
            
        } catch (\Exception $e) {
            error_log("[RegistrarUsuario] Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor'
            ];
        }
    }
}