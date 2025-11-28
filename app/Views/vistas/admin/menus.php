<?php
// Replicando el estilo de categories.php para la gestión de menús
if (!isset($data['pageCss'])) {
    $data['pageCss'] = 'admin';
}
// Compatibilidad: asegurar que $menus esté definido
$menus = $menus ?? ($data['menus'] ?? []);
ob_start();
?>
<!-- CSS específico para menús -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-categories.css">

<h1 class="mt-4">Gestión de Menús</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item active">Menús</li>
</ol>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="fas fa-table me-1"></i> Lista de Menús</span>
        <a href="<?= BASE_URL ?>/management/menus/create" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Crear Nuevo Menú
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover menus-table">
            <thead class="thead-custom">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>URL</th>
                    <th>Padre</th>
                    <th>Orden</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($menus)): ?>
                    <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td><?= htmlspecialchars($menu['idmenu']) ?></td>
                            <td><?= htmlspecialchars($menu['menombre']) ?></td>
                            <td><?= htmlspecialchars($menu['medescripcion']) ?></td>
                            <td><?= htmlspecialchars($menu['meurl']) ?></td>
                            <td><?= $menu['idpadre'] ? htmlspecialchars($menu['idpadre']) : 'Sin padre' ?></td>
                            <td><?= (int)$menu['meorden'] ?></td>
                            
                            <td>
                                <a href="<?= BASE_URL ?>/management/menus/edit/<?= $menu['idmenu'] ?>" class="btn btn-sm btn-warning me-1" title="Editar menú">
                                    <i class="fas fa-pencil-alt"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/management/menus/delete/<?= $menu['idmenu'] ?>" class="delete-menu-form d-inline">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar menú" onclick="return confirm('¿Estás seguro que deseas eliminar el menú <?= htmlspecialchars($menu['menombre']) ?>?');">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No hay menús para mostrar.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            </table>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once VIEWS_PATH . '/vistas/admin/admin_layout.php';
?>
