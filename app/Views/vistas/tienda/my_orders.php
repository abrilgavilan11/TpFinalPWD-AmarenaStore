<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<main class="my-orders-page">
    <div class="container mt-5">
        <h1 class="mb-4">Mis Órdenes</h1>
        
        <?php if (!empty($data['orders'])): ?>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Número de Orden</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['orders'] as $order): ?>
                            <tr>
                                <td>
                                    <strong>#<?php echo str_pad($order['idcompra'], 6, '0', STR_PAD_LEFT); ?></strong>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['cofecha'])); ?></td>
                                <td>
                                    <!-- Mejorado badge de estado con colores -->
                                    <span class="badge <?php echo $this->getStatusBadgeClass($order['estado_actual'] ?? 'desconocido'); ?>">
                                        <?php echo htmlspecialchars($order['estado_actual'] ?? 'desconocido'); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/orden/<?php echo $order['idcompra']; ?>" class="btn btn-sm btn-primary">
                                        Ver Detalle
                                    </a>
                                    <!-- Botón para descargar comprobante PDF -->
                                    <a href="/pdf/descargar-comprobante/<?php echo $order['idcompra']; ?>" class="btn btn-sm btn-success">
                                        Descargar PDF
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center py-5">
                <h5>No tienes órdenes aún</h5>
                <p>¡Explora nuestro catálogo y realiza tu primera compra!</p>
                <a href="/catalog" class="btn btn-primary mt-3">Explorar Productos</a>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
