<?php

namespace App\Views\Actions;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Utils\Email;

class AdminOrderAction
{
    /**
     * Prepara datos para listar todas las órdenes
     */
    public function prepareOrdersList()
    {
        $orderModel = new Order();
        $orders = $orderModel->getAll();

        return [
            'title' => 'Gestionar Órdenes - Admin',
            'pageCss' => 'admin',
            'orders' => $orders
        ];
    }

    /**
     * Prepara datos para ver detalle de una orden
     */
    public function prepareOrderDetail($orderId)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order) {
            return null;
        }

        $items = $orderModel->getItems($orderId);
        $statusHistory = $orderModel->getStatusHistory($orderId);
        $currentStatus = $orderModel->getCurrentStatus($orderId);

        $statusModel = new OrderStatus();
        $validTransitions = $statusModel->getValidTransitions($currentStatus['idcompraestadotipo']);
        $allStatusTypes = $statusModel->getAllStatusTypes();

        return [
            'title' => 'Detalle de Orden #' . $orderId . ' - Admin',
            'pageCss' => 'admin',
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory,
            'currentStatus' => $currentStatus,
            'validTransitions' => $validTransitions,
            'allStatusTypes' => $allStatusTypes
        ];
    }

    /**
     * Cambia el estado de una orden
     */
    public function changeOrderStatus($orderId, $newStatusId)
    {
        try {
            $orderId = intval($orderId);
            $newStatusId = intval($newStatusId);

            if (!$orderId || !$newStatusId) {
                return [
                    'success' => false,
                    'message' => 'Parámetros requeridos.'
                ];
            }

            $orderModel = new Order();
            $order = $orderModel->findById($orderId);

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Orden no encontrada.'
                ];
            }

            $orderStatusModel = new OrderStatus();
            $currentStatus = $orderModel->getCurrentStatus($orderId);

            // Validar transición
            $validTransitions = $orderStatusModel->getValidTransitions($currentStatus['idcompraestadotipo']);
            if (!in_array($newStatusId, $validTransitions)) {
                return [
                    'success' => false,
                    'message' => 'Transición de estado no válida.'
                ];
            }

            // Cambiar estado
            if ($orderStatusModel->changeStatus($orderId, $newStatusId)) {
                $newStatus = $orderStatusModel->getStatusTypeById($newStatusId);

                $emailer = new Email();
                $emailer->sendOrderStatusNotification(
                    $order['usmail'],
                    $order['usnombre'],
                    $orderId,
                    $newStatus['cetdescripcion'],
                    $newStatus['cetdetalle']
                );

                if ($newStatusId == 5) { // cancelada
                    $items = $orderModel->getItems($orderId);
                    $productModel = new Product();
                    foreach ($items as $item) {
                        $productModel->increaseStock($item['idproducto'], $item['cicantidad']);
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Estado actualizado a: ' . $newStatus['cetdescripcion']
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al cambiar estado.'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error al cambiar estado de orden: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno.'
            ];
        }
    }

    /**
     * Obtiene estadísticas de órdenes
     */
    public function getStats()
    {
        try {
            $orderModel = new Order();
            $allOrders = $orderModel->getAll();

            $stats = [
                'total_ordenes' => count($allOrders),
                'por_estado' => [],
                'ingresos_totales' => 0
            ];

            foreach ($allOrders as $order) {
                $estado = $order['estado_actual'] ?? 'desconocido';
                $stats['por_estado'][$estado] = ($stats['por_estado'][$estado] ?? 0) + 1;
            }

            return [
                'success' => true,
                'stats' => $stats
            ];
        } catch (\Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno.'
            ];
        }
    }
}
