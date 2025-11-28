<?php
ob_start(); // Inicia el buffer de salida para capturar todo el HTML siguiente.

// Determinar si estamos en modo "edición" o "creación"
$isEditing = isset($data['category']) || (isset($data['action']) && $data['action'] === 'edit');
$category = $data['category'] ?? null;

// Definir la URL del formulario y el método
$formAction = $isEditing ? "/management/categories/update/{$category['idcategoria']}" : "/management/categories/store";
$formMethod = "POST";

// Título dinámico
$pageTitle = $isEditing ? 'Editar Categoría' : 'Agregar Nueva Categoría';
?>

<!-- CSS específico para formularios de categorías -->
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-categories.css">

<h1 class="mt-4"><?= $pageTitle ?></h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="/management/categories">Categorías</a></li>
    <li class="breadcrumb-item active"><?= $isEditing ? 'Editar' : 'Nueva' ?></li>
</ol>

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg category-form-card">
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

                    <form action="<?= $formAction ?>" method="<?= $formMethod ?>" class="category-form">
                        
                        <div class="mb-4">
                            <label for="name" class="form-label required">
                                <i class="fas fa-tag me-2"></i>Nombre de la Categoría
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="name" 
                                   name="name" 
                                   value="<?= htmlspecialchars($category['catnombre'] ?? '') ?>" 
                                   required
                                   maxlength="50"
                                   placeholder="Ej: Remeras, Pantalones, Accesorios...">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Máximo 50 caracteres. Este nombre aparecerá en el catálogo público.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Descripción
                            </label>
                            <textarea class="form-control" 
                                      id="description" 
                                      name="description" 
                                      rows="4"
                                      maxlength="200"
                                      placeholder="Descripción opcional de la categoría..."><?= htmlspecialchars($category['catdescripcion'] ?? '') ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Opcional. Máximo 200 caracteres. Ayuda a los usuarios a entender qué productos incluye esta categoría.
                            </div>
                        </div>

                        <?php if ($isEditing && isset($category['product_count'])): ?>
                            <div class="mb-4">
                                <div class="alert alert-info" role="alert">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Información:</strong> Esta categoría tiene 
                                    <span class="badge bg-primary"><?= $category['product_count'] ?></span> 
                                    producto(s) asociado(s).
                                    <?php if ($category['product_count'] > 0): ?>
                                        <br><small>No podrás eliminar esta categoría mientras tenga productos asociados.</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/management/categories" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-<?= $isEditing ? 'save' : 'plus' ?> me-2"></i>
                                <?= $isEditing ? 'Actualizar Categoría' : 'Crear Categoría' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para mejorar la experiencia del formulario -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.category-form');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    
    // Contador de caracteres para el nombre
    const nameCounter = document.createElement('small');
    nameCounter.className = 'text-muted float-end';
    nameInput.parentNode.appendChild(nameCounter);
    
    function updateNameCounter() {
        const remaining = 50 - nameInput.value.length;
        nameCounter.textContent = `${nameInput.value.length}/50 caracteres`;
        nameCounter.className = remaining < 10 ? 'text-warning float-end' : 'text-muted float-end';
    }
    
    // Contador de caracteres para la descripción
    const descCounter = document.createElement('small');
    descCounter.className = 'text-muted float-end';
    descriptionInput.parentNode.appendChild(descCounter);
    
    function updateDescCounter() {
        const remaining = 200 - descriptionInput.value.length;
        descCounter.textContent = `${descriptionInput.value.length}/200 caracteres`;
        descCounter.className = remaining < 20 ? 'text-warning float-end' : 'text-muted float-end';
    }
    
    // Inicializar contadores
    updateNameCounter();
    updateDescCounter();
    
    // Eventos
    nameInput.addEventListener('input', updateNameCounter);
    descriptionInput.addEventListener('input', updateDescCounter);
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        
        if (name.length === 0) {
            e.preventDefault();
            alert('El nombre de la categoría es obligatorio.');
            nameInput.focus();
            return false;
        }
        
        if (name.length > 50) {
            e.preventDefault();
            alert('El nombre no puede exceder 50 caracteres.');
            nameInput.focus();
            return false;
        }
        
        if (descriptionInput.value.length > 200) {
            e.preventDefault();
            alert('La descripción no puede exceder 200 caracteres.');
            descriptionInput.focus();
            return false;
        }
        
        // Todo está bien, mostrar indicador de carga
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Guardando...';
        submitBtn.disabled = true;
    });
    
    // Auto-capitalizar la primera letra del nombre
    nameInput.addEventListener('blur', function() {
        if (this.value) {
            this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
        }
    });
});
</script>

<?php
$content = ob_get_clean(); // Captura todo el HTML anterior en la variable $content.
require_once VIEWS_PATH . '/vistas/admin/admin_layout.php'; // Carga el layout principal y le pasa el contenido.
?>