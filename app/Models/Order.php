<?php

namespace App\Models;

class Order extends BaseModel
{
    /**
     * Genera el siguiente número de orden secuencial
     */
    public function generateOrderNumber(): string
    {
        try {
            // Obtener el último ID de compra para generar número secuencial
            $stmt = $this->pdo->prepare("SELECT MAX(idcompra) as max_id FROM compra");
            $stmt->execute();
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $nextId = ($result['max_id'] ?? 0) + 1;
            
            // Formatear con ceros a la izquierda (6 dígitos)
            return sprintf('%06d', $nextId);
            
        } catch (\Exception $e) {
            error_log("Error generating order number: " . $e->getMessage());
            // Fallback: usar timestamp
            return sprintf('%06d', time() % 999999);
        }
    }

    /**
     * Busca una orden por número de orden
     */
    public function findByOrderNumber(string $orderNumber): ?array
    {
        // Convertir número de orden a ID (quitar ceros a la izquierda)
        $orderId = intval($orderNumber);
        
        if ($orderId <= 0) {
            return null;
        }
        
        return $this->findById($orderId);
    }

    /**
     * Crea una nueva orden/compra en la BD
     */
    public function create(int $userId, array $items): ?int
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("INSERT INTO compra (idusuario, cofecha) VALUES (?, NOW())");
            $stmt->execute([$userId]);
            $orderId = $this->pdo->lastInsertId();
            
