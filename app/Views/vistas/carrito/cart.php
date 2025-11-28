<?php
// No tiene acceso directo a $data del controlador, debe cargar dinámicamente
?>

<!-- Overlay para oscurecer el fondo cuando el carrito está visible -->
<div id="cart-overlay" class="cart-overlay"></div>

<!-- Contenedor del sidebar del carrito -->
<div id="cart-sidebar" class="cart-sidebar">
    <div class="cart-sidebar-header">
        <h3 class="mb-0">Tu Carrito</h3>
        <button id="close-cart-btn" class="btn-close"></button>
    </div>
    <div class="cart-sidebar-body">
        <!-- El contenido se carga dinámicamente vía JavaScript -->
        <div id="cart-items-container">
            <div class="text-center py-4">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Cargando carrito...</p>
            </div>
        </div>
    </div>
</div>

<script>
const sidebarBaseUrl = '<?= BASE_URL ?>';

document.addEventListener('DOMContentLoaded', function() {
    console.log("[v0] Cart sidebar initialized");

    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    const closeCartBtn = document.getElementById('close-cart-btn');
    const openCartBtn = document.getElementById('open-cart-btn'); 

    function openCart() {
        console.log("[v0] Opening cart sidebar");
        loadCartContents(); // Cargar contenido al abrir
        cartOverlay.classList.add('active');
        cartSidebar.classList.add('active');
    }

    function closeCart() {
        console.log("[v0] Closing cart sidebar");
        cartOverlay.classList.remove('active');
        cartSidebar.classList.remove('active');
    }

    if (openCartBtn) {
        openCartBtn.addEventListener('click', openCart);
    }
    if (closeCartBtn) {
        closeCartBtn.addEventListener('click', closeCart);
    }
    if (cartOverlay) {
        cartOverlay.addEventListener('click', closeCart);
    }

    function loadCartContents() {
        console.log("[v0] Loading cart contents");
        const container = document.getElementById('cart-items-container');
        
        fetch(`${sidebarBaseUrl}/cart/contents`)
            .then(response => response.json())
            .then(data => {
                console.log("[v0] Cart data received:", data);
                if (!data.success) {
                    container.innerHTML = '<div class="alert alert-danger m-3">Error al cargar el carrito</div>';
                    return;
                }

                if (data.items.length === 0) {
                    container.innerHTML = `
                        <div class="alert alert-info text-center py-4 m-3">
                            <p class="mb-2">Tu carrito está vacío.</p>
                            <a href="${sidebarBaseUrl}/catalog" class="btn btn-sm btn-primary">Explorar Productos</a>
                        </div>
                    `;
                    return;
                }

                let html = '<div class="cart-content"><div class="list-group">';
                
                data.items.forEach(item => {
                    const imagePath = item.image.startsWith('product_') 
                        ? `${sidebarBaseUrl}/uploads/products/${item.image}`
                        : `${sidebarBaseUrl}/img/ropa/${item.image}`;
                    
                    html += `
                        <div class="list-group-item cart-sidebar-item" data-item-id="${item.item_id}">
                            <div class="d-flex align-items-center">
                                <img src="${imagePath}" alt="${item.name}" class="cart-sidebar-item__img me-3">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">${item.name}</h6>
                                    <small class="text-muted">Talle: ${item.size} | Color: ${item.color}</small>
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <span class="fw-bold">$${item.itemTotal.toLocaleString('es-AR')}</span>
                                        <div class="d-flex align-items-center gap-2">
                                            <small class="text-muted">Cant: ${item.quantity}</small>
                                            <button class="btn btn-sm btn-outline-danger sidebar-remove-btn" data-item-id="${item.item_id}">
                                                ✕
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
                html += `
                    <div class="cart-sidebar-footer mt-3 p-3 bg-light">
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong>$${data.total.toLocaleString('es-AR')}</strong>
                        </div>
                        <a href="${sidebarBaseUrl}/carrito" class="btn btn-primary w-100 mb-2">Ver Carrito Completo</a>
                        <button id="sidebar-checkout-btn" class="btn btn-success w-100">Proceder al Pago</button>
                    </div>
                </div>`;

                container.innerHTML = html;
                attachSidebarEventListeners();
            })
            .catch(error => {
                console.error('[v0] Error loading cart:', error);
                container.innerHTML = '<div class="alert alert-danger m-3">Error al cargar el carrito</div>';
            });
    }

    function attachSidebarEventListeners() {
        document.querySelectorAll('.sidebar-remove-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const itemId = this.getAttribute('data-item-id');
                removeFromCart(itemId);
            });
        });

        const checkoutBtn = document.getElementById('sidebar-checkout-btn');
        if (checkoutBtn) {
            checkoutBtn.addEventListener('click', function() {
                window.location.href = `${sidebarBaseUrl}/carrito`;
            });
        }
    }

    function removeFromCart(itemId) {
        fetch(`${sidebarBaseUrl}/cart/eliminar`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'item_id=' + itemId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCartContents(); // Recargar contenido
                updateCartCounter(); // Actualizar contador
            }
        })
        .catch(error => console.error('[v0] Error:', error));
    }

    // Función para actualizar el contador de items del carrito
    function updateCartCounter() {
        const cartCounter = document.getElementById('cart-count');
        if (!cartCounter) return;

        fetch(`${sidebarBaseUrl}/cart/count`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log("[v0] Cart count updated:", data.count);
                    cartCounter.textContent = data.count;
                }
            })
            .catch(error => console.error('[v0] Error al contar items:', error));
    }

    // Llama a la función para establecer el contador inicial
    updateCartCounter();
});
</script>

<style>
.cart-sidebar {
    position: fixed;
    top: 0;
    right: -450px;
    width: 450px;
    max-width: 90%;
    height: 100%;
    background-color: #fff;
    box-shadow: -2px 0 5px rgba(0,0,0,0.1);
    transition: right 0.4s ease-in-out;
    z-index: 1050;
    display: flex;
    flex-direction: column;
}
.cart-sidebar.active {
    right: 0;
}
.cart-sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #dee2e6;
    background-color: #f8f9fa;
}
.cart-sidebar-body {
    flex-grow: 1;
    overflow-y: auto;
    padding: 0;
}
.cart-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.4s ease-in-out, visibility 0.4s;
    z-index: 1040;
}
.cart-overlay.active {
    opacity: 1;
    visibility: visible;
}
.cart-sidebar-item__img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 8px;
}
.cart-sidebar-footer {
    border-top: 1px solid #dee2e6;
}
</style>
