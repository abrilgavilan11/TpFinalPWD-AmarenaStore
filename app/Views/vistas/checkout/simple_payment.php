<?php
$title = $data['title'] ?? 'Procesar Pago - Amarena Store';
$orderId = $data['order_id'] ?? '';
$order = $data['order'] ?? null;
$message = $data['message'] ?? null;
$status = $data['status'] ?? 'pending';

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<div class="checkout-flow">
    <div class="container">
        <div class="checkout-content">
            <!-- Título de la página -->
            <div class="stage-header">
                <?php if ($status === 'error'): ?>
                    <h1><i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Error de Pago</h1>
                    <p>Ha ocurrido un problema con tu código QR</p>
                <?php elseif ($status === 'success'): ?>
                    <h1><i class="fas fa-check-circle" style="color: #28a745;"></i> Pago Completado</h1>
                    <p>Tu pago ya ha sido procesado exitosamente</p>
                <?php else: ?>
                    <h1><i class="fas fa-credit-card"></i> Procesar Pago</h1>
                    <p>Confirma el pago de tu pedido</p>
                <?php endif; ?>
            </div>
            
            <div class="form-container">
                <div class="simple-payment-content">
                    
                    <?php if ($message): ?>
                        <div class="status-message <?= $status ?>">
                            <?php if ($status === 'error'): ?>
                                <i class="fas fa-times-circle"></i>
                            <?php elseif ($status === 'success'): ?>
                                <i class="fas fa-check-circle"></i>
                            <?php else: ?>
                                <i class="fas fa-info-circle"></i>
                            <?php endif; ?>
                            <p><?= htmlspecialchars($message) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($order && $status !== 'error'): ?>
                        <!-- Información del pedido -->
                        <div class="order-info">
                            <div class="order-header">
                                <h2>Información del Pedido</h2>
                                <span class="order-number">#<?= htmlspecialchars($orderId) ?></span>
                            </div>
                            
                            <div class="order-details">
                                <div class="detail-row">
                                    <span class="label">Total a Pagar:</span>
                                    <span class="value total">$<?= number_format($order['cototal'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                                <?php 
                                // Debug temporal - mostrar todos los campos disponibles
                                if ($order && isset($_GET['debug'])) {
                                    echo '<div style="background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px;">';
                                    echo '<strong>Debug - Datos de la orden:</strong><br>';
                                    foreach ($order as $key => $value) {
                                        echo "$key: " . htmlspecialchars($value) . "<br>";
                                    }
                                    echo '</div>';
                                }
                                ?>
                                <div class="detail-row">
                                    <span class="label">Fecha:</span>
                                    <span class="value"><?= date('d/m/Y H:i', strtotime($order['cofecha'] ?? 'now')) ?></span>
                                </div>
                                <div class="detail-row">
                                    <span class="label">Estado:</span>
                                    <span class="value status-<?= $status ?>">
                                        <?php if ($status === 'success'): ?>
                                            PAGADO
                                        <?php else: ?>
                                            PENDIENTE
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($status === 'pending'): ?>
                            <!-- Instrucciones de pago -->
                            <div class="payment-instructions">
                                <h3><i class="fas fa-mobile-alt"></i> ¿Cómo realizar el pago?</h3>
                                <div class="instructions-list">
                                    <div class="instruction-item">
                                        <div class="step-number">1</div>
                                        <div class="step-content">
                                            <h4>Abre tu app de pagos</h4>
                                            <p>Mercado Pago, Ualá, Modo, etc.</p>
                                        </div>
                                    </div>
                                    <div class="instruction-item">
                                        <div class="step-number">2</div>
                                        <div class="step-content">
                                            <h4>Realiza el pago</h4>
                                            <p>Por el monto exacto de <strong>$<?= number_format($order['cototal'] ?? 0, 0, ',', '.') ?></strong></p>
                                        </div>
                                    </div>
                                    <div class="instruction-item">
                                        <div class="step-number">3</div>
                                        <div class="step-content">
                                            <h4>Confirma aquí</h4>
                                            <p>Una vez realizado el pago, confirma abajo</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="payment-actions">
                                <button type="button" class="btn-confirm-payment" onclick="confirmPayment('<?= htmlspecialchars($orderId) ?>')">
                                    <i class="fas fa-check-circle"></i>
                                    ¡Ya realicé el pago!
                                </button>
                                
                                <button type="button" class="btn-not-paid" onclick="showHelp()">
                                    <i class="fas fa-question-circle"></i>
                                    ¿Cómo pago?
                                </button>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <!-- Botones generales -->
                    <div class="general-actions">
                        <?php if ($status === 'error'): ?>
                            <a href="<?= BASE_URL ?>/catalog" class="btn-back">
                                <i class="fas fa-shopping-bag"></i>
                                Volver a la Tienda
                            </a>
                        <?php elseif ($status === 'success'): ?>
                            <a href="<?= BASE_URL ?>/catalog" class="btn-continue">
                                <i class="fas fa-shopping-bag"></i>
                                Seguir Comprando
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Información de contacto -->
                    <div class="contact-info">
                        <div class="info-box">
                            <i class="fas fa-headset"></i>
                            <div>
                                <h4>¿Necesitas ayuda?</h4>
                                <p>Si tienes problemas con el pago, contáctanos:</p>
                                <div class="contact-methods">
                                    <span><i class="fas fa-phone"></i> WhatsApp: +54 9 11 1234-5678</span>
                                    <span><i class="fas fa-envelope"></i> Email: soporte@amarenastore.com</span>
                                </div>
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
    
    if (!confirm('¿Confirmas que ya realizaste el pago por el monto exacto indicado?')) {
        return;
    }
    
    const btn = document.querySelector('.btn-confirm-payment');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Confirmando pago...';
    
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
            // Redirigir al inicio con parámetro para mostrar modal de agradecimiento
            window.location.href = '<?= BASE_URL ?>/?payment_success=1';
        } else {
            alert(data.message || 'Error al confirmar el pago');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle"></i> ¡Ya realicé el pago!';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error de conexión al confirmar el pago');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle"></i> ¡Ya realicé el pago!';
    });
}

