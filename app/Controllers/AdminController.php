<?php

namespace App\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Utils\Session;
use App\Utils\Auth;

class AdminController extends BaseController
{
    public function __construct()
    {
        Auth::requireAdmin();
    }

    public function index()
    {
        // Muestra la vista del panel principal del administrador.
        $this->view('vistas.admin.dashboard', [
            'title' => 'Dashboard - Admin',
            'pageCss' => 'admin'
        ]);
    }

    //==================== GESTIÓN DE ÓRDENES ====================
    public function orders()
    {
        // Muestra la vista para gestionar órdenes.
        $this->view('vistas.admin.orders', [
            'title' => 'Gestionar Órdenes - Admin',
            'pageCss' => 'admin'
        ]);
    }

    //==================== GESTIÓN DE PRODUCTOS ====================

    public function products()
    {
        // Obtenemos todos los productos desde el modelo.
        $productModel = new Product();
        $allProducts = $productModel->getAll();

        // Muestra la vista para gestionar productos.
        $this->view('vistas.admin.products', [
            'title' => 'Gestionar Productos - Admin',
            'pageCss' => 'admin',
            'products' => $allProducts
        ]);
    }

    public function createProductForm()
    {
        // Obtenemos todas las categorías para el menú desplegable del formulario.
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        // Mostramos la vista del formulario.
        $this->view('vistas.admin.product_form', [
            'title' => 'Agregar Producto - Admin',
            'pageCss' => 'admin-forms',
            'categories' => $categories
        ]);
    }

    public function storeProduct()
    {
        // Recolecta y valida los datos del formulario
        $nombre = trim($_POST['pronombre'] ?? '');
        $detalle = trim($_POST['prodetalle'] ?? '');
        $precio = filter_var($_POST['proprecio'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['procantstock'] ?? 0, FILTER_VALIDATE_INT);
        $categoriaId = filter_var($_POST['idcategoria'] ?? null, FILTER_VALIDATE_INT);
        $imagen = $_FILES['proimagen'] ?? null;

        if (empty($nombre) || empty($detalle) || $precio === false || $precio <= 0 || $stock === false || $stock < 0 || empty($categoriaId)) {
            Session::flash('error', 'Todos los campos son obligatorios y deben tener valores válidos.');
            $this->redirect('/admin/productos/nuevo');
            return;
        }

        // Maneja la subida de la imagen
        $nombreImagen = 'default.jpg';
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Genera un nombre de archivo único
            $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
            $nombreImagen = uniqid('product_', true) . '.' . $extension;
            
            // Mueve el archivo
            if (!move_uploaded_file($imagen['tmp_name'], $uploadDir . $nombreImagen)) {
                Session::flash('error', 'Hubo un error al subir la imagen.');
                $this->redirect('/admin/productos/nuevo');
                return;
            }
        }

        // Prepara los datos para el modelo
        $productData = [
            'pronombre' => $nombre,
            'prodetalle' => $detalle,
            'proprecio' => $precio,
            'procantstock' => $stock,
            'idcategoria' => $categoriaId,
            'proimagen' => $nombreImagen
        ];

        // Guarda en la base de datos a través del modelo
        $productModel = new Product();
        $productModel->create($productData);

        Session::flash('success', 'Producto agregado exitosamente.');
        $this->redirect('/admin/productos');
    }

    /**
     * Muestra el formulario para editar un producto existente.
     */
    public function editProductForm($id)
    {
        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product) {
            Session::flash('error', 'Producto no encontrado.');
            $this->redirect('/admin/productos');
            return;
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        $this->view('vistas.admin.product_form', [
            'title' => 'Editar Producto - Admin',
            'pageCss' => 'admin-forms',
            'categories' => $categories,
            'product' => $product
        ]);
    }

