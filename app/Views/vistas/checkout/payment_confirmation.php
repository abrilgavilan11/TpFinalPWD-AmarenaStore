<?php
$title = $data['title'] ?? 'Confirmar Pago - Amarena Store';
$order = $data['order'] ?? [];

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<div class="checkout-flow">
    <div class="container">
        <div class="checkout-content">
            <!-- Título de la página -->
            <div class="stage-header">
                <h1><i class="fas fa-credit-card"></i> Confirmar Pago</h1>
                <p>Confirma que realizaste el pago de tu orden</p>
            </div>
            
            <div class="form-container">
                <div class="payment-confirmation-content">
                    <!-- Información de la orden -->
                    <div class="order-info">
                        <div class="order-header">
                            <h2>Información de tu Pedido</h2>
                            <span class="order-number">#<?= htmlspecialchars($order['id'] ?? '') ?></span>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-row">
                                <span class="label">Total a Pagar:</span>
                                <span class="value total">$<?= number_format($order['cototal'] ?? 0, 0, ',', '.') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Fecha de Pedido:</span>
                                <span class="value"><?= date('d/m/Y H:i', strtotime($order['cofecha'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Instrucciones de pago -->
                    <div class="payment-instructions">
                        <h3><i class="fas fa-mobile-alt"></i> ¿Ya realizaste el pago?</h3>
                        <p>Si acabas de realizar el pago desde tu aplicación móvil, confirma aquí para actualizar el estado de tu pedido.</p>
                        
                        <div class="payment-methods-info">
                            <div class="method-item">
                                <i class="fab fa-cc-visa"></i>
                                <span>Mercado Pago</span>
                            </div>
                            <div class="method-item">
                                <i class="fas fa-university"></i>
                                <span>Transferencia</span>
                            </div>
                            <div class="method-item">
                                <i class="fas fa-mobile-alt"></i>
                                <span>Ualá, Modo, etc.</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="confirmation-actions">
                        <button type="button" class="btn-confirm-payment" onclick="confirmPayment('<?= htmlspecialchars($order['id'] ?? '') ?>')">
                            <i class="fas fa-check-circle"></i>
                            ¡Sí, ya pagué!
                        </button>
                        
                        <button type="button" class="btn-not-paid" onclick="notPaidYet()">
                            <i class="fas fa-clock"></i>
                            Aún no he pagado
                        </button>
                    </div>
                    
                    <!-- Información adicional -->
                    <div class="payment-info">
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <div>
                                <strong>Importante:</strong>
                                <p>Solo confirma el pago si ya lo realizaste desde tu app móvil. Una vez confirmado, recibirás un email con los detalles de tu pedido.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmPayment(orderId) {
    if (!orderId) {
        alert('Error: ID de orden no válido');
        return;
    }
    
    if (!confirm('¿Confirmas que ya realizaste el pago de esta orden?')) {
        return;
    }
    
    const btn = document.querySelector('.btn-confirm-payment');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirmando...';
    
    // Hacer petición para marcar como pagado
    fetch('<?= BASE_URL ?>/checkout/confirm-payment/' + orderId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            confirm_payment: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirigir a página de éxito
            window.location.href = '<?= BASE_URL ?>/checkout/payment-success/' + orderId;
        } else {
            alert(data.message || 'Error al confirmar el pago');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> ¡Sí, ya pagué!';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al confirmar el pago');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> ¡Sí, ya pagué!';
    });
}

function notPaidYet() {
    alert('Realiza el pago desde tu aplicación móvil y luego regresa aquí para confirmar.');
}
</script>

<style>
.payment-confirmation-content {
    padding: 2rem;
}

.order-info {
    background: var(--color-light);
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 2px solid var(--color-pastel);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--color-pastel);
}

.order-header h2 {
    color: var(--color-primary-dark);
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    margin: 0;
}

.order-number {
    background: var(--color-primary);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    font-weight: 700;
    font-family: 'Raleway', sans-serif;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    font-family: 'Raleway', sans-serif;
}

.detail-row .label {
    font-weight: 600;
    color: var(--color-text);
}

.detail-row .value {
    font-weight: 700;
    color: var(--color-primary-dark);
}

.detail-row .total {
    font-size: 1.3rem;
    color: var(--color-primary);
}

.payment-instructions {
    text-align: center;
    margin: 2rem 0;
}

.payment-instructions h3 {
    color: var(--color-primary-dark);
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    margin-bottom: 1rem;
}

.payment-instructions p {
    color: var(--color-text);
    font-family: 'Raleway', sans-serif;
    font-weight: 500;
    margin-bottom: 2rem;
}

.payment-methods-info {
    display: flex;
    justify-content: center;
    gap: 2rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.method-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    border: 1px solid var(--color-pastel);
    min-width: 100px;
}

.method-item i {
    font-size: 2rem;
    color: var(--color-primary);
}

.method-item span {
    font-family: 'Raleway', sans-serif;
    font-weight: 600;
    color: var(--color-text);
    font-size: 0.9rem;
}

.confirmation-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin: 3rem 0;
    flex-wrap: wrap;
}

.btn-confirm-payment {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 16px 32px;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Raleway', sans-serif;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
}

.btn-confirm-payment:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
}

.btn-not-paid {
    background: transparent;
    color: var(--color-text);
    border: 2px solid var(--color-pastel);
    padding: 16px 32px;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Raleway', sans-serif;
}

.btn-not-paid:hover {
    background: var(--color-pastel);
    color: var(--color-primary-dark);
    transform: translateY(-2px);
}

.payment-info {
    margin-top: 3rem;
}

@media (max-width: 768px) {
    .confirmation-actions {
        flex-direction: column;
    }
    
    .btn-confirm-payment,
    .btn-not-paid {
        width: 100%;
        justify-content: center;
    }
    
    .payment-methods-info {
        gap: 1rem;
    }
    
    .order-header {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<?php require_once VIEWS_PATH . '/vistas/layouts/footer.php'; ?>