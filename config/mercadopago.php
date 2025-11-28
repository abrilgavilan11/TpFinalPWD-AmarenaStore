<?php
/**
 * Configuración de Mercado Pago
 */

return [
    // Usa variables de entorno en producción
    'access_token' => getenv('MERCADOPAGO_ACCESS_TOKEN') ?: 'APP_USR-6915010810181427-112015-12499669a21ac630c2e3a9f062e68c0e-3005989032',
    'public_key' => getenv('MERCADOPAGO_PUBLIC_KEY') ?: 'APP_USR-eba1801c-9dc5-488d-8db8-6e41af109a66',
    'webhook_token' => getenv('MERCADOPAGO_WEBHOOK_TOKEN') ?: '71614d37ab30a4ef5abd5b97d6af4180265dc031b050792bb710ab6d0e53305c',
    // Compatibilidad: algunos entornos llaman a este valor "webhook_secret"
    'webhook_secret' => getenv('MERCADOPAGO_WEBHOOK_TOKEN') ?: '71614d37ab30a4ef5abd5b97d6af4180265dc031b050792bb710ab6d0e53305c',
];
