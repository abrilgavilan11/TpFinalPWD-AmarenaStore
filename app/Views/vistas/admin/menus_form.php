<?php
ob_start();

// Determinar si estamos en modo "edición" o "creación"
$isEditing = isset($data['menu']) || (isset($data['action']) && $data['action'] === 'edit');
$menu = $data['menu'] ?? null;
$menusPadre = $menusPadre ?? ($data['menusPadre'] ?? []);

// Definir la URL del formulario y el método
$formAction = $isEditing ? "/management/menus/edit/{$menu['idmenu']}" : "/management/menus/create";
$formMethod = "POST";

// Título dinámico
$pageTitle = $isEditing ? 'Editar Menú' : 'Agregar Nuevo Menú';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin-categories.css">

<h1 class="mt-4"><?= $pageTitle ?></h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="/management">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="/management/menus">Menús</a></li>
    <li class="breadcrumb-item active"><?= $isEditing ? 'Editar' : 'Nuevo' ?></li>
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

                    <form action="<?= $formAction ?>" method="<?= $formMethod ?>" class="menu-form">
                        <div class="mb-4">
                            <label for="menombre" class="form-label required">
                                <i class="fas fa-bars me-2"></i>Nombre del Menú
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   id="menombre" 
                                   name="menombre" 
                                   value="<?= htmlspecialchars($menu['menombre'] ?? '') ?>" 
                                   required
                                   maxlength="50"
                                   placeholder="Ej: Inicio, Catálogo, Contacto...">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Máximo 50 caracteres. Este nombre aparecerá en la navegación.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="medescripcion" class="form-label">
                                <i class="fas fa-align-left me-2"></i>Descripción
                            </label>
                            <input type="text" class="form-control" id="medescripcion" name="medescripcion" value="<?= htmlspecialchars($menu['medescripcion'] ?? '') ?>" maxlength="124" placeholder="Descripción opcional del menú...">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Opcional. Máximo 124 caracteres.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="meurl" class="form-label required">
                                <i class="fas fa-link me-2"></i>URL
                            </label>
                            <input type="text" class="form-control" id="meurl" name="meurl" value="<?= htmlspecialchars($menu['meurl'] ?? '') ?>" required maxlength="255" placeholder="Ej: /, /catalog, /contact">
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Ruta relativa o absoluta del menú.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="idpadre" class="form-label">
                                <i class="fas fa-sitemap me-2"></i>Menú Padre
                            </label>
                            <select class="form-select" id="idpadre" name="idpadre">
                                <option value="">Sin padre</option>
                                <?php foreach ($menusPadre as $padre): ?>
                                    <?php if (!$isEditing || $padre['idmenu'] != ($menu['idmenu'] ?? null)): ?>
                                        <option value="<?= $padre['idmenu'] ?>" <?= $isEditing && ($menu['idpadre'] ?? null) == $padre['idmenu'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($padre['menombre']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Puedes anidar menús para crear submenús.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="meorden" class="form-label required">
                                <i class="fas fa-sort-numeric-up me-2"></i>Orden
                            </label>
                            <input type="number" class="form-control" id="meorden" name="meorden" min="1" value="<?= isset($menu['meorden']) ? (int)$menu['meorden'] : 1 ?>" required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Indica el orden en el que aparecerá el menú.
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <a href="/management/menus" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times me-2"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-<?= $isEditing ? 'save' : 'plus' ?> me-2"></i>
                                <?= $isEditing ? 'Actualizar Menú' : 'Crear Menú' ?>
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
    const form = document.querySelector('.menu-form');
    const nameInput = document.getElementById('menombre');
    const descInput = document.getElementById('medescripcion');
    
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
    descInput.parentNode.appendChild(descCounter);
    
    function updateDescCounter() {
        const remaining = 124 - descInput.value.length;
        descCounter.textContent = `${descInput.value.length}/124 caracteres`;
        descCounter.className = remaining < 20 ? 'text-warning float-end' : 'text-muted float-end';
    }
    
    // Inicializar contadores
    updateNameCounter();
    updateDescCounter();
    
    // Eventos
    nameInput.addEventListener('input', updateNameCounter);
    descInput.addEventListener('input', updateDescCounter);
    
    // Validación del formulario
    form.addEventListener('submit', function(e) {
        const name = nameInput.value.trim();
        if (name.length === 0) {
            e.preventDefault();
            alert('El nombre del menú es obligatorio.');
            nameInput.focus();
            return false;
        }
        if (name.length > 50) {
            e.preventDefault();
            alert('El nombre no puede exceder 50 caracteres.');
            nameInput.focus();
            return false;
        }
        if (descInput.value.length > 124) {
            e.preventDefault();
            alert('La descripción no puede exceder 124 caracteres.');
            descInput.focus();
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
$content = ob_get_clean();
require_once VIEWS_PATH . '/vistas/admin/admin_layout.php';
?>