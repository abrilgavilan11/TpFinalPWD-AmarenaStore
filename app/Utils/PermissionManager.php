<?php

namespace App\Utils;

use App\Models\Permission;

class PermissionManager
{
    private static $userPermissions = [];
    
    /**
     * Verifica si el usuario actual tiene un permiso específico
     */
    public static function hasPermission(string $permission): bool
    {
        if (!Auth::isLoggedIn()) {
            return false;
        }

        $userId = Auth::getUserId();
        
        // Cache de permisos para evitar múltiples consultas
        if (!isset(self::$userPermissions[$userId])) {
            self::loadUserPermissions($userId);
        }

        return in_array($permission, self::$userPermissions[$userId]);
    }

    /**
     * Verifica múltiples permisos (requiere AL MENOS UNO)
     */
    public static function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (self::hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Verifica múltiples permisos (requiere TODOS)
     */
    public static function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!self::hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Carga los permisos del usuario en cache
     */
    private static function loadUserPermissions(int $userId): void
    {
        $permissionModel = new Permission();
        $permissions = $permissionModel->getUserPermissions($userId);
        
        self::$userPermissions[$userId] = array_column($permissions, 'codigo');
    }

    /**
     * Requiere un permiso específico (lanza excepción si no lo tiene)
     */
    public static function requirePermission(string $permission): void
    {
        if (!self::hasPermission($permission)) {
            self::handleUnauthorized($permission);
        }
    }

    /**
     * Requiere ser admin (método de compatibilidad)
     */
    public static function requireAdmin(): void
    {
        self::requirePermission('admin.access');
    }

    /**
     * Maneja acceso no autorizado
     */
    private static function handleUnauthorized(string $permission): void
    {
        if (!Auth::isLoggedIn()) {
            Session::flash('error', 'Debes iniciar sesión para acceder a esta sección.');
            header('Location: /?login_required=1');
            exit;
        } else {
            Session::flash('error', "No tienes permisos suficientes para realizar esta acción. Permiso requerido: $permission");
            header('Location: /');
            exit;
        }
    }

    /**
     * Obtiene todos los permisos del usuario actual
     */
    public static function getCurrentUserPermissions(): array
    {
        if (!Auth::isLoggedIn()) {
            return [];
        }

        $userId = Auth::getUserId();
        
        if (!isset(self::$userPermissions[$userId])) {
            self::loadUserPermissions($userId);
        }

        return self::$userPermissions[$userId];
    }

    /**
     * Limpia el cache de permisos (útil al cambiar roles)
     */
    public static function clearPermissionCache(int $userId = null): void
    {
        if ($userId) {
            unset(self::$userPermissions[$userId]);
        } else {
            self::$userPermissions = [];
        }
    }

    /**
     * Constantes de permisos comunes
     */
    const ADMIN_ACCESS = 'admin.access';
    const MANAGE_PRODUCTS = 'products.manage';
    const MANAGE_ORDERS = 'orders.manage';
    const MANAGE_USERS = 'users.manage';
    const MANAGE_CATEGORIES = 'categories.manage';
    const VIEW_REPORTS = 'reports.view';
    const MANAGE_SETTINGS = 'settings.manage';
    
    // Permisos de cliente
    const VIEW_CATALOG = 'catalog.view';
    const MAKE_PURCHASES = 'purchases.make';
    const VIEW_OWN_ORDERS = 'orders.view_own';
    const CANCEL_OWN_ORDERS = 'orders.cancel_own';
}