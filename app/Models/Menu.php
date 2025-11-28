<?php

namespace App\Models;

use App\Utils\Database;

class Menu
{

    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene el menú dinámico según los permisos del rol del usuario
     * @param int $roleId El ID del rol del usuario
     * @return array Menú estructurado por jerarquía
     */
    public function getMenuByRole(int $roleId): array
    {
        $sql = "SELECT m.* 
                FROM menu m
                JOIN menurol mr ON m.idmenu = mr.idmenu
                WHERE mr.idrol = ? 
                AND m.medeshabilitado IS NULL
                ORDER BY m.meorden ASC, m.menombre ASC";
        
        $menuItems = $this->db->fetchAll($sql, [$roleId]);
        
        // Estructurar jerárquicamente (padres e hijos)
        return $this->buildHierarchy($menuItems);
    }

    /**
     * Construye la jerarquía del menú
     */
    private function buildHierarchy(array $items, ?int $parentId = null): array
    {
        $result = [];
        
        foreach ($items as $item) {
            if ($item['idpadre'] == $parentId) {
                $children = $this->buildHierarchy($items, $item['idmenu']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $result[] = $item;
            }
        }
        
        return $result;
    }

    /**
     * Obtiene todos los menús para administración
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM menu ORDER BY idpadre, meorden, menombre";
        return $this->db->fetchAll($sql, []);
    }

    /**
     * Obtiene un menú por ID
     */
    public function findById(int $menuId): ?array
    {
        $sql = "SELECT * FROM menu WHERE idmenu = ?";
        return $this->db->fetchOne($sql, [$menuId]);
    }

    /**
     * Crea un nuevo menú
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO menu (menombre, medescripcion, meurl, idpadre, meorden) 
               VALUES (?, ?, ?, ?, ?)";
        
        try {
            $this->db->query($sql, [
                $data['menombre'],
                $data['medescripcion'],
                $data['meurl'] ?? null,
                $data['idpadre'] ?? null,
                $data['meorden'] ?? 0
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al crear menú: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un menú existente
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE menu SET menombre = ?, medescripcion = ?, meurl = ?, idpadre = ?, meorden = ? WHERE idmenu = ?";
        try {
            $this->db->query($sql, [
                $data['menombre'],
                $data['medescripcion'],
                $data['meurl'] ?? null,
                $data['idpadre'] ?? null,
                $data['meorden'] ?? 0,
                $id
            ]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al actualizar menú: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un menú
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM menu WHERE idmenu = ?";
        try {
            $this->db->query($sql, [$id]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al eliminar menú: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Asigna un menú a un rol
     */
    public function assignToRole(int $menuId, int $roleId): bool
    {
        $sql = "INSERT IGNORE INTO menurol (idmenu, idrol) VALUES (?, ?)";
        try {
            $this->db->query($sql, [$menuId, $roleId]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al asignar menú a rol: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Desasigna un menú de un rol
     */
    public function removeFromRole(int $menuId, int $roleId): bool
    {
        $sql = "DELETE FROM menurol WHERE idmenu = ? AND idrol = ?";
        try {
            $this->db->query($sql, [$menuId, $roleId]);
            return true;
        } catch (\Exception $e) {
            error_log("Error al desasignar menú de rol: " . $e->getMessage());
            return false;
        }
    }
}
