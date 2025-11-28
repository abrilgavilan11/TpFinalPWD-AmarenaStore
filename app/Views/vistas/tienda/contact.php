<?php
// Configurar CSS específico para la página de contacto
// $data['pageCss'] = 'contact'; // Temporalmente deshabilitado para usar estilos inline
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<section class="contact-page">
    <div class="contact-page__container">
        <h1>Contacto - Amarena Store</h1>
        
        <!-- Contenedor principal del formulario -->
        <div class="contact-content-wrapper contact-desktop-layout">
            <!-- Formulario de contacto con diseño de checkout -->
            <div class="form-container checkout-style">
                <div class="stage-header">
                    <h2><i class="fas fa-envelope"></i> Envíanos un Mensaje</h2>
                    <p>Completa el formulario y te contactaremos a la brevedad</p>
                </div>

                <?php 
                $errorMessage = \App\Utils\Session::flash('error');
                $successMessage = \App\Utils\Session::flash('success');
                if ($errorMessage): 
                ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($successMessage): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($successMessage) ?>
                </div>
                <?php endif; ?>

                <form action="<?= BASE_URL ?>/contact/enviar" method="POST" id="contact-form" novalidate>
                    <div class="form-grid">
                        <!-- Formulario Unificado -->
                        <div class="form-section">                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" 
                                           id="nombre" 
                                           name="nombre" 
                                           required 
                                           placeholder="Ej: Juan"
                                           pattern="[A-Za-zÀ-ÿ\u00f1\u00d1 ]+"
                                           title="Solo se permiten letras y espacios">
                                    <div class="error-message" id="nombre-error"></div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="apellido">Apellido *</label>
                                    <input type="text" 
                                           id="apellido" 
                                           name="apellido" 
                                           required 
                                           placeholder="Ej: Pérez"
                                           pattern="[A-Za-zÀ-ÿ\u00f1\u00d1 ]+"
                                           title="Solo se permiten letras y espacios">
                                    <div class="error-message" id="apellido-error"></div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       required 
                                       placeholder="tu@email.com">
                                <div class="error-message" id="email-error"></div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group" style="flex: 0 0 30%;">
                                    <label for="codigo_area">País *</label>
                                    <select id="codigo_area" name="codigo_area" required>
                                        <option value="+54" selected>Argentina (+54)</option>
                                        <option value="+55">Brasil (+55)</option>
                                        <option value="+56">Chile (+56)</option>
                                        <option value="+57">Colombia (+57)</option>
                                        <option value="+598">Uruguay (+598)</option>
                                        <option value="+595">Paraguay (+595)</option>
                                        <option value="+591">Bolivia (+591)</option>
                                        <option value="+593">Ecuador (+593)</option>
                                        <option value="+51">Perú (+51)</option>
                                        <option value="+58">Venezuela (+58)</option>
                                        <option value="+34">España (+34)</option>
                                        <option value="+1">Estados Unidos (+1)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group" style="flex: 1;">
                                    <label for="telefono">Teléfono *</label>
                                    <input type="tel" 
                                           id="telefono" 
                                           name="telefono" 
                                           required 
                                           placeholder="2995123456"
                                           pattern="[0-9]+"
                                           title="Solo se permiten números">
                                    <div class="error-message" id="telefono-error"></div>
                                </div>
                            </div>
                            
                            <div class="phone-preview" id="phone-preview">
                                
                                <i class="fas fa-phone"></i>
                                <span id="full-phone">+54 2995123456</span>
                            </div>
                            
                            <div class="form-group">
                                <label for="comentarios">Comentarios</label>
                                <textarea id="comentarios" 
                                          name="comentarios" 
                                          rows="4" 
                                          placeholder="Cuéntanos en qué podemos ayudarte... (opcional)"></textarea>
                                <div class="char-counter">
                                    <span id="char-count">0</span>/500 caracteres
                                </div>
                            </div>
                            <!-- Botón de envío -->
                            <div class="form-actions">
                                <button type="submit" class="btn-continue" id="submit-btn">
                                    Enviar Mensaje
                                </button>
                            </div>
                        </div>
                        
                    </div>

                    
                </form>
            </div>

            <!-- Columna derecha con información -->
            <div class="info-column">
                <!-- Información adicional -->
                <div class="info-box">
                    <div class="info-content">
                        <i class="fas fa-info-circle"></i>
                        <div>
                            <h4>¿Qué puedes preguntarnos?</h4>
                            <ul>
                                <li><strong>Productos:</strong> Disponibilidad, talles, colores, materiales</li>
                                <li><strong>Pedidos:</strong> Estado de tu compra, cambios o devoluciones</li>
                                <li><strong>Envíos:</strong> Tiempos de entrega, costos, seguimiento</li>
                                <li><strong>General:</strong> Cualquier consulta sobre nuestros servicios</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Información de contacto -->
            <div class="contact-info">
                <h2 style="text-align: left; font-size: 1.5rem; margin: 0; color: var(--color-primary-dark);">Información</h2>
                
                <div class="contact-info__item">
                    <div class="contact-info__title">📍 Ubicación</div>
                    <div class="contact-info__content">
                        Alejandro Lerner 2244 <br>
                        Plottier - Neuquén, Argentina <br>
                        <a href="https://maps.google.com/maps?q=Alejandro+Lerner+2244,+Plottier,+Neuquén,+Argentina" target="_blank" rel="noopener">Ver en Google Maps</a>
                    </div>
                </div>

                <div class="contact-info__item">
                    <div class="contact-info__title">📞 Teléfono</div>
                    <div class="contact-info__content">
                        <a href="tel:+5429952110099">+54 9 299 521-0099</a><br>
                        <small>Disponible por WhatsApp</small>
                    </div>
                </div>

                <div class="contact-info__item">
                    <div class="contact-info__title">⏰ Horarios</div>
                    <div class="contact-info__content">
                        Lunes a Viernes: 10:00 - 18:00<br>
                        Sábado: 10:00 - 14:00<br>
                        <small>Cerrado los domingos</small>
                    </div>
                </div>

                <div class="contact-info__item">
                    <div class="contact-info__title">✉️ Email</div>
                    <div class="contact-info__content">
                        <a href="mailto:amarenastore2244@gmail.com">amarenastore2244@gmail.com</a>
                    </div>
                </div>
            </div>
            <!-- Fin columna derecha -->
        </div>
    </div>
