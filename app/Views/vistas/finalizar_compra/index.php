<?php
use App\Utils\Session;

$title = 'Checkout - Amarena Store';
$pageCss = 'checkout';
$cartItems = $data['data']['cartItems'] ?? [];
$total = $data['data']['total'] ?? 0;
$user = Session::get('user_name') ? [
    'name' => Session::get('user_name'),
    'email' => Session::get('user_email', '')
] : null;

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<div class="checkout-page">
    <div class="checkout-container">
        <div class="checkout-header">
            <h1><i class="fas fa-lock"></i> Finalizar Compra</h1>
            <div class="checkout-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-label">Datos</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-label">Pago</span>
                </div>
                <div class="step-line"></div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-label">Confirmación</span>
                </div>
            </div>
        </div>

        <div class="checkout-content">
            <div class="checkout-form">
                <form id="checkout-form">
                    <!-- Información Personal -->
                    <div class="form-section">
                        <h2><i class="fas fa-user"></i> Información Personal</h2>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="full-name">Nombre Completo *</label>
                                <input type="text" id="full-name" name="full_name" required 
                                       value="<?= htmlspecialchars($user['name'] ?? '') ?>" 
                                       placeholder="Juan Pérez">
                            </div>
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" required 
                                       value="<?= htmlspecialchars($user['email'] ?? '') ?>" 
                                       placeholder="tu@email.com">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">Teléfono *</label>
                                <input type="tel" id="phone" name="phone" required 
                                       placeholder="+54 9 299 123-4567">
                            </div>
                            <div class="form-group">
                                <label for="dni">DNI *</label>
                                <input type="text" id="dni" name="dni" required 
                                       placeholder="12.345.678">
                            </div>
                        </div>
                    </div>

                    <!-- Dirección de Envío -->
                    <div class="form-section">
                        <h2><i class="fas fa-map-marker-alt"></i> Dirección de Envío</h2>
                        <div class="form-group">
                            <label for="address">Dirección *</label>
                            <input type="text" id="address" name="address" required 
                                   placeholder="Calle, Número, Piso, Dpto">
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">Ciudad *</label>
                                <input type="text" id="city" name="city" required 
                                       placeholder="Plottier">
                            </div>
                            <div class="form-group">
                                <label for="province">Provincia *</label>
                                <select id="province" name="province" required>
                                    <option value="">Seleccionar provincia</option>
                                    <option value="Neuquén" selected>Neuquén</option>
                                    <option value="Buenos Aires">Buenos Aires</option>
                                    <option value="Córdoba">Córdoba</option>
                                    <option value="Santa Fe">Santa Fe</option>
                                    <option value="Mendoza">Mendoza</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="postal-code">Código Postal *</label>
                                <input type="text" id="postal-code" name="postal_code" required 
                                       placeholder="8300">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notas adicionales (opcional)</label>
                            <textarea id="notes" name="notes" rows="3" 
                                      placeholder="Ej: Timbre roto, dejar con el portero, etc."></textarea>
                        </div>
                    </div>

                    <!-- Método de Pago -->
                    <div class="form-section">
                        <h2><i class="fas fa-credit-card"></i> Método de Pago</h2>
                        
                        <div class="payment-methods">
                            <!-- Mercado Pago eliminado -->

                            <div class="payment-method" data-method="transfer">
                                <input type="radio" id="transfer" name="payment_method" value="transfer">
                                <label for="transfer">
                                    <div class="method-icon">
                                        <i class="fas fa-university"></i>
                                    </div>
                                    <div class="method-info">
                                        <span class="method-name">Transferencia Bancaria</span>
                                        <span class="method-desc discount-badge">15% de descuento</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div id="transfer-info" class="transfer-info" style="display: none;">
                            <div class="info-box">
                                <h3><i class="fas fa-info-circle"></i> Datos para Transferencia</h3>
                                <div class="bank-details">
                                    <div class="detail-row">
                                        <span class="label">Banco:</span>
                                        <span class="value">Banco Provincia del Neuquén</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">CBU:</span>
                                        <span class="value">0970099730000012345678</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Alias:</span>
                                        <span class="value">AMARENA.STORE</span>
                                    </div>
                                    <div class="detail-row">
                                        <span class="label">Titular:</span>
                                        <span class="value">Amarena Store S.A.</span>
                                    </div>
                                </div>
                                <p class="info-note">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Envía el comprobante a <strong>pagos@amarenastore.com</strong> después de realizar la transferencia.
                                </p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <h2>Resumen del Pedido</h2>
                
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
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
                                <span class="item-qty"><?= $item['quantity'] ?></span>
                            </div>
                            <div class="item-details">
                                <h4><?= htmlspecialchars($item['name']) ?></h4>
                                <p><?= htmlspecialchars($item['size'] ?? 'N/A') ?> • <?= htmlspecialchars($item['color'] ?? 'N/A') ?></p>
                            </div>
                            <div class="item-price">
                                $<?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-totals">
                    <div class="total-row">
                        <span>Subtotal</span>
                        <span id="subtotal">$<?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                    <div class="total-row">
                        <span>Envío</span>
                        <span class="text-success">Gratis</span>
                    </div>
                    <div class="total-row discount-row" id="discount-row" style="display: none;">
                        <span>Descuento (15%)</span>
                        <span class="text-success" id="discount-amount">-$0</span>
                    </div>
                    <div class="total-divider"></div>
                    <div class="total-row total-final">
                        <span>Total</span>
                        <span id="final-total">$<?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                </div>

                <button type="button" class="btn btn--checkout" onclick="processCheckout()">
                    <i class="fas fa-lock"></i> Confirmar Compra
                </button>

                <div class="security-badges">
                    <div class="security-badge">
                        <i class="fas fa-shield-alt"></i>
                        <span>Pago 100% Seguro</span>
                    </div>
                    <div class="security-badge">
                        <i class="fas fa-lock"></i>
                        <span>Encriptación SSL</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const baseTotal = <?= $total ?>;
let currentTotal = baseTotal;

// Payment method change handler
document.querySelectorAll('input[name="payment_method"]').forEach(input => {
    input.addEventListener('change', function() {
        const transferInfo = document.getElementById('transfer-info');
        const discountRow = document.getElementById('discount-row');
        const discountAmount = document.getElementById('discount-amount');
        const finalTotal = document.getElementById('final-total');
        
        if (this.value === 'transfer') {
            transferInfo.style.display = 'block';
            discountRow.style.display = 'flex';
            
            const discount = Math.round(baseTotal * 0.15);
            currentTotal = baseTotal - discount;
            
            discountAmount.textContent = '-$' + discount.toLocaleString('es-AR');
            finalTotal.textContent = '$' + currentTotal.toLocaleString('es-AR');
        } else {
            transferInfo.style.display = 'none';
            discountRow.style.display = 'none';
            currentTotal = baseTotal;
            finalTotal.textContent = '$' + baseTotal.toLocaleString('es-AR');
        }
    });
});

function processCheckout() {
    const form = document.getElementById('checkout-form');
    
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
    
    if (paymentMethod === 'transfer') {
        // Handle transfer payment
        alert('Por favor realiza la transferencia con los datos proporcionados y envía el comprobante a pagos@amarenastore.com');
        // You can add more logic here to save the order with "pending payment" status
    }
}

console.log('Checkout page loaded');
</script>

<?php
$extraScripts = [];
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
