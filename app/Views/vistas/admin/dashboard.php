<?php
ob_start(); // Inicia el buffer de salida
?>

<h1>Panel de Administración</h1>
<p>Bienvenido, <strong><?= htmlspecialchars(\App\Utils\Session::get('user_name')) ?></strong>.</p>
<p>Desde aquí puedes gestionar los productos, órdenes y otros aspectos de la tienda.</p>

<!-- Tarjetas de estadísticas rápidas -->
<div class="dashboard-stats">
    <div class="stat-card">
        <div class="stat-icon orders-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number" id="total-orders">--</div>
            <div class="stat-label">Órdenes Totales</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon products-icon">
            <i class="fas fa-box"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number" id="low-stock-count">--</div>
            <div class="stat-label">Stock Bajo</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon pending-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-content">
            <div class="stat-number" id="pending-orders">--</div>
            <div class="stat-label">Pendientes</div>
        </div>
    </div>
</div>

<!-- Enlaces rápidos -->
<div class="quick-actions">
    <h3>Acciones Rápidas</h3>
    <div class="actions-grid">
        <a href="<?= BASE_URL ?>/management/products/create" class="action-btn">
            Nuevo Producto
        </a>
        <a href="<?= BASE_URL ?>/management/categories/create" class="action-btn">
            Nueva Categoría
        </a>
        <a href="<?= BASE_URL ?>/management/orders" class="action-btn">
            Ver Órdenes
        </a>
    </div>
</div>

<style>
.dashboard-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-right: 15px;
    color: white;
}

.orders-icon { background: #007bff; }
.products-icon { background: #28a745; }
.pending-icon { background: #ffc107; }

.stat-number {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

.quick-actions {
    margin: 30px 0;
}

.quick-actions h3 {
    margin-bottom: 15px;
    color: #333;
}

.actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    text-align: center;
}

.action-btn:hover {
    border-color: #007bff;
    color: #007bff;
    text-decoration: none;
}

.action-btn i {
    font-size: 24px;
    margin-bottom: 10px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
});

async function loadDashboardStats() {
    try {
        const response = await fetch('<?= BASE_URL ?>/management/dashboard/stats');
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('total-orders').textContent = data.stats.total_orders || 0;
            document.getElementById('low-stock-count').textContent = data.stats.low_stock_products || 0;
            
            // Calcular órdenes pendientes
            const pendingOrders = data.stats.orders_by_status['pendiente'] || 0;
            document.getElementById('pending-orders').textContent = pendingOrders;
        }
    } catch (error) {
        console.error('Error loading dashboard stats:', error);
        document.getElementById('total-orders').textContent = '0';
        document.getElementById('low-stock-count').textContent = '0';
        document.getElementById('pending-orders').textContent = '0';
    }
}
</script>

<?php
$content = ob_get_clean(); // Captura el contenido
require_once VIEWS_PATH . '/vistas/admin/admin_layout.php'; // Carga el layout
?>