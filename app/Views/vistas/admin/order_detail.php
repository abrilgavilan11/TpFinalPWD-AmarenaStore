<?php
// Incluimos los layouts principales para mantener la consistencia del sitio.
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';

// Extraer las variables del array $data
$order = $data['order'] ?? null;
$items = $data['items'] ?? [];
$statusHistory = $data['statusHistory'] ?? [];
$currentStatus = $data['currentStatus'] ?? null;
$allStatusTypes = $data['allStatusTypes'] ?? [];
?>

<main class="admin-page">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Menú Admin</h2>
            <nav>
                <a href="<?= BASE_URL ?>/management">Dashboard</a>
                <a href="<?= BASE_URL ?>/management/products">Gestionar Productos</a>
                <a href="<?= BASE_URL ?>/management/categories">Gestionar Categorías</a>
                <a href="<?= BASE_URL ?>/management/orders" class="active">Gestionar Órdenes</a>
            </nav>
        </aside>
        
        <section class="admin-content">
            <div class="order-header">
                <h1><i class="fas fa-receipt"></i> Orden #<?php echo $order ? htmlspecialchars($order['idcompra']) : 'N/A'; ?></h1>
                <a href="<?= BASE_URL ?>/management/orders" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Órdenes
                </a>
            </div>

            <div class="order-content">
                <div class="order-main">
            
            <div class="order-card">
                <div class="order-card-header">
                    <h3><i class="fas fa-user"></i> Detalles del Cliente</h3>
                </div>
                <div class="order-card-body">
                    <?php if ($order): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?php echo htmlspecialchars($order['usnombre'] ?? 'N/A'); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['usmail'] ?? 'N/A'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de Orden:</strong> <?php echo $order['cofecha'] ? date('d/m/Y H:i', strtotime($order['cofecha'])) : 'N/A'; ?></p>
                            <p><strong>ID Orden:</strong> <?php echo htmlspecialchars($order['idcompra'] ?? 'N/A'); ?></p>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-warning">
                        No se pudieron cargar los datos de la orden.
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="order-card">
                <div class="order-card-header">
                    <h3><i class="fas fa-shopping-bag"></i> Productos</h3>
                </div>
                <div class="order-card-body">
                    <?php if (!empty($items)): ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Precio Unitario</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    foreach ($items as $item): 
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
                                    <tr class="table-active fw-bold">
                                        <th colspan="3" class="text-end">TOTAL:</th>
                                        <th>$<?php echo number_format($total, 2); ?></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="order-card">
                <div class="order-card-header">
                    <h3><i class="fas fa-history"></i> Historial Completo de Estados</h3>
                </div>
                <div class="order-card-body">
                    <?php if (!empty($statusHistory)): ?>
                        <div class="timeline">
                            <?php foreach ($statusHistory as $status): ?>
                                <div class="timeline-item mb-4 pb-4" style="border-bottom: 1px solid #ddd;">
                                    <div class="d-flex">
                                        <div class="timeline-marker bg-success text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 45px; height: 45px; font-size: 20px;">
                                            ✓
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-1 text-uppercase fw-bold">
                                                <?php echo htmlspecialchars($status['cetdescripcion'] ?? ''); ?>
                                            </h6>
                                            <p class="text-muted mb-2">
                                                <?php echo htmlspecialchars($status['cetdetalle'] ?? ''); ?>
                                            </p>
                                            <small class="text-secondary">
                                                Desde: <?php
                                                    $fechaini = $status['cefechaini'] ?? null;
                                                    echo $fechaini ? date('d/m/Y H:i', strtotime($fechaini)) : '-';
                                                ?>
                                                <?php if (!empty($status['cefechafin'])): ?>
                                                    - Hasta: <?php echo date('d/m/Y H:i', strtotime($status['cefechafin'])); ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
                </div>
                </div>

                <div class="order-sidebar">
                    <div class="order-card sticky">
                        <div class="order-card-header">
                            <h3><i class="fas fa-edit"></i> Cambiar Estado</h3>
                        </div>
                        <div class="order-card-body">
                    <div class="mb-3">
                        <p class="mb-2"><strong>Estado Actual:</strong></p>
                        <h5 class="badge bg-primary w-100 py-2" style="font-size: 16px;">
                            <?php 
                            if ($currentStatus) {
                                echo htmlspecialchars($currentStatus['cetdescripcion']);
                            } elseif ($order) {
                                echo htmlspecialchars($order['estado_actual'] ?? 'Desconocido');
                            } else {
                                echo 'Desconocido';
                            }
                            ?>
                        </h5>
                    </div>

                    <?php if (!empty($allStatusTypes) && $order): ?>
                        <div class="form-group">
                            <label><strong>Nuevo Estado:</strong></label>
                            <select class="form-control" id="newStatusSelect">
                                <option value="">-- Selecciona un nuevo estado --</option>
                                <?php foreach ($allStatusTypes as $statusType): ?>
                                    <?php if ($statusType['idcompraestadotipo'] != ($currentStatus['idcompraestadotipo'] ?? 0)): ?>
                                        <option value="<?php echo $statusType['idcompraestadotipo']; ?>">
                                            <?php echo htmlspecialchars($statusType['cetdescripcion']); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button class="btn btn-primary btn-full" id="changeStatusBtn" data-order-id="<?php echo $order['idcompra']; ?>">
                            <i class="fas fa-sync"></i> Cambiar Estado
                        </button>
                        
                        <button class="btn btn-danger btn-full mt-2" onclick="deleteOrder(<?php echo $order['idcompra']; ?>)">
                            <i class="fas fa-trash"></i> Eliminar Orden
                        </button>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <small>No hay transiciones válidas para este estado.</small>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<style>
