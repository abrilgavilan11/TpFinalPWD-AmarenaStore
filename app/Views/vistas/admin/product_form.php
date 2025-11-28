<?php
ob_start(); // Inicia el buffer de salida para capturar todo el HTML siguiente.

// Determinar si estamos en modo "edición" o "creación"
$isEditing = isset($data['product']) || (isset($data['action']) && $data['action'] === 'edit');
$product = $data['product'] ?? null;

// Definir la URL del formulario y el método
$formAction = $isEditing ? "/management/products/update/{$product['idproducto']}" : "/management/products/store";
$formMethod = "POST";

// Título dinámico
$pageTitle = $isEditing ? 'Editar Producto' : 'Agregar Nuevo Producto';
?>

<!-- CSS específico para formularios de productos -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-products.css">

<h1 class="mt-4"><?= $pageTitle ?></h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="/management/products">Productos</a></li>
    <li class="breadcrumb-item active"><?= $isEditing ? 'Editar' : 'Nuevo' ?></li>
</ol>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg product-form-card">
                <div class="card-header">
                    <h2 class="mb-0">
                        <i class="fas fa-<?= $isEditing ? 'edit' : 'plus-circle' ?> me-2"></i>
                        <?= $pageTitle ?>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (isset($data['error'])): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($data['error']) ?>
                        </div>
                    <?php endif; ?>
                    <form action="<?= $formAction ?>" method="<?= $formMethod ?>" enctype="multipart/form-data" class="product-form">
                        
                        <div class="mb-4">
                            <label for="name" class="form-label required">
                                <i class="fas fa-box me-2"></i>Nombre del Producto
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($product['pronombre'] ?? '') ?>" 
                                   required
                                   placeholder="Ej: Remeras, Pantalones, Accesorios...">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Ingresa un nombre descriptivo para el producto
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label required">
                                <i class="fas fa-align-left me-2"></i>Detalle
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4" 
                                      placeholder="Describe las características principales del producto"
                                      required><?= htmlspecialchars($product['prodetalle'] ?? '') ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Describe las características principales del producto
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="price" class="form-label required">
                                    <i class="fas fa-dollar-sign me-2"></i>Precio
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" 
                                           class="form-control" 
                                           id="price" 
                                           name="price" 
                                           step="0.01" 
                                           value="<?= htmlspecialchars($product['proprecio'] ?? '') ?>" 
                                           placeholder="0.00"
                                           required>
                                </div>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Precio en pesos argentinos
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="stock" class="form-label required">
                                    <i class="fas fa-warehouse me-2"></i>Stock
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="stock" 
                                       name="stock" 
                                       value="<?= htmlspecialchars($product['procantstock'] ?? '') ?>" 
                                       placeholder="0"
                                       min="0"
                                       required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Cantidad disponible en inventario
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="category" class="form-label required">
                                <i class="fas fa-tags me-2"></i>Categoría
                            </label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Selecciona una categoría</option>
                                <?php foreach ($data['categories'] as $category): ?>
                                    <option value="<?= $category['idcategoria'] ?>" <?= ($isEditing && $product['idcategoria'] == $category['idcategoria']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['catnombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="image" class="form-label">
                                <i class="fas fa-image me-2"></i>Imagen del Producto
                            </label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <?php if ($isEditing && !empty($product['proimagen'])): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Imagen actual:</small>
                                    <img src="/uploads/products/<?= htmlspecialchars($product['proimagen']) ?>" alt="Imagen actual" style="max-width: 100px; margin-left: 10px;" class="border rounded">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Sube una nueva imagen solo si deseas reemplazar la actual
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/management/products" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-<?= $isEditing ? 'save' : 'plus-circle' ?> me-2"></i>
                                <?= $isEditing ? 'Actualizar Producto' : 'Crear Producto' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean(); // Captura todo el HTML anterior en la variable $content.
require_once VIEWS_PATH . '/vistas/admin/admin_layout.php'; // Carga el layout principal y le pasa el contenido.
?>