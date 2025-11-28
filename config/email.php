<?php
/**
 * Configuración de Email para Amarena Store
 */

return [
    // Configuración principal de SMTP
    'smtp' => [
        'enabled' => true,
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'encryption' => 'tls', 
        'username' => 'amarenastore2244@gmail.com',
        'password' => '',
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