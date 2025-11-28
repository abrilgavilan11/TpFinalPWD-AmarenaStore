<?php
require_once VIEWS_PATH . '/vistas/layouts/header.php';
require_once VIEWS_PATH . '/vistas/layouts/navbar.php';
?>
<!-- HERO SECTION - NUEVO DISEÑO -->
<section class="hero">
    <div class="hero__content">
        <div class="hero__text">
            <h1 class="hero__title">Estilo que empodera</h1>
            <h2 class="hero__subtitle">Talles reales. Belleza auténtica.</h2>
            <p class="hero__description">
                Descubrí tu mejor versión con nuestra nueva colección. Moda inclusiva que celebra la diversidad de cada mujer.
            </p>
            <div class="hero__buttons">
                <a href="<?= BASE_URL ?>/productos" class="hero__btn--primary">Ver Colección</a>
                <a href="<?= BASE_URL ?>/about" class="hero__btn--secondary">Conocenos</a>
            </div>
        </div>
        <div class="hero__image">
            <img src="<?= BASE_URL ?>/img/imageninicio.jpeg" alt="Amarena Store">
        </div>
    </div>
</section>

<!-- PRODUCTOS DESTACADOS -->
<section class="featured-products">
    <h2 class="featured-products__title">Productos Destacados</h2>
    <div class="featured-carousel">
        <div class="featured-carousel__track">
            <?php if (!empty($data['featuredProducts'])): ?>
                <?php foreach ($data['featuredProducts'] as $product): ?>
                    <div class="featured-carousel__slide">
                        <div class="featured-card">
                            <div class="featured-card__image">
                                <?php 
                                $imagePath = BASE_URL . '/img/ropa/' . ($product['proimagen'] ?? 'default.jpg');
                                $imageFile = PUBLIC_PATH . '/img/ropa/' . ($product['proimagen'] ?? 'default.jpg');
                                if (!file_exists($imageFile) || empty($product['proimagen'])) {
                                    $imagePath = BASE_URL . '/img/ropa/default.jpg';
                                }
                                ?>
                                <img src="<?= $imagePath ?>" alt="<?= htmlspecialchars($product['pronombre'] ?? '') ?>" />
                                <button class="featured-card__quick-view">+</button>
                            </div>
                            <div class="featured-card__info">
                                <h3 class="featured-card__title"><?= htmlspecialchars($product['pronombre'] ?? '') ?></h3>
                                <p class="featured-card__price">$<?= number_format($product['proprecio'] ?? 0, 0, ',', '.') ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Productos por defecto si no hay en BD -->
                <div class="featured-carousel__slide">
                    <div class="featured-card">
                        <div class="featured-card__image">
                            <img src="<?= BASE_URL ?>/img/ropa/default.jpg" alt="Remera de Ejemplo">
                        </div>
                        <div class="featured-card__info">
                            <h3 class="featured-card__title">Remera Estampada</h3>
                            <p class="featured-card__price">$12.500</p>
                        </div>
                    </div>
                </div>
                <div class="featured-carousel__slide">
                    <div class="featured-card">
                        <div class="featured-card__image">
                            <img src="<?= BASE_URL ?>/img/ropa/default.jpg" alt="Pantalón de Ejemplo">
                        </div>
                        <div class="featured-card__info">
                            <h3 class="featured-card__title">Pantalón Cargo</h3>
                            <p class="featured-card__price">$25.000</p>
                        </div>
                    </div>
                </div>
                <div class="featured-carousel__slide">
                    <div class="featured-card">
                        <div class="featured-card__image">
                            <img src="<?= BASE_URL ?>/img/ropa/default.jpg" alt="Buzo de Ejemplo">
                        </div>
                        <div class="featured-card__info">
                            <h3 class="featured-card__title">Buzo Oversize</h3>
                            <p class="featured-card__price">$28.900</p>
                        </div>
                    </div>
                </div>
                <div class="featured-carousel__slide">
                    <div class="featured-card">
                        <div class="featured-card__image">
                            <img src="<?= BASE_URL ?>/img/ropa/default.jpg" alt="Short de Ejemplo">
                        </div>
                        <div class="featured-card__info">
                            <h3 class="featured-card__title">Short de Jean</h3>
                            <p class="featured-card__price">$18.000</p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <button class="featured-carousel__btn featured-carousel__btn--left">‹</button>
        <button class="featured-carousel__btn featured-carousel__btn--right">›</button>
    </div>
