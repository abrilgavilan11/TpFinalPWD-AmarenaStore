<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use App\Utils\Auth;
use App\Utils\Email;
use App\Utils\Session;

class AdminOrderController extends BaseController
{
    /**
     * Constructor: verificar que sea admin
     */
    public function __construct()
    {
        Auth::requireAdmin();
    }

    /**
     * Muestra todas las órdenes (panel admin)
     */
    public function index()
    {
        $orderModel = new Order();
        $orders = $orderModel->getAll();
        
        $this->view('vistas.admin.orders', [
            'title' => 'Gestionar Órdenes - Admin',
            'pageCss' => 'admin',
            'orders' => $orders
        ]);
    }

    /**
     * Muestra detalle de una orden con historial de cambios
     */
    public function show($orderId)
    {
        $orderModel = new Order();
        $order = $orderModel->findById($orderId);
        
        if (!$order) {
            Session::flash('error', 'Orden no encontrada.');
            $this->redirect('/admin/ordenes');
            return;
        }
        
        $items = $orderModel->getItems($orderId);
        $statusHistory = $orderModel->getStatusHistory($orderId);
        $currentStatus = $orderModel->getCurrentStatus($orderId);
        
        $statusModel = new OrderStatus();
        $validTransitions = $statusModel->getValidTransitions($currentStatus['idcompraestadotipo']);
        $allStatusTypes = $statusModel->getAllStatusTypes();
        
        $this->view('vistas.admin.order_detail', [
            'title' => 'Detalle de Orden #' . $orderId . ' - Admin',
            'pageCss' => 'admin',
            'order' => $order,
            'items' => $items,
            'statusHistory' => $statusHistory,
            'currentStatus' => $currentStatus,
            'validTransitions' => $validTransitions,
            'allStatusTypes' => $allStatusTypes
        ]);
    }

    /**
     * Cambia el estado de una orden
     * Endpoint AJAX para cambiar estado de compra
     */
    public function changeStatus()
    {
        try {
            $orderId = intval($_POST['order_id'] ?? 0);
            $newStatusId = intval($_POST['status_id'] ?? 0);
            
            if (!$orderId || !$newStatusId) {
                $this->json(['success' => false, 'message' => 'Parámetros requeridos.'], 400);
                return;
            }
            
            $orderModel = new Order();
            $order = $orderModel->findById($orderId);
            
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Orden no encontrada.'], 404);
                return;
            }
            
            $orderStatusModel = new OrderStatus();
            $currentStatus = $orderModel->getCurrentStatus($orderId);
            
            // Validar transición
            $validTransitions = $orderStatusModel->getValidTransitions($currentStatus['idcompraestadotipo']);
            if (!in_array($newStatusId, $validTransitions)) {
                $this->json([
                    'success' => false, 
                    'message' => 'Transición de estado no válida.'
                ], 400);
                return;
            }
            
            // Cambiar estado
            if ($orderStatusModel->changeStatus($orderId, $newStatusId)) {
                $newStatus = $orderStatusModel->getStatusTypeById($newStatusId);
                
                $email = new Email();
                $email->sendOrderStatusNotification(
                    $order['usmail'],
                    $order['usnombre'],
                    $orderId,
                    $newStatus['cetdescripcion'],
                    $newStatus['cetdetalle']
                );
                
                if ($newStatusId == 5) {
                    $items = $orderModel->getItems($orderId);
                    $productModel = new Product();
                    foreach ($items as $item) {
                        $productModel->increaseStock($item['idproducto'], $item['cicantidad']);
                    }
                }
                
                $this->json([
                    'success' => true, 
                    'message' => 'Estado actualizado a: ' . $newStatus['cetdescripcion']
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Error al cambiar estado.'], 500);
            }
        } catch (\Exception $e) {
            error_log("Error al cambiar estado de orden: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }

    /**
     * Obtiene estadísticas de órdenes 
     * Endpoint AJAX para estadísticas
     */
    public function stats()
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
            
            $this->json(['success' => true, 'stats' => $stats]);
        } catch (\Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno.'], 500);
        }
    }
}
