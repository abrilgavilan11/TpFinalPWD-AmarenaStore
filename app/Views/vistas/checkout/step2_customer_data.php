<?php
$title = $data['title'] ?? 'Tus Datos - Amarena Store';
$userData = $data['userData'] ?? [];
$step = $data['step'] ?? 2;

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
                <div class="step active">
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
                <h1>Tus Datos de Entrega</h1>
                <p>Necesitamos esta información para procesar tu pedido y coordinar la entrega</p>
            </div>

            <?php 
            $errorMessage = \App\Utils\Session::flash('error');
            if ($errorMessage): 
            ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($errorMessage) ?>
            </div>
            <?php endif; ?>

            <div class="form-container">
                <form id="customer-form" method="POST" action="<?= BASE_URL ?>/checkout/customer-data">
                    <div class="form-grid">
                        <!-- Datos personales -->
                        <div class="form-section">
                            <h3>Información Personal</h3>
                            
                            <div class="form-group">
                                <label for="full_name">Nombre Completo *</label>
                                <input type="text" 
                                       id="full_name" 
                                       name="full_name" 
                                       value="<?= htmlspecialchars($userData['name'] ?? '') ?>"
                                       required 
                                       placeholder="Ej: Juan Carlos Pérez">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" 
                                           id="email" 
                                           name="email" 
                                           value="<?= htmlspecialchars($userData['email'] ?? '') ?>"
                                           required 
                                           placeholder="tu@email.com">
                                </div>
                                <div class="form-group">
                                    <label for="phone">Teléfono *</label>
                                    <input type="tel" 
                                           id="phone" 
                                           name="phone" 
                                           required 
                                           placeholder="2995123456">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="dni">DNI / CUIL *</label>
                                <input type="text" 
                                       id="dni" 
                                       name="dni" 
                                       required 
                                       placeholder="12345678">
                            </div>
                        </div>

                        <!-- Dirección de entrega -->
                        <div class="form-section">
                            <h3>Dirección de Entrega</h3>
                            
                            <div class="form-group">
                                <label for="address">Dirección Completa *</label>
                                <input type="text" 
                                       id="address" 
                                       name="address" 
                                       required 
                                       placeholder="Ej: Av. San Martín 1234, Depto 5B">
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="city">Ciudad *</label>
                                    <input type="text" 
                                           id="city" 
                                           name="city" 
                                           required 
                                           placeholder="Ej: Neuquén">
                                </div>
                                <div class="form-group">
                                    <label for="province">Provincia *</label>
                                    <select id="province" name="province" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Buenos Aires">Buenos Aires</option>
                                        <option value="Catamarca">Catamarca</option>
                                        <option value="Chaco">Chaco</option>
                                        <option value="Chubut">Chubut</option>
                                        <option value="Córdoba">Córdoba</option>
                                        <option value="Corrientes">Corrientes</option>
                                        <option value="Entre Ríos">Entre Ríos</option>
                                        <option value="Formosa">Formosa</option>
                                        <option value="Jujuy">Jujuy</option>
                                        <option value="La Pampa">La Pampa</option>
                                        <option value="La Rioja">La Rioja</option>
                                        <option value="Mendoza">Mendoza</option>
                                        <option value="Misiones">Misiones</option>
                                        <option value="Neuquén" selected>Neuquén</option>
                                        <option value="Río Negro">Río Negro</option>
                                        <option value="Salta">Salta</option>
                                        <option value="San Juan">San Juan</option>
                                        <option value="San Luis">San Luis</option>
                                        <option value="Santa Cruz">Santa Cruz</option>
                                        <option value="Santa Fe">Santa Fe</option>
                                        <option value="Santiago del Estero">Santiago del Estero</option>
                                        <option value="Tierra del Fuego">Tierra del Fuego</option>
                                        <option value="Tucumán">Tucumán</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="postal_code">Código Postal</label>
                                <input type="text" 
                                       id="postal_code" 
                                       name="postal_code" 
                                       placeholder="8300">
                            </div>

                            <div class="form-group">
                                <label for="notes">Notas adicionales</label>
                                <textarea id="notes" 
                                          name="notes" 
                                          rows="3" 
                                          placeholder="Ej: Timbre roto, dejar con el portero, entregar después de las 18hs..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Información importante -->
                    <div class="info-box">
                        <div class="info-content">
                            <div>
                                <h4>¿Por qué necesitamos estos datos?</h4>
                                <ul>
                                    <li><strong>Datos personales:</strong> Para generar la factura y contactarte si es necesario</li>
                                    <li><strong>Dirección:</strong> Para coordinar la entrega de tu pedido</li>
                                    <li><strong>Teléfono:</strong> Para comunicarnos contigo durante el proceso de envío</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Acciones del formulario -->
                    <div class="form-actions">
                        <button type="button" class="btn-back" onclick="goBackToCart()">
                            Volver al Carrito
                        </button>
                        
                        <button type="submit" class="btn-continue" id="submit-btn">
                            Continuar al Resumen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= BASE_URL ?>';

function goBackToCart() {
    window.location.href = `${baseUrl}/checkout/cart`;
}

// Validación del formulario
document.getElementById('customer-form').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submit-btn');
    
    // Validar datos
    if (!this.checkValidity()) {
        e.preventDefault();
        this.reportValidity();
        return;
    }
    
    // Mostrar loading (no prevenir submit aquí)
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    submitBtn.disabled = true;
    
    // Permitir que el formulario se envíe normalmente
});

// Animación de entrada
document.addEventListener('DOMContentLoaded', function() {
    const formSections = document.querySelectorAll('.form-section');
    formSections.forEach((section, index) => {
        section.style.animationDelay = `${index * 0.2}s`;
        section.classList.add('slide-in');
    });
    
    // Auto-focus en el primer campo
    document.getElementById('full_name').focus();
});

// Formatear teléfono mientras se escribe
document.getElementById('phone').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 10) value = value.substring(0, 10);
    this.value = value;
});

// Formatear DNI mientras se escribe
document.getElementById('dni').addEventListener('input', function() {
    let value = this.value.replace(/\D/g, '');
    if (value.length > 8) value = value.substring(0, 8);
    this.value = value;
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>