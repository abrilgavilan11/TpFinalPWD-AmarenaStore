<?php
ob_start();
$isEdit = isset($cliente);
$cliente = $cliente ?? ($data['cliente'] ?? null);
if (!$cliente) {
    echo '<div class="alert alert-danger">No se encontró el cliente a editar.</div>';
    $content = ob_get_clean();
    require_once VIEWS_PATH . '/vistas/admin/admin_layout.php';
    return;
}
$actionUrl = BASE_URL . '/management/clientes/update/' . $cliente['idusuario'];
$pageTitle = 'Editar Cliente';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-products.css">

<h1 class="mt-4">Editar Cliente</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="/management/clientes">Clientes</a></li>
    <li class="breadcrumb-item active">Editar</li>
</ol>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg product-form-card">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>
                        <?= $pageTitle ?>
                    </h2>
                </div>
                <div class="card-body">
                    <form action="<?= $actionUrl ?>" method="post" class="client-form">
                        <div class="mb-4">
                            <label for="nombre" class="form-label required">
                                <i class="fas fa-user me-2"></i>Nombre
                            </label>
                            <input type="text" class="form-control form-control-lg" id="nombre" name="nombre" value="<?= htmlspecialchars($cliente['usnombre'] ?? '') ?>" required maxlength="50" placeholder="Nombre completo">
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label required">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($cliente['usmail'] ?? '') ?>" required maxlength="100" placeholder="Email del cliente">
                        </div>
                        <div class="mb-4">
                            <label for="estado" class="form-label required">
                                <i class="fas fa-toggle-on me-2"></i>Estado de la cuenta
                            </label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="1" <?= ($cliente['usestado'] ?? 1) == 1 ? 'selected' : '' ?>>Activa</option>
                                <option value="0" <?= ($cliente['usestado'] ?? 1) == 0 ? 'selected' : '' ?>>Inactiva</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-shopping-cart me-2"></i>Compras realizadas
                            </label>
                            <input type="text" class="form-control" value="<?= (int)($cliente['compras'] ?? 0) ?>" readonly>
                        </div>
                        <!-- No hay campo de fecha de registro en la tabla usuario -->
                        <hr class="my-4">
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= BASE_URL ?>/management/clientes" class="btn btn-secondary btn-lg">
                            <form method="POST" action="<?= BASE_URL ?>/management/clientes/delete/<?= $cliente['idusuario'] ?>" class="d-inline-block ms-2" onsubmit="return confirm('¿Seguro que deseas eliminar este cliente?');">
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </button>
                            </form>
                                <i class="fas fa-arrow-left me-2"></i>Volver
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once VIEWS_PATH . '/vistas/admin/admin_layout.php';
?>
