<?php

namespace App\Controllers;

use App\Views\Actions\OrdenesCheckout\ProcesarCheckout;
use App\Models\Cart;
use App\Utils\Auth;
use App\Utils\Session;

class CheckoutController extends BaseController
{
    /**
     * Muestra la página de checkout
     */
    public function index()
    {
        try {
            Auth::requireLogin();

            $procesarAction = new ProcesarCheckout();
            $result = $procesarAction->execute(['action' => 'show']);

            if ($result['success']) {
                // Mostrar la vista de checkout
                $this->view('vistas.finalizar_compra.index', [
                    'title' => 'Checkout - Amarena Store',
                    'pageCss' => 'checkout',
                    'cartItems' => $result['cartItems'],
                    'total' => $result['total']
                ]);
            } else {
                Session::flash('error', $result['message']);
                $this->redirect('/carrito');
            }
        } catch (\Exception $e) {
            error_log("[CheckoutController] Error en index: " . $e->getMessage());
            Session::flash('error', 'Error interno del servidor');
            $this->redirect('/carrito');
        }
    }
}
