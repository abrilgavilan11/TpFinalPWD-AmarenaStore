<?php

namespace App\Views\Actions\CarritoCompras;

use App\Utils\Session;

/**
 * Action para actualizar cantidad de productos en el carrito
 */
class ActualizarCarrito
{
    public function execute($itemId, $quantity)
    {
        Session::start();
        
        $itemId = trim($itemId);
        $quantity = intval($quantity);
        
        if (empty($itemId) || $quantity < 1) {
            return [
                'success' => false,
                'message' => 'Parámetros inválidos'
            ];
        }
        
        $cartData = Session::get('cart', []);
        
        if (!isset($cartData[$itemId])) {
            return [
                'success' => false,
                'message' => 'Producto no encontrado en el carrito'
            ];
        }
        
        $cartData[$itemId]['quantity'] = $quantity;
        Session::set('cart', $cartData);
        
        return [
            'success' => true,
            'message' => 'Carrito actualizado',
            'cartCount' => count($cartData)
        ];
    }
}