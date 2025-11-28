<?php
// Extraemos los datos pasados por el controlador para un acceso más fácil.
$title = $data['data']['title'] ?? 'Carrito - Amarena Store';
$pageCss = $data['data']['pageCss'] ?? 'cart';
$cartItems = $data['data']['cartItems'] ?? [];
$total = $data['data']['total'] ?? 0;

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<div class="cart-page">
    <div class="cart-container">
        <h1><i class="fas fa-shopping-cart"></i> Tu Carrito</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">
                <div class="cart-empty__icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h3 class="cart-empty__title">Tu carrito está vacío</h3>
                <p class="cart-empty__message">Descubrí nuestra colección y encontrá tus prendas favoritas</p>
                <a href="/catalog" class="btn btn--primary">
                    <i class="fas fa-store"></i> Explorar Catálogo
                </a>
            </div>
        <?php else: ?>
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-item-id="<?= $item['id'] ?>">
                            <div class="cart-item__image">
                                <?php
                                $imagePath = BASE_URL . '/img/ropa/remera_placeholder.jpg';
                                if (!empty($item['image'])) {
                                    if (strpos($item['image'], 'product_') === 0) {
                                        $imagePath = BASE_URL . '/uploads/products/' . htmlspecialchars($item['image']);
                                    } else {
                                        $imagePath = BASE_URL . '/img/ropa/' . htmlspecialchars($item['image']);
                                    }
                                }
                                ?>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
                            
                            <div class="cart-item__details">
                                <h3 class="cart-item__name"><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="cart-item__variants">
                                    <span class="variant"><i class="fas fa-ruler"></i> Talle: <?= htmlspecialchars($item['size'] ?? 'N/A') ?></span>
                                    <span class="variant"><i class="fas fa-palette"></i> Color: <?= htmlspecialchars($item['color'] ?? 'N/A') ?></span>
                                </div>
                                <p class="cart-item__price">$<?= number_format($item['price'], 0, ',', '.') ?></p>
                            </div>
                            
                            <div class="cart-item__quantity">
                                <button class="qty-btn" onclick="updateQuantity(event, '<?= $item['id'] ?>', -1)">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" readonly>
                                <button class="qty-btn" onclick="updateQuantity(event, '<?= $item['id'] ?>', 1)">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                            
                            <div class="cart-item__subtotal">
                                <span class="subtotal-label">Subtotal</span>
                                <span class="subtotal-price">$<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></span>
                            </div>
                            
                            <button class="cart-item__remove" onclick="removeItem('<?= $item['id'] ?>')" title="Eliminar producto">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="cart-sidebar">
                    <div class="cart-summary">
                        <h2>Resumen de Compra</h2>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span>$<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Envío</span>
                            <span class="text-success">Gratis</span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row summary-total">
                            <span>Total</span>
                            <span id="cart-total">$<?= number_format($total, 0, ',', '.') ?></span>
                        </div>
                        
                        <div class="discount-info">
                            <i class="fas fa-info-circle"></i>
                            <span>15% de descuento pagando con transferencia</span>
                        </div>
                        
                        <button class="btn btn--checkout" onclick="goToCheckout()">
                            <i class="fas fa-lock"></i> Proceder al Pago
                        </button>
                        
                        <a href="/catalog" class="btn btn--secondary">
                            <i class="fas fa-arrow-left"></i> Seguir Comprando
                        </a>
                    </div>
                    
                    <div class="trust-badges">
                        <div class="badge">
                            <i class="fas fa-shield-alt"></i>
                            <span>Compra Segura</span>
                        </div>
                        <div class="badge">
                            <i class="fas fa-truck"></i>
                            <span>Envío Gratis</span>
                        </div>
                        <div class="badge">
                            <i class="fas fa-undo"></i>
                            <span>Devolución Fácil</span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
console.log("Modern cart page loaded");
console.log("Cart items:", <?= json_encode($cartItems) ?>);

function goToCheckout() {
    window.location.href = '/checkout';
}
</script>

<?php
$extraScripts = ['carrito']; 
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
