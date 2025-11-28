<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>

<section class="about-page">
    <div class="about-page__container">
        <div class="about-page__header">
            <h1>¿Quiénes Somos?</h1>
            <p class="about-page__subtitle">Moda inclusiva para todas</p>
        </div>
        
        <!-- Mejorar layout con grid para logo y cards -->
        <div class="about-page__content">
            <div class="about-page__logo">
                <img src="<?= BASE_URL ?>/img/logo-AmarenaStore.svg" alt="Logo Amarena Store" class="about-page__logo-img">
            </div>
            
            <div class="about-page__info">
                <div class="about-page__card">
                    <h2>MODA INCLUSIVA | CALIDAD & TENDENCIA</h2>
                    <p>
                        Amarena Store nació con la misión de vestir a todas las mujeres, celebrando la diversidad de cuerpos y estilos.
                        Ofrecemos talles reales y prendas para cada ocasión, desde fiestas hasta looks casuales de temporada. 
                        Seleccionamos materiales de calidad y seguimos las últimas tendencias para que te sientas única.
                    </p>
                </div>
                
                <div class="about-page__card">
                    <h3>Nuestra Misión</h3>
                    <p>
                        Creemos que la moda debe ser accesible para todas. Por eso trabajamos con talles reales, 
                        desde XS hasta XXL, asegurándonos de que cada mujer encuentre prendas que la hagan sentir 
                        cómoda y segura de sí misma.
                    </p>
                </div>
                
                <div class="about-page__card">
                    <h3>Nuestros Valores</h3>
                    <ul>
                        <li>Inclusión y diversidad</li>
                        <li>Calidad en cada prenda</li>
                        <li>Tendencias actuales</li>
                        <li>Atención personalizada</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
