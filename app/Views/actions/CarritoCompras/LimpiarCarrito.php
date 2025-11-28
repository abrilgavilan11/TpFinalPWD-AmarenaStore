<?php

namespace App\Views\Actions\CarritoCompras;

use App\Utils\Session;

/**
 * Action para limpiar/vaciar el carrito completamente
 */
class LimpiarCarrito
{
    public function execute()
    {
        Session::start();
        Session::remove('cart');
        
        return [
            'success' => true,
            'message' => 'Carrito vaciado exitosamente',
            'cartCount' => 0
        ];
    }
}