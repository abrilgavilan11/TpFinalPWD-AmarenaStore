<?php

namespace App\Views\Actions;

class ContactAction
{
    /**
     * Prepara datos para la página de contacto
     */
    public function prepareContactPage()
    {
        return [
            'title' => 'Contacto - Amarena Store',
            'pageCss' => 'contact'
        ];
    }

    /**
     * Valida y procesa el envío de un mensaje de contacto
     */
    public function sendMessage($name, $lastname, $email, $phone, $message)
    {
        $name = trim($name);
        $lastname = trim($lastname);
        $email = trim($email);
        $phone = trim($phone);

        if (empty($name) || empty($lastname) || empty($email) || empty($phone)) {
            return [
                'success' => false,
                'message' => 'Por favor completa todos los campos obligatorios.'
            ];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'success' => false,
                'message' => 'El formato del email no es válido.'
            ];
        }

        return [
            'success' => true,
            'message' => '¡Mensaje enviado correctamente! Te contactaremos pronto.'
        ];
    }
}