</section>

<style>
/* Estilos similares al checkout usando variables correctas */
.contact-page .checkout-style {
    font-family: var(--font-primary);
}

.contact-page .form-container {
    background: var(--color-white);
    border-radius: 25px;
    padding: 2.5rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--color-pastel);
    animation: fadeInUp 0.6s ease;
}

.contact-page {
    padding: 80px 20px;
    min-height: 80vh;
    background: linear-gradient(135deg, var(--color-light) 0%, white 100%);
}

.contact-page__container {
    max-width: 1200px;
    margin: 0 auto;
}

.contact-page__container h1 {
    font-size: 3rem;
    color: var(--color-primary-dark);
    text-align: center;
    margin-bottom: 50px;
    font-weight: 800;
    animation: slideUp 0.6s ease-out;
}

.contact-page .stage-header {
    text-align: center;
    margin-bottom: 2rem;
    padding: 2rem 0;
    border-bottom: 2px solid var(--color-pastel);
}

.contact-page .stage-header h2 {
    font-size: 2.2rem;
    font-weight: 700;
    color: var(--color-primary-dark);
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.contact-page .stage-header p {
    font-size: 1.1rem;
    color: var(--color-text);
    margin: 0;
    opacity: 0.8;
}

.contact-page .form-grid {
    display: grid;
    gap: 2rem;
    margin-bottom: 2rem;
}

.contact-page .form-section {
    background: var(--color-white);
    border-radius: 25px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--color-pastel);
    animation: fadeInUp 0.6s ease;
}

.contact-page .form-section h3 {
    color: var(--color-primary-dark);
    font-size: 1.3rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--color-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-family: var(--font-primary);
    font-weight: 700;
}

.contact-page .form-group {
    margin-bottom: 1.5rem;
    position: relative;
}

.contact-page .form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.contact-page .form-group label {
    display: block;
    font-weight: 700;
    color: var(--color-primary-dark);
    margin-bottom: 0.5rem;
    font-family: var(--font-primary);
}

.contact-page .form-group input,
.contact-page .form-group select,
.contact-page .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--color-pastel);
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
    font-family: var(--font-primary);
    font-weight: 400;
    box-sizing: border-box;
}

.contact-page .form-group input:focus,
.contact-page .form-group select:focus,
.contact-page .form-group textarea:focus {
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(217, 106, 126, 0.25);
    outline: none;
}

.contact-page .form-group.valid input {
    border-color: #28a745;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 8 8'%3e%3cpath fill='%2328a745' d='m2.3 6.73.94-.94 1.06 1.06-2 2L.5 6.41l.94-.94z'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
}

.contact-page .form-group.invalid input {
    border-color: #dc3545;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath d='m5.8 4.3 2.4 2.4m0-2.4-2.4 2.4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px;
    padding-right: 40px;
}

.contact-page .error-message {
    color: #dc3545;
    font-size: 0.875rem;
    margin-top: 0.25rem;
    display: block;
    font-weight: 500;
    min-height: 1.2em;
}

.contact-page .phone-preview {
    background: var(--color-light);
    border: 2px solid var(--color-pastel);
    border-radius: 25px;
    padding: 12px 16px;
    margin-top: 0.5rem;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: var(--color-primary-dark);
}

