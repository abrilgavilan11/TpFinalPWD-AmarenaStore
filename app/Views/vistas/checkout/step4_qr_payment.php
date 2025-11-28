<?php
$title = $data['title'] ?? 'Pagar con QR - Amarena Store';
$qrData = $data['qrData'] ?? [];
$orderData = $data['orderData'] ?? [];
$customerData = $data['customerData'] ?? [];
$step = $data['step'] ?? 4;

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
                <div class="step completed">
                    <div class="step-number"><i class="fas fa-check"></i></div>
                    <div class="step-label">Resumen</div>
                </div>
                <div class="step-line completed"></div>
                <div class="step active">
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
                <h1>¡Casi listo! Escanea para pagar</h1>
                <p>Usa tu app de pagos favorita para completar la compra</p>
            </div>

            <div class="qr-payment-container">
                <div class="qr-payment-grid">
                    <!-- Código QR Principal -->
                    <div class="qr-section">
                        <div class="qr-card">
                            <div class="qr-header">
                                <h3>Escanea con tu celular</h3>
                                <div class="amount-display">
                                    <span class="amount">$<?= number_format($orderData['total'] ?? 0, 0, ',', '.') ?></span>
                                </div>
                            </div>
                            
                            <div class="qr-image-container">
                                <?php if(!empty($qrData['qr_path'])): ?>
                                    <?php if (strpos($qrData['qr_path'], 'data:image/svg+xml') === 0): ?>
                                        <!-- SVG en línea -->
                                        <img src="<?= htmlspecialchars($qrData['qr_path']) ?>" 
                                             alt="Código QR de pago" 
                                             class="qr-code"
                                             id="qr-code-image">
                                    <?php else: ?>
                                        <!-- Archivo SVG -->
                                        <img src="<?= BASE_URL ?><?= htmlspecialchars($qrData['qr_path']) ?>" 
                                             alt="Código QR de pago" 
                                             class="qr-code"
                                             id="qr-code-image">
                                    <?php endif; ?>
                                <?php elseif(!empty($qrData['qr_svg'])): ?>
                                    <!-- SVG directo -->
                                    <div class="qr-svg-container">
                                        <?= $qrData['qr_svg'] ?>
                                    </div>
                                <?php else: ?>
                                    <div class="qr-error">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <h3>Error al generar el código QR</h3>
                                        <p>No se pudo generar el código QR para el pago. Por favor, contacta al soporte.</p>
                                        <?php if (!empty($qrData)): ?>
                                            <small>Debug: <?= htmlspecialchars(json_encode(array_keys($qrData))) ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="qr-actions">
                                <button type="button" class="btn-download" onclick="downloadQR()">
                                    Descargar QR
                                </button>
                                
                                <button type="button" class="btn-share" onclick="shareQR()">
                                    Compartir
                                </button>
                            </div>
                        </div>

                        <!-- Apps compatibles -->
                        <div class="compatible-apps">
                            <h4>Apps compatibles:</h4>
                            <div class="apps-grid">
                                <div class="app-item">
                                    <span>Mercado Pago</span>
                                </div>
                                <div class="app-item">
                                    <span>Ualá</span>
                                </div>
                                <div class="app-item">
                                    <span>Modo</span>
                                </div>
                                <div class="app-item">
                                    <span>Banco</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Información del pedido -->
                    <div class="order-info-section">
                        <div class="order-summary-card">
                            <div class="order-header">
                                <h3>Resumen del Pedido</h3>
                                <span class="order-number">Pedido #<?= $orderData['order_id'] ?? 'PENDING' ?></span>
                            </div>
                            
                            <div class="order-details">
                                <div class="customer-info">
                                    <h4>Cliente</h4>
                                    <p><strong><?= htmlspecialchars($customerData['full_name'] ?? '') ?></strong></p>
                                    <p><?= htmlspecialchars($customerData['email'] ?? '') ?></p>
                                    <p><?= htmlspecialchars($customerData['phone'] ?? '') ?></p>
                                </div>
                                
                                <div class="delivery-info">
                                    <h4>Entrega</h4>
                                    <p><?= htmlspecialchars($customerData['address'] ?? '') ?></p>
                                    <p><?= htmlspecialchars($customerData['city'] ?? '') ?>, <?= htmlspecialchars($customerData['province'] ?? '') ?></p>
                                </div>
                                
                                <div class="payment-details">
                                    <h4>Pago</h4>
                                    <div class="payment-row">
                                        <span>Subtotal:</span>
                                        <span>$<?= number_format($orderData['subtotal'] ?? 0, 0, ',', '.') ?></span>
                                    </div>
                                    <div class="payment-row">
                                        <span>Envío:</span>
                                        <span>
                                            <?php if(($orderData['shipping'] ?? 0) == 0): ?>
                                                <span class="free">GRATIS</span>
                                            <?php else: ?>
                                                $<?= number_format($orderData['shipping'], 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <div class="payment-row total">
                                        <span><strong>Total:</strong></span>
                                        <span><strong>$<?= number_format($orderData['total'] ?? 0, 0, ',', '.') ?></strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Instrucciones de pago -->
                        <div class="instructions-card">
                            <h3>¿Cómo pagar?</h3>
                            <ol class="payment-steps">
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Abre tu app de pagos en el celular</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Escanea el código QR de la izquierda</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>Confirma el pago en tu app</span>
                                </li>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <span>¡Listo! Te notificaremos cuando esté confirmado</span>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>

                <!-- Acciones adicionales -->
                <div class="additional-actions">
                    <button type="button" class="btn-secondary" onclick="goBackToSummary()">
                        Volver al Resumen
                    </button>
                    
                    <button type="button" class="btn-help" onclick="showHelp()">
                        ¿Necesitas ayuda?
                    </button>
                    
                    <button type="button" class="btn-refresh" onclick="refreshStatus()">
                        Verificar Estado
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de ayuda -->
<div class="modal" id="help-modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-question-circle"></i> ¿Problemas para pagar?</h3>
            <button class="modal-close" onclick="closeModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="help-section">
                <h4>Problemas frecuentes:</h4>
                <ul>
                    <li><strong>No puedo escanear el QR:</strong> Asegúrate de tener buena iluminación y que la cámara esté enfocada</li>
                    <li><strong>Mi app no lo reconoce:</strong> Intenta con otra app de pagos compatible</li>
                    <li><strong>El código expiró:</strong> Haz clic en "Verificar Estado" para generar uno nuevo</li>
                    <li><strong>El pago no se refleja:</strong> Los pagos pueden demorar hasta 5 minutos en confirmarse</li>
                </ul>
            </div>
            <div class="help-contact">
                <p><strong>¿Sigues teniendo problemas?</strong></p>
                <p>Contáctanos al WhatsApp: <a href="https://wa.me/5492995123456" target="_blank">+54 299 512-3456</a></p>
            </div>
        </div>
    </div>
</div>

<script>
const baseUrl = '<?= BASE_URL ?>';
const orderId = '<?= $orderData['order_id'] ?? '' ?>';
const paymentToken = '<?= $qrData['payment_token'] ?? '' ?>';
let statusInterval;
let timeLeft = 900; // 15 minutos en segundos

// Debug inicial
console.log('Variables iniciales:', {
    baseUrl: baseUrl,
    orderId: orderId,
    paymentToken: paymentToken,
    orderData: <?= json_encode($orderData ?? []) ?>,
    qrData: <?= json_encode($qrData ?? []) ?>
});

// Verificar BASE_URL y contexto
console.log('=== DEBUG DE CONFIGURACIÓN ===');
console.log('BASE_URL desde PHP:', '<?= BASE_URL ?>');
console.log('window.location.href:', window.location.href);
console.log('window.location.origin:', window.location.origin);
console.log('baseUrl variable:', baseUrl);

// Para servidor integrado, usar directamente el origin
let correctedBaseUrl = baseUrl;
if (window.location.port === '8000') {
    // Servidor integrado sirve directamente desde public
    correctedBaseUrl = window.location.origin;
    console.log('🔧 baseUrl corregido para servidor integrado:', correctedBaseUrl);
}

// Variable global corregida
const CORRECTED_BASE_URL = correctedBaseUrl;

console.log('URL completa para verify-payment:', `${correctedBaseUrl}/checkout/verify-payment`);
console.log('URL completa para test/verify:', `${correctedBaseUrl}/test/verify`);

// Test de conectividad muy simple
async function testConnection() {
    console.log('=== INICIANDO TEST DE CONECTIVIDAD ===');
    
    // Test 1: Página actual
    try {
        console.log('Test 1: Verificando acceso a página actual...');
        const currentResponse = await fetch(window.location.href);
        console.log('✅ Página actual accesible:', currentResponse.status);
    } catch (error) {
        console.error('❌ Error accediendo página actual:', error);
    }
    
    // Test 2: Root del sitio
    try {
        console.log('Test 2: Verificando acceso al root...');
        const rootResponse = await fetch(window.location.origin);
        console.log('✅ Root accesible:', rootResponse.status);
    } catch (error) {
        console.error('❌ Error accediendo root:', error);
    }
    
    // Test 3: Test endpoint
    try {
        console.log('Test 3: Verificando endpoint de test...');
        const testUrl = `${baseUrl}/test/verify`;
        console.log('Intentando:', testUrl);
        const response = await fetch(testUrl);
        const result = await response.json();
        console.log('✅ Test de conectividad exitoso:', result);
        return true;
    } catch (error) {
        console.error('❌ Test de conectividad falló:', error);
        
        // Test 4: Endpoint directo (sin routing)
        try {
            console.log('Test 4: Probando endpoint directo...');
            const directUrl = window.location.origin + '/test_verify_direct.php';
            console.log('Intentando URL directa:', directUrl);
            const directResponse = await fetch(directUrl);
            const directResult = await directResponse.json();
            console.log('✅ Endpoint directo funcionó:', directResult);
            
            // Test 4.5: Test POST al endpoint directo
            console.log('Test 4.5: Probando POST al endpoint directo...');
            const postResponse = await fetch(directUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({order_number: orderId, test: true})
            });
            const postResult = await postResponse.json();
            console.log('✅ POST directo funcionó:', postResult);
            
            // Test 5: Debug URL
            console.log('Test 5: Probando debug URL...');
            const debugUrl = window.location.origin + '/debug_url.php';
            console.log('Intentando debug URL:', debugUrl);
            window.open(debugUrl, '_blank');
            
            return true;
        } catch (directError) {
            console.error('❌ Endpoint directo también falló:', directError);
            
            // Test 6: URL con amarena en el path
            try {
                console.log('Test 6: Probando con /amarena/ en el path...');
                const amarenaUrl = window.location.origin + '/amarena/test/verify';
                console.log('Intentando URL con amarena:', amarenaUrl);
                const amarenaResponse = await fetch(amarenaUrl);
                const amarenaResult = await amarenaResponse.json();
                console.log('✅ URL con amarena funcionó:', amarenaResult);
                return true;
            } catch (amarenaError) {
                console.error('❌ Todas las URLs fallaron:', amarenaError);
                return false;
            }
        }
    }
}

