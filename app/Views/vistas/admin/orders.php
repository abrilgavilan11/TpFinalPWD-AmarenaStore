<?php
// Incluimos los layouts principales para mantener la consistencia del sitio.
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
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
                <a href="<?= BASE_URL ?>/management/clientes">Gestionar Clientes</a>
                <a href="<?= BASE_URL ?>/management/menus">Gestionar Menús</a>
            </nav>
        </aside>
        <section class="admin-content">
            <h1>Gestionar Órdenes</h1>
            <p>Aquí puedes ver y administrar las órdenes realizadas por los clientes.</p>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID Orden</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['orders'])): ?>
                        <tr>
                            <td colspan="6" class="text-center">No hay órdenes para mostrar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['orders'] as $order): ?>
                        <tr>
                            <td>#<?= htmlspecialchars($order['idcompra']) ?></td>
                            <td><?= htmlspecialchars($order['usnombre']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($order['cofecha'])) ?></td>
                            <td>
                                <?php
                                // Calcular total de la orden
                                $orderModel = new App\Models\Order();
                                $items = $orderModel->getItems($order['idcompra']);
                                $total = 0;
                                foreach ($items as $item) {
                                    $total += $item['cicantidad'] * $item['ciprecio'];
                                }
                                echo '$' . number_format($total, 0, ',', '.');
                                ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $order['estado_actual'])) ?>">
                                    <?= htmlspecialchars(ucfirst($order['estado_actual'])) ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="<?= BASE_URL ?>/management/orders/show/<?= $order['idcompra'] ?>" class="btn btn-sm btn-info" title="Ver detalles completos">
                                    <i class="fas fa-eye"></i> Ver
                                </a>
                                <button type="button" class="btn btn-sm btn-warning" onclick="changeOrderStatus(<?= $order['idcompra'] ?>)" title="Cambiar estado rápido">
                                    <i class="fas fa-edit"></i> Estado
                                </button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteOrderQuick(<?= $order['idcompra'] ?>)" title="Eliminar orden">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>
</main>

<style>
.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.admin-table th,
.admin-table td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.admin-table th {
    background: var(--color-primary);
    color: white;
    font-weight: 600;
    font-family: 'Raleway', sans-serif;
}

.admin-table tr:hover {
    background-color: #f8f9fa;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: capitalize;
}

.status-iniciada {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-aceptada {
    background: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.status-enviada {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.status-entregada {
    background: #e2e3e5;
    color: #383d41;
    border: 1px solid #d6d8db;
}

.status-cancelada {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.actions {
    white-space: nowrap;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin: 0 2px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.75rem;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-dark);
    transform: translateY(-1px);
}

.btn-info {
    background: #17a2b8;
    color: white;
    border: 1px solid #17a2b8;
}

.btn-info:hover {
    background: #138496;
    border-color: #117a8b;
    transform: translateY(-1px);
    color: white;
    text-decoration: none;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
    border: 1px solid #ffc107;
}

.btn-warning:hover {
    background: #e0a800;
    border-color: #d39e00;
    transform: translateY(-1px);
}

.btn-danger {
    background: #dc3545;
    color: white;
    border: 1px solid #dc3545;
}

.btn-danger:hover {
    background: #c82333;
    border-color: #bd2130;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border: 1px solid #6c757d;
}

.btn-secondary:hover {
    background: #5a6268;
    border-color: #545b62;
    transform: translateY(-1px);
}

.text-center {
    text-align: center;
}
</style>

<!-- Modal para cambio de estado -->
<div id="statusModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4 id="modalTitle">Cambiar estado de orden</h4>
            <button type="button" class="close-btn" onclick="closeStatusModal()">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="currentOrderId">
            <div class="form-group">
                <label for="newStatusSelect">Seleccionar nuevo estado:</label>
                <select id="newStatusSelect" class="form-control">
                    <option value="">-- Seleccionar estado --</option>
                    <option value="1">Iniciada</option>
                    <option value="2">Aceptada</option>
                    <option value="3">Enviada</option>
                    <option value="4">Entregada</option>
                    <option value="5">Cancelada</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeStatusModal()">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="button" class="btn btn-primary" onclick="confirmStatusChange()">
                <i class="fas fa-check"></i> Confirmar Cambio
            </button>
        </div>
    </div>
</div>

<style>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    display: flex;
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: white;
    border-radius: var(--amarena-radius);
    width: 90%;
    max-width: 400px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h4 {
    margin: 0;
    color: var(--color-primary-dark);
    font-family: var(--font-primary);
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.close-btn:hover {
    color: var(--color-primary);
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 20px;
    border-top: 1px solid #e0e0e0;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group label {
    font-weight: 600;
    color: var(--color-primary-dark);
    margin-bottom: 8px;
    display: block;
    font-family: var(--font-primary);
}

.form-control {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--color-pastel);
    border-radius: 25px;
    font-family: var(--font-primary);
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 3px rgba(217, 106, 126, 0.25);
}
</style>

<script>
function changeOrderStatus(orderId) {
    // Mostrar modal para cambiar estado
    document.getElementById('statusModal').style.display = 'flex';
    document.getElementById('currentOrderId').value = orderId;
    document.getElementById('modalTitle').textContent = `Cambiar estado de la orden #${orderId}`;
    document.getElementById('newStatusSelect').value = '';
}

function closeStatusModal() {
    document.getElementById('statusModal').style.display = 'none';
}

function confirmStatusChange() {
    const orderId = document.getElementById('currentOrderId').value;
    const newStatus = document.getElementById('newStatusSelect').value;
    
    if (!newStatus) {
        alert('Por favor selecciona un nuevo estado');
        return;
    }
    
    // Obtener texto del estado seleccionado
    const statusText = document.querySelector(`#newStatusSelect option[value="${newStatus}"]`).textContent;
    
    if (confirm(`¿Cambiar el estado de la orden #${orderId} a "${statusText}"?`)) {
        // Deshabilitar botón y mostrar loading
        const confirmBtn = document.querySelector('.modal-footer .btn:last-child');
        const originalText = confirmBtn.innerHTML;
        confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cambiando...';
        confirmBtn.disabled = true;
        
        // Hacer petición para cambiar estado
        const formData = new FormData();
        formData.append('status', newStatus);
        formData.append('notes', '');
        
        fetch(`<?= BASE_URL ?>/management/orders/update-status/${orderId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('✓ Estado actualizado correctamente a: ' + statusText);
                closeStatusModal();
                location.reload();
            } else {
                alert('❌ Error al actualizar el estado: ' + (data.message || 'Error desconocido'));
                // Restaurar botón
                confirmBtn.innerHTML = originalText;
                confirmBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('❌ Error de conexión al actualizar el estado');
            // Restaurar botón
            confirmBtn.innerHTML = originalText;
            confirmBtn.disabled = false;
        });
    }
}

// Función para eliminar orden desde la tabla principal
function deleteOrderQuick(orderId) {
    if (confirm('⚠️ ¿Estás seguro de que quieres eliminar la orden #' + orderId + '?')) {
        if (confirm('🔥 ÚLTIMA CONFIRMACIÓN: Esta acción NO se puede deshacer')) {
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
                    location.reload();
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

// Cerrar modal al hacer click fuera
document.addEventListener('click', function(event) {
    const modal = document.getElementById('statusModal');
    if (event.target === modal) {
        closeStatusModal();
    }
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>