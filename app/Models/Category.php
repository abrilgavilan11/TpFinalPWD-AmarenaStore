<?php

namespace App\Models;

class Category extends BaseModel
{
    /**
     * Obtiene todas las categorías activas de la base de datos, ordenadas por nombre.
     *
     * @param bool $onlyActive Si true, solo devuelve categorías activas
     * @return array Un array de categorías.
     */
    public function getAll(bool $onlyActive = true): array
    {
        $sql = "SELECT * FROM categoria";
        if ($onlyActive) {
            $sql .= " WHERE activo = 1";
        }
        $sql .= " ORDER BY catnombre";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca una única categoría activa por su ID.
     *
     * @param int $id El ID de la categoría a buscar.
     * @return array|null La categoría como un array, o null si no se encuentra.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM categoria WHERE idcategoria = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtiene todas las categorías con el conteo de productos asociados.
     *
     * @param bool $onlyActive Si true, solo devuelve categorías activas
     * @return array Un array de categorías con su conteo de productos.
     */
    public function getAllWithProductCount(bool $onlyActive = false): array
    {
        $sql = "SELECT c.*, 
                       COUNT(p.idproducto) as product_count 
                FROM categoria c 
                LEFT JOIN producto p ON c.idcategoria = p.idcategoria";
        if ($onlyActive) {
            $sql .= " WHERE c.activo = 1";
        }
        $sql .= " GROUP BY c.idcategoria, c.catnombre, c.catdescripcion, c.activo 
                ORDER BY c.catnombre";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca una categoría por su ID (alias para findById para compatibilidad).
     *
     * @param int $id El ID de la categoría.
     * @return array|null La categoría encontrada o null.
     */
    public function find(int $id): ?array
    {
        return $this->findById($id);
    }

    /**
     * Crea una nueva categoría.
     *
     * @param array $data Datos de la categoría a crear.
     * @return bool True si se creó exitosamente, false en caso contrario.
     */
    public function create(array $data): bool
    {
        try {
            $sql = "INSERT INTO categoria (catnombre, catdescripcion) VALUES (?, ?)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['catnombre'],
                $data['catdescripcion'] ?? null
            ]);
        } catch (\Exception $e) {
            error_log("Error al crear categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza una categoría existente.
     *
     * @param int $id ID de la categoría a actualizar.
     * @param array $data Nuevos datos para la categoría.
     * @return bool True si se actualizó exitosamente, false en caso contrario.
     */
    public function update(int $id, array $data): bool
    {
        try {
            $sql = "UPDATE categoria SET catnombre = ?, catdescripcion = ? WHERE idcategoria = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([
                $data['catnombre'],
                $data['catdescripcion'] ?? null,
                $id
            ]);
        } catch (\Exception $e) {
            error_log("Error al actualizar categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una categoría.
     *
     * @param int $id ID de la categoría a eliminar.
     * @return bool True si se eliminó exitosamente, false en caso contrario.
     */
    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM categoria WHERE idcategoria = ?";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute([$id]);
        } catch (\Exception $e) {
            error_log("Error al eliminar categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si existe una categoría con el nombre dado.
     *
     * @param string $name Nombre a verificar.
     * @param int|null $excludeId ID a excluir de la búsqueda (para edición).
     * @return bool True si existe, false en caso contrario.
     */
    public function existsByName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) FROM categoria WHERE catnombre = ?";
        $params = [$name];
        
        if ($excludeId !== null) {
            $sql .= " AND idcategoria != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Obtiene el número de productos asociados a una categoría.
     *
     * @param int $categoryId ID de la categoría.
     * @return int Número de productos asociados.
     */
    public function getProductCount(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) FROM producto WHERE idcategoria = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$categoryId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Cambia el estado activo/inactivo de una categoría.
     *
     * @param int $id ID de la categoría.
     * @param bool $active Nuevo estado (true = activo, false = inactivo).
     * @return bool True si se actualizó exitosamente, false en caso contrario.
     */
    public function toggleActive(int $id, bool $active = null): bool
    {
        try {
            if ($active === null) {
                // Si no se especifica el estado, cambiar al opuesto
                $sql = "UPDATE categoria SET activo = NOT activo WHERE idcategoria = ?";
                $params = [$id];
            } else {
                $sql = "UPDATE categoria SET activo = ? WHERE idcategoria = ?";
                $params = [$active ? 1 : 0, $id];
            }
            
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (\Exception $e) {
            error_log("Error al cambiar estado de categoría: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene categorías activas para mostrar en menús públicos.
     *
     * @return array Array de categorías activas.
     */
    public function getActiveCategories(): array
    {
        return $this->getAll(true);
    }
}
