<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Utils\Auth;

class ProductController extends BaseController
{
    /**
     * Muestra el catálogo de productos
     */
    public function index()
    {
        // Obtener parámetros de búsqueda y filtro
        $searchQuery = $_GET['search'] ?? '';
        $categoryId = $_GET['category'] ?? null;

        // 1. Obtenemos el modelo de productos
        $productModel = new Product();
        
        // 2. Preparar filtros
        $filters = [];
        if (!empty($searchQuery)) {
            $filters['search'] = $searchQuery;
        }
        if (!empty($categoryId)) {
            $filters['category'] = $categoryId;
        }

        // 3. Obtener productos con filtros
        if (!empty($filters)) {
            $products = $productModel->searchAndFilter($filters);
        } else {
            $products = $productModel->getAll();
        }

        // 4. Obtenemos solo categorías activas para el filtro
        $categoryModel = new Category();
        $categories = $categoryModel->getActiveCategories();

        // 5. Mostramos la vista con los productos
        $this->view('vistas.tienda.catalog', [
            'title' => 'Catálogo - Amarena Store',
            'pageCss' => 'catalog',
            'products' => $products,
            'categories' => $categories,
            'searchQuery' => $searchQuery,
            'currentCategory' => $categoryId
        ]);
    }

    /**
     * Muestra los detalles de un producto específico
     */
    public function show($productId)
    {
        // 1. Validamos que el ID sea un número
        $productId = intval($productId);

        if ($productId <= 0) {
            $this->redirect('/catalog');
            return;
        }

        // 2. Obtenemos el producto del modelo
        $productModel = new Product();
        $product = $productModel->findById($productId);
        
        // 3. Si no existe, redirigimos al catálogo
        if (!$product) {
            $this->redirect('/catalog');
            return;
        }

        // 3.1. Verificar que la categoría del producto esté activa
        $categoryModel = new Category();
        $category = $categoryModel->findById($product['idcategoria']);
        if (!$category || !$category['activo']) {
            $this->redirect('/catalog');
            return;
        }

        // 4. Obtenemos productos relacionados (usando el mismo filtro por categoría)
        $relatedProducts = $productModel->searchAndFilter(['category' => $product['idcategoria']]);
        $relatedProducts = array_filter($relatedProducts, function($p) use ($productId) {
            return $p['idproducto'] != $productId;
        });
        $relatedProducts = array_slice($relatedProducts, 0, 4);

        // 5. Mostramos la vista con el producto
        $this->view('vistas.tienda.product_detail', [
            'title' => $product['pronombre'] . ' - Amarena Store',
            'pageCss' => 'product',
            'product' => $product,
            'relatedProducts' => $relatedProducts
        ]);
    }

    /**
     * Obtiene el stock actual de un producto (público)
     * Endpoint AJAX para verificar disponibilidad desde el carrito.
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

        // Verificar que la categoría esté activa
        $categoryModel = new Category();
        $category = $categoryModel->findById($product['idcategoria']);
        if (!$category || !$category['activo']) {
            $this->json(['success' => false, 'message' => 'Producto no disponible'], 404);
            return;
        }

        $hasStock = $product['procantstock'] >= $quantity;

        $this->json([
            'has_stock' => $hasStock,
            'current_stock' => (int)$product['procantstock']
        ]);
    }
}