</section>

<!-- FAQ INTEGRADO -->
<section class="faq-section">
    <div class="faq-section__container">
        <h2 class="faq-section__title">Preguntas Frecuentes</h2>
        <div class="faq-accordion">
            <div class="faq-item">
                <button class="faq-question" type="button">
                    <span>¿Cómo puedo realizar un pedido?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Podés realizar tu pedido a través de nuestro WhatsApp (+54 9 299 521-0099) o visitando nuestra tienda física. También podés navegar por nuestro catálogo online y contactarnos directamente con los productos que te interesen.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button">
                    <span>¿Qué talles manejan?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Manejamos talles reales desde XS hasta XXL. Creemos en la moda inclusiva y trabajamos para que todas las mujeres encuentren su talle perfecto.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button">
                    <span>¿Cuáles son los métodos de pago?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Aceptamos efectivo, transferencia bancaria y pagos con QR. Para compras online, generamos un código QR para mayor comodidad y seguridad en tus transacciones.</p>
                </div>
            </div>
            <div class="faq-item">
                <button class="faq-question" type="button">
                    <span>¿Hacen envíos?</span>
                    <span class="faq-icon">+</span>
                </button>
                <div class="faq-answer">
                    <p>Sí, realizamos envíos a toda la provincia de Neuquén. Los costos varían según la distancia. Para Plottier y alrededores, el envío es gratuito en compras superiores a $50.000.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal de agradecimiento por compra -->
<div id="payment-success-modal" class="payment-modal" style="display: none;">
    <div class="payment-modal__content">
        <div class="payment-modal__header">
            <i class="fas fa-check-circle payment-modal__icon"></i>
            <h2>¡Gracias por tu compra!</h2>
        </div>
        <div class="payment-modal__body">
            <p>Tu pedido ha sido procesado exitosamente.</p>
            <p>Nos estaremos comunicando contigo muy pronto para coordinar la entrega.</p>
            <div class="payment-modal__contact">
                <p><i class="fas fa-phone"></i> WhatsApp: +54 9 11 1234-5678</p>
                <p><i class="fas fa-envelope"></i> Email: soporte@amarenastore.com</p>
            </div>
        </div>
        <div class="payment-modal__actions">
            <button type="button" class="payment-modal__btn" onclick="closePaymentModal()">
                <i class="fas fa-heart"></i> ¡Perfecto!
            </button>
        </div>
    </div>
</div>

<style>
.payment-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.payment-modal__content {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    max-width: 500px;
    width: 90%;
    text-align: center;
    position: relative;
    animation: slideIn 0.3s ease;
}

.payment-modal__header {
    margin-bottom: 1.5rem;
}

.payment-modal__icon {
    font-size: 4rem;
    color: #28a745;
    margin-bottom: 1rem;
    animation: pulse 2s infinite;
}

.payment-modal__header h2 {
    color: var(--color-primary);
    font-family: 'Raleway', sans-serif;
    font-weight: 700;
    margin: 0;
}

.payment-modal__body p {
    color: var(--color-text);
    font-family: 'Raleway', sans-serif;
    font-weight: 500;
    margin: 1rem 0;
    line-height: 1.6;
}

.payment-modal__contact {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 1rem;
    margin: 1.5rem 0;
}

.payment-modal__contact p {
    margin: 0.5rem 0;
    color: var(--color-primary);
    font-weight: 600;
}

.payment-modal__btn {
    background: linear-gradient(135deg, var(--color-primary), var(--color-accent));
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: 'Raleway', sans-serif;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin: 0 auto;
}

.payment-modal__btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(217, 106, 126, 0.4);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        transform: translateY(-50px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
</style>

<script>
// Verificar si se debe mostrar el modal de pago exitoso
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('payment_success') === '1') {
        document.getElementById('payment-success-modal').style.display = 'flex';
        
        // Limpiar URL sin recargar la página
        const url = new URL(window.location);
        url.searchParams.delete('payment_success');
        window.history.replaceState({}, document.title, url);
    }
});

function closePaymentModal() {
    document.getElementById('payment-success-modal').style.display = 'none';
}

// Cerrar modal al hacer clic fuera del contenido
document.getElementById('payment-success-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closePaymentModal();
    }
});
</script>

<?php
require_once VIEWS_PATH . '/vistas/layouts/footer.php';
?>
