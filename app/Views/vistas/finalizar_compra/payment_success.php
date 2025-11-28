<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<main class="payment-success-page">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow-lg border-success">
                    <div class="card-body text-center py-5">
                        <div style="font-size: 80px; color: #28a745; margin-bottom: 20px;">
                            ✓
                        </div>
                        <h2 class="card-title text-success mb-3">¡Pago Exitoso!</h2>
                        <p class="card-text text-muted mb-4">
                            Tu compra ha sido confirmada y procesada correctamente.
                        </p>
                        <p class="card-text text-muted mb-4">
                            Puedes ver el detalle de tu orden y descargar el comprobante en "Mis Órdenes".
                        </p>
                        
                        <div class="alert alert-info" role="alert">
                            <h5>¿Qué ocurre ahora?</h5>
                            <ul style="text-align: left; margin-bottom: 0;">
                                <li>Recibirás un email de confirmación en breve</li>
                                <li>Tu pedido será procesado en las próximas 24 horas</li>
                                <li>Te notificaremos el estado del envío</li>
                            </ul>
                        </div>

                        <a href="/mis-ordenes" class="btn btn-success btn-lg mt-4">
                            Ver Mis Órdenes
                        </a>
                        <a href="/catalog" class="btn btn-secondary btn-lg mt-4 ms-2">
                            Seguir Comprando
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
