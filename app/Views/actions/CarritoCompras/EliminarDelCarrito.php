<?php

namespace App\Views\Actions\CarritoCompras;

use App\Utils\Session;

/**
 * Action para eliminar productos del carrito
 */
class EliminarDelCarrito
{
    public function execute($itemId)
    {
        Session::start();
        
        $itemId = trim($itemId);
        
        if (empty($itemId)) {
            return [
                'success' => false,
                'message' => 'ID de producto requerido'
            ];
        }
        
        $cartData = Session::get('cart', []);
        
        if (!isset($cartData[$itemId])) {
            return [
                'success' => false,
                'message' => 'Producto no encontrado en el carrito'
            ];
        }
        
        unset($cartData[$itemId]);
        Session::set('cart', $cartData);
        
        return [
            'success' => true,
            'message' => 'Producto eliminado del carrito',
            'cartCount' => count($cartData)
        ];
    }
}