.contact-page .char-counter {
    text-align: right;
    font-size: 0.875rem;
    color: var(--color-text);
    margin-top: 0.25rem;
}

.contact-page .char-counter.warning {
    color: #ffc107;
}

.contact-page .char-counter.danger {
    color: #dc3545;
}

.contact-page .info-box {
    background: var(--color-white);
    border-radius: 25px;
    padding: 2rem;
    margin: 2rem 0;
    border: 2px solid var(--color-pastel);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    max-width: 800px;
}

.contact-page .info-content {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.contact-page .info-content i {
    color: var(--color-primary);
    font-size: 1.5rem;
    margin-top: 0.25rem;
}

.contact-page .info-content h4 {
    color: var(--color-primary-dark);
    margin: 0 0 0.5rem 0;
    font-weight: 700;
}

.contact-page .info-content ul {
    margin: 0;
    padding-left: 1rem;
}

.contact-page .info-content li {
    margin-bottom: 0.25rem;
    color: var(--color-text);
}

.contact-page .form-actions {
    text-align: center;
    padding: 2rem 0;
}

.contact-page .btn-continue {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: var(--color-white);
    border: none;
    padding: 15px 40px;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 700;
    font-family: var(--font-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 15px rgba(217, 106, 126, 0.3);
}

.contact-page .btn-continue:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(217, 106, 126, 0.4);
}

.contact-page .btn-continue:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
}

