<?php

namespace App\Models;

class Product extends BaseModel
{
    /**
     * Crea un nuevo producto en la base de datos.
     *
     * @param array $data Datos del producto a crear.
     * @return bool True si fue exitoso, False en caso de error.
     */
    public function create(array $data): bool
    {
        $sql = "INSERT INTO producto (pronombre, prodetalle, proprecio, procantstock, idcategoria, proimagen) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['pronombre'],
            $data['prodetalle'],
            $data['proprecio'],
            $data['procantstock'],
            $data['idcategoria'],
            $data['proimagen']
        ]);
    }

    /**
     * Obtiene todos los productos de la base de datos.
     * 
     * @param bool $onlyActiveCategories Si true, solo incluye productos de categorías activas
     */
    public function getAll(bool $onlyActiveCategories = true): array
    {
        $sql = "SELECT p.*, c.catnombre FROM producto p JOIN categoria c ON p.idcategoria = c.idcategoria";
        if ($onlyActiveCategories) {
            $sql .= " WHERE c.activo = 1";
        }
        $sql .= " ORDER BY p.idproducto DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un número limitado de productos (para la página de inicio).
     * Solo incluye productos de categorías activas.
     */
    public function getFeatured(int $limit): array
    {
        $sql = "SELECT p.*, c.catnombre FROM producto p 
                JOIN categoria c ON p.idcategoria = c.idcategoria 
                WHERE c.activo = 1 
                ORDER BY p.idproducto DESC LIMIT ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Busca un producto por su ID.
     *
     * @param int $id El ID del producto.
     * @return array|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT p.*, c.catnombre 
                FROM producto p 
                LEFT JOIN categoria c ON p.idcategoria = c.idcategoria 
                WHERE p.idproducto = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Busca y filtra productos para el catálogo.
     *
     * @param array $filters Filtros como 'search', 'category'.
     * @return array
     */
    public function searchAndFilter(array $filters = []): array
    {
        $sql = "SELECT p.*, c.catnombre 
                FROM producto p 
                JOIN categoria c ON p.idcategoria = c.idcategoria";
        
        $whereClauses = ["c.activo = 1"]; // Siempre filtrar por categorías activas
        $params = [];

        if (!empty($filters['search'])) {
            $whereClauses[] = "(p.pronombre LIKE ? OR p.prodetalle LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        if (!empty($filters['category'])) {
            $whereClauses[] = "p.idcategoria = ?";
            $params[] = (int)$filters['category'];
        }

        $sql .= " WHERE " . implode(' AND ', $whereClauses);
        $sql .= " ORDER BY p.idproducto DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza un producto existente en la base de datos.
     *
     * @param int $id El ID del producto a actualizar.
     * @param array $data Los nuevos datos del producto.
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE producto SET 
                    pronombre = ?, 
                    prodetalle = ?, 
                    proprecio = ?, 
                    procantstock = ?, 
                    idcategoria = ?, 
                    proimagen = ? 
                WHERE idproducto = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['pronombre'],
            $data['prodetalle'],
            $data['proprecio'],
            $data['procantstock'],
            $data['idcategoria'],
            $data['proimagen'],
            $id
        ]);
    }

    /**
     * Elimina un producto de la base de datos.
     *
     * @param int $id El ID del producto a eliminar.
     * @return bool
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM producto WHERE idproducto = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }

    /**
     * Disminuye el stock de un producto.
     */
    public function decreaseStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE producto SET procantstock = procantstock - ? WHERE idproducto = ? AND procantstock >= ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $productId, $quantity]);
    }

    /**
     * Aumenta el stock de un producto.
     */
    public function increaseStock(int $productId, int $quantity): bool
    {
        $sql = "UPDATE producto SET procantstock = procantstock + ? WHERE idproducto = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$quantity, $productId]);
    }

    /**
     * Obtiene productos con stock bajo según el umbral especificado.
     */
    public function getLowStockProducts(int $threshold = 10): array
    {
        $sql = "SELECT p.*, c.catnombre 
                FROM producto p 
                LEFT JOIN categoria c ON p.idcategoria = c.idcategoria 
                WHERE p.procantstock < ?
                ORDER BY p.procantstock ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$threshold]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el stock de un producto específico.
     */
    public function updateStock(int $productId, int $newStock): bool
    {
        $sql = "UPDATE producto SET procantstock = ? WHERE idproducto = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$newStock, $productId]);
    }

    /**
     * Obtiene estadísticas básicas de productos.
     */
    public function getProductStats(): array
    {
        $stats = [];
        
        // Total de productos
        $sql = "SELECT COUNT(*) as total FROM producto";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['total_products'] = $stmt->fetchColumn();
        
        // Productos con stock bajo (menos del umbral)
        $sql = "SELECT COUNT(*) as low_stock FROM producto WHERE procantstock < 10";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['low_stock'] = $stmt->fetchColumn();
        
        // Productos sin stock
        $sql = "SELECT COUNT(*) as out_of_stock FROM producto WHERE procantstock = 0";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $stats['out_of_stock'] = $stmt->fetchColumn();
        
        return $stats;
    }
}