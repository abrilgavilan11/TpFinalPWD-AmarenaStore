<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Utils\Session;
use App\Middleware\PermissionMiddleware;

class ManagementController extends BaseController
{
    /**
     * Constructor: verificar que sea admin
     */
    public function __construct()
    {
        PermissionMiddleware::requireAdmin();
    }

    /**
     * Dashboard principal de gestión
     */
    public function index()
    {
        $this->view('vistas.admin.dashboard', [
            'title' => 'Dashboard de Gestión',
            'pageCss' => 'admin'
        ]);
    }

    /**
     * Dashboard de órdenes con estadísticas
     */
    public function ordersDashboard()
    {
        PermissionMiddleware::requireOrderManagement();
        
        $this->view('management.dashboard.orders', [
            'title' => 'Dashboard de Órdenes',
            'pageCss' => 'admin'
        ]);
    }

    /**
     * API endpoint para estadísticas del dashboard
     */
    public function dashboardStats()
    {
        try {
            $orderModel = new Order();
            $productModel = new Product();
            
            // Obtener estadísticas básicas
            $allOrders = $orderModel->getAll();
            $totalOrders = count($allOrders);
            
            // Productos con stock bajo
            $lowStockProducts = $productModel->getLowStockProducts(10);
            $lowStockCount = count($lowStockProducts);
            
            // Estadísticas por estado de orden
            $ordersByStatus = [];
            
            foreach ($allOrders as $order) {
                $status = $order['estado_actual'] ?? 'pendiente';
                $ordersByStatus[$status] = ($ordersByStatus[$status] ?? 0) + 1;
            }

            $this->json([
                'success' => true,
                'stats' => [
                    'total_orders' => $totalOrders,
                    'low_stock_products' => $lowStockCount,
                    'orders_by_status' => $ordersByStatus,
                    'last_updated' => date('Y-m-d H:i:s')
                ]
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Lista de productos para gestión
     */
    public function products()
    {
        PermissionMiddleware::requireProductManagement();
        
        $productModel = new Product();
        $allProducts = $productModel->getAll();

        $this->view('vistas.admin.products', [
            'title' => 'Gestionar Productos',
            'pageCss' => 'admin',
            'products' => $allProducts
        ]);
    }

    /**
     * Lista de categorías para gestión
     */
    public function categories()
    {
        PermissionMiddleware::requireCategoryManagement();
        
        $categoryModel = new Category();
        $allCategories = $categoryModel->getAllWithProductCount(false);

        $this->view('vistas.admin.categories', [
            'title' => 'Gestionar Categorías',
            'pageCss' => 'admin',
            'categories' => $allCategories
        ]);
    }

    /**
     * Formulario para crear nueva categoría
     */
    public function createCategoryForm()
    {
        PermissionMiddleware::requireCategoryManagement();
        
        $this->view('vistas.admin.category_form', [
            'title' => 'Crear Categoría',
            'pageCss' => 'admin',
            'action' => 'create'
        ]);
    }

    /**
     * Formulario para editar categoría
     */
    public function editCategoryForm($categoryId)
    {
        PermissionMiddleware::requireCategoryManagement();
        
        $categoryModel = new Category();
        $category = $categoryModel->findById($categoryId);
        
        if (!$category) {
            Session::flash('error', 'Categoría no encontrada.');
            $this->redirect('/categories');
            return;
        }

        $this->view('vistas.admin.category_form', [
            'title' => 'Editar Categoría',
            'pageCss' => 'admin',
            'category' => $category,
            'action' => 'edit'
        ]);
    }

    /**
     * Formulario para crear nuevo producto
     */
    public function createProductForm()
    {
        PermissionMiddleware::requireProductManagement();
        
        $categoryModel = new Category();
        $allCategories = $categoryModel->getAll();

        $this->view('vistas.admin.product_form', [
            'title' => 'Crear Producto',
            'pageCss' => 'admin',
            'categories' => $allCategories,
            'action' => 'create'
        ]);
    }

    /**
     * Formulario para editar producto
     */
    public function editProductForm($productId)
    {
        PermissionMiddleware::requireProductManagement();
        
        $productModel = new Product();
        $product = $productModel->findById($productId);
        
        if (!$product) {
            Session::flash('error', 'Producto no encontrado.');
            $this->redirect('/management/products');
            return;
        }

        $categoryModel = new Category();
        $allCategories = $categoryModel->getAll();

        $this->view('vistas.admin.product_form', [
            'title' => 'Editar Producto',
            'pageCss' => 'admin',
            'product' => $product,
            'categories' => $allCategories,
            'action' => 'edit'
        ]);
    }

    /**
     * Crear nueva categoría
     */
    public function storeCategory()
    {
        PermissionMiddleware::requireCategoryManagement();
        
        try {
            $categoryModel = new Category();
            
            $categoryData = [
                'catnombre' => $_POST['name'],
                'catdescripcion' => $_POST['description'] ?? null,
                'activo' => 1
            ];

            if ($categoryModel->create($categoryData)) {
                Session::flash('success', 'Categoría creada exitosamente.');
            } else {
                Session::flash('error', 'Error al crear la categoría.');
            }
            
            $this->redirect('/management/categories');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/management/categories/create');
        }
    }

    /**
     * Actualizar categoría existente
     */
    public function updateCategory($categoryId)
    {
        PermissionMiddleware::requireCategoryManagement();
        
        try {
            $categoryModel = new Category();
            
            $categoryData = [
                'catnombre' => $_POST['name'],
                'catdescripcion' => $_POST['description'] ?? null
            ];

            if ($categoryModel->update($categoryId, $categoryData)) {
                Session::flash('success', 'Categoría actualizada exitosamente.');
            } else {
                Session::flash('error', 'Error al actualizar la categoría.');
            }
            
            $this->redirect('/management/categories');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/management/categories/edit/' . $categoryId);
        }
    }

    /**
     * Eliminar categoría
     */
    public function deleteCategory($categoryId)
    {
        PermissionMiddleware::requireCategoryManagement();
        
        try {
            $categoryModel = new Category();
            
            if ($categoryModel->delete($categoryId)) {
                $this->json(['success' => true, 'message' => 'Categoría eliminada exitosamente.']);
            } else {
                $this->json(['success' => false, 'message' => 'Error al eliminar la categoría.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle estado de categoría (activo/inactivo)
     */
    public function toggleCategoryStatus($categoryId)
    {
        PermissionMiddleware::requireCategoryManagement();
        
        try {
            $categoryModel = new Category();
            $category = $categoryModel->findById($categoryId);
            
            if (!$category) {
                $this->json(['success' => false, 'message' => 'Categoría no encontrada.']);
                return;
            }

            $newStatus = $category['activo'] ? 0 : 1;
            
            if ($categoryModel->toggleActive($categoryId, $newStatus)) {
                $this->json([
                    'success' => true, 
                    'message' => 'Estado actualizado exitosamente.',
                    'new_status' => $newStatus
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Error al cambiar el estado.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Crear nuevo producto
     */
    public function storeProduct()
    {
        PermissionMiddleware::requireProductManagement();
        
        try {
            $productModel = new Product();
            
            // Procesar imagen si se subió
            $imageName = 'default.jpg';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageName = $this->handleImageUpload($_FILES['image']);
            }

            $productData = [
                'pronombre' => $_POST['name'],
                'prodetalle' => $_POST['description'],
                'procantstock' => intval($_POST['stock']),
                'proprecio' => floatval($_POST['price']),
                'idcategoria' => intval($_POST['category']),
                'proimagen' => $imageName
            ];

            if ($productModel->create($productData)) {
                Session::flash('success', 'Producto creado exitosamente.');
            } else {
                Session::flash('error', 'Error al crear el producto.');
            }
            
            $this->redirect('/management/products');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/management/products/create');
        }
    }

    /**
     * Actualizar producto existente
     */
    public function updateProduct($productId)
    {
        PermissionMiddleware::requireProductManagement();
        
        try {
            $productModel = new Product();
            $product = $productModel->findById($productId);
            
            if (!$product) {
                Session::flash('error', 'Producto no encontrado.');
                $this->redirect('/management/products');
                return;
            }

            // Procesar nueva imagen si se subió
            $imageName = $product['proimagen']; // mantener imagen actual
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $imageName = $this->handleImageUpload($_FILES['image']);
            }

            $productData = [
                'pronombre' => $_POST['name'],
                'prodetalle' => $_POST['description'],
                'procantstock' => intval($_POST['stock']),
                'proprecio' => floatval($_POST['price']),
                'idcategoria' => intval($_POST['category']),
                'proimagen' => $imageName
            ];

            if ($productModel->update($productId, $productData)) {
                Session::flash('success', 'Producto actualizado exitosamente.');
            } else {
                Session::flash('error', 'Error al actualizar el producto.');
            }
            
            $this->redirect('/management/products');
        } catch (\Exception $e) {
            Session::flash('error', 'Error: ' . $e->getMessage());
            $this->redirect('/management/products/edit/' . $productId);
        }
    }

    /**
     * Eliminar producto
     */
    public function deleteProduct($productId)
    {
        PermissionMiddleware::requireProductManagement();
        
        try {
            $productModel = new Product();
            
            if ($productModel->delete($productId)) {
                $this->json(['success' => true, 'message' => 'Producto eliminado exitosamente.']);
            } else {
                $this->json(['success' => false, 'message' => 'Error al eliminar el producto.']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Verifica stock de productos (endpoint AJAX)
     */
    public function checkStock()
    {
        PermissionMiddleware::requireProductManagement();
        
        try {
            $productModel = new Product();
            $requestData = json_decode(file_get_contents('php://input'), true);
            
            if (!$requestData || !isset($requestData['items'])) {
                $this->json(['success' => false, 'message' => 'Datos inválidos.']);
                return;
            }

            $stockInfo = [];
            $allAvailable = true;

            foreach ($requestData['items'] as $item) {
                $productId = $item['id'];
                $requestedQuantity = $item['quantity'];
                
                $product = $productModel->findById($productId);
                
                if (!$product) {
                    $stockInfo[] = [
                        'id' => $productId,
                        'available' => false,
                        'stock' => 0,
                        'requested' => $requestedQuantity,
                        'message' => 'Producto no encontrado'
                    ];
                    $allAvailable = false;
                } else {
                    $available = $product['procantstock'] >= $requestedQuantity;
                    if (!$available) $allAvailable = false;
                    
                    $stockInfo[] = [
                        'id' => $productId,
                        'name' => $product['pronombre'],
                        'available' => $available,
                        'stock' => $product['procantstock'],
                        'requested' => $requestedQuantity,
                        'message' => $available ? 'Disponible' : 'Stock insuficiente'
                    ];
                }
            }

            $this->json([
                'success' => true,
                'all_available' => $allAvailable,
                'items' => $stockInfo
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Actualiza stock de productos (endpoint AJAX)
     */
    public function updateStock()
    {
        PermissionMiddleware::requireProductManagement();
        
        try {
            $productModel = new Product();
            $requestData = json_decode(file_get_contents('php://input'), true);
            
            if (!$requestData || !isset($requestData['updates'])) {
                $this->json(['success' => false, 'message' => 'Datos inválidos.']);
                return;
            }

            $results = [];
            foreach ($requestData['updates'] as $update) {
                $productId = $update['id'];
                $newStock = $update['stock'];
                
                if ($productModel->updateStock($productId, $newStock)) {
                    $results[] = ['id' => $productId, 'success' => true];
                } else {
                    $results[] = ['id' => $productId, 'success' => false, 'message' => 'Error al actualizar'];
                }
            }

            $this->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtiene productos con stock bajo (endpoint AJAX)
     */
    public function lowStockProducts()
    {
        PermissionMiddleware::requireProductManagement();
        
        try {
            $productModel = new Product();
            $threshold = $_GET['threshold'] ?? 10;
            
            $lowStockProducts = $productModel->getLowStockProducts($threshold);
            
            $this->json([
                'success' => true,
                'products' => $lowStockProducts,
                'count' => count($lowStockProducts)
            ]);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    /**
     * Maneja la subida de imágenes de productos
     */
    private function handleImageUpload($file): string
    {
        $uploadDir = PUBLIC_PATH . '/uploads/products/';
        
        // Crear directorio si no existe
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new \Exception('Tipo de archivo no permitido. Solo se permiten imágenes.');
        }

        $maxSize = 5 * 1024 * 1024; // 5MB
        if ($file['size'] > $maxSize) {
            throw new \Exception('El archivo es demasiado grande. Máximo 5MB.');
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            throw new \Exception('Error al subir la imagen.');
        }

        return $filename;
    }

    /**
     * Lista de órdenes para gestión
     */
    public function orders()
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $orderModel = new Order();
            $allOrders = $orderModel->getAll();

            $this->view('vistas.admin.orders', [
                'title' => 'Gestionar Órdenes',
                'pageCss' => 'admin',
                'orders' => $allOrders
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Error al cargar las órdenes: ' . $e->getMessage());
            $this->redirect('/management');
        }
    }

    /**
     * Mostrar detalles de una orden específica
     */
    public function showOrder($orderId)
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            $orderModel = new Order();
            
            // Obtener datos de la orden
            $order = $orderModel->getOrderWithUserDetails($orderId);
            if (!$order) {
                Session::flash('error', 'Orden no encontrada.');
                $this->redirect('/management/orders');
                return;
            }

            // Obtener items de la orden
            $items = $orderModel->getItems($orderId);
            
            // Obtener historial de estados
            $statusHistory = $orderModel->getStatusHistory($orderId);
            
            // Obtener estado actual
            $currentStatus = $orderModel->getCurrentStatus($orderId);
            
            // Obtener todos los tipos de estado
            $allStatusTypes = $orderModel->getStatusTypes();

            $this->view('vistas.admin.order_detail', [
                'title' => 'Detalles de Orden #' . $orderId,
                'pageCss' => 'admin',
                'order' => $order,
                'items' => $items,
                'statusHistory' => $statusHistory,
                'currentStatus' => $currentStatus,
                'allStatusTypes' => $allStatusTypes
            ]);
        } catch (\Exception $e) {
            Session::flash('error', 'Error al cargar los detalles: ' . $e->getMessage());
            $this->redirect('/management/orders');
        }
    }

    /**
     * Actualizar estado de una orden (AJAX)
     */
    public function updateOrderStatus($orderId)
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Método no permitido']);
                return;
            }

            $newStatusId = intval($_POST['status'] ?? 0);
            $notes = $_POST['notes'] ?? '';
            
            if ($newStatusId <= 0) {
                $this->json(['success' => false, 'message' => 'Estado inválido']);
                return;
            }

            $orderModel = new Order();
            
            // Verificar que la orden existe
            $order = $orderModel->findById($orderId);
            
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Orden no encontrada']);
                return;
            }

            // Actualizar estado
            if ($orderModel->updateStatus($orderId, $newStatusId)) {
                // Obtener nombre del nuevo estado
                $allStatusTypes = $orderModel->getStatusTypes();
                
                $newStatusName = '';
                foreach ($allStatusTypes as $status) {
                    if ($status['idcompraestadotipo'] == $newStatusId) {
                        $newStatusName = $status['cetdescripcion'];
                        break;
                    }
                }
                
                $this->json([
                    'success' => true, 
                    'message' => 'Estado actualizado a: ' . $newStatusName,
                    'new_status' => $newStatusName
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Error al actualizar el estado']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    /**
     * Eliminar una orden (AJAX)
     */
    public function deleteOrder($orderId)
    {
        PermissionMiddleware::requireOrderManagement();
        
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->json(['success' => false, 'message' => 'Método no permitido']);
                return;
            }

            $orderModel = new Order();
            
            // Verificar que la orden existe
            $order = $orderModel->findById($orderId);
            if (!$order) {
                $this->json(['success' => false, 'message' => 'Orden no encontrada']);
                return;
            }

            // Eliminar orden (esto también debería eliminar los items relacionados)
            if ($orderModel->delete($orderId)) {
                $this->json([
                    'success' => true, 
                    'message' => 'Orden #' . $orderId . ' eliminada correctamente'
                ]);
            } else {
                $this->json(['success' => false, 'message' => 'Error al eliminar la orden de la base de datos']);
            }
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}