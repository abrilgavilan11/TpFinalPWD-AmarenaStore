<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<main class="admin-page">
    <div class="admin-container">
        <aside class="admin-sidebar">
            <h2>Menú Admin</h2>
            <nav>
                <a href="<?= BASE_URL ?>/management" class="<?= (strpos($_SERVER['REQUEST_URI'], '/management/productos') === false && strpos($_SERVER['REQUEST_URI'], '/management/categorias') === false && strpos($_SERVER['REQUEST_URI'], '/management/ordenes') === false && (strpos($_SERVER['REQUEST_URI'], '/management') !== false || strpos($_SERVER['REQUEST_URI'], '/admin') !== false)) ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= BASE_URL ?>/management/products" class="<?= strpos($_SERVER['REQUEST_URI'], '/management/products') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/productos') !== false ? 'active' : '' ?>">Gestionar Productos</a>
                <a href="<?= BASE_URL ?>/management/categories" class="<?= strpos($_SERVER['REQUEST_URI'], '/management/categories') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/categorias') !== false ? 'active' : '' ?>">Gestionar Categorías</a>
                <a href="<?= BASE_URL ?>/management/orders" class="<?= strpos($_SERVER['REQUEST_URI'], '/management/orders') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/ordenes') !== false ? 'active' : '' ?>">Gestionar Órdenes</a>
                <a href="<?= BASE_URL ?>/management/clientes" class="<?= strpos($_SERVER['REQUEST_URI'], '/management/clientes') !== false || strpos($_SERVER['REQUEST_URI'], '/admin/clientes') !== false ? 'active' : '' ?>">Gestionar Clientes</a> 
                <a href="<?= BASE_URL ?>/management/menus" class="<?= strpos($_SERVER['REQUEST_URI'], '/management/menus') !== false || strpos($_SERVER['REQUEST_URI'], '/management/menus') !== false ? 'active' : '' ?>">Gestionar Menús
                </a>
            </nav>
        </aside>
        <section class="admin-content">
            <?= $content ?>
        </section>
    </div>
</main>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>