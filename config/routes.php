<?php
/**
 * Configuración de rutas
 * Sistema basado en permisos con controladores genéricos
 */

return [
    // Rutas públicas (sin autenticación requerida)
    'GET' => [
        '/' => 'HomeController@index',
        '/home' => 'HomeController@index',
        '/catalog' => 'ProductController@index',
        '/productos' => 'ProductController@index',
        '/producto/{id}' => 'ProductController@show',
        '/about' => 'AboutController@index',
        '/contact' => 'ContactController@index',

        // Autenticación
        '/login' => 'AuthController@loginForm',
        '/register' => 'AuthController@registerForm',

        // Carrito de compras
        '/cart' => 'CartController@index',
        '/carrito' => 'CartController@index',
        '/cart/count' => 'CartController@count',
        '/cart/contents' => 'CartController@contents',
        '/carrito/vaciar' => 'CartController@vaciar',
        
        // Flujo de checkout con QR
        '/checkout' => 'CheckoutFlowController@cart',
        '/checkout/cart' => 'CheckoutFlowController@cart',
        '/checkout/customer-data' => 'CheckoutFlowController@customerData',
        '/checkout/summary' => 'CheckoutFlowController@summary',
        '/checkout/qr-payment' => 'CheckoutFlowController@qrPayment',

        // Órdenes de usuario
        '/orders/my-orders' => 'OrderController@myOrders',
        '/mis-ordenes' => 'OrderController@myOrders',
        '/orders/{id}' => 'OrderController@myOrderDetails',
        '/orden/{id}' => 'OrderController@show',

        // Dashboard de Cliente
        '/customer/dashboard' => 'CustomerDashboardController@index',
        '/customer/orders' => 'CustomerDashboardController@orders',
        '/customer/orders/{id}' => 'CustomerDashboardController@orderDetail',
        '/customer/profile' => 'CustomerDashboardController@profile',

        // Sistema de gestión basado en permisos (reemplaza /admin)
        '/management' => 'ManagementController@index',
        '/management/dashboard' => 'ManagementController@index',
        '/management/dashboard/orders' => 'ManagementController@ordersDashboard',
        '/management/dashboard/stats' => 'ManagementController@dashboardStats',

        // Gestión de productos
        '/management/products' => 'ManagementController@products',
        '/management/products/create' => 'ManagementController@createProductForm',
        '/management/products/edit/{id}' => 'ManagementController@editProductForm',

        // Gestión de categorías
        '/management/categories' => 'ManagementController@categories',
        '/management/categories/create' => 'ManagementController@createCategoryForm',
        '/management/categories/edit/{id}' => 'ManagementController@editCategoryForm',

        // Gestión de órdenes
        '/management/orders' => 'ManagementController@orders',
        '/management/orders/show/{id}' => 'ManagementController@showOrder',
        '/management/orders/pending' => 'ManagementController@pendingOrders',
        '/management/orders/export' => 'ManagementController@exportOrdersCSV',

        // Gestión de menús (CRUD)
        '/management/menus' => 'managementMenuController@index',
        '/management/menus/create' => 'managementMenuController@create',
        '/management/menus/edit/{id}' => 'managementMenuController@edit',
        '/management/menus/delete/{id}' => 'managementMenuController@delete',

        // Gestión de clientes (CRUD)
        '/management/clientes' => 'adminClientesController@index',
        '/management/clientes/edit/{id}' => 'adminClientesController@edit',
        '/management/clientes/delete/{id}' => 'adminClientesController@delete',
        
        // Rutas de compatibilidad (redirigen a las nuevas)
        '/management' => 'ManagementController@index',
        '/management/productos' => 'ManagementController@products',
        '/management/categorias' => 'ManagementController@categories',
        '/management/ordenes' => 'OrderManagementController@index',

        // Pagos
        // '/pago/exitoso' => 'PaymentController@success',
        // '/pago/fallido' => 'PaymentController@failure',
        // '/pago/pendiente' => 'PaymentController@pending',
        '/pagar/{id}' => 'CheckoutFlowController@processPayment',
        '/pdf/descargar-comprobante/{id}' => 'PdfController@downloadReceipt',

        
    ],
    
    // Acciones POST (autenticación, formularios, APIs)
    'POST' => [
        // Gestión de menús (CRUD)
        '/management/menus/create' => 'ManagementMenuController@store',
        '/management/menus/edit/{id}' => 'ManagementMenuController@update',
        '/management/menus/delete/{id}' => 'ManagementMenuController@delete',

        // Gestión de clientes (CRUD)
        '/management/clientes/update/{id}' => 'AdminClientesController@update',

        // Autenticación
        '/login' => 'AuthController@login',
        '/register' => 'AuthController@register',
        '/logout' => 'AuthController@logout',

        // Acciones públicas
        '/contact/enviar' => 'ContactController@send',
        
        // Carrito de compras
        '/cart/agregar' => 'CartController@add',
        '/carrito/agregar' => 'CartController@add',
        '/cart/actualizar' => 'CartController@update',
        '/carrito/actualizar' => 'CartController@actualizar',
        '/cart/eliminar' => 'CartController@remove',
        '/carrito/eliminar' => 'CartController@eliminar',
        '/carrito/vaciar' => 'CartController@vaciar',
        '/cart/validate-stock' => 'CartController@validateStock',
        
        // Flujo de checkout con QR
        '/checkout/customer-data' => 'CheckoutFlowController@processCustomerData',
        '/checkout/generate-qr' => 'CheckoutFlowController@generateQR',
        '/checkout/verify-payment' => 'CheckoutFlowController@verifyPayment',
        '/checkout/verify-payment/{id}' => 'CheckoutFlowController@verifyPayment',
        '/test/verify' => 'CheckoutFlowController@testVerify',
        '/checkout/confirm-payment/{id}' => 'CheckoutFlowController@confirmPayment',
        '/checkout/payment-success/{id}' => 'CheckoutFlowController@paymentSuccess',

        '/payment/verify/{id}' => 'CheckoutFlowController@verifyPayment',
        '/pagar/{id}' => 'CheckoutFlowController@processPayment',

        // Órdenes
        '/orders/create' => 'OrderController@create',
        '/orden/crear' => 'OrderController@create',
        '/orders/cancel/{id}' => 'OrderController@cancel',
        '/orden/cancelar' => 'OrderController@cancel',

        // Verificación de stock (público)
        '/verificar-stock' => 'ProductController@checkStock',

        // Dashboard de Cliente - Acciones
        '/customer/profile/update' => 'CustomerDashboardController@updateProfile',
        '/customer/profile/change-password' => 'CustomerDashboardController@changePassword',
        '/customer/profile/request-password-reset' => 'CustomerDashboardController@requestPasswordReset',

        // Gestión de productos (basado en permisos)
        '/management/products/store' => 'ManagementController@storeProduct',
        '/management/products/update/{id}' => 'ManagementController@updateProduct',
        '/management/products/check-stock' => 'ManagementController@checkStock',
        '/management/products/update-stock' => 'ManagementController@updateStock',
        '/management/products/low-stock' => 'ManagementController@lowStockProducts',
        '/management/products/delete/{id}' => 'ManagementController@deleteProduct',

        // Gestión de categorías (basado en permisos)
        '/management/categories/store' => 'ManagementController@storeCategory',
        '/management/categories/update/{id}' => 'ManagementController@updateCategory',
        '/management/categories/toggle-status/{id}' => 'ManagementController@toggleCategoryStatus',
        '/management/categories/delete/{id}' => 'ManagementController@deleteCategory',

        // Gestión de órdenes (basado en permisos)
        '/management/orders/update-status/{id}' => 'ManagementController@updateOrderStatus',
        '/management/orders/delete/{id}' => 'ManagementController@deleteOrder',
        '/management/orders/bulk-update' => 'ManagementController@bulkUpdateOrders',
        '/management/orders/summary' => 'ManagementController@ordersSummary',
        '/management/orders/statistics/{period}' => 'ManagementController@ordersStatistics',
        '/management/orders/search' => 'ManagementController@searchOrders',

        // Pagos
        // '/pago/iniciar' => 'PaymentController@initiate',
        // '/pago/webhook' => 'PaymentController@webhook',
    ],

    // Métodos DELETE
    'DELETE' => [
        '/management/products/delete/{id}' => 'ManagementController@deleteProduct',
        '/management/categories/delete/{id}' => 'ManagementController@deleteCategory',
        '/management/orders/delete/{id}' => 'ManagementController@deleteOrder',
    ],

    // Métodos PUT/PATCH para actualizaciones
    'PUT' => [
        '/management/products/{id}' => 'ManagementController@updateProduct',
        '/management/categories/{id}' => 'ManagementController@updateCategory',
    ],
];
