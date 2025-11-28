<?php
use App\Utils\Auth;
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<main class="order-detail-page">
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8">
                <h1>Orden #<?php echo str_pad($data['order']['idcompra'], 6, '0', STR_PAD_LEFT); ?></h1>
                
                <!-- Card mejorada con diseño rosado para información de la orden -->
                <div class="card mt-4 border-pink">
                    <div class="card-header bg-pink text-white">
                        <h5 class="mb-0">Detalles de la Orden</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($data['order']['usnombre']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($data['order']['usmail']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($data['order']['cofecha'])); ?></p>
                                <p><strong>ID Orden:</strong> #<?php echo str_pad($data['order']['idcompra'], 6, '0', STR_PAD_LEFT); ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card de productos con tabla mejorada -->
                <div class="card mt-4 border-pink">
                    <div class="card-header bg-pink text-white">
                        <h5 class="mb-0">Productos</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($data['items'])): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Cantidad</th>
                                            <th>Precio</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total = 0;
                                        foreach ($data['items'] as $item): 
                                            $subtotal = $item['cicantidad'] * $item['ciprecio'];
                                            $total += $subtotal;
                                        ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['pronombre']); ?></td>
                                                <td><?php echo htmlspecialchars($item['cicantidad']); ?></td>
                                                <td>$<?php echo number_format($item['ciprecio'], 2); ?></td>
                                                <td>$<?php echo number_format($subtotal, 2); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-active">
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th class="text-pink">$<?php echo number_format($total, 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Historial de estados con timeline mejorado -->
                <div class="card mt-4 border-pink">
                    <div class="card-header bg-pink text-white">
                        <h5 class="mb-0">Historial de Estados</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($data['statusHistory'])): ?>
                            <div class="timeline">
                                <?php foreach ($data['statusHistory'] as $status): ?>
                                    <div class="timeline-item mb-4">
                                        <div class="d-flex">
                                            <div class="timeline-marker bg-pink text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; flex-shrink: 0;">
                                                ✓
                                            </div>
                                            <div class="ms-3">
                                                <h6 class="mb-1 text-pink"><?php echo htmlspecialchars($status['cetdescripcion']); ?></h6>
                                                <p class="text-muted mb-1"><?php echo htmlspecialchars($status['cetdetalle']); ?></p>
                                                <small class="text-secondary">
                                                    <?php echo date('d/m/Y H:i', strtotime($status['cefechaini'])); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!Auth::isAdmin() && !empty($data['statusHistory']) && $data['statusHistory'][0]['idcompraestadotipo'] == 1): ?>
                    <div class="mt-4">
                        <button class="btn btn-danger" id="cancelOrderBtn" data-order-id="<?php echo $data['order']['idcompra']; ?>">
                            Cancelar Orden
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar con estado actual y botón de descargar PDF -->
            <div class="col-md-4">
                <div class="card border-pink sticky-top" style="top: 20px;">
                    <div class="card-header bg-pink text-white">
                        <h5 class="mb-0">Estado Actual</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php if (!empty($data['statusHistory'])): ?>
                            <h4 class="badge bg-pink" style="font-size: 18px;">
                                <?php echo htmlspecialchars($data['statusHistory'][0]['cetdescripcion']); ?>
                            </h4>
                            <p class="mt-3 text-muted">
                                <?php echo htmlspecialchars($data['statusHistory'][0]['cetdetalle']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <!-- Botón para descargar comprobante PDF -->
                        <a href="/pdf/descargar-comprobante/<?php echo $data['order']['idcompra']; ?>" class="btn btn-success w-100 mt-3">
                            📥 Descargar Comprobante
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

<style>
    .border-pink {
        border-color: #E91E63 !important;
        border-width: 2px !important;
    }

    .bg-pink {
        background-color: #E91E63 !important;
    }

    .text-pink {
        color: #E91E63 !important;
    }

    .timeline {
        position: relative;
        padding-left: 0;
    }

    .timeline-item {
        position: relative;
    }

    .timeline-marker {
        min-width: 40px;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .sticky-top {
            position: relative !important;
            top: 0 !important;
            margin-top: 20px;
        }
    }
</style>

<script>
document.getElementById('cancelOrderBtn')?.addEventListener('click', function() {
    if (!confirm('¿Estás seguro de que deseas cancelar esta orden?')) {
        return;
    }

    const orderId = this.getAttribute('data-order-id');
    
    fetch('/orden/cancelar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'order_id=' + orderId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al cancelar la orden');
    });
});
</script>
