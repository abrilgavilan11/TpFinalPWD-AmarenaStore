<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';

// Recuperar los valores de búsqueda y categoría para mantenerlos en el formulario
$currentSearch = htmlspecialchars($data['searchQuery'] ?? '');
$currentCategory = htmlspecialchars($data['currentCategory'] ?? '');
?>

<main class="catalog">
    <div class="catalog__container">
        <h1 class="catalog__title">Nuestro Catálogo</h1>
        
        <!-- PANEL DE BÚSQUEDA Y FILTROS -->
        <div class="catalog__controls">
            <form action="/catalog" method="GET" class="catalog__search-form">
                <input type="search" name="search" placeholder="Buscar por nombre..." value="<?= $currentSearch ?>" class="catalog__search-input">
                <button type="submit" class="catalog__search-btn">Buscar</button>
            </form>
            
            <div class="catalog__filters">
                <span class="filters__label">Filtrar por:</span>
                <div class="filters__options">
                    <a href="/catalog" class="filter-btn <?= empty($currentCategory) ? 'active' : '' ?>">Todos</a>
                    <?php if (!empty($data['categories'])): ?>
                        <?php foreach ($data['categories'] as $category): ?>
                            <a href="/catalog?category=<?= htmlspecialchars($category['idcategoria']) ?>" 
                               class="filter-btn <?= ($currentCategory == $category['idcategoria']) ? 'active' : '' ?>">
                                <?= htmlspecialchars($category['catnombre']) ?>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- GRID DE PRODUCTOS -->
        <div class="catalog__grid">
            <?php if (!empty($data['products'])): ?>
                <?php foreach ($data['products'] as $product): ?>
                    <div class="product-card">
                        <div class="product-card__image">
                            <?php
                            // Lógica corregida para determinar la ruta de la imagen de forma segura
                            $imageName = $product['proimagen'] ?? 'remera_placeholder.jpg';
                            if (isset($product['proimagen']) && strpos($product['proimagen'], 'product_') === 0) {
                                // Es una imagen subida por el administrador
                                $imagePath = BASE_URL . '/uploads/products/' . htmlspecialchars($product['proimagen']);
                            } else {
                                // Es una imagen por defecto del sistema
                                $imagePath = BASE_URL . '/img/ropa/' . htmlspecialchars($imageName);
                            }
                            ?>
                            <img src="<?= $imagePath ?>"
                                 alt="<?= htmlspecialchars($product['pronombre'] ?? '') ?>">
                        </div>
                        <div class="product-card__info">
                            <h4 class="product-card__title"><?= htmlspecialchars($product['pronombre'] ?? '') ?></h4>
                            <p class="product-card__price">$<?= number_format($product['proprecio'] ?? 0, 0, ',', '.') ?></p>
                            
                            <div class="product-card__sizes">
                                <span class="sizes-label">Talles:</span>
                                <div class="sizes-options">
                                    <button class="size-btn" data-size="XS">XS</button>
                                    <button class="size-btn" data-size="S">S</button>
                                    <button class="size-btn" data-size="M">M</button>
                                    <button class="size-btn" data-size="L">L</button>
                                    <button class="size-btn" data-size="XL">XL</button>
                                    <button class="size-btn" data-size="XXL">XXL</button>
                                </div>
                            </div>
                            
                            <div class="product-card__colors">
                                <span class="colors-label">Colores:</span>
                                <div class="colors-options">
                                    <button class="color-btn" data-color="Rosa" style="background-color: #F2B6B6"></button>
                                    <button class="color-btn" data-color="Blanco" style="background-color: #ffffff; border: 2px solid #ddd"></button>
                                </div>
                            </div>
                            
                            <button class="product-card__add-btn" 
                                    data-product-id="<?= htmlspecialchars($product['idproducto']) ?>"
                                    data-product-name="<?= htmlspecialchars($product['pronombre'] ?? '') ?>"
                                    data-product-price="<?= htmlspecialchars($product['proprecio'] ?? 0) ?>">
                                Agregar al Carrito
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="catalog__empty">No hay productos disponibles en este momento.</p>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
// Definir base URL para las peticiones AJAX
const baseUrl = '<?= BASE_URL ?>';

/**
 * Muestra una notificación "toast" en la pantalla.
 * @param {string} message El mensaje a mostrar.
 * @param {string} type 'success' (verde) o 'error' (rojo).
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => document.body.removeChild(toast), 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log("Catalog page loaded");
    const productCards = document.querySelectorAll('.product-card');

    productCards.forEach(card => {
        const sizeButtons = card.querySelectorAll('.size-btn');
        const colorButtons = card.querySelectorAll('.color-btn');
        const addButton = card.querySelector('.product-card__add-btn');

        let selectedSize = null;
        let selectedColor = null;

        // Evento para botones de talle
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Quitar 'active' de otros botones de talle en la misma tarjeta
                sizeButtons.forEach(s => s.classList.remove('active'));
                // Añadir 'active' al botón clickeado
                this.classList.add('active');
                selectedSize = this.dataset.size;
                console.log("Size selected:", selectedSize);
            });
        });

        // Evento para botones de color
        colorButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                // Quitar 'active' de otros botones de color en la misma tarjeta
                colorButtons.forEach(c => c.classList.remove('active'));
                // Añadir 'active' al botón clickeado
                this.classList.add('active');
                selectedColor = this.dataset.color;
                console.log("Color selected:", selectedColor);
            });
        });

        // Evento para el botón "Agregar al Carrito"
        addButton.addEventListener('click', function() {
            console.log("Add to cart clicked");
            if (!selectedSize) {
                showToast('Por favor, selecciona un talle.', 'error');
                return;
            }
            if (!selectedColor) {
                showToast('Por favor, selecciona un color.', 'error');
                return;
            }

            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            
            console.log("Adding product:", productId, "Size:", selectedSize, "Color:", selectedColor);

            const formData = new URLSearchParams();
            formData.append('product_id', productId);
            formData.append('quantity', 1); // Cantidad fija a 1 por ahora
            formData.append('size', selectedSize);
            formData.append('color', selectedColor);

            fetch(`${baseUrl}/cart/agregar`, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                console.log("Add to cart response:", data);
                if (data.success) {
                    showToast(`'${productName}' fue agregado al carrito.`);

                    // Lógica mejorada para actualizar el contador del carrito
                    let cartCounter = document.querySelector('.cart-counter');
                    if (!cartCounter) {
                        // Si el contador no existe (carrito estaba vacío), lo creamos
                        cartCounter = document.createElement('span');
                        cartCounter.className = 'cart-counter';
                        document.querySelector('.navbar__carrito').appendChild(cartCounter);
                    }
                    cartCounter.textContent = data.cartCount; // Usamos el valor del backend
                    console.log("Cart counter updated to:", data.cartCount);
                } else {
                    // Verificar si requiere autenticación
                    if (data.requiresAuth) {
                        // Mostrar el modal de login existente
                        showToast('Debes iniciar sesión para agregar productos al carrito.', 'warning');
                        document.getElementById('login-modal').style.display = 'flex';
                    } else {
                        showToast('Error: ' + (data.message || 'No se pudo agregar el producto.'), 'error');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Ocurrió un error de conexión. Inténtalo de nuevo.', 'error');
            });
        });
    });
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
