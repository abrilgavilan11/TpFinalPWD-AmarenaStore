<?php
/**
 * Nuevo archivo: Configuración de correo electrónico
 * Usar variables de entorno para máxima seguridad
 */

return [
    /**
     * Driver de mail: 'smtp' o 'sendmail'
     */
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
    
    /**
     * Host SMTP
     * Para Gmail: smtp.gmail.com
     * Para Hotmail: smtp-mail.outlook.com
     * Para Mailtrap: smtp.mailtrap.io
     */
    'host' => getenv('MAIL_HOST') ?: 'smtp.gmail.com',
    
    /**
     * Puerto SMTP
     * 587 para TLS
     * 465 para SSL
     */
    'port' => getenv('MAIL_PORT') ?: 587,
    
    /**
     * Autenticación
     */
    'username' => getenv('MAIL_USERNAME'),
    'password' => getenv('MAIL_PASSWORD'),
    
    /**
     * Encriptación: 'tls' o 'ssl'
     */
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
    
    /**
     * Email y nombre del remitente
     */
    'from' => [
        'email' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@amarenastore.com',
        'name' => getenv('MAIL_FROM_NAME') ?: 'Amarena Store'
    ],
];
