<?php
$title = $data['title'] ?? 'Pago Exitoso - Amarena Store';
$order = $data['order'] ?? [];
$message = $data['message'] ?? 'Pago completado exitosamente';

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<div class="checkout-flow">
    <div class="container">
        <div class="checkout-content">
            <!-- Título de la página -->
            <div class="stage-header">
                <h1><i class="fas fa-check-circle" style="color: #28a745;"></i> ¡Pago Exitoso!</h1>
                <p>Tu pedido ha sido procesado correctamente</p>
            </div>
            
            <div class="form-container">
                <div class="payment-success-content">
                    <!-- Icono de éxito -->
                    <div class="success-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    
                    <h2>¡Gracias por tu compra!</h2>
                    
                    <div class="success-message">
                        <p><?= htmlspecialchars($message) ?></p>
                    </div>
                    
                    <!-- Información de la orden -->
                    <div class="order-summary">
                        <div class="order-header">
                            <h3>Resumen de tu Pedido</h3>
                            <span class="order-number">#<?= htmlspecialchars($order['id'] ?? '') ?></span>
                        </div>
                        
                        <div class="order-details">
                            <div class="detail-row">
                                <span class="label">Total Pagado:</span>
                                <span class="value total">$<?= number_format($order['cototal'] ?? 0, 0, ',', '.') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Fecha de Pago:</span>
                                <span class="value"><?= date('d/m/Y H:i') ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="label">Estado:</span>
                                <span class="value status-paid">✓ PAGADO</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Próximos pasos -->
                    <div class="next-steps">
                        <h3><i class="fas fa-truck"></i> ¿Qué sigue ahora?</h3>
                        <div class="steps-list">
                            <div class="step-item completed">
                                <div class="step-icon"><i class="fas fa-check"></i></div>
                                <div class="step-content">
                                    <h4>Pago Confirmado</h4>
                                    <p>Tu pago ha sido procesado exitosamente</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-icon"><i class="fas fa-cog"></i></div>
                                <div class="step-content">
                                    <h4>Preparando Pedido</h4>
                                    <p>Estamos preparando tus productos para el envío</p>
                                </div>
                            </div>
                            
                            <div class="step-item">
                                <div class="step-icon"><i class="fas fa-shipping-fast"></i></div>
                                <div class="step-content">
                                    <h4>Envío</h4>
                                    <p>Te enviaremos un email con el código de seguimiento</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="success-actions">
                        <a href="<?= BASE_URL ?>/catalog" class="btn-continue-shopping">
                            <i class="fas fa-shopping-bag"></i>
                            Seguir Comprando
                        </a>
                        
                        <a href="<?= BASE_URL ?>/orders" class="btn-view-orders">
                            <i class="fas fa-list"></i>
                            Ver Mis Pedidos
                        </a>
                    </div>
                    
                    <!-- Información de contacto -->
                    <div class="contact-info">
                        <div class="info-box">
                            <i class="fas fa-headset"></i>
                            <div>
                                <h4>¿Tienes alguna pregunta?</h4>
                                <p>Contáctanos y te ayudaremos con cualquier duda sobre tu pedido.</p>
                                <div class="contact-methods">
                                    <span><i class="fas fa-envelope"></i> soporte@amarenastore.com</span>
                                    <span><i class="fas fa-phone"></i> +54 9 11 1234-5678</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-success-content {
    text-align: center;
    padding: 3rem 2rem;
}

.success-icon {
    font-size: 5rem;
    color: #28a745;
    margin-bottom: 1.5rem;
    animation: successPulse 2s infinite;
}

@keyframes successPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.payment-success-content h2 {
    color: var(--color-primary-dark);
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    font-family: 'Raleway', sans-serif;
}

.success-message {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 2rem 0;
    color: #155724;
}

.success-message p {
    margin: 0;
    font-size: 1.2rem;
    font-family: 'Raleway', sans-serif;
    font-weight: 600;
}

.order-summary {
    background: var(--color-light);
    border-radius: 15px;
    padding: 2rem;
    margin: 2rem 0;
    border: 2px solid var(--color-pastel);
    text-align: left;
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--color-pastel);
}

.order-header h3 {
    color: var(--color-primary-dark);
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    margin: 0;
}

.order-number {
    background: #28a745;
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
    color: #28a745;
}

.status-paid {
    background: #28a745;
    color: white;
    padding: 0.3rem 0.8rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

.next-steps {
    margin: 3rem 0;
    text-align: left;
}

.next-steps h3 {
    color: var(--color-primary-dark);
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    margin-bottom: 2rem;
    text-align: center;
}

.steps-list {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.step-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    border: 1px solid var(--color-pastel);
}

.step-item.completed {
    background: #f8f9fa;
    border-color: #28a745;
}

.step-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--color-pastel);
    color: var(--color-primary);
    font-size: 1.2rem;
}

.step-item.completed .step-icon {
    background: #28a745;
    color: white;
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

.success-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin: 3rem 0;
    flex-wrap: wrap;
}

.btn-continue-shopping,
.btn-view-orders {
    padding: 16px 32px;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: 'Raleway', sans-serif;
}

.btn-continue-shopping {
    background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
    color: white;
    box-shadow: 0 4px 15px rgba(217, 106, 126, 0.3);
}

.btn-continue-shopping:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(217, 106, 126, 0.4);
    text-decoration: none;
    color: white;
}

.btn-view-orders {
    background: transparent;
    color: var(--color-text);
    border: 2px solid var(--color-pastel);
}

.btn-view-orders:hover {
    background: var(--color-pastel);
    color: var(--color-primary-dark);
    transform: translateY(-2px);
    text-decoration: none;
}

.contact-info {
    margin-top: 3rem;
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
    .success-actions {
        flex-direction: column;
    }
    
    .btn-continue-shopping,
    .btn-view-orders {
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