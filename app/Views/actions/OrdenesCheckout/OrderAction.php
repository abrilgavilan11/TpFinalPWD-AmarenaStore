<?php

namespace App\Views\Actions;

use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\User;
use App\Utils\Email;

class OrderAction
{
    /**
     * Prepara datos para mostrar todas las órdenes del usuario
     */
    public function prepareMyOrders($userId)
    {
        $orderModel = new Order();
        $orders = $orderModel->findByUserId($userId);

        return [
            'title' => 'Mis Órdenes - Amarena Store',
            'pageCss' => 'orders',
            'orders' => $orders
        ];
    }

    /**
     * Prepara datos para mostrar detalle de una orden
     */
    public function prepareOrderDetail($orderId, $userId = null, $isAdmin = false)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId);

        if (!$order) {
            return null;
        }

        // Verificar permisos
        if ($userId && !$isAdmin && $order['idusuario'] != $userId) {
            return null;
        }

        $items = $orderModel->getItems($orderId);
        $statusHistory = $orderModel->getStatusHistory($orderId);

        return [
            'title' => 'Detalle de Orden #' . $orderId,
            'pageCss' => 'orders',
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory
        ];
    }

    /**
     * Crea una nueva orden desde el carrito
     */
    public function createOrder($userId)
    {
        try {
            $cartModel = new Cart();
            $cartContents = $cartModel->getCartContents();

            if (empty($cartContents['items'])) {
                return [
                    'success' => false,
                    'message' => 'El carrito está vacío.'
                ];
            }

            $items = [];
            $productModel = new Product();

            foreach ($cartContents['items'] as $item) {
                $product = $productModel->findById($item['product_id']);
                if (!$product || $product['procantstock'] < $item['quantity']) {
                    return [
                        'success' => false,
                        'message' => 'Stock insuficiente para: ' . $item['name']
                    ];
                }

                $items[] = [
                    'idproducto' => $item['product_id'],
                    'cantidad' => $item['quantity'],
                    'precio' => $item['price']
                ];
            }

            $orderModel = new Order();
            $orderId = $orderModel->create($userId, $items);

            if (!$orderId) {
                return [
                    'success' => false,
                    'message' => 'Error al crear la orden.'
                ];
            }

            foreach ($items as $item) {
                $productModel->decreaseStock($item['idproducto'], $item['cantidad']);
            }

            $cartModel->clear();

            $userModel = new User();
            $user = $userModel->findById($userId);
            $emailer = new Email();
            $emailer->sendOrderConfirmation($user['usmail'], $user['usnombre'], $orderId, $cartContents['total']);

            return [
                'success' => true,
                'message' => 'Orden creada exitosamente',
                'order_id' => $orderId
            ];
        } catch (\Exception $e) {
            error_log("Error al crear orden: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor.'
            ];
        }
    }

    /**
     * Cancela una orden
     */
    public function cancelOrder($orderId, $userId, $isAdmin = false)
    {
        try {
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);

            if (!$order) {
                return [
                    'success' => false,
                    'message' => 'Orden no encontrada.'
                ];
            }

            // Verificar permisos
            if (!$isAdmin && $order['idusuario'] != $userId) {
                return [
                    'success' => false,
                    'message' => 'No tienes permiso.'
                ];
            }

            // Obtener estado actual
            $currentStatus = $orderModel->getCurrentStatus($orderId);

            // Solo se puede cancelar en estado "iniciada" (id=1) para clientes
            if (!$isAdmin && $currentStatus['idcompraestadotipo'] != 1) {
                return [
                    'success' => false,
                    'message' => 'Solo puedes cancelar órdenes en estado iniciada.'
                ];
            }

            // Cambiar a estado "cancelada" (5)
            $orderStatusModel = new \App\Models\OrderStatus();
            if ($orderStatusModel->changeStatus($orderId, 5)) {
                // Devolver stock
                $items = $orderModel->getItems($orderId);
                $productModel = new Product();
                foreach ($items as $item) {
                    $productModel->increaseStock($item['idproducto'], $item['cicantidad']);
                }

                // Enviar email de cancelación
                $emailer = new Email();
                $emailer->sendOrderStatusNotification(
                    $order['usmail'],
                    $order['usnombre'],
                    $orderId,
                    'cancelada',
                    'Tu pedido ha sido cancelado.'
                );

                return [
                    'success' => true,
                    'message' => 'Orden cancelada exitosamente.'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al cancelar la orden.'
                ];
            }
        } catch (\Exception $e) {
            error_log("Error al cancelar orden: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno.'
            ];
        }
    }
}
