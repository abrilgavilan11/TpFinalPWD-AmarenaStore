<?php

namespace App\Views\Actions\CarritoCompras;

use App\Models\Product;
use App\Utils\Session;

/**
 * Action específico para agregar productos al carrito
 */
class AgregarAlCarrito
{
    /**
     * Agrega un producto al carrito de compras
     */
    public function execute($productId, $quantity, $size, $color)
    {
        Session::start();

        $productId = trim($productId);
        $quantity = intval($quantity);
        $size = trim($size);
        $color = trim($color);

        if (empty($productId) || $quantity < 1 || empty($size) || empty($color)) {
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

        if ($product['procantstock'] < $quantity) {
            return [
                'success' => false,
                'message' => 'Stock insuficiente'
            ];
        }

        $cartData = Session::get('cart', []);
        $itemId = $productId . '_' . $size . '_' . $color;

        if (isset($cartData[$itemId])) {
            $cartData[$itemId]['quantity'] += $quantity;
            $cartData[$itemId]['id'] = $itemId; // Asegurar que el ID esté presente
        } else {
            $cartData[$itemId] = [
                'id' => $itemId,
                'product_id' => $productId,
                'name' => $product['pronombre'],
                'price' => $product['proprecio'],
                'quantity' => $quantity,
                'size' => $size,
                'color' => $color,
                'image' => $product['proimagen'] ?? 'default.jpg'
            ];
        }

        Session::set('cart', $cartData);

        return [
            'success' => true,
            'message' => 'Producto agregado al carrito',
            'cartCount' => count($cartData)
        ];
    }
}