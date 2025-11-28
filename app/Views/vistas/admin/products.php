<?php
// Asegurar que se carguen los estilos específicos para administración y productos
if (!isset($data['pageCss'])) {
    $data['pageCss'] = 'admin';
}

ob_start(); // Inicia el buffer de salida para capturar el contenido de la vista
?>

<!-- CSS específico para productos -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-products.css">

<h1 class="mt-4">Gestionar Productos</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item active">Productos</li>
</ol>


<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-table me-1"></i> Lista de Productos</span>
        <a href="/management/products/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Agregar Nuevo Producto
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover products-table">
            <thead class="thead-custom">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Estado Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['products'])): ?>
                    <?php foreach ($data['products'] as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['idproducto']) ?></td>
                            <td>
                                <div class="product-name">
                                    <?= htmlspecialchars($product['pronombre']) ?>
                                </div>
                                <small class="product-detail"><?= htmlspecialchars(substr($product['prodetalle'], 0, 50)) ?><?= strlen($product['prodetalle']) > 50 ? '...' : '' ?></small>
                            </td>
                            <td>
                                <span class="badge category-badge">
                                    <?= htmlspecialchars($product['catnombre']) ?>
                                </span>
                            </td>
                            <td class="price-cell">$<?= number_format($product['proprecio'], 2, ',', '.') ?></td>
                            <td class="stock-cell">
                                <span class="stock-number"><?= htmlspecialchars($product['procantstock']) ?></span>
                            </td>
                            <td>
                                <?php
                                    // Umbral configurable para bajo stock
                                    $lowStockThreshold = 10; // Umbral fijo para bajo stock
                                    if ($product['procantstock'] == 0): ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Sin Stock
                                        </span>
                                <?php elseif ($product['procantstock'] <= $lowStockThreshold): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Bajo Stock
                                        </span>
                                <?php else: ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i>En Stock
                                        </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="/management/products/edit/<?= $product['idproducto'] ?>" class="btn btn-sm btn-info" title="Editar producto">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="/management/products/delete/<?= $product['idproducto'] ?>" 
                                      class="delete-product-form"
                                      data-product-name="<?= htmlspecialchars($product['pronombre']) ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar producto">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay productos para mostrar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean(); // Captura el contenido y limpia el buffer

// Agregar el script directamente al final del contenido
$content .= '
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Confirmar eliminación de producto
    document.querySelectorAll(".delete-product-form").forEach(function(form) {
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const productName = this.getAttribute("data-product-name");
            let message = `¿Estás seguro que deseas eliminar el producto "${productName}"?`;
            message += "\n\nEsta acción no se puede deshacer.";
            
            if (confirm(message)) {
                this.submit();
            }
        });
    });
});
</script>';

require_once VIEWS_PATH . '/vistas/admin/admin_layout.php'; // Carga el layout principal
?>