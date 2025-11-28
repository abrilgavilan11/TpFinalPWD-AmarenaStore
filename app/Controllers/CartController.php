<?php

namespace App\Controllers;

use App\Views\Actions\CarritoCompras\AgregarAlCarrito;
use App\Views\Actions\CarritoCompras\ActualizarCarrito;
use App\Views\Actions\CarritoCompras\EliminarDelCarrito;
use App\Views\Actions\CarritoCompras\LimpiarCarrito;
use App\Models\Cart;
use App\Models\Product;
use App\Utils\Session;

class CartController extends BaseController
{
    /**
     * Muestra la página del carrito
     */
    public function index()
    {
        Session::start();
        
        $cartData = Session::get('cart', []);
        
        // Debug logs
        error_log("===== Carrito page debug =====");
        error_log("Session ID: " . session_id());
        error_log("Raw cart data: " . json_encode($cartData));
        error_log("Cart count: " . count($cartData));
        error_log("Is empty: " . (empty($cartData) ? 'Si' : 'No'));
        
        // Proceso de preparación de datos para la vista
        $cartItems = [];
        $total = 0;
        
        foreach ($cartData as $itemId => $item) {
            error_log("Procesando item: " . json_encode($item));
            $item['itemTotal'] = $item['price'] * $item['quantity'];
            $item['id'] = $itemId; // Preservar el ID único del item
            $cartItems[] = $item;
            $total += $item['itemTotal'];
        }
        
        error_log("Cantidad final de items en carrito: " . count($cartItems));
        error_log("Total final: " . $total);
        error_log("===========================");
        
        // Pasar datos a la vista - BaseController espera un array $data
        $data = [
            'title' => 'Tu Carrito - Amarena Store',
            'pageCss' => 'cart',
            'cartItems' => $cartItems,
            'total' => $total
        ];
        
        $this->view('vistas.carrito.cart_page', $data);
    }

    /**
     * API: Obtener cantidad de items en el carrito
     */
    public function count()
    {
        Session::start();
        $cartData = Session::get('cart', []);
        
        error_log("Cantidad de items en carrito solicitada: " . count($cartData));
        
        $this->json([
            'success' => true,
            'count' => count($cartData)
        ]);
    }

    /**
     * API: Obtener contenido completo del carrito
     */
    public function contents()
    {
        Session::start();
        $cartData = Session::get('cart', []);
        
        error_log("Contenido del carrito solicitado");
        error_log("Datos del carrito: " . json_encode($cartData));
        
        $cartItems = [];
        $total = 0;
        
        foreach ($cartData as $itemId => $item) {
            $item['itemTotal'] = $item['price'] * $item['quantity'];
            $cartItems[] = $item;
            $total += $item['itemTotal'];
        }
        
        error_log("Devolviendo " . count($cartItems) . " items, total: $total");
        
        $this->json([
            'success' => true, 
            'items' => $cartItems,
            'total' => $total,
            'count' => count($cartItems)
        ]);
    }

    /**
     * Agrega un producto al carrito
     */
    public function add()
    {
        try {
            // Verificar si el usuario está autenticado
            if (!Session::get('user_id')) {
                $this->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para agregar productos al carrito',
                    'requiresAuth' => true
                ], 401);
                return;
            }

            $agregarAction = new AgregarAlCarrito();
            $result = $agregarAction->execute(
                $_POST['product_id'] ?? '',
                $_POST['quantity'] ?? 1,
                $_POST['size'] ?? 'M',
                $_POST['color'] ?? 'default'
            );
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => $result['message'],
                    'cartCount' => $result['cartCount']
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            error_log("[CartController] Error en add: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Actualiza la cantidad de un producto en el carrito
     */
    public function actualizar()
    {
        try {
            $actualizarAction = new ActualizarCarrito();
            $result = $actualizarAction->execute(
                $_POST['item_id'] ?? '',
                $_POST['quantity'] ?? 1
            );
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => $result['message'],
                    'cartCount' => $result['cartCount']
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            error_log("[CartController] Error en actualizar: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Elimina un producto del carrito
     */
    public function eliminar()
    {
        try {
            $eliminarAction = new EliminarDelCarrito();
            $result = $eliminarAction->execute($_POST['item_id'] ?? '');
            
            if ($result['success']) {
                $this->json([
                    'success' => true,
                    'message' => $result['message'],
                    'cartCount' => $result['cartCount']
                ]);
            } else {
                $this->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            error_log("[CartController] Error en eliminar: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Vacía el carrito completo
     */
    public function vaciar()
    {
        try {
            $limpiarAction = new LimpiarCarrito();
            $result = $limpiarAction->execute();
            
            header('Location: ' . BASE_URL . '/carrito?message=' . urlencode($result['message']));
            exit;
        } catch (\Exception $e) {
            error_log("[CartController] Error en vaciar: " . $e->getMessage());
            header('Location: ' . BASE_URL . '/carrito?error=' . urlencode('Error interno del servidor'));
            exit;
        }
    }

    /**
     * Alias methods para compatibilidad con rutas estándar
     */
    public function update()
    {
        return $this->actualizar();
    }

    public function remove()
    {
        return $this->eliminar();
    }

    public function clear()
    {
        return $this->vaciar();
    }

    public function validateStock()
    {
        try {
            Session::start();
            
            // Verificar autenticación
            if (!Session::get('user_id')) {
                $this->json([
                    'success' => false,
                    'message' => 'Debes iniciar sesión para continuar',
                    'requiresAuth' => true
                ], 401);
                return;
            }

            $cartData = Session::get('cart', []);
            
            if (empty($cartData)) {
                $this->json([
                    'success' => false,
                    'message' => 'El carrito está vacío'
                ], 400);
                return;
            }

            $productModel = new Product();
            $stockIssues = [];

            foreach ($cartData as $itemId => $item) {
                $productId = $item['product_id'] ?? null;
                $requestedQty = $item['quantity'] ?? 0;
                
                if (!$productId) {
                    $stockIssues[] = "Producto inválido: {$item['name']}";
                    continue;
                }

                // Verificar stock disponible
                $product = $productModel->findById($productId);
                if (!$product) {
                    $stockIssues[] = "Producto no encontrado: {$item['name']}";
                    continue;
                }

                $availableStock = $product['procantstock'] ?? 0;
                if ($requestedQty > $availableStock) {
                    $stockIssues[] = "Stock insuficiente para {$item['name']}. Disponible: {$availableStock}, Solicitado: {$requestedQty}";
                }
            }

            if (!empty($stockIssues)) {
                $this->json([
                    'success' => false,
                    'message' => 'Problemas de stock encontrados',
                    'issues' => $stockIssues
                ], 400);
                return;
            }

            // Todo el stock está disponible
            $this->json([
                'success' => true,
                'message' => 'Stock validado correctamente'
            ]);

        } catch (\Exception $e) {
            error_log("[CartController] Error en validateStock: " . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }
}