            // Crear los items de la compra
            foreach ($items as $item) {
                $stmt = $this->pdo->prepare("INSERT INTO compraitem (idcompra, idproducto, cicantidad, ciprecio) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $orderId,
                    $item['idproducto'],
                    $item['cantidad'],
                    $item['precio']
                ]);
            }
            
            // Crear estado inicial: "iniciada"
            $stmt = $this->pdo->prepare("INSERT INTO compraestado (idcompra, idcompraestadotipo, cefechaini) VALUES (?, 1, NOW())");
            $stmt->execute([$orderId]);

            $this->pdo->commit();
            return $orderId;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al crear orden: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene una orden por su ID con todos sus detalles
     */
    public function findById($orderId): ?array
    {
        $sql = "SELECT c.*, u.usmail, u.usnombre,
                       (SELECT cetdescripcion FROM compraestadotipo cet 
                        JOIN compraestado ce ON ce.idcompraestadotipo = cet.idcompraestadotipo
                        WHERE ce.idcompra = c.idcompra 
                        ORDER BY ce.cefechaini DESC LIMIT 1) as estado_actual
                FROM compra c
                JOIN usuario u ON c.idusuario = u.idusuario
                WHERE c.idcompra = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtiene todas las órdenes de un usuario
     */
    public function findByUserId(int $userId): array
    {
        $sql = "SELECT c.*, 
                       (SELECT cetdescripcion FROM compraestadotipo cet 
                        JOIN compraestado ce ON ce.idcompraestadotipo = cet.idcompraestadotipo
                        WHERE ce.idcompra = c.idcompra 
                        ORDER BY ce.cefechaini DESC LIMIT 1) as estado_actual
                FROM compra c
                WHERE c.idusuario = ?
                ORDER BY c.cofecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene todas las órdenes (para admin)
     */
    public function getAll(): array
    {
        $sql = "SELECT c.*, u.usnombre, u.usmail,
                       (SELECT cetdescripcion FROM compraestadotipo cet 
                        JOIN compraestado ce ON ce.idcompraestadotipo = cet.idcompraestadotipo
                        WHERE ce.idcompra = c.idcompra 
                        ORDER BY ce.cefechaini DESC LIMIT 1) as estado_actual
                FROM compra c
                JOIN usuario u ON c.idusuario = u.idusuario
                ORDER BY c.cofecha DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene los items de una orden
     */
    public function getItems(int $orderId): array
    {
        $sql = "SELECT ci.*, p.pronombre, p.proimagen 
                FROM compraitem ci
                JOIN producto p ON ci.idproducto = p.idproducto
                WHERE ci.idcompra = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene el estado actual de una orden
     */
    public function getCurrentStatus(int $orderId): ?array
    {
        $sql = "SELECT ce.*, cet.cetdescripcion, cet.cetdetalle
                FROM compraestado ce
                JOIN compraestadotipo cet ON ce.idcompraestadotipo = cet.idcompraestadotipo
                WHERE ce.idcompra = ?
                ORDER BY ce.cefechaini DESC
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtiene el historial de estados de una orden
     */
    public function getStatusHistory(int $orderId): array
    {
        $sql = "SELECT ce.*, cet.cetdescripcion, cet.cetdetalle
            FROM compraestado ce
            JOIN compraestadotipo cet ON ce.idcompraestadotipo = cet.idcompraestadotipo
            WHERE ce.idcompra = ?
            ORDER BY ce.cefechaini DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Devolver los datos tal como los espera la vista
        return $results;
    }
    
    /**
     * Obtiene descripción del estado
     */
    private function getStateDescription(string $state): string
    {
        $descriptions = [
            'iniciada' => 'Orden creada y pendiente de confirmación',
            'aceptada' => 'Orden confirmada y en preparación',
            'enviada' => 'Orden enviada y en camino',
            'entregada' => 'Orden entregada exitosamente'
        ];
        
        return $descriptions[$state] ?? 'Estado desconocido';
    }

    /**
     * Marca una orden como pagada (cambia estado a "aceptada")
     */
    public function markAsPaid($orderId): bool
    {
        try {
            error_log("markAsPaid: Starting transaction for order $orderId");
            $this->pdo->beginTransaction();

            // Cerrar el estado actual (iniciada) si existe
            $stmt = $this->pdo->prepare("
                UPDATE compraestado 
                SET cefechafin = NOW() 
                WHERE idcompra = ? AND cefechafin IS NULL
            ");
            $result = $stmt->execute([$orderId]);
            $updatedRows = $stmt->rowCount();
            error_log("markAsPaid: Updated $updatedRows existing states for order $orderId");

            // Buscar el ID del estado "aceptada"
            $stmtFind = $this->pdo->prepare("
                SELECT idcompraestadotipo FROM compraestadotipo 
                WHERE cetdescripcion = 'aceptada'
            ");
            $stmtFind->execute();
            $estadoAceptada = $stmtFind->fetch(\PDO::FETCH_ASSOC);
            
            if (!$estadoAceptada) {
                error_log("markAsPaid: Estado 'aceptada' no encontrado en la tabla");
                throw new \Exception("Estado 'aceptada' no encontrado");
            }
            
            $estadoId = $estadoAceptada['idcompraestadotipo'];
            error_log("markAsPaid: Found estado 'aceptada' with ID: $estadoId");

            // Agregar nuevo estado: aceptada
            $stmt = $this->pdo->prepare("
                INSERT INTO compraestado (idcompra, idcompraestadotipo, cefechaini) 
                VALUES (?, ?, NOW())
            ");
            $result = $stmt->execute([$orderId, $estadoId]);
            error_log("markAsPaid: Inserted new 'aceptada' status for order $orderId");

            $this->pdo->commit();
            error_log("markAsPaid: Transaction committed successfully for order $orderId");
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al marcar orden como pagada: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Verifica si una orden está pagada (estado "aceptada")
     */
    public function isPaid($orderId): bool
    {
        $currentStatus = $this->getCurrentStatus($orderId);
        if (!$currentStatus) {
            error_log("Order $orderId: No current status found");
            return false;
        }
        
        error_log("Order $orderId: Current status is " . $currentStatus['cetdescripcion']);
        
        // Considerar como "pagada" los estados: aceptada, enviada, entregada
        $paidStatuses = ['aceptada', 'enviada', 'entregada'];
        $isPaid = in_array($currentStatus['cetdescripcion'], $paidStatuses);
        
        error_log("Order $orderId: Is paid = " . ($isPaid ? 'true' : 'false'));
        
        return $isPaid;
    }

    /**
     * Debug: Obtiene todos los estados disponibles
     */
    public function getAvailableStatuses(): array
    {
        $sql = "SELECT * FROM compraestadotipo ORDER BY idcompraestadotipo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Debug: Obtiene todos los estados de una orden específica
     */
    public function debugOrderStatuses($orderId): array
    {
        $sql = "SELECT ce.*, cet.cetdescripcion, cet.cetdetalle
                FROM compraestado ce
                JOIN compraestadotipo cet ON ce.idcompraestadotipo = cet.idcompraestadotipo
                WHERE ce.idcompra = ?
                ORDER BY ce.cefechaini DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene una orden con detalles completos del usuario
     */
    public function getOrderWithUserDetails(int $orderId): ?array
    {
        $sql = "SELECT c.*, u.usnombre, u.usmail,
                       (SELECT cetdescripcion FROM compraestadotipo cet 
                        JOIN compraestado ce ON ce.idcompraestadotipo = cet.idcompraestadotipo
                        WHERE ce.idcompra = c.idcompra 
                        ORDER BY ce.cefechaini DESC LIMIT 1) as estado_actual,
                       SUM(ci.cicantidad * ci.ciprecio) as total
                FROM compra c
                JOIN usuario u ON c.idusuario = u.idusuario
                JOIN compraitem ci ON ci.idcompra = c.idcompra
                WHERE c.idcompra = ?
                GROUP BY c.idcompra, u.idusuario";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Actualiza el estado de una orden
     */
    public function updateStatus(int $orderId, int $newStatusId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Cerrar el estado actual si existe
            $stmt = $this->pdo->prepare("
                UPDATE compraestado 
                SET cefechafin = NOW() 
                WHERE idcompra = ? AND cefechafin IS NULL
            ");
            $stmt->execute([$orderId]);

            // Agregar nuevo estado
            $stmt = $this->pdo->prepare("
                INSERT INTO compraestado (idcompra, idcompraestadotipo, cefechaini) 
                VALUES (?, ?, NOW())
            ");
            $stmt->execute([$orderId, $newStatusId]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al actualizar estado de orden: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina una orden y todos sus datos relacionados
     */
    public function delete(int $orderId): bool
    {
        try {
            $this->pdo->beginTransaction();

            // Eliminar estados de la compra
            $stmt = $this->pdo->prepare("DELETE FROM compraestado WHERE idcompra = ?");
            $stmt->execute([$orderId]);

            // Eliminar items de la compra
            $stmt = $this->pdo->prepare("DELETE FROM compraitem WHERE idcompra = ?");
            $stmt->execute([$orderId]);

            // Eliminar la compra
            $stmt = $this->pdo->prepare("DELETE FROM compra WHERE idcompra = ?");
            $stmt->execute([$orderId]);

            $this->pdo->commit();
            return true;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            error_log("Error al eliminar orden: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los tipos de estado disponibles
     */
    public function getStatusTypes(): array
    {
        $sql = "SELECT * FROM compraestadotipo ORDER BY idcompraestadotipo";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