function showHelp() {
    alert('Pasos para pagar:\n\n1. Abre tu app favorita (Mercado Pago, Ualá, Modo, etc.)\n2. Selecciona "Pagar" o "Transferir"\n3. Ingresa el monto exacto mostrado\n4. Completa el pago\n5. Vuelve aquí y confirma\n\n¿Necesitas más ayuda? Contáctanos por WhatsApp.');
}

// Redirigir automáticamente si el pago ya está completado
<?php if ($status === 'success'): ?>
document.addEventListener('DOMContentLoaded', function() {
    // Esperar 2 segundos y luego redirigir al inicio con el modal
    setTimeout(function() {
        window.location.href = '<?= BASE_URL ?>/?payment_success=1';
    }, 2000);
});
<?php endif; ?>
</script>

<style>
.simple-payment-content {
    padding: 2rem;
}

.status-message {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    font-family: 'Raleway', sans-serif;
}

.status-message.error {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.status-message.success {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.status-message.pending {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
}

.status-message i {
    font-size: 1.5rem;
}

.status-message p {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
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
    font-size: 1.4rem;
    color: var(--color-primary);
}

.status-pending {
    background: #ffc107;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.status-success {
    background: #28a745;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.payment-instructions {
    margin: 2rem 0;
}

.payment-instructions h3 {
    color: var(--color-primary-dark);
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    margin-bottom: 1.5rem;
    text-align: center;
}

.instructions-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.instruction-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    border: 1px solid var(--color-pastel);
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--color-primary);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-family: 'Raleway', sans-serif;
}

.step-content h4 {
    margin: 0 0 0.5rem 0;
    color: var(--color-primary-dark);
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
}

.step-content p {
    margin: 0;
    color: var(--color-text);
    font-family: 'Raleway', sans-serif;
    font-weight: 500;
}

.payment-actions {
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
    color: var(--color-primary);
    border: 2px solid var(--color-primary);
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
    background: var(--color-primary);
    color: white;
    transform: translateY(-2px);
}

.general-actions {
    display: flex;
    justify-content: center;
    margin: 2rem 0;
}

.contact-methods {
    display: flex;
    gap: 2rem;
    justify-content: center;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.contact-methods span {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Raleway', sans-serif;
    font-weight: 500;
}

.contact-methods i {
    color: var(--color-primary);
}

@media (max-width: 768px) {
    .payment-actions {
        flex-direction: column;
    }
    
    .btn-confirm-payment,
    .btn-not-paid {
        width: 100%;
        justify-content: center;
    }
    
    .order-header {
        flex-direction: column;
        gap: 1rem;
    }
    
    .contact-methods {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>

<?php require_once VIEWS_PATH . '/vistas/layouts/footer.php'; ?>