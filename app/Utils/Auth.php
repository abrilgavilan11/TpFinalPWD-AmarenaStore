<?php

namespace App\Utils;

use App\Models\Menu;

/**
 * Utilidades de autenticación y autorización
 * Centraliza la lógica de permisos y acceso con sistema híbrido:
 * - Mantiene compatibilidad con roles existentes
 * - Agrega soporte para permisos granulares
 */
class Auth
{
    /**
     * Verifica si el usuario está autenticado (alias para compatibilidad)
     */
    public static function isAuthenticated(): bool
    {
        return self::isLoggedIn();
    }

    /**
     * Verifica si el usuario está logueado
     */
    public static function isLoggedIn(): bool
    {
        return Session::has('user_id');
    }

    /**
     * Obtiene el ID del usuario actual
     */
    public static function getUserId(): ?int
    {
        return Session::get('user_id');
    }

    /**
     * Obtiene el rol del usuario actual
     */
    public static function getUserRole(): ?string
    {
        return Session::get('user_role');
    }

    /**
     * Obtiene el nombre del usuario actual
     */
    public static function getUserName(): ?string
    {
        return Session::get('user_name');
    }

    /**
     * Obtiene el email del usuario actual
     */
    public static function getUserEmail(): ?string
    {
        return Session::get('user_email');
    }

    /**
     * Verifica si el usuario tiene un rol específico
     */
    public static function hasRole(string $role): bool
    {
        return self::getUserRole() === $role;
    }

    /**
     * Verifica si el usuario es administrador
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('Administrador');
    }

    /**
     * Verifica si el usuario es cliente
     */
    public static function isClient(): bool
    {
        return self::hasRole('Cliente');
    }

    /**
     * Redirige si no está autenticado
     */
    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            Session::flash('error', 'Debes iniciar sesión para acceder a esta página.');
            header('Location: /?login=1');
            exit;
        }
    }

    /**
     * Redirige si no es administrador (método de compatibilidad)
     * DEPRECATED: Usar PermissionMiddleware::requireAdmin() en su lugar
     */
    public static function requireAdmin(): void
    {
        self::requireLogin();
        if (!self::isAdmin()) {
            Session::flash('error', 'No tienes permiso para acceder a esta página.');
            header('Location: /');
            exit;
        }
    }

    /**
     * Establece la sesión del usuario al hacer login
     */
    public static function setUserSession(array $userData): void
    {
        Session::set('user_id', $userData['idusuario']);
        Session::set('user_name', $userData['usnombre']);
        Session::set('user_email', $userData['usmail']);
        Session::set('user_role', $userData['rol'] ?? 'Cliente');
    }

    /**
     * Limpia la sesión del usuario al hacer logout
     */
    public static function clearUserSession(): void
    {
        Session::remove('user_id');
        Session::remove('user_name');
        Session::remove('user_email');
        Session::remove('user_role');
        
        // Limpiar cache de permisos
        if (class_exists('App\Utils\PermissionManager')) {
            \App\Utils\PermissionManager::clearPermissionCache();
        }
    }

    /**
     * Obtiene el menú dinámico según el rol del usuario
     */
    public static function getMenuByRole(): array
    {
        $role = self::getUserRole();
        
        // Mapear rol a ID
        $roleMap = ['Administrador' => 1, 'Cliente' => 2];
        $roleId = $roleMap[$role] ?? 2;
        
        $menuModel = new Menu();
        return $menuModel->getMenuByRole($roleId);
    }
}
