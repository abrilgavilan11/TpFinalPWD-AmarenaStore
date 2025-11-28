<?php
use App\Utils\Auth;
use App\Utils\Session;

ob_start(); // Inicia el buffer de salida

$userName = Auth::getUserName() ?? 'Usuario';
$userEmail = Auth::getUserEmail() ?? '';
$order = $data['order'] ?? null;
$items = $data['items'] ?? [];
$statusHistory = $data['statusHistory'] ?? [];

if (!$order) {
    echo '<div class="alert alert-danger">Orden no encontrada.</div>';
    $content = ob_get_clean();
    require_once VIEWS_PATH . '/vistas/customer/customer_layout.php';
    return;
}

// Calcular total de la orden
$total = 0;
foreach ($items as $item) {
    $total += $item['cicantidad'] * $item['ciprecio'];
}
?>
            <div class="customer-page-header">
                <div class="customer-page-title">
                    <div class="icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <h1>Detalles de Orden #<?= $order['idcompra'] ?></h1>
                        <p>Información completa de tu pedido</p>
                    </div>
                </div>
                <div class="order-status-badge">
                    <?php $estado = $order['estado_actual'] ?? 'iniciada'; ?>
                    <span class="status-badge status-<?= strtolower($estado) ?>">
                        <?= ucfirst($estado) ?>
                    </span>
                </div>
            </div>

            <!-- Información General de la Orden -->
            <div class="order-detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-info-circle"></i> Información General</h3>
                </div>
                <div class="order-info-grid">
                    <div class="info-card">
                        <div class="info-label">
                            <i class="fas fa-hashtag"></i>
                            Número de Orden
                        </div>
                        <div class="info-value">#<?= $order['idcompra'] ?></div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-label">
                            <i class="fas fa-calendar"></i>
                            Fecha de Pedido
                        </div>
                        <div class="info-value">
                            <?= date('d/m/Y H:i', strtotime($order['cofecha'])) ?>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-label">
                            <i class="fas fa-dollar-sign"></i>
                            Total
                        </div>
                        <div class="info-value total-amount">
                            $<?= number_format($total, 2) ?>
                        </div>
                    </div>
                    
                    <div class="info-card">
                        <div class="info-label">
                            <i class="fas fa-truck"></i>
                            Estado
                        </div>
                        <div class="info-value">
                            <?php $estadoInfo = $order['estado_actual'] ?? 'iniciada'; ?>
                            <span class="status-badge status-<?= strtolower($estadoInfo) ?>">
                                <?= ucfirst($estadoInfo) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Productos de la Orden -->
            <div class="order-detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-box"></i> Productos (<?= count($items) ?>)</h3>
                </div>
                <div class="order-items-detail">
                    <?php foreach ($items as $item): ?>
                        <div class="order-item-card">
                            <div class="item-image">
                                <?php if (!empty($item['proimagen'])): ?>
                                    <?php
                                    // Determinar la ruta correcta de la imagen
                                    if (strpos($item['proimagen'], 'product_') === 0) {
                                        $imagePath = BASE_URL . '/uploads/products/' . htmlspecialchars($item['proimagen']);
                                    } else {
                                        $imagePath = BASE_URL . '/img/ropa/' . htmlspecialchars($item['proimagen']);
                                    }
                                    ?>
                                    <img src="<?= $imagePath ?>" 
                                         alt="<?= htmlspecialchars($item['pronombre']) ?>">
                                <?php else: ?>
                                    <div class="no-image">
                                        <i class="fas fa-image"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-details">
                                <h4><?= htmlspecialchars($item['pronombre']) ?></h4>
                                <div class="item-meta">
                                    <div class="price-info">
                                        <span class="unit-price">Precio unitario: $<?= number_format($item['ciprecio'], 2) ?></span>
                                        <span class="quantity">Cantidad: <?= $item['cicantidad'] ?></span>
                                    </div>
                                    <div class="subtotal">
                                        Subtotal: $<?= number_format($item['cicantidad'] * $item['ciprecio'], 2) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Historial de Estados -->
            <?php if (!empty($statusHistory)): ?>
            <div class="order-detail-section">
                <div class="section-header">
                    <h3><i class="fas fa-history"></i> Historial de Estados</h3>
                </div>
                <div class="status-timeline">
                    <?php foreach ($statusHistory as $index => $status): ?>
                        <?php 
                        $statusEstado = $status['estado'] ?? 'desconocido';
                        $statusFecha = $status['fecha'] ?? null;
                        $statusComentario = $status['comentario'] ?? '';
                        ?>
                        <div class="timeline-item <?= $index === 0 ? 'current' : '' ?>">
                            <div class="timeline-marker">
                                <i class="fas fa-<?= getStatusIcon($statusEstado) ?>"></i>
                            </div>
                            <div class="timeline-content">
                                <h5><?= ucfirst($statusEstado) ?></h5>
                                <p class="timeline-date">
                                    <?= $statusFecha ? date('d/m/Y H:i', strtotime($statusFecha)) : 'Fecha no disponible' ?>
                                </p>
                                <?php if (!empty($statusComentario)): ?>
                                    <p class="timeline-comment"><?= htmlspecialchars($statusComentario) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Acciones de la Orden -->
            <div class="order-actions">
                <a href="<?= BASE_URL ?>/customer/orders" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Volver a Mis Órdenes
                </a>
                
                <?php 
                $estadoActual = $order['estado_actual'] ?? '';
                if (in_array($estadoActual, ['iniciada'])): 
                ?>
                    <button class="btn btn-danger" onclick="cancelOrder(<?= $order['idcompra'] ?>)">
                        <i class="fas fa-times"></i>
                        Cancelar Orden
                    </button>
                <?php endif; ?>
                
                <a href="<?= BASE_URL ?>/pdf/descargar-comprobante/<?= $order['idcompra'] ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download"></i>
                    Descargar Comprobante
                </a>
            </div>