/* Estilos para la página de detalles de orden */
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--color-pastel);
}

.order-header h1 {
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-content {
    display: grid;
    grid-template-columns: 1fr 350px;
    gap: 2rem;
    align-items: start;
}

.order-card {
    background: var(--color-white);
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    border: 2px solid var(--color-pastel);
    margin-bottom: 2rem;
    overflow: hidden;
}

.order-card-header {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
    padding: 1.5rem 2rem;
    border-bottom: none;
}

.order-card-header h3 {
    margin: 0;
    font-family: var(--font-primary);
    font-weight: 600;
    font-size: 1.2rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-card-body {
    padding: 2rem;
}

.order-sidebar .order-card.sticky {
    position: sticky;
    top: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--color-pastel);
    border-radius: 25px;
    font-size: 1rem;
    font-family: var(--font-primary);
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(217, 106, 126, 0.25);
}

.btn-full {
    width: 100%;
    padding: 12px 20px;
    border: none;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 600;
    font-family: var(--font-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(217, 106, 126, 0.4);
}

.btn-danger {
    background: linear-gradient(135deg, #dc3545, #c82333);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #6c757d, #5a6268);
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-secondary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    color: white;
    text-decoration: none;
}

/* Responsive */
@media (max-width: 1024px) {
    .order-content {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .order-sidebar .order-card.sticky {
        position: static;
    }
}

@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-card-body {
        padding: 1.5rem;
    }
}

/* Tabla responsive */
.table-responsive {
    overflow-x: auto;
    border-radius: 10px;
}

.table {
    margin: 0;
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.table th {
    background: var(--color-light);
    font-weight: 600;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table tfoot th {
    background: var(--color-primary);
    color: white;
    font-size: 1.1rem;
}

/* Timeline styles */
.timeline-item {
    position: relative;
    padding-left: 0;
}

.timeline-marker {
    background: var(--color-primary) !important;
    color: white;
    font-weight: bold;
}

.badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    font-weight: 600;
    text-align: center;
}

.bg-primary {
    background: var(--color-primary) !important;
    color: white;
}

/* Alert styles */
.alert {
    padding: 1rem 1.5rem;
    border-radius: 10px;
    margin-bottom: 1rem;
    border: 1px solid transparent;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
    color: #856404;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
    .timeline {
        position: relative;
        padding-left: 0;
    }

    .timeline-marker {
        min-width: 45px;
        flex-shrink: 0;
    }

    .sticky-top {
        z-index: 100;
    }
</style>

<script>
// Cambiar estado de orden
document.getElementById('changeStatusBtn')?.addEventListener('click', function() {
    const orderId = this.getAttribute('data-order-id');
    const newStatusId = document.getElementById('newStatusSelect').value;

    if (!newStatusId) {
        alert('Por favor selecciona un nuevo estado');
        return;
    }

    if (confirm('¿Estás seguro de que quieres cambiar el estado de esta orden?')) {
        // Mostrar loading
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
        this.disabled = true;

        fetch('<?= BASE_URL ?>/management/orders/update-status/' + orderId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'status=' + newStatusId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Estado actualizado correctamente: ' + data.message);
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
                // Restaurar botón
                this.innerHTML = '<i class="fas fa-sync"></i> Cambiar Estado';
                this.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error de conexión al cambiar el estado');
            // Restaurar botón
            this.innerHTML = '<i class="fas fa-sync"></i> Cambiar Estado';
            this.disabled = false;
        });
    }
});

// Eliminar orden
function deleteOrder(orderId) {
    if (confirm('⚠️ ¿Estás SEGURO de que quieres eliminar esta orden?\n\nEsta acción NO se puede deshacer.')) {
        if (confirm('🔥 ÚLTIMA CONFIRMACIÓN: ¿Eliminar la orden #' + orderId + '?')) {
            fetch('<?= BASE_URL ?>/management/orders/delete/' + orderId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✓ Orden eliminada correctamente');
                    window.location.href = '<?= BASE_URL ?>/management/orders';
                } else {
                    alert('❌ Error al eliminar: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('❌ Error de conexión al eliminar la orden');
            });
        }
    }
}
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
