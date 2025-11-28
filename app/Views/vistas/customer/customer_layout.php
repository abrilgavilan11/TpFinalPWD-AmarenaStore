<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/css/customer.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/css/admin.css">

<main class="customer-dashboard">
    <div class="customer-layout">
        <aside class="customer-sidebar">
            <h2>Cliente de Prueba</h2>
            <nav class="customer-nav">
                <a href="<?= BASE_URL ?>/customer/dashboard" class="<?= (strpos($_SERVER['REQUEST_URI'], '/customer/dashboard') !== false) ? 'active' : '' ?>">
                    Dashboard
                </a>
                <a href="<?= BASE_URL ?>/customer/orders" class="<?= strpos($_SERVER['REQUEST_URI'], '/customer/orders') !== false ? 'active' : '' ?>">
                    Mis Órdenes
                </a>
                <a href="<?= BASE_URL ?>/customer/profile" class="<?= strpos($_SERVER['REQUEST_URI'], '/customer/profile') !== false ? 'active' : '' ?>">
                    Mi Perfil
                </a>
            </nav>
        </aside>
        <section class="customer-content">
            <?= $content ?>
        </section>
    </div>
</main>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>