<style>
/* Estilos específicos para order detail */
.customer-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid var(--customer-border);
}

.customer-page-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.customer-page-title .icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, var(--customer-primary), var(--customer-secondary));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--customer-white);
    font-size: 1.5rem;
    box-shadow: var(--customer-shadow);
}

.customer-page-title h1 {
    margin: 0;
    color: var(--customer-primary-dark);
    font-family: var(--font-primary);
    font-weight: 700;
}

.customer-page-title p {
    margin: 0.25rem 0 0 0;
    color: var(--customer-text-muted);
}

.order-status-badge {
    text-align: right;
}

.order-detail-section {
    background: var(--customer-white);
    border: 2px solid var(--customer-border);
    border-radius: 15px;
    margin-bottom: 2rem;
    overflow: hidden;
}

.section-header {
    background: linear-gradient(135deg, #f8f9fa, var(--customer-accent));
    padding: 1.5rem 2rem;
    border-bottom: 2px solid var(--customer-border);
}

.section-header h3 {
    margin: 0;
    color: var(--customer-primary-dark);
    font-family: var(--font-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    padding: 2rem;
}

.info-card {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1.5rem;
    text-align: center;
    border: 1px solid var(--customer-border);
}

.info-label {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    color: var(--customer-text-muted);
    font-size: 0.9rem;
    margin-bottom: 0.75rem;
}

.info-value {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--customer-primary-dark);
    font-family: var(--font-primary);
}

.total-amount {
    font-size: 1.5rem;
    color: var(--customer-primary);
}

.order-items-detail {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.order-item-card {
    display: flex;
    gap: 1.5rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid var(--customer-border);
}

.item-image {
    width: 100px;
    height: 100px;
    border-radius: 10px;
    overflow: hidden;
    background: var(--customer-border);
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 2rem;
}

.item-details {
    flex: 1;
}

.item-details h4 {
    margin: 0 0 1rem 0;
    color: var(--customer-primary-dark);
    font-weight: 600;
}

.item-meta {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}

.price-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.unit-price, .quantity {
    color: var(--customer-text-muted);
    font-size: 0.9rem;
}

.subtotal {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--customer-primary);
}

.status-timeline {
    padding: 2rem;
}

.timeline-item {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: var(--customer-border);
}

.timeline-marker {
    width: 40px;
    height: 40px;
    background: var(--customer-border);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    position: relative;
    z-index: 1;
}

.timeline-item.current .timeline-marker {
    background: var(--customer-primary);
    color: white;
}

.timeline-content h5 {
    margin: 0 0 0.5rem 0;
    color: var(--customer-primary-dark);
    font-weight: 600;
}

.timeline-date {
    margin: 0 0 0.5rem 0;
    color: var(--customer-text-muted);
    font-size: 0.9rem;
}

.timeline-comment {
    margin: 0;
    color: var(--customer-text);
    font-style: italic;
}

.order-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin: 2rem 0;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: var(--customer-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--customer-primary-dark);
    transform: translateY(-2px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

.btn-danger {
    background: var(--customer-danger);
    color: white;
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-2px);
}

/* Status badges */
.status-badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-iniciada {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-aceptada {
    background: #d4edda;
    color: #155724;
    border: 1px solid #74c0fc;
}

.status-enviada {
    background: #cce7ff;
    color: #004085;
    border: 1px solid #74c0fc;
}

.status-entregada {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #17a2b8;
}

.status-cancelada {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 768px) {
    .customer-page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .order-info-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item-card {
        flex-direction: column;
        text-align: center;
    }
    
    .item-meta {
        flex-direction: column;
        gap: 1rem;
        align-items: center;
    }
    
    .order-actions {
        flex-direction: column;
    }
}
</style>

<script>
function cancelOrder(orderId) {
    if (confirm('¿Estás seguro de que quieres cancelar la orden #' + orderId + '?')) {
        fetch('<?= BASE_URL ?>/orders/cancel', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'order_id=' + orderId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Orden cancelada exitosamente');
                location.reload();
            } else {
                alert('❌ Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error de conexión');
        });
    }
}
</script>

<?php
// Función helper para iconos de estado
function getStatusIcon($status) {
    if (empty($status)) {
        return 'question';
    }
    
    switch(strtolower(trim($status))) {
        case 'iniciada':
            return 'clock';
        case 'aceptada':
            return 'check';
        case 'enviada':
            return 'truck';
        case 'entregada':
            return 'check-double';
        case 'cancelada':
            return 'times';
        default:
            return 'question';
    }
}

$content = ob_get_clean(); // Obtiene el contenido del buffer
require_once VIEWS_PATH . '/vistas/customer/customer_layout.php';
?>