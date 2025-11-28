<?php
// Asegurar que se carguen los estilos específicos para administración y clientes
if (!isset($data['pageCss'])) {
    $data['pageCss'] = 'admin';
}

// Compatibilidad: asegurar que $clientes esté definido
$clientes = $clientes ?? ($data['clientes'] ?? []);

ob_start();
?>
<!-- CSS específico para clientes -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-products.css">

<h1 class="mt-4">Gestión de Clientes</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item active">Clientes</li>
</ol>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people me-1"></i> Lista de Clientes</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover clients-table">
            <thead class="thead-custom">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Estado</th>
                    <th>Compras</th>
                    <!-- <th>Fecha Registro</th> -->
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($clientes)): ?>
                    <?php foreach ($clientes as $cliente): ?>
                        <tr>
                            <td><?= htmlspecialchars($cliente['idusuario']) ?></td>
                            <td><?= htmlspecialchars($cliente['usnombre'] ?? '') ?></td>
                            <td><?= htmlspecialchars($cliente['usmail'] ?? '') ?></td>
                            <td>
                                <?php if (($cliente['usestado'] ?? 1) == 1): ?>
                                    <span class="badge bg-success">Activa</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactiva</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    <?= (int)($cliente['compras'] ?? 0) ?>
                                </span>
                            </td>
                            <!-- <td><?= htmlspecialchars(date('d/m/Y', strtotime($cliente['fecharegistro']))) ?></td> -->
                            <td>
                                <a href="<?= BASE_URL ?>/management/clientes/edit/<?= $cliente['idusuario'] ?>" class="btn btn-sm btn-info" title="Editar cliente">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form method="POST" action="<?= BASE_URL ?>/management/clientes/delete/<?= $cliente['idusuario'] ?>" class="delete-client-form d-inline" data-client-name="<?= htmlspecialchars($cliente['usnombre'] ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar cliente">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No hay clientes para mostrar.</td>
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
