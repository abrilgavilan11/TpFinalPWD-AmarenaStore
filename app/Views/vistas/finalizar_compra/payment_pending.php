<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<main class="payment-pending-page">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-lg border-warning">
                    <div class="card-body text-center py-5">
                        <div style="font-size: 80px; color: #ffc107; margin-bottom: 20px;">
                            ⏳
                        </div>
                        <h2 class="card-title text-warning mb-3">Pago Pendiente</h2>
                        <p class="card-text text-muted mb-4">
                            Tu pago está siendo procesado. Por favor espera a que se confirme.
                        </p>
                        
                        <div class="alert alert-info" role="alert">
                            <h5>Información importante</h5>
                            <ul style="text-align: left; margin-bottom: 0;">
                                <li>El procesamiento puede tomar entre 2 a 5 minutos</li>
                                <li>Recibirás un email cuando tu pago sea confirmado</li>
                                <li>No cierres esta ventana</li>
                            </ul>
                        </div>

                        <a href="/mis-ordenes" class="btn btn-warning btn-lg mt-4">
                            Ver Mis Órdenes
                        </a>
                        <a href="/carrito" class="btn btn-secondary btn-lg mt-4 ms-2">
                            Volver al Carrito
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
