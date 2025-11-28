<?php
$title = $data['title'] ?? 'Resumen de Compra - Amarena Store';
$cartItems = $data['cartItems'] ?? [];
$customerData = $data['customerData'] ?? [];
$totals = $data['totals'] ?? [];
$step = $data['step'] ?? 3;

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<div class="checkout-flow">
    <!-- Indicador de progreso -->
    <div class="progress-indicator">
        <div class="container">
            <div class="steps">
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Carrito</div>
                </div>
                <div class="step-line completed"></div>
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Tus Datos</div>
                </div>
                <div class="step-line completed"></div>
                <div class="step active">
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
                <h1>Resumen de tu Pedido</h1>
                <p>Revisa todos los detalles antes de generar el código QR de pago</p>
            </div>

            <div class="summary-container">
                <div class="summary-grid">
                    <!-- Productos del pedido -->
                    <div class="order-section">
                        <div class="section-header">
                            <h3>Productos (<?= count($cartItems) ?>)</h3>
                        </div>
                        
                        <div class="products-list">
                            <?php foreach($cartItems as $item): ?>
                            <div class="product-summary-item">
                                <div class="product-image">
                                    <?php
                                    $imagePath = BASE_URL . '/img/ropa/' . htmlspecialchars($item['image'] ?? 'default.jpg');
                                    if (!empty($item['image']) && strpos($item['image'], 'product_') === 0) {
                                        $imagePath = BASE_URL . '/uploads/products/' . htmlspecialchars($item['image']);
                                    }
                                    ?>
                                    <img src="<?= $imagePath ?>" 
                                         alt="<?= htmlspecialchars($item['name']) ?>">
                                </div>
                                <div class="product-details">
                                    <h4><?= htmlspecialchars($item['name']) ?></h4>
                                    <div class="product-meta">
                                        <span class="quantity">Cantidad: <?= $item['quantity'] ?></span>
                                        <span class="unit-price">Precio unitario: $<?= number_format($item['price'], 0, ',', '.') ?></span>
                                    </div>
                                    <div class="product-total">
                                        <strong>Subtotal: $<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></strong>
                                    </div>
                                </div>
                                <div class="edit-product">
                                    <button type="button" class="btn-edit-cart" onclick="goBackToCart()">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Datos del cliente -->
                    <div class="customer-section">
                        <div class="section-header">
                            <h3>Datos del Cliente</h3>
                            <button type="button" class="btn-edit" onclick="goBackToData()">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                        </div>
                        
                        <div class="customer-info">
                            <div class="info-row">
                                <span class="label">Nombre:</span>
                                <span class="value"><?= htmlspecialchars($customerData['full_name'] ?? '') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Email:</span>
                                <span class="value"><?= htmlspecialchars($customerData['email'] ?? '') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Teléfono:</span>
                                <span class="value"><?= htmlspecialchars($customerData['phone'] ?? '') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">DNI:</span>
                                <span class="value"><?= htmlspecialchars($customerData['dni'] ?? '') ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Dirección de entrega -->
                    <div class="delivery-section">
                        <div class="section-header">
                            <h3>Entrega</h3>
                        </div>
                        
                        <div class="delivery-info">
                            <div class="address-block">
                                <p><strong><?= htmlspecialchars($customerData['address'] ?? '') ?></strong></p>
                                <p><?= htmlspecialchars($customerData['city'] ?? '') ?>, <?= htmlspecialchars($customerData['province'] ?? '') ?></p>
                                <?php if(!empty($customerData['postal_code'])): ?>
                                <p>CP: <?= htmlspecialchars($customerData['postal_code']) ?></p>
                                <?php endif; ?>
                                <?php if(!empty($customerData['notes'])): ?>
                                <div class="delivery-notes">
                                    <strong>Notas:</strong> <?= htmlspecialchars($customerData['notes']) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumen de totales -->
                <div class="totals-section">
                    <div class="totals-card">
                        <h3>Total del Pedido</h3>
                        
                        <div class="totals-breakdown">
                            <div class="total-row">
                                <span class="label">Subtotal (<?= $totals['total_items'] ?> productos):</span>
                                <span class="value">$<?= number_format($totals['subtotal'], 0, ',', '.') ?></span>
                            </div>
                            
                            <?php if(isset($totals['discount']) && $totals['discount'] > 0): ?>
                            <div class="total-row discount">
                                <span class="label">Descuento:</span>
                                <span class="value">-$<?= number_format($totals['discount'], 0, ',', '.') ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="total-row shipping">
                                <span class="label">Envío:</span>
                                <span class="value shipping-cost">
                                    <?php if($totals['shipping'] == 0): ?>
                                        <span class="free-shipping">GRATIS</span>
                                    <?php else: ?>
                                        $<?= number_format($totals['shipping'], 0, ',', '.') ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            
                            <hr class="divider">
                            
                            <div class="total-row final-total">
                                <span class="label"><strong>Total a Pagar:</strong></span>
                                <span class="value"><strong>$<?= number_format($totals['total'], 0, ',', '.') ?></strong></span>
                            </div>
                        </div>

                        <!-- Métodos de pago disponibles -->
                        <div class="payment-info">
                            <h4>Método de Pago</h4>
                            <div class="payment-method">
                                <div class="qr-payment-info">
                                    <div class="payment-details">
                                        <strong>Pago con Código QR</strong>
                                        <p>Escanea con tu app de pagos favorita</p>
                                        <div class="payment-apps">
                                            <span class="app-tag">Mercado Pago</span>
                                            <span class="app-tag">Ualá</span>
                                            <span class="app-tag">Modo</span>
                                            <span class="app-tag">+ más</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Términos y condiciones -->
                <div class="terms-section">
                    <label class="terms-checkbox">
                        <input type="checkbox" id="accept-terms" required>
                        <span class="checkmark"></span>
                        <span class="terms-text">
                            Acepto los <a href="<?= BASE_URL ?>/terms" target="_blank">Términos y Condiciones</a> 
                            y la <a href="<?= BASE_URL ?>/privacy" target="_blank">Política de Privacidad</a>
                        </span>
                    </label>
                </div>

                <!-- Acciones del resumen -->
                <div class="summary-actions">
                    <button type="button" class="btn-back" onclick="goBackToData()">
                        Editar Datos
                    </button>
                    
                    <button type="button" class="btn-generate-qr" id="generate-qr-btn" onclick="generateQR()">
                        Generar Código QR
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= BASE_URL ?>';

