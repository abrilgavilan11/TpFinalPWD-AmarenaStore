<?php
$title = $data['title'] ?? 'Tu Carrito - Amarena Store';
$cartItems = $data['cartItems'] ?? [];
$total = $data['total'] ?? 0;
$step = $data['step'] ?? 1;

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';

// Debug output
echo "<!-- [v0] Checkout Step1 Debug: Items count = " . count($cartItems) . ", Total = $total -->\n";
?>

<div class="checkout-flow">
    <!-- Indicador de progreso -->
    <div class="progress-indicator">
        <div class="container">
            <div class="steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Carrito</div>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-label">Tus Datos</div>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Resumen</div>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <div class="step-number">4</div>
                    <div class="step-label">Pago QR</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="checkout-content">
            <!-- Título de la etapa -->
            <div class="stage-header">
                <h1><i class="fas fa-shopping-cart"></i> Tu Carrito de Compras</h1>
                <p>Revisa los productos seleccionados antes de continuar con el proceso de compra</p>
            </div>
            
            <?php if (empty($cartItems)): ?>
                <!-- Estado de carrito vacío -->
                <div class="form-container">
                    <div class="cart-empty">
                        <div class="cart-empty__icon">🛍️</div>
                        <h2 class="cart-empty__title">Tu carrito está vacío</h2>
                        <p class="cart-empty__message">¡Aún no has agregado productos! Explora nuestro catálogo y encuentra lo que te encanta</p>
                        <a href="<?= BASE_URL ?>/catalog" class="btn btn-primary">Explorar Catálogo</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Contenido del carrito -->
                <div class="form-container">
                    <!-- Lista de items del carrito -->
                    <div class="form-section">
                        <h3>Productos Seleccionados</h3>
                        
                        <div class="cart-items-list">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="product-item" data-item-id="<?= $item['id'] ?>" data-product-id="<?= $item['product_id'] ?>">
                                    <div class="product-card">
                                        <!-- Imagen del producto -->
                                        <div class="product-image">
                                            <?php
                                            $imagePath = BASE_URL . '/img/ropa/' . htmlspecialchars($item['image'] ?? 'default.jpg');
                                            if (strpos($item['image'], 'product_') === 0) {
                                                $imagePath = BASE_URL . '/uploads/products/' . htmlspecialchars($item['image']);
                                            }
                                            ?>
                                            <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                        </div>

                                        <!-- Detalles del producto -->
                                        <div class="product-details">
                                            <h4 class="product-name"><?= htmlspecialchars($item['name']) ?></h4>
                                            <div class="product-specs">
                                                <span class="spec-item">Talle: <strong><?= htmlspecialchars($item['size']) ?></strong></span>
                                                <span class="spec-item">Color: <strong><?= htmlspecialchars($item['color']) ?></strong></span>
                                            </div>
                                            <div class="product-price">$<?= number_format($item['price'], 0, ',', '.') ?></div>
                                        </div>

                                        <!-- Controles de cantidad -->
                                        <div class="product-controls">
                                            <div class="quantity-section">
                                                <label class="quantity-label">Cantidad:</label>
                                                <div class="quantity-controls">
                                                    <button type="button" class="qty-btn qty-minus">-</button>
                                                    <span class="qty-display"><?= $item['quantity'] ?></span>
                                                    <button type="button" class="qty-btn qty-plus">+</button>
                                                </div>
                                            </div>
                                            
                                            <!-- Subtotal -->
                                            <div class="subtotal-section">
                                                <span class="subtotal-label">Subtotal:</span>
                                                <span class="subtotal-amount">$<?= number_format($item['itemTotal'], 0, ',', '.') ?></span>
                                            </div>
                                        </div>

                                        <!-- Botón eliminar -->
                                        <div class="product-actions">
                                            <button type="button" class="btn-remove remove-btn" data-item-id="<?= $item['id'] ?>" title="Eliminar producto">
                                                Borrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <br>
                    <!-- Resumen de compra -->
                    <div class="form-section">
                        <h3>Resumen del Pedido</h3>
                        
                        <div class="summary-content">
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

                            <div class="summary-total">
                                <span class="summary-label">Total:</span>
                                <span class="summary-value-total" id="total">$<?= number_format($total, 0, ',', '.') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Información importante -->
                    <div class="info-box">
                        <div class="info-content">
                            <div>
                                <h4>Tu compra está 100% protegida</h4>
                                <ul>
                                    <li><strong>Pagos seguros:</strong> Utilizamos códigos QR seguros para procesar tu pago</li>
                                    <li><strong>Envío gratuito:</strong> Todos los pedidos incluyen envío sin costo adicional</li>
                                    <li><strong>Soporte 24/7:</strong> Estamos aquí para ayudarte en cualquier momento</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones del carrito -->
                    <div class="form-actions">
                        <button type="button" class="btn-back" onclick="goToCatalog()">
                            Seguir Comprando
                        </button>
                        
                        <button type="button" class="btn-secondary btn-clear-cart">
                            Vaciar Carrito
                        </button>
                        
                        <button type="button" class="btn-continue" id="checkoutBtn">
                            Continuar a Tus Datos
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once VIEWS_PATH . '/vistas/layouts/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const baseUrl = '<?= BASE_URL ?>';
    
    // Usamos delegación de eventos en el contenedor principal para todos los botones.
    // Esto evita errores si los botones no existen al cargar la página (ej. carrito vacío).
    const cartContainer = document.querySelector('.form-container');
    if (!cartContainer) return;

    cartContainer.addEventListener('click', function(e) {
        const target = e.target;
        console.log('Button clicked:', target.className, target);
        
        // Botones de cantidad
        if (target.classList.contains('qty-plus')) {
            console.log('Quantity plus clicked');
            handleQuantityChange(target, 1);
        } else if (target.classList.contains('qty-minus')) {
            console.log('Quantity minus clicked');
            handleQuantityChange(target, -1);
        } 
        // Botón de eliminar item
        else if (target.classList.contains('remove-btn')) {
            console.log('Remove button clicked');
            handleRemoveItem(target);
        } 
        // Botón de vaciar carrito
        else if (target.classList.contains('btn-clear-cart')) {
            console.log('Clear cart clicked');
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
        // Botón de continuar al paso 2
        else if (target.id === 'checkoutBtn' && !target.disabled) {
            console.log('Checkout button clicked');
            handleCheckout();
        } else {
            console.log('Unhandled click on:', target.className, target.tagName, target.id);
        }
    });

    function handleQuantityChange(button, change) {
        const productItem = button.closest('.product-item');
        if (!productItem) return;

        const itemId = productItem.getAttribute('data-item-id');
        const productId = productItem.getAttribute('data-product-id');
        const qtyDisplay = productItem.querySelector('.qty-display');
        
        if (!qtyDisplay) return;

        let quantity = parseInt(qtyDisplay.textContent) + change;
        if (quantity < 1) quantity = 1;

        console.log('Updating quantity:', itemId, 'to', quantity);
        updateCartItem(itemId, quantity);
    }

    function handleRemoveItem(button) {
        const productItem = button.closest('.product-item');
        if (!productItem) return;

        const itemId = button.getAttribute('data-item-id') || productItem.getAttribute('data-item-id');
        const productName = productItem.querySelector('.product-name').textContent;

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
        const items = document.querySelectorAll('.product-item');
        if (items.length === 0) {
            alert("Tu carrito está vacío.");
            return;
        }

        console.log('Starting checkout process - going to step 2');
        
        const checkoutBtn = document.getElementById('checkoutBtn');
        checkoutBtn.disabled = true;
        checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

        const itemsToCheck = Array.from(items).map(item => ({
            productId: item.getAttribute('data-product-id'),
            quantity: parseInt(item.querySelector('.qty-display').textContent),
            productName: item.querySelector('.product-name').textContent
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
                checkoutBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Continuar a Datos';
                return;
            }

            // Redirigir al paso 2 del checkout
            window.location.href = baseUrl + '/checkout/customer-data';
        })
        .catch(error => {
            console.error('Error al verificar stock:', error);
            alert('Error al verificar stock');
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-arrow-right"></i> Continuar a Datos';
        });
    }
});

// Función para ir al catálogo
function goToCatalog() {
    const baseUrl = '<?= BASE_URL ?>';
    window.location.href = baseUrl + '/catalog';
}

// Animación de entrada
document.addEventListener('DOMContentLoaded', function() {
    const formSections = document.querySelectorAll('.form-section');
    formSections.forEach((section, index) => {
        section.style.animationDelay = `${index * 0.2}s`;
        section.classList.add('slide-in');
    });
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>