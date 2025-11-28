<?php
use App\Utils\Auth;

ob_start(); // Inicia el buffer de salida

$userName = Auth::getUserName() ?? 'Usuario';
$userEmail = Auth::getUserEmail() ?? '';
?>
            <h1>Mi Dashboard</h1>
            <p>Bienvenido de vuelta, <strong><?= htmlspecialchars($userName) ?></strong>.</p>
            <p>Desde aquí puedes gestionar tus pedidos, perfil y otros aspectos de tu cuenta.</p>

            <!-- Tarjetas de estadísticas rápidas -->
            <div class="dashboard-stats">
                <div class="stat-card">
                    <div class="stat-icon orders-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $totalOrders ?? 14 ?></div>
                        <div class="stat-label">Órdenes Totales</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon products-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $completedOrders ?? 0 ?></div>
                        <div class="stat-label">Completadas</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon pending-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?= $pendingOrders ?? 14 ?></div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
            </div>



            <!-- Enlaces rápidos -->
            <div class="quick-actions">
                <h3>Acciones Rápidas</h3>
                <div class="actions-grid">
                    <a href="<?= BASE_URL ?>/customer/orders" class="action-btn">
                        <i class="fas fa-shopping-bag"></i>
                        Ver Mis Pedidos
                    </a>
                    <a href="<?= BASE_URL ?>/customer/profile" class="action-btn">
                        <i class="fas fa-user-edit"></i>
                        Actualizar Perfil
                    </a>
                    <a href="<?= BASE_URL ?>/productos" class="action-btn">
                        <i class="fas fa-shopping-cart"></i>
                        Seguir Comprando
                    </a>
                    <a href="<?= BASE_URL ?>/contact" class="action-btn">
                        <i class="fas fa-headset"></i>
                        Contacto
                    </a>
                </div>
            </div>

<?php
$content = ob_get_clean(); // Obtiene el contenido del buffer
require_once VIEWS_PATH . '/vistas/customer/customer_layout.php';
?>