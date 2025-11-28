<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<main class="payment-failure-page">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-lg border-danger">
                    <div class="card-body text-center py-5">
                        <div style="font-size: 80px; color: #dc3545; margin-bottom: 20px;">
                            ✕
                        </div>
                        <h2 class="card-title text-danger mb-3">Pago Rechazado</h2>
                        <p class="card-text text-muted mb-4">
                            Lamentablemente, tu pago no pudo ser procesado.
                        </p>
                        
                        <div class="alert alert-warning" role="alert">
                            <h5>Posibles razones:</h5>
                            <ul style="text-align: left; margin-bottom: 0;">
                                <li>Fondos insuficientes en tu cuenta</li>
                                <li>Datos de pago incorrectos</li>
                                <li>Tarjeta expirada o bloqueada</li>
                                <li>Límite de transacciones superado</li>
                            </ul>
                        </div>

                        <p class="card-text text-muted mb-4">
                            Tu carrito sigue disponible. Puedes intentar nuevamente o contactarnos para obtener ayuda.
                        </p>

                        <a href="/cart" class="btn btn-danger btn-lg mt-4">
                            Volver al Carrito
                        </a>
                        <a href="/contact" class="btn btn-secondary btn-lg mt-4 ms-2">
                            Contactar Soporte
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