function goBackToSummary() {
    window.location.href = `${baseUrl}/checkout/summary`;
}

function downloadQR() {
    const qrImage = document.getElementById('qr-code-image');
    if (qrImage) {
        const link = document.createElement('a');
        link.download = `qr-pago-amarena-${orderId}.png`;
        link.href = qrImage.src;
        link.click();
    }
}

function shareQR() {
    if (navigator.share) {
        navigator.share({
            title: 'Código QR - Amarena Store',
            text: `Pagar pedido #${orderId} - $${formatNumber(<?= $orderData['total'] ?? 0 ?>)}`,
            url: window.location.href
        });
    } else {
        // Fallback: copiar enlace
        navigator.clipboard.writeText(window.location.href);
        showNotification('Enlace copiado al portapapeles');
    }
}

async function refreshStatus() {
    const refreshBtn = document.querySelector('.btn-refresh');
    if (!refreshBtn) {
        console.error('Botón refresh no encontrado');
        return;
    }
    
    const originalText = refreshBtn.innerHTML;
    
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
    refreshBtn.disabled = true;
    
    try {
        const requestData = {
            order_number: orderId
        };
        
        // Solo incluir token si existe
        if (paymentToken) {
            requestData.payment_token = paymentToken;
        }
        
        // Corregir baseUrl si estamos en servidor integrado
        let finalBaseUrl = baseUrl;
        if (window.location.port === '8000') {
            finalBaseUrl = window.location.origin;
            console.log('🔧 BaseUrl corregido para servidor integrado:', finalBaseUrl);
        }
        
        // Primero probar conectividad básica
        console.log('Probando URL corregida:', `${finalBaseUrl}/checkout/verify-payment`);
        
        // Test directo sin async/await primero
        fetch(`${finalBaseUrl}/checkout/verify-payment`, {
            method: 'GET'
        }).then(response => {
            console.log('Test GET simple:', response.status, response.statusText);
        }).catch(error => {
            console.error('Test GET falló:', error);
        });
        
        const canConnect = await testConnection();
        if (!canConnect) {
            throw new Error('No se puede conectar con el servidor');
        }
        
        console.log('Enviando verificación:', requestData);
        
        const response = await fetch(`${finalBaseUrl}/checkout/verify-payment`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });
        
        console.log('Respuesta del servidor:', response.status, response.statusText);
        console.log('URL completa:', `${finalBaseUrl}/checkout/verify-payment`);
        
        if (!response.ok) {
            // Intentar leer el texto de error del servidor
            let errorMessage = `HTTP ${response.status}: ${response.statusText}`;
            try {
                const errorText = await response.text();
                console.log('Error del servidor:', errorText);
                if (errorText) {
                    errorMessage += ' - ' + errorText;
                }
            } catch (e) {
                console.log('No se pudo leer el error del servidor');
            }
            throw new Error(errorMessage);
        }
        
        const result = await response.json();
        console.log('Resultado de verificación:', result);
        
        if (result.success && result.payment_status === 'completed') {
            showPaymentSuccess();
        } else if (result.expired) {
            showPaymentExpired();
        } else {
            showNotification(result.message || 'El pago aún no se ha procesado');
        }
        
    } catch (error) {
        console.error('Error completo:', error);
        showNotification('Error al verificar el estado del pago: ' + error.message);
    } finally {
        refreshBtn.innerHTML = originalText;
        refreshBtn.disabled = false;
    }
}

