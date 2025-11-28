<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';

$cartItems = $data['cartItems'] ?? [];
$total = $data['total'] ?? 0;

// Debug output
echo "<!-- [v0] Cart Debug: Items count = " . count($cartItems) . ", Total = $total -->\n";
?>

<section class="cart-page">
    <div class="cart-container">
        <h1>Tu Carrito de Compras</h1>
        
        <?php if (empty($cartItems)): ?>
            <!-- Estado de carrito vacío -->
            <div class="cart-empty">
                <div class="cart-empty__icon">🛍️</div>
                <h2 class="cart-empty__title">Tu carrito está vacío</h2>
                <p class="cart-empty__message">¡Aún no has agregado productos! Explora nuestro catálogo y encuentra lo que te encanta</p>
                <a href="<?= BASE_URL ?>/catalog" class="btn btn-primary">Explorar Catálogo</a>
            </div>
        <?php else: ?>
            <!-- Contenido del carrito -->
            <div class="cart-content">
                <!-- Lista de items -->
                <div class="cart-items-wrapper">
                    <div class="cart-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item" data-item-id="<?= $item['id'] ?>" data-product-id="<?= $item['product_id'] ?>">
                                <!-- Imagen del producto -->
                                <div class="cart-item__image">
                                    <?php
                                    $imagePath = BASE_URL . '/img/ropa/' . htmlspecialchars($item['image'] ?? 'default.jpg');
                                    if (strpos($item['image'], 'product_') === 0) {
                                        $imagePath = BASE_URL . '/uploads/products/' . htmlspecialchars($item['image']);
                                    }
                                    ?>
                                    <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                </div>

                                <!-- Detalles del producto -->
                                <div class="cart-item__details">
                                    <h3 class="cart-item__name"><?= htmlspecialchars($item['name']) ?></h3>
                                    <p class="cart-item__specs">
                                        <span class="spec">Talle: <strong><?= htmlspecialchars($item['size']) ?></strong></span>
                                        <span class="spec">Color: <strong><?= htmlspecialchars($item['color']) ?></strong></span>
                                    </p>
                                    <p class="cart-item__price">$<?= number_format($item['price'], 0, ',', '.') ?></p>
                                </div>

                                <!-- Cantidad -->
                                <div class="quantity-controls">
                                    <button class="qty-btn qty-minus" aria-label="Disminuir cantidad">−</button>
                                    <input type="number" value="<?= $item['quantity'] ?>" min="1" class="qty-input" readonly>
                                    <button class="qty-btn qty-plus" aria-label="Aumentar cantidad">+</button>
                                </div>

                                <!-- Subtotal -->
                                <div class="cart-item__subtotal">
                                    <span class="item-total">$<?= number_format($item['itemTotal'], 0, ',', '.') ?></span>
                                </div>

                                <!-- Botón eliminar -->
                                <div class="cart-item__actions">
                                    <button class="btn-remove remove-btn" aria-label="Eliminar producto">✕</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Resumen de compra -->
                <aside class="cart-summary">
                    <div class="summary-card">
                        <h3 class="summary-title">Resumen</h3>
                        
                        <div class="summary-divider"></div>

                        <!-- Desglose -->
                        <div class="summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value" id="subtotal">$<?= number_format($total, 0, ',', '.') ?></span>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">Envío:</span>
                            <span class="summary-value summary-free">Gratis</span>
                        </div>

                        <div class="summary-row">
                            <span class="summary-label">Descuento:</span>
                            <span class="summary-value summary-discount">$0</span>
                        </div>

                        <div class="summary-divider"></div>

                        <!-- Total -->
                        <div class="summary-total">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value-total" id="total">$<?= number_format($total, 0, ',', '.') ?></span>
                        </div>

                        <!-- Botones de acción -->
                        <button id="checkoutBtn" class="btn btn-checkout" <?= empty($cartItems) ? 'disabled' : '' ?>>
                            Proceder al Pago 💳
                        </button>

                        <a href="<?= BASE_URL ?>/catalog" class="btn btn-secondary">
                            Seguir Comprando
                        </a>

                        <button class="btn btn-outline-danger btn-clear-cart">
                            Vaciar Carrito
                        </button>
                    </div>

                    <!-- Info de seguridad -->
                    <div class="security-badge">
                        <p>🔒 Tu compra está 100% protegida</p>
                        <small>Pagos seguros con Mercado Pago</small>
                    </div>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php require_once VIEWS_PATH . '/vistas/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const baseUrl = window.location.origin;
    
    // Usamos delegación de eventos en el contenedor principal para todos los botones.
    // Esto evita errores si los botones no existen al cargar la página (ej. carrito vacío).
    const cartContainer = document.querySelector('.cart-container');
    if (!cartContainer) return;

    cartContainer.addEventListener('click', function(e) {
        const target = e.target;
        
        // Botones de cantidad
        if (target.classList.contains('qty-plus')) {
            handleQuantityChange(target, 1);
        } else if (target.classList.contains('qty-minus')) {
            handleQuantityChange(target, -1);
        } 
        // Botón de eliminar item
        else if (target.classList.contains('remove-btn')) {
            handleRemoveItem(target);
        } 
        // Botón de vaciar carrito
        else if (target.classList.contains('btn-clear-cart')) {
            if (confirm('¿Estás seguro que deseas vaciar el carrito?')) {
                fetch(baseUrl + '/carrito/vaciar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.reload();
                    } else {
                        alert('Error al vaciar el carrito');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error de conexión');
                });
            }
        } 
        // Botón de proceder al pago
        else if (target.id === 'checkoutBtn' && !target.disabled) {
            handleCheckout();
        }
    });

    function handleQuantityChange(button, change) {
        const cartItem = button.closest('.cart-item');
        if (!cartItem) return;

        const itemId = cartItem.getAttribute('data-item-id');
        const productId = cartItem.getAttribute('data-product-id');
        const input = cartItem.querySelector('.qty-input');
        
        if (!input) return;

        let quantity = parseInt(input.value) + change;
        if (quantity < 1) quantity = 1;

        console.log('Updating quantity:', itemId, 'to', quantity);
        updateCartItem(itemId, quantity);
    }

    function handleRemoveItem(button) {
        const cartItem = button.closest('.cart-item');
        if (!cartItem) return;

        const itemId = cartItem.getAttribute('data-item-id');
        const productName = cartItem.querySelector('.cart-item__name').textContent;

        console.log('Removing item:', itemId);
        console.log('Using URL:', baseUrl + '/carrito/eliminar');

        if (confirm('¿Deseas eliminar "' + productName + '" del carrito?')) {
            fetch(baseUrl + '/carrito/eliminar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'item_id=' + itemId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Recargar para mantener consistencia
                    location.reload();
                } else {
                    alert(data.message || 'Error al eliminar el producto');
                }
            })
            .catch(error => {
                console.error('Error al eliminar:', error);
                alert('Error al eliminar el producto');
            });
        }
    }

    function updateCartItem(itemId, quantity) {
        console.log('Updating item:', itemId, 'quantity:', quantity);
        console.log('Using URL:', baseUrl + '/carrito/actualizar');
        fetch(baseUrl + '/carrito/actualizar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'item_id=' + itemId + '&quantity=' + quantity
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Recargar para mantener consistencia
                location.reload();
            } else {
                alert(data.message || 'Error al actualizar la cantidad');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error al actualizar:', error);
            alert('Error al actualizar la cantidad');
            location.reload();
        });
    }

    function handleCheckout() {
        const items = document.querySelectorAll('.cart-item');
        if (items.length === 0) {
            alert("Tu carrito está vacío.");
            return;
        }

        console.log('Starting checkout process');
        
        const checkoutBtn = document.getElementById('checkoutBtn');
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Verificando...';

        const itemsToCheck = Array.from(items).map(item => ({
            productId: item.getAttribute('data-product-id'),
            quantity: parseInt(item.querySelector('.qty-input').value),
            productName: item.querySelector('.cart-item__name').textContent
        }));

        Promise.all(itemsToCheck.map(item => 
            fetch(baseUrl + '/verificar-stock', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'product_id=' + item.productId + '&quantity=' + item.quantity
            })
            .then(response => response.json())
            .then(data => ({
                ...item,
                hasStock: data.has_stock,
                currentStock: data.current_stock
            }))
        ))
        .then(results => {
            const noStock = results.filter(r => !r.hasStock);
            if (noStock.length > 0) {
                let msg = 'Stock insuficiente en:\n';
                noStock.forEach(item => {
                    msg += '- ' + item.productName + ' (disponibles: ' + item.currentStock + ')\n';
                });
                alert(msg);
                checkoutBtn.disabled = false;
                checkoutBtn.textContent = 'Proceder al Pago 💳'; // Corregido: Usar alert en lugar de showToast
                return;
            }

            initializePayment(checkoutBtn);
        })
        .catch(error => {
            console.error('[v0] Error:', error);
            alert('Error al verificar stock'); // Corregido: Usar alert en lugar de showToast
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Proceder al Pago 💳';
        });
    }

    function initializePayment(checkoutBtn) {
        console.log("[v0] Initializing new QR checkout flow");
        
        // Redirigir directamente al nuevo flujo de checkout
        window.location.href = baseUrl + '/checkout/cart';
    }
});
</script>
