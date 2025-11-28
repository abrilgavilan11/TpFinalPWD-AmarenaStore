<?php

namespace App\Models;

class OrderStatus extends BaseModel
{
    /**
     * Cambia el estado de una compra y registra el cambio en el historial
     * @return bool true si fue exitoso
     */
    public function changeStatus(int $orderId, int $newStatusTypeId): bool
    {
        try {
            // 1. Validar que el nuevo estado existe
            $stmt = $this->pdo->prepare("SELECT idcompraestadotipo FROM compraestadotipo WHERE idcompraestadotipo = ?");
            $stmt->execute([$newStatusTypeId]);
            $statusType = $stmt->fetch();
            
            if (!$statusType) {
                throw new \Exception("Tipo de estado no válido");
            }

            // 2. Cerrar el estado anterior
            $stmt = $this->pdo->prepare("UPDATE compraestado 
                         SET cefechafin = NOW() 
                         WHERE idcompra = ? AND cefechafin IS NULL");
            $stmt->execute([$orderId]);

            // 3. Crear el nuevo estado
            $stmt = $this->pdo->prepare("INSERT INTO compraestado (idcompra, idcompraestadotipo, cefechaini) VALUES (?, ?, NOW())");
            $stmt->execute([$orderId, $newStatusTypeId]);

            return true;
        } catch (\Exception $e) {
            error_log("Error al cambiar estado: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los tipos de estado disponibles
     */
    public function getAllStatusTypes(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM compraestadotipo ORDER BY idcompraestadotipo");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtiene un tipo de estado por su ID
     */
    public function getStatusTypeById(int $statusTypeId): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM compraestadotipo WHERE idcompraestadotipo = ?");
        $stmt->execute([$statusTypeId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtiene posibles transiciones de estados válidas
     */
    public function getValidTransitions(int $currentStatusTypeId): array
    {
        // Definir transiciones válidas según la lógica de negocio
        $transitions = [
            1 => [2, 5], // iniciada -> aceptada o cancelada
            2 => [3, 5], // aceptada -> enviada o cancelada
            3 => [4, 5], // enviada -> entregada o cancelada
            4 => [],     // entregada -> (no hay transiciones)
            5 => []      // cancelada -> (no hay transiciones)
        ];

        return $transitions[$currentStatusTypeId] ?? [];
    }
}