function showPaymentSuccess() {
    clearInterval(statusInterval);
    
    const statusCard = document.getElementById('payment-status');
    statusCard.innerHTML = `
        <div class="status-content success">
            <i class="fas fa-check-circle status-icon"></i>
            <h3>¡Pago confirmado!</h3>
            <p>Tu compra ha sido procesada exitosamente</p>
            <button class="btn-continue" onclick="goToOrderConfirmation()">
                <i class="fas fa-arrow-right"></i>
                Ver detalles del pedido
            </button>
        </div>
    `;
}

function showPaymentExpired() {
    clearInterval(statusInterval);
    
    const statusCard = document.getElementById('payment-status');
    statusCard.innerHTML = `
        <div class="status-content expired">
            <i class="fas fa-times-circle status-icon"></i>
            <h3>Código QR expirado</h3>
            <p>El tiempo límite ha sido alcanzado</p>
            <button class="btn-retry" onclick="generateNewQR()">
                <i class="fas fa-refresh"></i>
                Generar nuevo código
            </button>
        </div>
    `;
}

function goToOrderConfirmation() {
    window.location.href = `${baseUrl}/orders/confirmation/${orderId}`;
}

function showHelp() {
    document.getElementById('help-modal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('help-modal').style.display = 'none';
}

function showNotification(message) {
    // Crear notificación temporal
    const notification = document.createElement('div');
    notification.className = 'notification';
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function formatNumber(number) {
    return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

function startTimer() {
    // Verificar si existe elemento timer en el DOM
    const timerElement = document.getElementById('timer');
    
    statusInterval = setInterval(() => {
        timeLeft--;
        
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        
        // Solo actualizar timer si el elemento existe
        if (timerElement) {
            timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
        
        if (timeLeft <= 0) {
            showPaymentExpired();
        } else if (timeLeft % 30 === 0) {
            // Verificar estado cada 30 segundos
            refreshStatus();
        }
    }, 1000);
}

// Inicializar la página
document.addEventListener('DOMContentLoaded', function() {
    // Validar que tenemos los datos necesarios
    if (!orderId) {
        console.error('Order ID no definido');
        showNotification('Error: ID de orden no encontrado');
        return;
    }
    
    console.log('Inicializando página con:', { orderId, paymentToken, baseUrl });
    
    startTimer();
    
    // Animación de entrada
    const sections = document.querySelectorAll('.qr-payment-grid > div');
    sections.forEach((section, index) => {
        section.style.animationDelay = `${index * 0.2}s`;
        section.classList.add('slide-in');
    });
    
    // Auto-verificar cada 2 minutos
    setInterval(refreshStatus, 120000);
});

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>