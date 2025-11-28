<?php

namespace App\Views\Actions\OrdenesCheckout;

use App\Models\Cart;
use App\Models\Order;
use App\Utils\Session;
use App\Utils\Auth;

/**
 * Action para procesar el checkout y crear una orden
 * Valida el carrito, crea la orden y limpia el carrito
 * También maneja mostrar la página de checkout
 */
class ProcesarCheckout
{
    public function execute(array $data): array
    {
        try {
            Session::start();
            
            // Verificar que el usuario esté logueado
            if (!Auth::isLoggedIn()) {
                return [
                    'success' => false,
                    'message' => 'Debes iniciar sesión para acceder al checkout'
                ];
            }
            
            $action = $data['action'] ?? 'show';
            
            if ($action === 'show') {
                return $this->showCheckout();
            } elseif ($action === 'process') {
                return $this->processCheckout($data);
            }
            
            return [
                'success' => false,
                'message' => 'Acción no válida'
            ];
            
        } catch (\Exception $e) {
            error_log("[ProcesarCheckout] Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error interno del servidor',
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function showCheckout(): array
    {
        // Obtener el carrito
        $cartModel = new Cart();
        $cartContents = $cartModel->getCartContents();
        
        // Verificar que el carrito no esté vacío
        if (empty($cartContents['items'])) {
            return [
                'success' => false,
                'message' => 'Tu carrito está vacío'
            ];
        }
        
        return [
            'success' => true,
            'cartItems' => $cartContents['items'],
            'total' => $cartContents['total']
        ];
    }
    
    private function processCheckout(array $data): array
    {
        // Obtener el carrito
        $cartModel = new Cart();
        $cartContents = $cartModel->getCartContents();
        
        // Verificar que el carrito no esté vacío
        if (empty($cartContents['items'])) {
            return [
                'success' => false,
                'message' => 'El carrito está vacío'
            ];
        }
        
        // Validar datos del formulario usando los campos originales
        $requiredFields = ['firstName', 'lastName', 'email', 'phone', 'address', 'city', 'postalCode'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return [
                    'success' => false,
                    'message' => 'Todos los campos son requeridos'
                ];
            }
        }
        
        try {
            // Crear la orden
            $orderModel = new Order();
            
            $orderId = $orderModel->create([
                'user_id' => Auth::getUserId(),
                'first_name' => $data['firstName'],
                'last_name' => $data['lastName'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'city' => $data['city'],
                'postal_code' => $data['postalCode'],
                'notes' => $data['notes'] ?? '',
                'cart_data' => Session::get('cart', [])
            ]);
            
            if ($orderId) {
                // Limpiar el carrito después de crear la orden exitosamente
                $cartModel->clear();
                
                error_log("[ProcesarCheckout] Orden creada exitosamente: $orderId");
                
                return [
                    'success' => true,
                    'message' => 'Pedido procesado exitosamente',
                    'orderId' => $orderId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Error al procesar el pedido. Inténtalo de nuevo.'
                ];
            }
        } catch (\Exception $e) {
            error_log("[ProcesarCheckout] Error al crear orden: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error del sistema al procesar la orden'
            ];
        }
    }
}