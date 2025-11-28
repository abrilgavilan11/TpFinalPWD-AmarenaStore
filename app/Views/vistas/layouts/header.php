<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'Amarena Store - Moda para todas' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/base.css">
    <!-- Añadimos Bootstrap para estilos de tablas, tarjetas y botones -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <?php if (isset($data['pageCss'])): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/<?= $data['pageCss'] ?>.css?v=<?= time() ?>">
    <?php endif; ?>
    <!-- CSS del flujo de checkout (solo cargar si es necesario) -->
    <?php if (isset($data['pageCss']) && $data['pageCss'] === 'checkout-flow'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/css/checkout-flow.css?v=<?= time() ?>">
    <?php endif; ?>
    <!-- Favicon y iconos para diferentes dispositivos -->
    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/img/logo-AmarenaStore.svg">
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico">
    <link rel="shortcut icon" href="<?= BASE_URL ?>/favicon.ico">
    <!-- Para dispositivos Apple -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/img/logo-AmarenaStore.svg">
    <!-- Para Android Chrome -->
    <meta name="theme-color" content="#d96a7e">
    <!-- Meta tags para mejor SEO -->
    <meta name="description" content="Amarena Store - Moda femenina de calidad. Descubre nuestra colección de ropa y accesorios.">
    <meta name="keywords" content="moda femenina, ropa mujer, accesorios, Amarena Store, tienda online">
    <meta name="author" content="Amarena Store">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Raleway ya está incluido en base.css -->
    <!-- FontAwesome 6 - Versión más reciente y estable -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- FontAwesome respaldo desde jsdelivr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css">
    <!-- Bootstrap Icons para los iconos de la interfaz -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <!-- CSS de respaldo para iconos -->
    <style>
        /* Respaldo para cuando los iconos no cargan */
        .fas, .far, .fab, .fal, .fad, .fa {
            font-family: "Font Awesome 6 Free", "Font Awesome 5 Free", "FontAwesome", sans-serif;
            font-weight: 900;
            font-style: normal;
            display: inline-block;
            text-rendering: auto;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Si FontAwesome falla, mostrar símbolos unicode alternativos */
        .fa-user::before { content: "👤" !important; }
        .fa-shopping-cart::before { content: "🛒" !important; }
        .fa-shopping-bag::before { content: "🛍️" !important; }
        .fa-tachometer-alt::before { content: "📊" !important; }
        .fa-calendar::before { content: "📅" !important; }
        .fa-check-circle::before { content: "✅" !important; }
        .fa-clock::before { content: "⏰" !important; }
        .fa-truck::before { content: "🚚" !important; }
        .fa-eye::before { content: "👁️" !important; }
        .fa-edit::before { content: "✏️" !important; }
        .fa-times::before { content: "❌" !important; }
        .fa-arrow-left::before { content: "←" !important; }
        .fa-download::before { content: "⬇️" !important; }
        .fa-receipt::before { content: "🧾" !important; }
        .fa-info-circle::before { content: "ℹ️" !important; }
        .fa-hashtag::before { content: "#" !important; }
        .fa-dollar-sign::before { content: "$" !important; }
        .fa-box::before { content: "📦" !important; }
        .fa-history::before { content: "🕒" !important; }
        .fa-check::before { content: "✓" !important; }
        .fa-check-double::before { content: "✓✓" !important; }
        .fa-question::before { content: "?" !important; }
        .fa-image::before { content: "🖼️" !important; }
        .fa-plus::before { content: "+" !important; }
        .fa-headset::before { content: "🎧" !important; }
        .fa-user-edit::before { content: "👤✏️" !important; }
    </style>

</head>
<body>
    <?php
    require_once VIEWS_PATH . '/vistas/layouts/notification_modal.php';
    ?>
    <div class="main-content">
