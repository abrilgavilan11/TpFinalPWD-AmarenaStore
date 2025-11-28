<?php
use App\Utils\Auth;
use App\Utils\Session;

ob_start(); // Inicia el buffer de salida

$userName = Auth::getUserName() ?? 'Usuario';
$userEmail = Auth::getUserEmail() ?? '';
$orders = $data['orders'] ?? [];
?>
            <h1>Mis Órdenes</h1>
            <p>Historial completo de todas tus compras.</p>

            <?php if (empty($orders)): ?>
                <div class="empty-state">
                    <i class="fas fa-shopping-bag"></i>
                    <h3>No tienes órdenes aún</h3>
                    <p>¡Empieza a explorar nuestros productos y realiza tu primera compra!</p>
                    <a href="<?= BASE_URL ?>/catalog" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> Explorar Productos
                    </a>
                </div>
            <?php else: ?>
                <div class="orders-container">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        // Calcular total de la orden
                        $orderModel = new \App\Models\Order();
                        $items = $orderModel->getItems($order['idcompra']);
                        $total = 0;
                        foreach ($items as $item) {
                            $total += $item['cicantidad'] * $item['ciprecio'];
                        }
                        ?>
                        <div class="order-card">
                            <div class="order-header">
                                <div class="order-info">
                                    <h3>Orden #<?= $order['idcompra'] ?></h3>
                                    <p class="order-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('d/m/Y H:i', strtotime($order['cofecha'])) ?>
                                    </p>
                                </div>
                                
                                <div class="order-status">
                                    <span class="status-badge status-<?= strtolower($order['estado_actual'] ?? 'iniciada') ?>">
                                        <?= ucfirst($order['estado_actual'] ?? 'Iniciada') ?>
                                    </span>
                                </div>
                                
                                <div class="order-total">
                                    <span class="total-label">Total:</span>
                                    <span class="total-amount">$<?= number_format($total, 2) ?></span>
                                </div>
                            </div>
                            
                            <div class="order-body">
                                <div class="order-items-preview">
                                    <h4><i class="fas fa-box"></i> Productos (<?= count($items) ?>)</h4>
                                    <div class="items-list">
                                        <?php foreach (array_slice($items, 0, 3) as $item): ?>
                                            <div class="item-preview">
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
                                                <div class="item-info">
                                                    <span class="item-name"><?= htmlspecialchars($item['pronombre']) ?></span>
                                                    <span class="item-qty">x<?= $item['cicantidad'] ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                        
                                        <?php if (count($items) > 3): ?>
                                            <div class="more-items">
                                                <i class="fas fa-plus"></i>
                                                <?= count($items) - 3 ?> productos más
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="order-actions">
                                    <a href="<?= BASE_URL ?>/customer/orders/<?= $order['idcompra'] ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                    
                                    <?php if (in_array($order['estado_actual'], ['iniciada'])): ?>
                                        <button class="btn btn-outline btn-cancel" onclick="cancelOrder(<?= $order['idcompra'] ?>)">
                                            <i class="fas fa-times"></i> Cancelar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
<style>
/* Estilos específicos para órdenes */
.orders-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.order-card {
    background: var(--color-white);
    border: 2px solid var(--color-pastel);
    border-radius: 15px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.order-card:hover {
    border-color: var(--color-primary);
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(217, 106, 126, 0.2);
}

.order-header {
    background: linear-gradient(135deg, #f8f9fa, var(--color-pastel));
    padding: 1.5rem 2rem;
    display: grid;
    grid-template-columns: 1fr auto auto;
    gap: 2rem;
    align-items: center;
    border-bottom: 2px solid var(--color-pastel);
}

.order-info h3 {
    margin: 0 0 0.5rem 0;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
    font-weight: 700;
}

.order-date {
    margin: 0;
    color: #666;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.order-total {
    text-align: right;
}

.total-label {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.25rem;
}

.total-amount {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
}

.order-body {
    padding: 2rem;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 2rem;
    align-items: start;
}

.order-items-preview h4 {
    margin: 0 0 1rem 0;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.item-preview {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem;
    background: #f8f9fa;
    border-radius: 10px;
}

.item-image {
    width: 50px;
    height: 50px;
    border-radius: 8px;
    overflow: hidden;
    background: var(--color-pastel);
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
}

.item-info {
    flex: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item-name {
    font-weight: 600;
    color: var(--color-primary-dark);
}

.item-qty {
    color: #666;
    font-size: 0.9rem;
}

.more-items {
    padding: 0.75rem;
    background: var(--color-pastel);
    border-radius: 10px;
    text-align: center;
    color: #666;
    font-style: italic;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.order-actions {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    min-width: 150px;
}

.btn-cancel {
    border-color: #dc3545;
    color: #dc3545;
}

.btn-cancel:hover {
    background: #dc3545;
    color: white;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
}

/* Estado vacío mejorado */
.empty-state {
    text-align: center;
    padding: 4rem 2rem;
    color: #666;
}

.empty-state i {
    font-size: 5rem;
    color: var(--color-primary);
    margin-bottom: 1.5rem;
}

.empty-state h3 {
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
    font-weight: 600;
    margin-bottom: 1rem;
}

.empty-state p {
    font-size: 1.1rem;
    margin-bottom: 2rem;
}

/* Responsive */
@media (max-width: 1024px) {
    .order-header {
        grid-template-columns: 1fr;
        text-align: center;
        gap: 1rem;
    }
    
    .order-body {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .order-actions {
        flex-direction: row;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .order-header {
        padding: 1rem 1.5rem;
    }
    
    .order-body {
        padding: 1.5rem;
    }
    
    .item-preview {
        padding: 0.5rem;
    }
    
    .item-image {
        width: 40px;
        height: 40px;
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
$content = ob_get_clean(); // Obtiene el contenido del buffer
require_once VIEWS_PATH . '/vistas/customer/customer_layout.php';
?>