<?php

namespace App\Middleware;

use App\Utils\PermissionManager;
use App\Utils\Session;
use App\Utils\Auth;

class PermissionMiddleware
{
    /**
     * Middleware que requiere un permiso específico
     */
    public static function requirePermission(string $permission): void
    {
        PermissionManager::requirePermission($permission);
    }

    /**
     * Middleware que requiere cualquiera de los permisos dados
     */
    public static function requireAnyPermission(array $permissions): void
    {
        if (!PermissionManager::hasAnyPermission($permissions)) {
            self::handleUnauthorized('Requiere uno de: ' . implode(', ', $permissions));
        }
    }

    /**
     * Middleware que requiere todos los permisos dados
     */
    public static function requireAllPermissions(array $permissions): void
    {
        if (!PermissionManager::hasAllPermissions($permissions)) {
            self::handleUnauthorized('Requiere todos: ' . implode(', ', $permissions));
        }
    }

    /**
     * Middleware para acceso de administrador (compatibilidad)
     */
    public static function requireAdmin(): void
    {
        self::requirePermission(PermissionManager::ADMIN_ACCESS);
    }

    /**
     * Middleware que verifica si el usuario está autenticado
     */
    public static function requireAuthenticated(): void
    {
        if (!Auth::isLoggedIn()) {
            Session::flash('error', 'Debes iniciar sesión para acceder a esta sección.');
            header('Location: /?login_required=1');
            exit;
        }
    }

    /**
     * Middleware para operaciones específicas de productos
     */
    public static function requireProductManagement(): void
    {
        self::requirePermission(PermissionManager::MANAGE_PRODUCTS);
    }

    /**
     * Middleware para operaciones específicas de órdenes
     */
    public static function requireOrderManagement(): void
    {
        self::requirePermission(PermissionManager::MANAGE_ORDERS);
    }

    /**
     * Middleware para operaciones específicas de categorías
     */
    public static function requireCategoryManagement(): void
    {
        self::requirePermission(PermissionManager::MANAGE_CATEGORIES);
    }

    /**
     * Middleware para operaciones específicas de usuarios
     */
    public static function requireUserManagement(): void
    {
        self::requirePermission(PermissionManager::MANAGE_USERS);
    }

    /**
     * Middleware para ver reportes
     */
    public static function requireReportsAccess(): void
    {
        self::requirePermission(PermissionManager::VIEW_REPORTS);
    }

    /**
     * Middleware que verifica si el usuario puede ver sus propias órdenes
     */
    public static function requireViewOwnOrders(): void
    {
        self::requireAuthenticated();
        self::requirePermission(PermissionManager::VIEW_OWN_ORDERS);
    }

    /**
     * Middleware que verifica si el usuario puede hacer compras
     */
    public static function requirePurchaseAccess(): void
    {
        self::requireAuthenticated();
        self::requirePermission(PermissionManager::MAKE_PURCHASES);
    }

    /**
     * Middleware condicional - permite acceso si NO está logueado o si tiene permiso
     */
    public static function allowGuestOrPermission(string $permission): void
    {
        if (Auth::isLoggedIn() && !PermissionManager::hasPermission($permission)) {
            self::handleUnauthorized("Permiso requerido: $permission");
        }
    }

    /**
     * Maneja acceso no autorizado
     */
    private static function handleUnauthorized(string $reason): void
    {
        Session::flash('error', "Acceso denegado. $reason");
        
        if (!Auth::isLoggedIn()) {
            header('Location: /?login_required=1');
        } else {
            header('Location: /');
        }
        exit;
    }

    /**
     * Obtiene el middleware apropiado para una ruta
     */
    public static function getMiddlewareForRoute(string $route): ?callable
    {
        $middlewareMap = [
            // Rutas de productos
            '/products' => [self::class, 'requireProductManagement'],
            '/products/create' => [self::class, 'requireProductManagement'],
            '/products/edit' => [self::class, 'requireProductManagement'],
            '/products/delete' => [self::class, 'requireProductManagement'],
            
            // Rutas de categorías  
            '/categories' => [self::class, 'requireCategoryManagement'],
            '/categories/create' => [self::class, 'requireCategoryManagement'],
            '/categories/edit' => [self::class, 'requireCategoryManagement'],
            '/categories/delete' => [self::class, 'requireCategoryManagement'],
            
            // Rutas de órdenes
            '/orders' => [self::class, 'requireOrderManagement'],
            '/orders/manage' => [self::class, 'requireOrderManagement'],
            '/orders/change-status' => [self::class, 'requireOrderManagement'],
            
            // Rutas de usuario/cliente
            '/my-orders' => [self::class, 'requireViewOwnOrders'],
            '/cart' => [self::class, 'requirePurchaseAccess'],
            '/checkout' => [self::class, 'requirePurchaseAccess'],
            
            // Rutas de administración general
            '/dashboard' => [self::class, 'requireAdmin'],
            '/users' => [self::class, 'requireUserManagement'],
            '/reports' => [self::class, 'requireReportsAccess'],
        ];

        return $middlewareMap[$route] ?? null;
    }
}