.contact-page .alert {
    padding: 1rem 1.5rem;
    border-radius: var(--amarena-radius);
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.contact-page .alert-error {
    background-color: #f8d7da;
    border: 1px solid #f1aeb5;
    color: #721c24;
}

.contact-page .alert-success {
    background-color: #d1edff;
    border: 1px solid #9fcdff;
    color: #0c4128;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Layout para desktop - dos columnas con grid reorganizado */
.contact-desktop-layout {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 3rem;
    align-items: start;
    max-width: 1200px;
    margin: 0 auto;
}

.contact-desktop-layout .form-container {
    grid-column: 1;
}

.contact-desktop-layout .info-column {
    grid-column: 2;
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.contact-page .contact-info {
    background: var(--color-white);
    border-radius: 25px;
    padding: 2rem;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--color-pastel);
}

.contact-page .contact-info__item {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background: var(--color-light);
    border-radius: 15px;
    border-left: 3px solid var(--color-primary);
}

.contact-page .contact-info__item:last-child {
    margin-bottom: 0;
}

.contact-page .contact-info__title {
    font-weight: 700;
    color: var(--color-primary-dark);
    margin-bottom: 0.5rem;
    font-size: 1rem;
    font-family: var(--font-primary);
}

.contact-page .contact-info__content {
    color: var(--color-text);
    line-height: 1.4;
}

.contact-page .contact-info__content a {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 600;
}

.contact-page .contact-info__content a:hover {
    text-decoration: underline;
}

.contact-page .contact-info__content small {
    color: var(--color-text);
    opacity: 0.7;
}

/* Responsive */
@media (max-width: 1024px) {
    .contact-desktop-layout {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    
    .contact-desktop-layout .form-container {
        grid-column: 1;
    }
    
    .contact-desktop-layout .info-column {
        grid-column: 1;
        gap: 1.5rem;
    }
}

@media (max-width: 768px) {
    .contact-page .form-row {
        grid-template-columns: 1fr;
    }
    
    .contact-page .stage-header h2 {
        font-size: 1.8rem;
    }
    
    .contact-page .form-section {
        padding: 1.5rem;
    }
    
    .contact-desktop-layout {
        gap: 1.5rem;
    }
}
</style>



<script>
// Validaciones en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const submitBtn = document.getElementById('submit-btn');
    
    // Elementos del formulario
    const nombreInput = document.getElementById('nombre');
    const apellidoInput = document.getElementById('apellido');
    const emailInput = document.getElementById('email');
    const telefonoInput = document.getElementById('telefono');
    const codigoAreaSelect = document.getElementById('codigo_area');
    const comentariosTextarea = document.getElementById('comentarios');
    const charCount = document.getElementById('char-count');
    const charCounter = document.querySelector('.char-counter');
    const fullPhoneSpan = document.getElementById('full-phone');
    
    // Validación en tiempo real para nombre
    nombreInput.addEventListener('input', function() {
        validateNameField(this, 'nombre-error');
        updateSubmitButton();
    });
    
    // Validación en tiempo real para apellido
    apellidoInput.addEventListener('input', function() {
        validateNameField(this, 'apellido-error');
        updateSubmitButton();
    });
    
    // Validación en tiempo real para email
    emailInput.addEventListener('input', function() {
        validateEmail();
        updateSubmitButton();
    });
    
    // Validación en tiempo real para teléfono
    telefonoInput.addEventListener('input', function() {
        validatePhone();
        updatePhonePreview();
        updateSubmitButton();
    });
    
    // Actualizar vista previa del teléfono cuando cambia el código de área
    codigoAreaSelect.addEventListener('change', function() {
        updatePhonePreview();
    });
    
    // Contador de caracteres para comentarios
    comentariosTextarea.addEventListener('input', function() {
        const length = this.value.length;
        charCount.textContent = length;
        
        if (length > 500) {
            this.value = this.value.substring(0, 500);
            charCount.textContent = '500';
            charCounter.classList.add('danger');
        } else if (length > 400) {
            charCounter.classList.remove('danger');
            charCounter.classList.add('warning');
        } else {
            charCounter.classList.remove('danger', 'warning');
        }
    });
    
    // Funciones de validación
    function validateNameField(input, errorId) {
        const value = input.value.trim();
        const errorElement = document.getElementById(errorId);
        const fieldName = input.id === 'nombre' ? 'nombre' : 'apellido';
        
        if (value === '') {
            showError(input, errorElement, `El ${fieldName} es requerido`);
            return false;
        } else if (!/^[A-Za-zÀ-ÿ\u00f1\u00d1 ]+$/.test(value)) {
            showError(input, errorElement, `El ${fieldName} solo debe contener letras y espacios`);
            return false;
        } else if (value.length < 2) {
            showError(input, errorElement, `El ${fieldName} debe tener al menos 2 caracteres`);
            return false;
        } else {
            showSuccess(input, errorElement);
            return true;
        }
    }
    
    function validateEmail() {
        const value = emailInput.value.trim();
        const errorElement = document.getElementById('email-error');
        
        if (value === '') {
            showError(emailInput, errorElement, 'El email es requerido');
            return false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            showError(emailInput, errorElement, 'El formato del email no es válido');
            return false;
        } else {
            showSuccess(emailInput, errorElement);
            return true;
        }
    }
    
    function validatePhone() {
        let value = telefonoInput.value.replace(/\D/g, '');
        const errorElement = document.getElementById('telefono-error');
        
        // Formatear automáticamente (solo números)
        telefonoInput.value = value;
        
        if (value === '') {
            showError(telefonoInput, errorElement, 'El teléfono es requerido');
            return false;
        } else if (value.length < 8) {
            showError(telefonoInput, errorElement, 'El teléfono debe tener al menos 8 dígitos');
            return false;
        } else if (value.length > 15) {
            showError(telefonoInput, errorElement, 'El teléfono no puede tener más de 15 dígitos');
            return false;
        } else {
            showSuccess(telefonoInput, errorElement);
            return true;
        }
    }
    
    function showError(input, errorElement, message) {
        input.parentNode.classList.remove('valid');
        input.parentNode.classList.add('invalid');
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
    
    function showSuccess(input, errorElement) {
        input.parentNode.classList.remove('invalid');
        input.parentNode.classList.add('valid');
        errorElement.textContent = '';
        errorElement.style.display = 'none';
    }
    
    function updatePhonePreview() {
        const codigo = codigoAreaSelect.value;
        const numero = telefonoInput.value;
        fullPhoneSpan.textContent = `${codigo} ${numero}`;
    }
    
    function updateSubmitButton() {
        const isValid = validateForm(false);
        submitBtn.disabled = !isValid;
    }
    
    function validateForm(showErrors = true) {
        let isValid = true;
        
        if (showErrors) {
            isValid &= validateNameField(nombreInput, 'nombre-error');
            isValid &= validateNameField(apellidoInput, 'apellido-error');
            isValid &= validateEmail();
            isValid &= validatePhone();
        } else {
            // Validación silenciosa para habilitar/deshabilitar botón
            const nombre = nombreInput.value.trim();
            const apellido = apellidoInput.value.trim();
            const email = emailInput.value.trim();
            const telefono = telefonoInput.value.trim();
            
            isValid = nombre.length >= 2 && /^[A-Za-zÀ-ÿ\u00f1\u00d1 ]+$/.test(nombre) &&
                     apellido.length >= 2 && /^[A-Za-zÀ-ÿ\u00f1\u00d1 ]+$/.test(apellido) &&
                     /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) &&
                     telefono.length >= 8 && telefono.length <= 15;
        }
        
        return isValid;
    }
    
    // Manejar envío del formulario
    form.addEventListener('submit', function(e) {
        if (!validateForm(true)) {
            e.preventDefault();
            return;
        }
        
        // Mostrar loading
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        submitBtn.disabled = true;
    });
    
    // Inicialización
    updatePhonePreview();
    updateSubmitButton();
    
    // Auto-focus en el primer campo
    nombreInput.focus();
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