function goBackToCart() {
    window.location.href = `${baseUrl}/checkout/cart`;
}

function goBackToData() {
    window.location.href = `${baseUrl}/checkout/customer-data`;
}

async function generateQR() {
    const termsCheckbox = document.getElementById('accept-terms');
    const generateBtn = document.getElementById('generate-qr-btn');
    
    if (!termsCheckbox.checked) {
        alert('Debes aceptar los términos y condiciones para continuar');
        termsCheckbox.focus();
        return;
    }
    
    // Mostrar loading
    const originalText = generateBtn.innerHTML;
    generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generando QR...';
    generateBtn.disabled = true;
    
    try {
        const response = await fetch(`${baseUrl}/checkout/generate-qr`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                accepted_terms: true
            })
        });
        
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const responseText = await response.text();
        console.log('Response text:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Respuesta inválida del servidor');
        }
        
        if (result.success) {
            window.location.href = `${baseUrl}/checkout/qr-payment`;
        } else {
            throw new Error(result.message || 'Error al generar el código QR');
        }
        
    } catch (error) {
        console.error('Error completo:', error);
        alert(`Error: ${error.message}`);
        
        // Restaurar botón
        generateBtn.innerHTML = originalText;
        generateBtn.disabled = false;
    }
}

// Animación de entrada
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.summary-container > div');
    sections.forEach((section, index) => {
        section.style.animationDelay = `${index * 0.1}s`;
        section.classList.add('fade-in');
    });
});

// Efecto hover en productos
document.querySelectorAll('.product-summary-item').forEach(item => {
    item.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px)';
    });
    
    item.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>