<?php

namespace App\Views\Actions;

use App\Models\Category;
use App\Models\Product;
use App\Utils\Session;

class AdminAction
{
    /**
     * Prepara datos para el dashboard de admin
     */
    public function prepareDashboard()
    {
        return [
            'title' => 'Dashboard - Admin',
            'pageCss' => 'admin'
        ];
    }

    /**
     * Prepara datos para listar todos los productos
     */
    public function prepareProductsList()
    {
        $productModel = new Product();
        $allProducts = $productModel->getAll();

        return [
            'title' => 'Gestionar Productos - Admin',
            'pageCss' => 'admin',
            'products' => $allProducts
        ];
    }

    /**
     * Prepara datos para listar órdenes
     */
    public function prepareOrdersList()
    {
        return [
            'title' => 'Gestionar Órdenes - Admin',
            'pageCss' => 'admin'
        ];
    }

    /**
     * Prepara formulario para crear producto
     */
    public function prepareCreateProductForm()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        return [
            'title' => 'Agregar Producto - Admin',
            'pageCss' => 'admin-forms',
            'categories' => $categories
        ];
    }

    /**
     * Prepara formulario para editar producto
     */
    public function prepareEditProductForm($productId)
    {
        $productModel = new Product();
        $product = $productModel->findById($productId);

        if (!$product) {
            return null;
        }

        $categoryModel = new Category();
        $categories = $categoryModel->getAll();

        return [
            'title' => 'Editar Producto - Admin',
            'pageCss' => 'admin-forms',
            'categories' => $categories,
            'product' => $product
        ];
    }

    /**
     * Guarda un nuevo producto
     */
    public function storeProduct($data, $imagen = null)
    {
        $nombre = trim($data['pronombre'] ?? '');
        $detalle = trim($data['prodetalle'] ?? '');
        $precio = filter_var($data['proprecio'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($data['procantstock'] ?? 0, FILTER_VALIDATE_INT);
        $categoriaId = filter_var($data['idcategoria'] ?? null, FILTER_VALIDATE_INT);

        if (empty($nombre) || empty($detalle) || $precio === false || $precio <= 0 || $stock === false || $stock < 0 || empty($categoriaId)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son obligatorios y deben tener valores válidos.'
            ];
        }

        $nombreImagen = 'default.jpg';
        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
            $nombreImagen = uniqid('product_', true) . '.' . $extension;

            if (!move_uploaded_file($imagen['tmp_name'], $uploadDir . $nombreImagen)) {
                return [
                    'success' => false,
                    'message' => 'Hubo un error al subir la imagen.'
                ];
            }
        }

        $productData = [
            'pronombre' => $nombre,
            'prodetalle' => $detalle,
            'proprecio' => $precio,
            'procantstock' => $stock,
            'idcategoria' => $categoriaId,
            'proimagen' => $nombreImagen
        ];

        $productModel = new Product();
        $productModel->create($productData);

        return [
            'success' => true,
            'message' => 'Producto agregado exitosamente.'
        ];
    }

    /**
     * Actualiza un producto existente
     */
    public function updateProduct($productId, $data, $imagen = null)
    {
        $nombre = trim($data['pronombre'] ?? '');
        $detalle = trim($data['prodetalle'] ?? '');
        $precio = filter_var($data['proprecio'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stock = filter_var($data['procantstock'] ?? 0, FILTER_VALIDATE_INT);
        $categoriaId = filter_var($data['idcategoria'] ?? null, FILTER_VALIDATE_INT);

        if (empty($nombre) || empty($detalle) || $precio === false || $precio <= 0 || $stock === false || $stock < 0 || empty($categoriaId)) {
            return [
                'success' => false,
                'message' => 'Todos los campos son obligatorios.'
            ];
        }

        $productModel = new Product();
        $currentProduct = $productModel->findById($productId);

        if (!$currentProduct) {
            return [
                'success' => false,
                'message' => 'Producto no encontrado.'
            ];
        }

        $nombreImagen = $currentProduct['proimagen'];

        if ($imagen && $imagen['error'] === UPLOAD_ERR_OK) {
            $uploadDir = PUBLIC_PATH . '/uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $extension = pathinfo($imagen['name'], PATHINFO_EXTENSION);
            $nombreImagen = uniqid('product_', true) . '.' . $extension;

            if (!move_uploaded_file($imagen['tmp_name'], $uploadDir . $nombreImagen)) {
                return [
                    'success' => false,
                    'message' => 'Hubo un error al subir la nueva imagen.'
                ];
            }
        }

        $productData = [
            'pronombre' => $nombre,
            'prodetalle' => $detalle,
            'proprecio' => $precio,
            'procantstock' => $stock,
            'idcategoria' => $categoriaId,
            'proimagen' => $nombreImagen
        ];

        $productModel->update($productId, $productData);

        return [
            'success' => true,
            'message' => 'Producto actualizado exitosamente.'
        ];
    }

    /**
     * Elimina un producto
     */
    public function deleteProduct($productId)
    {
        $productModel = new Product();
        $product = $productModel->findById($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Producto no encontrado.'
            ];
        }

        $productModel->delete($productId);

        return [
            'success' => true,
            'message' => 'Producto eliminado exitosamente.'
        ];
    }

    /**
     * Verifica el stock de un producto
     */
    public function checkStock($productId, $quantity)
    {
        $productId = intval($productId);
        $quantity = intval($quantity);

        if (!$productId || $quantity < 1) {
            return [
                'success' => false,
                'message' => 'Parámetros inválidos'
            ];
        }

        $productModel = new Product();
        $product = $productModel->findById($productId);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Producto no encontrado'
            ];
        }

        $hasStock = $product['procantstock'] >= $quantity;

        return [
            'success' => true,
            'hasStock' => $hasStock,
            'availableStock' => $product['procantstock']
        ];
    }
}
