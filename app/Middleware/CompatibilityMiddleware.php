<?php

namespace App\Middleware;

use App\Utils\Session;

class CompatibilityMiddleware
{
    /**
     * Manejar redirecciones de rutas legacy a nuevas rutas basadas en permisos
     */
    public static function handleLegacyRoutes($requestUri)
    {
        // Mapeo de rutas antiguas a nuevas
        $legacyRouteMappings = [
            // Admin dashboard
            '/admin' => '/management',
            '/admin/dashboard' => '/management/dashboard',
            
            // Productos
            '/admin/productos' => '/management/products',
            '/admin/productos/nuevo' => '/management/products/create',
            '/admin/productos/crear' => '/management/products/store',
            '/admin/productos/editar' => '/management/products/edit',
            '/admin/productos/actualizar' => '/management/products/update',
            '/admin/productos/eliminar' => '/management/products/delete',
            
            // Categorías
            '/admin/categorias' => '/management/categories',
            '/admin/categorias/nuevo' => '/management/categories/create',
            '/admin/categorias/crear' => '/management/categories/store',
            '/admin/categorias/editar' => '/management/categories/edit',
            '/admin/categorias/actualizar' => '/management/categories/update',
            '/admin/categorias/eliminar' => '/management/categories/delete',
            
            // Órdenes
            '/admin/ordenes' => '/management/orders',
            '/admin/ordenes/estadisticas' => '/management/orders/statistics',
            '/admin/orden/cambiar-estado' => '/management/orders/update-status',
        ];

        // Buscar coincidencia exacta
        if (isset($legacyRouteMappings[$requestUri])) {
            return $legacyRouteMappings[$requestUri];
        }

        // Buscar coincidencias con parámetros
        foreach ($legacyRouteMappings as $legacyRoute => $newRoute) {
            // Convertir rutas con parámetros
            $legacyPattern = str_replace('{id}', '(\d+)', $legacyRoute);
            $legacyPattern = '#^' . $legacyPattern . '$#';
            
            if (preg_match($legacyPattern, $requestUri, $matches)) {
                // Reemplazar {id} en la nueva ruta con el valor capturado
                if (count($matches) > 1) {
                    return str_replace('{id}', $matches[1], $newRoute);
                }
                return $newRoute;
            }
        }

        // Si no hay mapeo, devolver la ruta original
        return $requestUri;
    }

    /**
     * Verificar si el usuario tiene permisos para acceder a rutas de gestión
     */
    public static function checkManagementAccess($requestUri)
    {
        // Si la ruta no es de gestión, permitir acceso
        if (strpos($requestUri, '/management') !== 0) {
            return true;
        }

        // Verificar si el usuario está autenticado
        if (!Session::get('user_id')) {
            // Redirigir a login con URL de retorno
            Session::flash('error', 'Debes iniciar sesión para acceder al panel de gestión.');
            header('Location: /login?return_url=' . urlencode($requestUri));
            exit;
        }

        // Verificar si el usuario tiene rol de administrador
        $userId = Session::get('user_id');
        
        try {
            // Cargar configuración de base de datos
            $dbConfig = require BASE_PATH . '/config/database.php';
            
            // Conectar a la base de datos para verificar el rol
            $pdo = new \PDO(
                "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
                $dbConfig['username'],
                $dbConfig['password'],
                $dbConfig['options']
            );
            
            // Consultar el rol del usuario
            $stmt = $pdo->prepare("
                SELECT r.idrol, r.rodescripcion 
                FROM usuario u 
                JOIN usuariorol ur ON u.idusuario = ur.idusuario 
                JOIN rol r ON ur.idrol = r.idrol 
                WHERE u.idusuario = ?
            ");
            $stmt->execute([$userId]);
            $userRole = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // Verificar si es administrador (idrol = 1)
            if (!$userRole || $userRole['idrol'] != 1) {
                Session::flash('error', 'No tienes permisos para acceder a esta sección.');
                header('Location: /');
                exit;
            }
            
            return true;
            
        } catch (\Exception $e) {
            // En caso de error de BD, denegar acceso por seguridad
            error_log('Error verificando permisos: ' . $e->getMessage());
            Session::flash('error', 'Error verificando permisos. Inténtalo de nuevo.');
            header('Location: /');
            exit;
        }
    }

    /**
     * Middleware principal que maneja compatibilidad y permisos
     */
    public static function handle($requestUri, $requestMethod = 'GET')
    {
        // 1. Manejar redirecciones de rutas legacy
        $newUri = self::handleLegacyRoutes($requestUri);
        
        // Si la URI cambió, hacer redirección permanente
        if ($newUri !== $requestUri) {
            Session::flash('info', 'Redirigido a la nueva estructura de gestión.');
            header('Location: ' . $newUri, true, 301);
            exit;
        }

        // 2. Verificar permisos para rutas de gestión
        self::checkManagementAccess($requestUri);

        return $requestUri;
    }

    /**
     * Generar enlaces compatibles automáticamente
     */
    public static function generateCompatibleLink($route, $params = [])
    {
        $reverseMapping = [
            '/management' => '/admin',
            '/management/products' => '/admin/productos',
            '/management/categories' => '/admin/categorias',
            '/management/orders' => '/admin/ordenes',
        ];

        $finalRoute = $route;
        
        // Si existe mapeo inverso, usar la ruta nueva
        if (isset($reverseMapping[$route])) {
            $finalRoute = $route;
        }

        // Reemplazar parámetros
        foreach ($params as $key => $value) {
            $finalRoute = str_replace('{' . $key . '}', $value, $finalRoute);
        }

        return $finalRoute;
    }

    /**
     * Helper para obtener enlaces del menú de navegación basados en permisos
     */
    public static function getNavigationMenu()
    {
        $menu = [];

        // Verificar si el usuario tiene permisos básicos de gestión
        if (Session::get('user_role') === 'admin') {
            $menu = [
                [
                    'title' => 'Dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'url' => '/management',
                    'permission' => 'admin_access'
                ],
                [
                    'title' => 'Productos',
                    'icon' => 'fas fa-box',
                    'url' => '/management/products',
                    'permission' => 'manage_products',
                    'submenu' => [
                        ['title' => 'Ver Todos', 'url' => '/management/products'],
                        ['title' => 'Nuevo Producto', 'url' => '/management/products/create'],
                        ['title' => 'Stock Bajo', 'url' => '/management/products?filter=low_stock']
                    ]
                ],
                [
                    'title' => 'Categorías',
                    'icon' => 'fas fa-tags',
                    'url' => '/management/categories',
                    'permission' => 'manage_categories',
                    'submenu' => [
                        ['title' => 'Ver Todas', 'url' => '/management/categories'],
                        ['title' => 'Nueva Categoría', 'url' => '/management/categories/create']
                    ]
                ],
                [
                    'title' => 'Órdenes',
                    'icon' => 'fas fa-shopping-cart',
                    'url' => '/management/orders',
                    'permission' => 'manage_orders',
                    'submenu' => [
                        ['title' => 'Todas las Órdenes', 'url' => '/management/orders'],
                        ['title' => 'Pendientes', 'url' => '/management/orders/pending'],
                        ['title' => 'Estadísticas', 'url' => '/management/orders/statistics'],
                        ['title' => 'Exportar', 'url' => '/management/orders/export']
                    ]
                ]
            ];
        }

        return $menu;
    }
}