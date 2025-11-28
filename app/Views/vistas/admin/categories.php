<?php
// Asegurar que se carguen los estilos específicos para administración y categorías
if (!isset($data['pageCss'])) {
    $data['pageCss'] = 'admin';
}

ob_start(); // Inicia el buffer de salida para capturar el contenido de la vista
?>

<!-- CSS específico para categorías -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-categories.css">

<h1 class="mt-4">Gestionar Categorías</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item active">Categorías</li>
</ol>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-1"></i> Lista de Categorías</span>
        <a href="/management/categories/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Agregar Nueva Categoría
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover categories-table">
            <thead class="thead-custom">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Cantidad de Productos</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($data['categories'])): ?>
                    <?php foreach ($data['categories'] as $category): ?>
                        <tr>
                            <td><?= htmlspecialchars($category['idcategoria']) ?></td>
                            <td><?= htmlspecialchars($category['catnombre']) ?></td>
                            <td><?= htmlspecialchars($category['catdescripcion'] ?? 'Sin descripción') ?></td>
                            <td>
                                <span class="badge bg-info">
                                    <?= isset($category['product_count']) ? $category['product_count'] : '0' ?> productos
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm toggle-status-btn <?= $category['activo'] ? 'btn-success' : 'btn-secondary' ?>" 
                                        data-category-id="<?= $category['idcategoria'] ?>" 
                                        data-current-status="<?= $category['activo'] ?>" 
                                        title="Click para cambiar estado">
                                    <i class="fas <?= $category['activo'] ? 'fa-eye' : 'fa-eye-slash' ?>"></i>
                                    <?= $category['activo'] ? 'Activa' : 'Desactivada' ?>
                                </button>
                            </td>
                            <td>
                                <a href="/management/categories/edit/<?= $category['idcategoria'] ?>" class="btn btn-sm btn-info" title="Editar">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form method="POST" action="/management/categories/delete/<?= $category['idcategoria'] ?>" 
                                      class="delete-category-form"
                                      data-category-name="<?= htmlspecialchars($category['catnombre']) ?>"
                                      data-product-count="<?= isset($category['product_count']) ? $category['product_count'] : '0' ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar categoría">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No hay categorías para mostrar.</td>
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
    // Cambiar estado de categoría
    document.querySelectorAll(".toggle-status-btn").forEach(function(btn) {
        btn.addEventListener("click", function() {
            const categoryId = this.getAttribute("data-category-id");
            const currentStatus = this.getAttribute("data-current-status") === "1";
            const button = this;
            
            // Deshabilitar botón temporalmente
            button.disabled = true;
            
            // Detectar si estamos en desarrollo (puerto 8000) o producción (puerto 80)
            const baseUrl = window.location.port === "8000" ? 
                `${window.location.origin}/management/categories/toggle-status/${categoryId}` :
                `${window.location.origin}/amarena/management/categories/toggle-status/${categoryId}`;
            
            console.log("Requesting URL:", baseUrl);
            
            fetch(baseUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                }
            })
            .then(response => {
                console.log("Response status:", response.status);
                console.log("Response headers:", response.headers);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    const newStatus = !currentStatus;
                    
                    // Actualizar atributos del botón
                    button.setAttribute("data-current-status", newStatus ? "1" : "0");
                    
                    // Actualizar clases y contenido
                    if (newStatus) {
                        button.className = "btn btn-sm toggle-status-btn btn-success";
                        button.innerHTML = `<i class="fas fa-eye"></i> Activa`;
                    } else {
                        button.className = "btn btn-sm toggle-status-btn btn-secondary";
                        button.innerHTML = `<i class="fas fa-eye-slash"></i> Desactivada`;
                    }
                } else {
                    alert("Error al cambiar el estado de la categoría: " + (data.message || "Error desconocido"));
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("Error de conexión");
            })
            .finally(() => {
                button.disabled = false;
            });
        });
    });
    
    // Confirmar eliminación de categoría
    document.querySelectorAll(".delete-category-form").forEach(function(form) {
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            
            const categoryName = this.getAttribute("data-category-name");
            const productCount = this.getAttribute("data-product-count");
            
            let message = `¿Estás seguro que deseas eliminar la categoría "${categoryName}"?`;
            
            if (parseInt(productCount) > 0) {
                message += `\n\nATENCIÓN: Esta categoría tiene ${productCount} productos asociados.`;
                message += "\nAl eliminarla, los productos quedarán sin categoría.";
            }
            
            if (confirm(message)) {
                this.submit();
            }
        });
    });
});
</script>';

require_once VIEWS_PATH . '/vistas/admin/admin_layout.php'; // Carga el layout principal
?>
