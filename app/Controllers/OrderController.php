<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Utils\Session;
use App\Utils\Auth;
use App\Utils\Email;

class OrderController extends BaseController
{
    /**
     * Muestra todas las órdenes del usuario actual
     */
    public function myOrders()
    {
        Auth::requireLogin();
        
        $orderModel = new Order();
        $orders = $orderModel->findByUserId(Auth::getUserId());
        
        $this->view('vistas.tienda.my_orders', [
            'title' => 'Mis Órdenes - Amarena Store',
            'pageCss' => 'orders',
            'orders' => $orders
        ]);
    }

    /**
     * Muestra los detalles de una orden específica
     */
    public function show($orderId)
    {
        Auth::requireLogin();
        
        $orderModel = new Order();
        $order = $orderModel->findById($orderId);
        
        // Verificar que la orden pertenezca al usuario o que sea admin
        if (!$order || ($order['idusuario'] != Auth::getUserId() && !Auth::isAdmin())) {
            Session::flash('error', 'No tienes permiso para ver esta orden.');
            $this->redirect('/');
            return;
        }
        
        $items = $orderModel->getItems($orderId);
        $statusHistory = $orderModel->getStatusHistory($orderId);
        
        $this->view('vistas.tienda.order_detail', [
            'title' => 'Detalle de Orden #' . $orderId,
            'pageCss' => 'orders',
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory
        ]);
    }

    /**
     * Crea una nueva orden desde el carrito
     * Endpoint AJAX para procesar el pago y crear la orden
     */
    public function create()
    {
        Auth::requireLogin();
        
        try {
            $userId = Auth::getUserId();
            $cartModel = new Cart();
            $cartContents = $cartModel->getCartContents();
            
            if (empty($cartContents['items'])) {
                $this->json(['success' => false, 'message' => 'El carrito está vacío.'], 400);
                return;
            }
            
            // Preparar items para la orden
            $items = [];
            $productModel = new Product();
            
            foreach ($cartContents['items'] as $item) {
                $product = $productModel->findById($item['product_id']);
                if (!$product || $product['procantstock'] < $item['quantity']) {
                    $this->json([
                        'success' => false, 
                        'message' => 'Stock insuficiente para: ' . $item['name']
                    ], 400);
                    return;
                }
                
                $items[] = [
                    'idproducto' => $item['product_id'],
                    'cantidad' => $item['quantity'],
                    'precio' => $item['price']
                ];
            }
            
            // Crear la orden
            $orderModel = new Order();
            $orderId = $orderModel->create($userId, $items);
            
            if (!$orderId) {
                $this->json(['success' => false, 'message' => 'Error al crear la orden.'], 500);
                return;
            }
            
            foreach ($items as $item) {
                $productModel->decreaseStock($item['idproducto'], $item['cantidad']);
            }
            
            // Vaciar el carrito
            $cartModel->clear();
            
            $userModel = new \App\Models\User();
            $user = $userModel->findById($userId);
            $email = new Email();
            $email->sendOrderConfirmation($user['usmail'], $user['usnombre'], $orderId, $cartContents['total']);
            
            Session::flash('success', 'Orden creada exitosamente. Puedes verla en Mis Órdenes.');
            
            $this->json([
                'success' => true, 
                'message' => 'Orden creada exitosamente',
                'order_id' => $orderId
            ]);
        } catch (\Exception $e) {
            error_log("Error al crear orden: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno del servidor.'], 500);
        }
    }

    /**
     * Cancela una orden (solo si está en estado "iniciada")
     * Endpoint AJAX para cancelar compra
     */
    public function cancel()
    {
        Auth::requireLogin();
        
        try {
            $orderId = intval($_POST['order_id'] ?? 0);
            
            if (!$orderId) {
                $this->json(['success' => false, 'message' => 'ID de orden requerido.'], 400);
                return;
            }
            
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);
            
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Orden no encontrada.'], 404);
                return;
            }
            
            // Verificar permisos
            if ($order['idusuario'] != Auth::getUserId() && !Auth::isAdmin()) {
                $this->json(['success' => false, 'message' => 'No tienes permiso.'], 403);
                return;
            }
            
            // Obtener estado actual
            $currentStatus = $orderModel->getCurrentStatus($orderId);
            
            // Solo se puede cancelar en estado "iniciada" (id=1) para clientes
            if (!Auth::isAdmin() && $currentStatus['idcompraestadotipo'] != 1) {
                $this->json([
                    'success' => false, 
                    'message' => 'Solo puedes cancelar órdenes en estado iniciada.'
                ], 400);
                return;
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
                $email = new Email();
                $email->sendOrderStatusNotification(
                    $order['usmail'],
                    $order['usnombre'],
                    $orderId,
                    'cancelada',
                    'Tu pedido ha sido cancelado.'
                );
                
                $this->json(['success' => true, 'message' => 'Orden cancelada exitosamente.']);
            } else {
                $this->json(['success' => false, 'message' => 'Error al cancelar la orden.'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error al cancelar orden: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }
}
