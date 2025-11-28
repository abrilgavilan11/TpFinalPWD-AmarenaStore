<?php
use App\Utils\Auth;
use App\Utils\Session;

ob_start(); // Inicia el buffer de salida

$userName = Auth::getUserName() ?? 'Usuario';
$userEmail = Auth::getUserEmail() ?? '';
$user = $data['user'] ?? null;
?>
<h1>Mi Perfil</h1>
<p>Administra tu información personal y configuraciones.</p>

            <!-- Mostrar mensajes flash -->
            <?php if (Session::has('success')): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= Session::getFlash('success') ?>
                </div>
            <?php endif; ?>

            <?php if (Session::has('error')): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= Session::getFlash('error') ?>
                </div>
            <?php endif; ?>

            <div class="profile-sections">
                <!-- Información Personal -->
                <div class="profile-section">
                    <div class="section-header">
                        <h3><i class="fas fa-user"></i> Información Personal</h3>
                        <p>Actualiza tus datos personales</p>
                    </div>
                    
                    <form method="POST" action="<?= BASE_URL ?>/customer/profile/update" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i> Nombre completo *
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="<?= htmlspecialchars($user['usnombre'] ?? '') ?>" 
                                       required 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email *
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?= htmlspecialchars($user['usmail'] ?? '') ?>" 
                                       required 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i> Teléfono
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?= htmlspecialchars($user['ustelefono'] ?? '') ?>" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="address">
                                    <i class="fas fa-map-marker-alt"></i> Dirección
                                </label>
                                <textarea id="address" 
                                          name="address" 
                                          class="form-control" 
                                          rows="3"><?= htmlspecialchars($user['usdireccion'] ?? '') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Cambiar Contraseña -->
                <div class="profile-section">
                    <div class="section-header">
                        <h3><i class="fas fa-lock"></i> Cambiar Contraseña</h3>
                        <p>Actualiza tu contraseña para mayor seguridad</p>
                    </div>
                    
                    <form method="POST" action="<?= BASE_URL ?>/customer/profile/change-password" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="current_password">
                                    <i class="fas fa-key"></i> Contraseña actual *
                                </label>
                                <input type="password" 
                                       id="current_password" 
                                       name="current_password" 
                                       required 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">
                                    <i class="fas fa-lock"></i> Nueva contraseña *
                                </label>
                                <input type="password" 
                                       id="new_password" 
                                       name="new_password" 
                                       required 
                                       minlength="6"
                                       class="form-control">
                                <small class="form-help">Mínimo 6 caracteres</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">
                                    <i class="fas fa-lock"></i> Confirmar contraseña *
                                </label>
                                <input type="password" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required 
                                       minlength="6"
                                       class="form-control">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-shield-alt"></i> Cambiar Contraseña
                            </button>
                            
                            <button type="button" class="btn btn-outline" onclick="requestPasswordReset()">
                                <i class="fas fa-envelope"></i> Enviar enlace por email
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Información de la Cuenta -->
                <div class="profile-section">
                    <div class="section-header">
                        <h3><i class="fas fa-info-circle"></i> Información de la Cuenta</h3>
                        <p>Detalles de tu cuenta</p>
                    </div>
                    
                    <div class="account-info">
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-calendar"></i> Miembro desde
                            </div>
                            <div class="info-value">
                                <?= date('d/m/Y', strtotime($user['usfechaalta'] ?? 'now')) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-user-tag"></i> Tipo de cuenta
                            </div>
                            <div class="info-value">
                                <span class="account-type">Cliente</span>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">
                                <i class="fas fa-shield-check"></i> Estado de la cuenta
                            </div>
                            <div class="info-value">
                                <span class="account-status active">
                                    <i class="fas fa-check-circle"></i> Activa
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

<style>
/* Estilos específicos para el perfil */
.profile-sections {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.profile-section {
    background: var(--color-white);
    border: 2px solid var(--color-pastel);
    border-radius: 15px;
    overflow: hidden;
}

.section-header {
    background: linear-gradient(135deg, #f8f9fa, var(--color-pastel));
    padding: 1.5rem 2rem;
    border-bottom: 2px solid var(--color-pastel);
}

.section-header h3 {
    margin: 0 0 0.5rem 0;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-header p {
    margin: 0;
    color: #666;
}

.profile-form {
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    font-weight: 600;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--color-pastel);
    border-radius: 25px;
    font-family: var(--font-primary);
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(217, 106, 126, 0.25);
}

textarea.form-control {
    border-radius: 15px;
    resize: vertical;
    min-height: 80px;
}

.form-help {
    color: #666;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

/* Información de la cuenta */
.account-info {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px solid var(--color-pastel);
}

.info-label {
    font-weight: 600;
    color: var(--color-primary-dark);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.info-value {
    font-weight: 500;
    color: #666;
}

.account-type {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.875rem;
    font-weight: 600;
}

.account-status.active {
    color: #28a745;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

/* Alertas */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 10px;
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 500;
}

.alert-success {
    background: #d4edda;
    border: 2px solid #c3e6cb;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    border: 2px solid #f5c6cb;
    color: #721c24;
}

/* Botones */
.btn {
    padding: 12px 20px;
    border: 2px solid transparent;
    border-radius: 25px;
    font-weight: 600;
    font-family: var(--font-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    text-decoration: none;
    font-size: 1rem;
}

.btn-primary {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(217, 106, 126, 0.4);
}

.btn-outline {
    border-color: var(--color-primary);
    color: var(--color-primary);
    background: transparent;
}

.btn-outline:hover {
    background: var(--color-primary);
    color: white;
}

/* Responsive */
@media (max-width: 1024px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .profile-form {
        padding: 1.5rem;
    }
    
    .section-header {
        padding: 1rem 1.5rem;
    }
    
    .account-info {
        padding: 1.5rem;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<script>
function requestPasswordReset() {
    if (confirm('¿Enviar un enlace de recuperación de contraseña a tu email?')) {
        fetch('<?= BASE_URL ?>/customer/profile/request-password-reset', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ ' + data.message);
            } else {
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error de conexión');
        });
    }
}

// Validar que las contraseñas coincidan
document.addEventListener('DOMContentLoaded', function() {
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePasswords() {
        if (newPassword.value && confirmPassword.value) {
            if (newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    newPassword.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});
</script>

<?php
$content = ob_get_clean(); // Obtiene el contenido del buffer
require_once VIEWS_PATH . '/vistas/customer/customer_layout.php';
?>