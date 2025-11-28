<?php

namespace App\Models;

class Permission extends BaseModel
{
    /**
     * Obtiene todos los permisos de un usuario por ID
     */
    public function getUserPermissions(int $userId): array
    {
        $sql = "SELECT DISTINCT p.* 
                FROM permisos p
                JOIN rolpermiso rp ON p.idpermiso = rp.idpermiso
                JOIN usuariorol ur ON rp.idrol = ur.idrol
                WHERE ur.idusuario = ? AND p.activo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene permisos por rol
     */
    public function getPermissionsByRole(int $roleId): array
    {
        $sql = "SELECT p.* 
                FROM permisos p
                JOIN rolpermiso rp ON p.idpermiso = rp.idpermiso
                WHERE rp.idrol = ? AND p.activo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$roleId]);
        return $stmt->fetchAll();
    }

    /**
     * Verifica si un usuario tiene un permiso específico
     */
    public function userHasPermission(int $userId, string $permission): bool
    {
        $sql = "SELECT COUNT(*) as count
                FROM permisos p
                JOIN rolpermiso rp ON p.idpermiso = rp.idpermiso
                JOIN usuariorol ur ON rp.idrol = ur.idrol
                WHERE ur.idusuario = ? AND p.codigo = ? AND p.activo = 1";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $permission]);
        $result = $stmt->fetch();
        return $result && $result['count'] > 0;
    }

    /**
     * Obtiene todos los permisos disponibles
     */
    public function getAllPermissions(): array
    {
        $sql = "SELECT * FROM permisos WHERE activo = 1 ORDER BY categoria, nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Crea un nuevo permiso
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO permisos (codigo, nombre, descripcion, categoria) VALUES (?, ?, ?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['codigo'],
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['categoria'] ?? 'general'
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al crear permiso: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asigna un permiso a un rol
     */
    public function assignToRole(int $permissionId, int $roleId): bool
    {
        $sql = "INSERT IGNORE INTO rolpermiso (idrol, idpermiso) VALUES (?, ?)";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$roleId, $permissionId]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al asignar permiso a rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Remueve un permiso de un rol
     */
    public function removeFromRole(int $permissionId, int $roleId): bool
    {
        $sql = "DELETE FROM rolpermiso WHERE idrol = ? AND idpermiso = ?";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$roleId, $permissionId]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al remover permiso de rol: " . $e->getMessage());
            return false;
        }
    }
}