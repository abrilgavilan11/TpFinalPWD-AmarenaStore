<?php


use App\Utils\Session;
use App\Models\Menu;
use App\Models\Category;
// Cargar menús principales desde la base de datos
$menuModel = new Menu();
if (Session::has('user_id') && isset($userRoleId)) {
    $mainMenus = $menuModel->getMenuByRole($userRoleId);
} else {
    $mainMenus = $menuModel->getAll();
}
// Cargar categorías activas para el dropdown de Catálogo
$categoryModel = new Category();
$navbarCategories = $categoryModel->getActiveCategories();

$currentUser = Session::get('user_name', 'Usuario');
$userRole = Session::get('user_role', '');
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$cartCount = count(Session::get('cart', []));


?>
<header class="navbar">
    <div class="navbar__logo">
        <a href="<?= BASE_URL ?>">
            <span>Amarena Store</span>
        </a>
    </div>
    <nav class="navbar__links">

        <?php
        // Agrupar menús por padre
        $menuPadres = array_filter($mainMenus, fn($m) => empty($m['idpadre']));
        function getMenuHijos($mainMenus, $parentId) {
            return array_filter($mainMenus, fn($m) => $m['idpadre'] == $parentId);
        }
        $panelRendered = false;
        foreach ($menuPadres as $menu):
            $menuUrl = htmlspecialchars($menu['meurl']);
            $isHome = ($menuUrl === '/' || $menuUrl === '/home');
            $isActive = false;
            if ($isHome) {
                $isActive = ($currentPath === '/' || $currentPath === '/home');
            } else {
                $isActive = ($menuUrl !== '/' && strpos($currentPath, $menuUrl) === 0);
            }
            $hijos = getMenuHijos($mainMenus, $menu['idmenu']);
            // Catálogo: dropdown de categorías
            if (strtolower($menu['menombre']) === 'catálogo' || strtolower($menu['menombre']) === 'catalogo') {
                ?>
                <div class="navbar__dropdown">
                    <a href="<?= $menuUrl ?>" class="dropdown-toggle<?= $isActive ? ' active' : '' ?>" id="catalogoDropdown">
                        <?= htmlspecialchars($menu['menombre']) ?>
                    </a>
                    <div class="dropdown-menu">
                        <?php foreach ($navbarCategories as $cat): ?>
                            <a class="dropdown-item" href="/catalog?category=<?= htmlspecialchars($cat['idcategoria']) ?>">
                                <?= htmlspecialchars($cat['catnombre']) ?>
                            </a>
                        <?php endforeach; ?>
                        <?php if (Session::has('user_id') && isset($userRole) && $userRole === 'Administrador'): ?>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item dropdown-add-category" href="/management/categories/create" style="font-weight:bold; color:#c04a6b;">
                                Agregar Categorías
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php
            }
            // Panel Admin y Panel Cliente: solo mostrar el panel correspondiente al rol, como dropdown si tiene hijos
            elseif (
                (stripos($menu['menombre'], 'panel admin') !== false || stripos($menu['menombre'], 'panel cliente') !== false)
            ) {
                // Si ya se mostró un panel, omitir el resto
                if ($panelRendered) {
                    continue;
                }
                // Si no hay sesión, no mostrar ningún panel
                if (!Session::has('user_id')) {
                    continue;
                }
                // Si es admin, solo mostrar Panel Admin
                if ($userRole === 'Administrador' && stripos($menu['menombre'], 'panel admin') !== false) {
                    if ($panelRendered) continue;
                    $panelRendered = true;
                    if (!empty($hijos)) {
                        // Renderizar solo como dropdown
                        ?>
                        <div class="navbar__dropdown">
                            <a href="<?= $menuUrl ?>" class="dropdown-toggle<?= $isActive ? ' active' : '' ?>">
                                <?= htmlspecialchars($menu['menombre']) ?>
                            </a>
                            <div class="dropdown-menu">
                                <?php foreach ($hijos as $hijo): ?>
                                    <a class="dropdown-item" href="<?= htmlspecialchars($hijo['meurl']) ?>">
                                        <?= htmlspecialchars($hijo['menombre']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php
                        continue;
                    } else {
                        // Sin hijos, mostrar solo como enlace normal
                        ?>
                        <a href="<?= $menuUrl ?>" class="<?= $isActive ? 'active' : '' ?>">
                            <?= htmlspecialchars($menu['menombre']) ?>
                        </a>
                        <?php
                        continue;
                    }
                }
                // Si es cliente, solo mostrar Panel Cliente
                if ($userRole !== 'Administrador' && stripos($menu['menombre'], 'panel cliente') !== false) {
                    if ($panelRendered) continue;
                    $panelRendered = true;
                    if (!empty($hijos)) {
                        // Renderizar solo como dropdown
                        ?>
                        <div class="navbar__dropdown">
                            <a href="<?= $menuUrl ?>" class="dropdown-toggle<?= $isActive ? ' active' : '' ?>">
                                <?= htmlspecialchars($menu['menombre']) ?>
                            </a>
                            <div class="dropdown-menu">
                                <?php foreach ($hijos as $hijo): ?>
                                    <a class="dropdown-item" href="<?= htmlspecialchars($hijo['meurl']) ?>">
                                        <?= htmlspecialchars($hijo['menombre']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php
                        continue;
                    } else {
                        // Sin hijos, mostrar solo como enlace normal
                        ?>
                        <a href="<?= $menuUrl ?>" class="<?= $isActive ? 'active' : '' ?>">
                            <?= htmlspecialchars($menu['menombre']) ?>
                        </a>
                        <?php
                        continue;
                    }
                }
                // Si el panel no corresponde al rol, no mostrar
                continue;
            }
            // Menú normal
            else {
                ?>
                <a href="<?= $menuUrl ?>" class="<?= $isActive ? 'active' : '' ?>">
                    <?= htmlspecialchars($menu['menombre']) ?>
                </a>
                <?php
            }
        endforeach;
        ?>
        <?php if (Session::has('user_id')): ?>
            <!-- Botón usuario con icono y nombre -->
            <div class="navbar__dropdown navbar__user" style="margin-right: 8px;">
                <a href="#" class="dropdown-toggle navbar__user-link">
                    <span><?= htmlspecialchars($currentUser) ?></span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="#" class="dropdown-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        Cerrar Sesión
                    </a>
                    <form id="logout-form" action="<?= BASE_URL ?>/logout" method="POST" style="display: none;"></form>
                </div>
            </div>
        <?php else: ?>
            <a href="#" class="navbar__login" onclick="event.preventDefault(); document.getElementById('login-modal').style.display='flex';">
                Ingresar
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>/carrito" class="navbar__carrito <?= strpos($currentPath, '/cart') !== false || strpos($currentPath, '/carrito') !== false ? 'active' : '' ?>">
            <i class="fas fa-shopping-cart fa-lg"></i>
            <!-- El contador solo se muestra cuando hay productos en el carrito -->
            <?php if ($cartCount > 0): ?>
                <span class="cart-counter"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
    </nav>
</header>
