<?php
namespace App\Models;

use App\Utils\Session;
use App\Models\Product;

class Cart
{
    /**
     * Agrega un producto a un array de carrito o actualiza su cantidad si ya existe.
     * Este método ya NO modifica la sesión directamente, solo trabaja con el array que recibe.
     *
     * @param array $currentCart El array del carrito actual.
     * @param int $productId El ID del producto.
     * @param int $quantity La cantidad a agregar.
     * @param string $size El talle seleccionado.
     * @param string $color El color seleccionado.
     * @return array El array del carrito actualizado.
     */
    public function addItem($currentCart, $productId, $quantity, $size, $color)
    {
        // 1. Generamos un ID único para este item específico (producto + talle + color).
        $itemId = md5($productId . $size . $color);

        // 2. Verificamos si un item con esta combinación ya existe en el carrito.
        if (isset($currentCart[$itemId])) {
            // Si existe, simplemente incrementamos su cantidad.
            $currentCart[$itemId]['quantity'] += $quantity;
        } else {
            // Si no existe, obtenemos los datos del producto y lo agregamos como un nuevo item.
            $productModel = new Product();
            $product = $productModel->findById($productId);

            if ($product) {
                $currentCart[$itemId] = [
                    'item_id' => $itemId, // ID único para el item en el carrito
                    'product_id' => $productId,
                    'name' => $product['pronombre'],
                    'price' => $product['proprecio'],
                    'image' => $product['proimagen'],
                    'quantity' => $quantity,
                    'size' => $size,
                    'color' => $color,
                ];
            }
        }
        
        // 3. Devolvemos el array del carrito modificado.
        return $currentCart;
    }

    /**
     * Actualiza la cantidad de un item específico en el carrito.
     * Este método SÍ modifica la sesión directamente, ya que el controlador no espera un retorno.
     */
    public function updateItemQuantity($itemId, $quantity)
    {
        Session::start();
        $cart = Session::get('cart', []);
        if (isset($cart[$itemId])) {
            $cart[$itemId]['quantity'] = $quantity;
            Session::set('cart', $cart);
        }
    }

    /**
     * Elimina un item del carrito.
     * Este método SÍ modifica la sesión directamente.
     */
    public function removeItem($itemId)
    {
        Session::start();
        $cart = Session::get('cart', []);
        if (isset($cart[$itemId])) {
            unset($cart[$itemId]);
            Session::set('cart', $cart);
        }
    }

    /**
     * Vacía completamente el carrito.
     */
    public function clear()
    {
        Session::start();
        Session::remove('cart');
    }

    /**
     * Obtiene el contenido completo del carrito desde la sesión.
     * Calcula el total y prepara los datos para la vista.
     *
     * @return array Un array con los items del carrito y el total.
     */
    public function getCartContents()
    {
        Session::start();
        $cartData = Session::get('cart', []);
        $cartItems = [];
        $total = 0;

        foreach (array_values($cartData) as $item) {
            $item['itemTotal'] = $item['price'] * $item['quantity'];
            $cartItems[] = $item;
            $total += $item['itemTotal'];
        }

        return [
            'items' => $cartItems,
            'total' => $total,
        ];
    }

    /**
     * Devuelve la cantidad de tipos de productos diferentes en el carrito.
     *
     * @return int
     */
    public function getItemCount()
    {
        Session::start();
        return count(Session::get('cart', []));
    }
}