    /**
     * Procesa la actualización de un producto existente.
     */
    public function updateProduct($id)
    {
        // Recolecta y valida los datos
        $nombre = trim($_POST['pronombre'] ?? '');
        $detalle = trim($_POST['prodetalle'] ?? '');
        $precio = filter_var($_POST['proprecio'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($_POST['procantstock'] ?? 0, FILTER_VALIDATE_INT);
        $categoriaId = filter_var($_POST['idcategoria'] ?? null, FILTER_VALIDATE_INT);
        $imagen = $_FILES['proimagen'] ?? null;

        if (empty($nombre) || empty($detalle) || $precio === false || $precio <= 0 || $stock === false || $stock < 0 || empty($categoriaId)) {
            Session::flash('error', 'Todos los campos son obligatorios.');
            $this->redirect('/admin/productos/editar/' . $id);
            return;
        }

        $productModel = new Product();
        $currentProduct = $productModel->findById($id);

        // Maneja la subida de la imagen (si se proporciona una nueva)
        $nombreImagen = $currentProduct['proimagen'];

        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Genera un nombre de archivo único
            $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
            $nombreImagen = uniqid('product_', true) . '.' . $extension;
            
            // Mueve el nuevo archivo
            if (!move_uploaded_file($imagen['tmp_name'], $uploadDir . $nombreImagen)) {
                Session::flash('error', 'Hubo un error al subir la nueva imagen.');
                $this->redirect('/admin/productos/editar/' . $id);
                return;
            }
        }

        // Prepara los datos para el modelo, incluyendo la imagen
        $productData = [
            'pronombre' => $nombre,
            'prodetalle' => $detalle,
            'proprecio' => $precio,
            'procantstock' => $stock,
            'idcategoria' => $categoriaId,
            'proimagen' => $nombreImagen, 
        ];

        // Actualiza en la base de datos
        $productModel->update($id, $productData);

        Session::flash('success', 'Producto actualizado exitosamente.');
        $this->redirect('/admin/productos');
    }

    /**
     * Elimina un producto.
     */
    public function deleteProduct($id)
    {
        $productModel = new Product();
        $product = $productModel->findById($id);

        if (!$product) {
            Session::flash('error', 'Producto no encontrado.');
            $this->redirect('/admin/productos');
            return;
        }

        $productModel->delete($id);

        Session::flash('success', 'Producto eliminado exitosamente.');
        $this->redirect('/admin/productos');
    }

    /**
     * Obtiene el stock actual de un producto
     * Endpoint AJAX para verificar disponibilidad
     */
    public function checkStock()
    {
        $productId = intval($_POST['product_id'] ?? 0);
        $quantity = intval($_POST['quantity'] ?? 1);

        if (!$productId || $quantity < 1) {
            $this->json(['success' => false, 'message' => 'Parámetros inválidos'], 400);
            return;
        }

        $productModel = new Product();
        $product = $productModel->findById($productId);

        if (!$product) {
            $this->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
            return;
        }

        $hasStock = $product['procantstock'] >= $quantity;

        $this->json([
            'success' => true,
            'product_id' => $productId,
            'current_stock' => $product['procantstock'],
            'requested_quantity' => $quantity,
            'has_stock' => $hasStock,
            'message' => $hasStock ? 
                'Stock disponible' : 
                'Stock insuficiente. Disponibles: ' . $product['procantstock']
        ]);
    }

    /**
     * Actualiza el stock manualmente desde admin
     * Endpoint AJAX para ajustar stock
     */
    public function updateStock()
    {
        Auth::requireAdmin();

        try {
            $productId = intval($_POST['product_id'] ?? 0);
            $newStock = intval($_POST['new_stock'] ?? 0);
            $reason = trim($_POST['reason'] ?? 'Ajuste manual');

            if (!$productId || $newStock < 0) {
                $this->json(['success' => false, 'message' => 'Parámetros inválidos'], 400);
                return;
            }

            $productModel = new Product();
            $product = $productModel->findById($productId);

            if (!$product) {
                $this->json(['success' => false, 'message' => 'Producto no encontrado'], 404);
                return;
            }

            $oldStock = $product['procantstock'];

            // Actualizar el stock
            $sql = "UPDATE producto SET procantstock = ? WHERE idproducto = ?";
            $db = \App\Utils\Database::getInstance();
            $db->query($sql, [$newStock, $productId]);

            // Registrar el cambio
            error_log("Stock actualizado - Producto: {$productId}, Anterior: {$oldStock}, Nuevo: {$newStock}, Razón: {$reason}");

            $this->json([
                'success' => true,
                'message' => "Stock actualizado: {$oldStock} → {$newStock}",
                'old_stock' => $oldStock,
                'new_stock' => $newStock
            ]);
        } catch (\Exception $e) {
            error_log("Error al actualizar stock: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }

    /**
     * Obtiene productos con bajo stock
     * Endpoint para alertas de reorden
     */
    public function lowStockProducts()
    {
        Auth::requireAdmin();

        try {
            $threshold = intval($_GET['threshold'] ?? 5);

            $db = \App\Utils\Database::getInstance();
            $sql = "SELECT p.*, c.catnombre 
                    FROM producto p 
                    LEFT JOIN categoria c ON p.idcategoria = c.idcategoria 
                    WHERE p.procantstock <= ? 
                    ORDER BY p.procantstock ASC";
            
            $products = $db->fetchAll($sql, [$threshold]);

            $this->json([
                'success' => true,
                'threshold' => $threshold,
                'count' => count($products),
                'products' => $products
            ]);
        } catch (\Exception $e) {
            error_log("Error al obtener productos con bajo stock: " . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Error interno'], 500);
        }
    }

    // ==================== GESTIÓN DE CATEGORÍAS ====================

    /**
     * Muestra la lista de categorías con el conteo de productos.
     */
    public function categories()
    {
        try {
            $categoryModel = new Category();
            $allCategories = $categoryModel->getAllWithProductCount();

            $this->view('vistas.admin.categories', [
                'title' => 'Gestionar Categorías - Admin',
                'pageCss' => 'admin',
                'categories' => $allCategories
            ]);
        } catch (\Exception $e) {
            error_log("Error al cargar categorías: " . $e->getMessage());
            $this->view('vistas.admin.categories', [
                'title' => 'Gestionar Categorías - Admin',
                'pageCss' => 'admin',
                'categories' => [],
                'error' => 'Error al cargar las categorías'
            ]);
        }
    }

    /**
     * Muestra el formulario para crear una nueva categoría.
     */
    public function createCategoryForm()
    {
        $this->view('vistas.admin.category_form', [
            'title' => 'Nueva Categoría - Admin',
            'pageCss' => 'admin-forms',
            'action' => 'crear',
            'category' => null
        ]);
    }

    /**
     * Procesa la creación de una nueva categoría.
     */
    public function storeCategory()
    {
        try {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            // Validaciones básicas
            if (empty($name)) {
                Session::flash('error', 'El nombre de la categoría es obligatorio.');
                header('Location: /admin/categorias/nuevo');
                exit;
            }

            if (strlen($name) > 50) {
                Session::flash('error', 'El nombre no puede exceder 50 caracteres.');
                header('Location: /admin/categorias/nuevo');
                exit;
            }

            if (strlen($description) > 200) {
                Session::flash('error', 'La descripción no puede exceder 200 caracteres.');
                header('Location: /admin/categorias/nuevo');
                exit;
            }

            $categoryModel = new Category();
            
            // Verificar si ya existe una categoría con ese nombre
            if ($categoryModel->existsByName($name)) {
                Session::flash('error', 'Ya existe una categoría con ese nombre.');
                header('Location: /admin/categorias/nuevo');
                exit;
            }

            $result = $categoryModel->create([
                'catnombre' => $name,
                'catdescripcion' => $description
            ]);

            if ($result) {
                Session::flash('success', 'Categoría creada exitosamente.');
            } else {
                Session::flash('error', 'Error al crear la categoría.');
            }

        } catch (\Exception $e) {
            error_log("Error al crear categoría: " . $e->getMessage());
            Session::flash('error', 'Error interno al crear la categoría.');
        }

        header('Location: /admin/categorias');
        exit;
    }

    /**
     * Muestra el formulario para editar una categoría existente.
     */
    public function editCategoryForm($id)
    {
        try {
            $categoryModel = new Category();
            $category = $categoryModel->find($id);

            if (!$category) {
                Session::flash('error', 'Categoría no encontrada.');
                header('Location: /admin/categorias');
                exit;
            }

            $this->view('vistas.admin.category_form', [
                'title' => 'Editar Categoría - Admin',
                'pageCss' => 'admin-forms',
                'action' => 'editar',
                'category' => $category
            ]);

        } catch (\Exception $e) {
            error_log("Error al cargar categoría para edición: " . $e->getMessage());
            Session::flash('error', 'Error al cargar la categoría.');
            header('Location: /admin/categorias');
            exit;
        }
    }

    /**
     * Procesa la actualización de una categoría existente.
     */
    public function updateCategory($id)
    {
        try {
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            // Validaciones básicas
            if (empty($name)) {
                Session::flash('error', 'El nombre de la categoría es obligatorio.');
                header('Location: /admin/categorias/editar/' . $id);
                exit;
            }

            if (strlen($name) > 50) {
                Session::flash('error', 'El nombre no puede exceder 50 caracteres.');
                header('Location: /admin/categorias/editar/' . $id);
                exit;
            }

            if (strlen($description) > 200) {
                Session::flash('error', 'La descripción no puede exceder 200 caracteres.');
                header('Location: /admin/categorias/editar/' . $id);
                exit;
            }

            $categoryModel = new Category();
            
            // Verificar si la categoría existe
            $existingCategory = $categoryModel->find($id);
            if (!$existingCategory) {
                Session::flash('error', 'Categoría no encontrada.');
                header('Location: /admin/categorias');
                exit;
            }

            // Verificar si ya existe otra categoría con ese nombre
            if ($categoryModel->existsByName($name, $id)) {
                Session::flash('error', 'Ya existe otra categoría con ese nombre.');
                header('Location: /admin/categorias/editar/' . $id);
                exit;
            }

            $result = $categoryModel->update($id, [
                'catnombre' => $name,
                'catdescripcion' => $description
            ]);

            if ($result) {
                Session::flash('success', 'Categoría actualizada exitosamente.');
            } else {
                Session::flash('error', 'Error al actualizar la categoría.');
            }

        } catch (\Exception $e) {
            error_log("Error al actualizar categoría: " . $e->getMessage());
            Session::flash('error', 'Error interno al actualizar la categoría.');
        }

        header('Location: /admin/categorias');
        exit;
    }

    /**
     * Elimina una categoría si no tiene productos asociados.
     */
    public function deleteCategory($id)
    {
        try {
            $categoryModel = new Category();
            
            // Verificar si la categoría existe
            $category = $categoryModel->find($id);
            if (!$category) {
                Session::flash('error', 'Categoría no encontrada.');
                header('Location: /admin/categorias');
                exit;
            }

            // Verificar si la categoría tiene productos asociados
            $productCount = $categoryModel->getProductCount($id);
            if ($productCount > 0) {
                Session::flash('error', 'No se puede eliminar una categoría que tiene productos asociados.');
                header('Location: /admin/categorias');
                exit;
            }

            $result = $categoryModel->delete($id);

            if ($result) {
                Session::flash('success', 'Categoría eliminada exitosamente.');
            } else {
                Session::flash('error', 'Error al eliminar la categoría.');
            }

        } catch (\Exception $e) {
            error_log("Error al eliminar categoría: " . $e->getMessage());
            Session::flash('error', 'Error interno al eliminar la categoría.');
        }

        header('Location: /admin/categorias');
        exit;
    }

    /**
     * Cambia el estado activo/inactivo de una categoría
     */
    public function toggleCategoryStatus($id)
    {
        header('Content-Type: application/json');
        
        error_log("Toggle request received for ID: " . $id);
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        error_log("Headers: " . json_encode(getallheaders()));
        
        try {
            $categoryModel = new Category();
            
            // Verificar que la categoría existe
            $category = $categoryModel->findById($id);
            if (!$category) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Categoría no encontrada']);
                return;
            }

            // Cambiar el estado
            $success = $categoryModel->toggleActive($id);
            
            if ($success) {
                // Obtener el nuevo estado
                $updatedCategory = $categoryModel->findById($id);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Estado actualizado correctamente',
                    'newStatus' => (bool)$updatedCategory['activo']
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
            }

        } catch (\Exception $e) {
            error_log("Error al cambiar estado de categoría: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}
