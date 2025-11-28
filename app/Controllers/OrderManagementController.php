<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\OrderDetail;
use App\Models\OrderStatus;
use App\Utils\Session;
use App\Utils\Email;
use App\Middleware\PermissionMiddleware;

class OrderManagementController extends BaseController
{
    /**
     * Listar todas las órdenes (gestión administrativa)
     */
    public function index()
    {
        PermissionMiddleware::requireOrderManagement();
        
        $orderModel = new Order();
        $allOrders = $orderModel->getAll();

        $this->view('vistas.admin.orders', [
            'title' => 'Gestionar Órdenes',
            'pageCss' => 'admin',
            'orders' => $allOrders
        ]);
    }

    /**
     * Ver detalles de una orden específica (gestión)
     */
    public function show($orderId)
    {
        PermissionMiddleware::requireOrderManagement();
        
        $orderModel = new Order();
        $order = $orderModel->getOrderWithUserDetails($orderId);
        
        if (!$order) {
            Session::flash('error', 'Orden no encontrada.');
            $this->redirect('/management/orders');
            return;
        }

        $items = $orderModel->getItems($orderId);
        $statusHistory = $orderModel->getStatusHistory($orderId);
        
        // Obtener estado actual y transiciones válidas
        $orderStatusModel = new OrderStatus();
        $currentStatus = $orderModel->getCurrentStatus($orderId);
        $allStatusTypes = $orderStatusModel->getAllStatusTypes();
        $validTransitions = [];
        
        if ($currentStatus) {
            $validTransitions = $orderStatusModel->getValidTransitions($currentStatus['idcompraestadotipo']);
        }

        $this->view('vistas.admin.order_detail', [
            'title' => 'Detalles de Orden #' . $orderId,
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
     * Actualizar estado de orden
     */
    public function updateStatus($orderId)
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $newStatus = intval($_POST['status'] ?? 0);
            $notes = $_POST['notes'] ?? '';
            
            if (!$newStatus) {
                $this->json(['success' => false, 'message' => 'Estado requerido.']);
                return;
            }

            $orderStatusModel = new OrderStatus();
            $orderModel = new Order();
            
            // Obtener información de la orden y usuario
            $order = $orderModel->getOrderWithUserDetails($orderId);
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Orden no encontrada.']);
                return;
            }

            // Cambiar estado
            if ($orderStatusModel->changeStatus($orderId, $newStatus, $notes)) {
                // Obtener nombre del estado para el email
                $statusName = $orderStatusModel->getStatusName($newStatus);
                
                // Enviar notificación por email
                $email = new Email();
                $email->sendOrderStatusNotification(
                    $order['usmail'],
                    $order['usnombre'],
                    $orderId,
                    $statusName,
                    $notes ?: 'Tu pedido ha cambiado de estado.'
                );
                
                // Si se cancela, restaurar stock
                if ($newStatus == 5) { // 5 = Cancelada
                    $this->restoreStockFromOrder($orderId);
                }
                
                $this->json([
                    'success' => true, 
                    'message' => 'Estado actualizado exitosamente.',
                    'new_status' => $statusName
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Error al actualizar el estado.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener órdenes por estado (endpoint AJAX)
     */
    public function getByStatus($statusId = null)
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $orderModel = new Order();
            
            if ($statusId && $statusId !== 'all') {
                $orders = $orderModel->getByStatus($statusId);
            } else {
                $orders = $orderModel->getAllWithUserDetails();
            }

            $this->json([
                'success' => true,
                'orders' => $orders,
                'count' => count($orders)
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener estadísticas de órdenes (endpoint AJAX)
     */
    public function statistics($period = 'month')
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $orderModel = new Order();
            
            switch ($period) {
                case 'week':
                    $startDate = date('Y-m-d', strtotime('-7 days'));
                    break;
                case 'month':
                    $startDate = date('Y-m-d', strtotime('-30 days'));
                    break;
                case 'year':
                    $startDate = date('Y-m-d', strtotime('-365 days'));
                    break;
                default:
                    $startDate = date('Y-m-d', strtotime('-30 days'));
            }

            $orders = $orderModel->getOrdersSince($startDate);
            
            // Calcular estadísticas
            $stats = [
                'total_orders' => count($orders),
                'total_revenue' => 0,
                'average_order_value' => 0,
                'orders_by_status' => [],
                'orders_by_day' => [],
                'top_products' => []
            ];

            $productSales = [];

            foreach ($orders as $order) {
                $stats['total_revenue'] += $order['total'];
                
                // Agrupar por estado
                $statusId = $order['estado_id'];
                $stats['orders_by_status'][$statusId] = ($stats['orders_by_status'][$statusId] ?? 0) + 1;
                
                // Agrupar por día
                $day = date('Y-m-d', strtotime($order['cofecha']));
                if (!isset($stats['orders_by_day'][$day])) {
                    $stats['orders_by_day'][$day] = ['count' => 0, 'revenue' => 0];
                }
                $stats['orders_by_day'][$day]['count']++;
                $stats['orders_by_day'][$day]['revenue'] += $order['total'];
                
                // Contar productos vendidos
                $items = $orderModel->getItems($order['idcompra']);
                foreach ($items as $item) {
                    $productId = $item['idproducto'];
                    if (!isset($productSales[$productId])) {
                        $productSales[$productId] = [
                            'name' => $item['pronombre'],
                            'quantity' => 0,
                            'revenue' => 0
                        ];
                    }
                    $productSales[$productId]['quantity'] += $item['cicantidad'];
                    $productSales[$productId]['revenue'] += $item['cicantidad'] * $item['ciprecio'];
                }
            }

            if ($stats['total_orders'] > 0) {
                $stats['average_order_value'] = $stats['total_revenue'] / $stats['total_orders'];
            }

            // Top 5 productos más vendidos
            uasort($productSales, function($a, $b) {
                return $b['quantity'] - $a['quantity'];
            });
            $stats['top_products'] = array_slice($productSales, 0, 5, true);

            $this->json([
                'success' => true,
                'period' => $period,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Buscar órdenes por diversos criterios
     */
    public function search()
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $query = $_GET['q'] ?? '';
            $statusId = $_GET['status'] ?? '';
            $dateFrom = $_GET['date_from'] ?? '';
            $dateTo = $_GET['date_to'] ?? '';
            $userId = $_GET['user_id'] ?? '';
            
            $orderModel = new Order();
            $orders = $orderModel->searchOrders($query, $statusId, $dateFrom, $dateTo, $userId);
            
            $this->json([
                'success' => true,
                'orders' => $orders,
                'count' => count($orders),
                'filters' => [
                    'query' => $query,
                    'status' => $statusId,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'user_id' => $userId
                ]
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Exportar órdenes a CSV
     */
    public function exportCSV()
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $orderModel = new Order();
            $orders = $orderModel->getAllWithUserDetails();
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="orders_' . date('Y-m-d') . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Encabezados CSV
            fputcsv($output, [
                'ID Orden',
                'Usuario',
                'Email',
                'Total',
                'Estado Actual',
                'Fecha Creación',
                'Última Actualización'
            ]);
            
            // Datos
            foreach ($orders as $order) {
                fputcsv($output, [
                    $order['idcompra'],
                    $order['usnombre'],
                    $order['usmail'],
                    $order['total'],
                    $order['estado_nombre'],
                    $order['cofecha'],
                    $order['ultima_actualizacion'] ?? $order['cofecha']
                ]);
            }
            
            fclose($output);
        } catch (\Exception $e) {
            Session::flash('error', 'Error al exportar: ' . $e->getMessage());
            $this->redirect('/management/orders');
        }
    }

    /**
     * Vista de órdenes pendientes
     */
    public function pending()
    {
        PermissionMiddleware::requireOrderManagement();
        
        $orderModel = new Order();
        $pendingOrders = $orderModel->getByStatus(1); // Estado "iniciada"

        $this->view('vistas.admin.orders', [
            'title' => 'Órdenes Pendientes',
            'pageCss' => 'admin',
            'orders' => $pendingOrders
        ]);
    }

    /**
     * Procesar múltiples órdenes
     */
    public function bulkUpdate()
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $orderIds = $_POST['order_ids'] ?? [];
            $newStatus = intval($_POST['status'] ?? 0);
            $notes = $_POST['notes'] ?? '';
            
            if (empty($orderIds) || !$newStatus) {
                $this->json(['success' => false, 'message' => 'Datos requeridos faltantes.']);
                return;
            }

            $orderStatusModel = new OrderStatus();
            $orderModel = new Order();
            $email = new Email();
            
            $updated = 0;
            $errors = [];

            foreach ($orderIds as $orderId) {
                try {
                    $order = $orderModel->getOrderWithUserDetails($orderId);
                    if (!$order) {
                        $errors[] = "Orden #$orderId no encontrada";
                        continue;
                    }

                    if ($orderStatusModel->changeStatus($orderId, $newStatus, $notes)) {
                        $updated++;
                        
                        // Obtener nombre del estado
                        $statusName = $orderStatusModel->getStatusName($newStatus);
                        
                        // Enviar notificación
                        $email->sendOrderStatusNotification(
                            $order['usmail'],
                            $order['usnombre'],
                            $orderId,
                            $statusName,
                            $notes ?: 'Actualización masiva de pedidos.'
                        );
                        
                        // Si se cancela, restaurar stock
                        if ($newStatus == 5) {
                            $this->restoreStockFromOrder($orderId);
                        }
                    } else {
                        $errors[] = "Error al actualizar orden #$orderId";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error en orden #$orderId: " . $e->getMessage();
                }
            }

            $message = "Se actualizaron $updated órdenes exitosamente.";
            if (!empty($errors)) {
                $message .= " Errores: " . implode(', ', $errors);
            }

            $this->json([
                'success' => $updated > 0,
                'message' => $message,
                'updated' => $updated,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener resumen de órdenes para dashboard
     */
    public function dashboardSummary()
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $orderModel = new Order();
            
            // Contadores por estado
            $summary = [
                'pending' => count($orderModel->getByStatus(1)),     
                'confirmed' => count($orderModel->getByStatus(2)),    
                'processing' => count($orderModel->getByStatus(3)),  
                'shipped' => count($orderModel->getByStatus(4)),     
                'cancelled' => count($orderModel->getByStatus(5)),   
                'total_today' => count($orderModel->getTodayOrders()),
                'revenue_today' => $orderModel->getTodayRevenue(),
                'recent_orders' => $orderModel->getRecentOrders(10)
            ];

            $this->json([
                'success' => true,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Restaurar stock de productos de una orden cancelada
     */
    private function restoreStockFromOrder($orderId)
    {
        try {
            $orderModel = new Order();
            $productModel = new Product();
            
            $items = $orderModel->getItems($orderId);
            
            foreach ($items as $item) {
                $productModel->increaseStock($item['idproducto'], $item['cicantidad']);
            }
        } catch (\Exception $e) {
            // Log error pero no interrumpir el proceso
            error_log('Error al restaurar stock de orden ' . $orderId . ': ' . $e->getMessage());
        }
    }
}