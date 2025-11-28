<?php
/**
 * Configuración de Email para Amarena Store
 * 
 * Instrucciones de configuración:
 * 
 * 1. PARA GMAIL:
 *    - Habilita la autenticación de 2 factores en tu cuenta Gmail
 *    - Genera una "Contraseña de aplicación" en tu cuenta Google
 *    - Usa esa contraseña en SMTP_PASSWORD (no tu contraseña normal)
 * 
 * 2. PARA OTROS PROVEEDORES:
 *    - Consulta la documentación de tu proveedor de email
 *    - Cambia los valores según corresponda
 * 
 * 3. PARA DESARROLLO LOCAL (XAMPP):
 *    - Puedes usar servicios como MailHog o configurar sendmail
 *    - O usar servicios externos como SendGrid, Mailgun, etc.
 */

return [
    // Configuración principal de SMTP
    'smtp' => [
        'enabled' => true,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls', // 'tls' o 'ssl'
        'username' => 'amarenastore2244@gmail.com',
        'password' => '', // ⚠️ IMPORTANTE: Usar contraseña de aplicación para Gmail
        'timeout' => 30
    ],
    
    // Configuración del remitente
    'from' => [
        'email' => 'amarenastore2244@gmail.com',
        'name' => 'Amarena Store'
    ],
    
    // Email de destino para contactos
    'contact' => [
        'to_email' => 'amarenastore2244@gmail.com',
        'subject_prefix' => '[CONTACTO] '
    ],
    
    // Configuración para desarrollo
    'development' => [
        'log_emails' => true,
        'debug_mode' => true,
        'test_email' => 'test@amarenastore.com'
    ],
    
    // Proveedores alternativos (APIs)
    'alternatives' => [
        'sendgrid' => [
            'enabled' => false,
            'api_key' => '', // Tu API key de SendGrid
            'endpoint' => 'https://api.sendgrid.com/v3/mail/send'
        ],
        'mailgun' => [
            'enabled' => false,
            'domain' => '', // Tu dominio de Mailgun
            'api_key' => '', // Tu API key de Mailgun
            'endpoint' => 'https://api.mailgun.net/v3/'
        ]
    ]
];