<?php
$title = $data['title'] ?? 'Error de Pago - Amarena Store';
$errorMessage = $data['error_message'] ?? 'Error desconocido';

require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<div class="checkout-flow">
    <div class="container">
        <div class="checkout-content">
            <!-- Título de la página -->
            <div class="stage-header">
                <h1><i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i> Error de Pago</h1>
                <p>Ha ocurrido un problema con la verificación de tu pago</p>
            </div>
            
            <div class="form-container">
                <div class="payment-error-content">
                    <div class="error-icon">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    
                    <h2>No se pudo procesar el pago</h2>
                    
                    <div class="error-message">
                        <p><?= htmlspecialchars($errorMessage) ?></p>
                    </div>
                    
                    <div class="error-actions">
                        <a href="<?= BASE_URL ?>/checkout" class="btn-continue">
                            <i class="fas fa-redo"></i>
                            Intentar Nuevamente
                        </a>
                        
                        <a href="<?= BASE_URL ?>/catalog" class="btn-back">
                            <i class="fas fa-shopping-bag"></i>
                            Volver al Catálogo
                        </a>
                    </div>
                    
                    <div class="help-section">
                        <h3>¿Necesitas ayuda?</h3>
                        <p>Si continúas teniendo problemas, puedes contactarnos:</p>
                        <ul>
                            <li><i class="fas fa-envelope"></i> Email: soporte@amarenastore.com</li>
                            <li><i class="fas fa-phone"></i> WhatsApp: +54 9 11 1234-5678</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.payment-error-content {
    text-align: center;
    padding: 3rem 2rem;
}

.error-icon {
    font-size: 4rem;
    color: #dc3545;
    margin-bottom: 1.5rem;
}

.payment-error-content h2 {
    color: var(--color-primary-dark);
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    font-family: 'Raleway', sans-serif;
}

.error-message {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    border-radius: 12px;
    padding: 1.5rem;
    margin: 2rem 0;
    color: #721c24;
}

.error-message p {
    margin: 0;
    font-size: 1.1rem;
    font-family: 'Raleway', sans-serif;
    font-weight: 500;
}

.error-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.help-section {
    background: var(--color-light);
    border-radius: 15px;
    padding: 2rem;
    margin-top: 3rem;
    border: 1px solid var(--color-pastel);
}

.help-section h3 {
    color: var(--color-primary-dark);
    margin-bottom: 1rem;
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
}

.help-section ul {
    list-style: none;
    padding: 0;
    margin: 1rem 0;
}

.help-section li {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
    font-family: 'Raleway', sans-serif;
    font-weight: 500;
}

.help-section i {
    color: var(--color-primary);
    width: 20px;
}

@media (max-width: 768px) {
    .error-actions {
        flex-direction: column;
    }
    
    .error-actions .btn-continue,
    .error-actions .btn-back {
        width: 100%;
        justify-content: center;
    }
}
</style>

<?php require_once VIEWS_PATH . '/vistas/layouts/footer.php'; ?>