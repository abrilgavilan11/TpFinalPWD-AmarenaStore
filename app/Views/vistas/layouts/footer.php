    </div> <!-- Fin main-content -->

    <footer class="footer">
        <div class="footer__social">
            <a href="https://instagram.com/" target="_blank">Instagram</a>
            <a href="mailto:abrilcgavilan@gmail.com">Gmail</a>
            <a href="https://wa.me/2995345386" target="_blank">WhatsApp</a>
            <a href="https://www.facebook.com/" target="_blank">Facebook</a>
        </div>
        <div class="footer__info">
            <p>&copy; 2025 Amarena Store. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Modal de Éxito para Contacto -->
    <?php
    $contactSuccess = isset($_GET['success']);
    $successMessage = \App\Utils\Session::flash('success');
    ?>
    <?php if ($contactSuccess && $successMessage): ?>
    <div id="contact-success-modal" class="success-modal">
        <div class="success-modal-content">
            <div class="success-modal-icon">
                <div class="checkmark"></div>
            </div>
            <h2>¡Enviado!</h2>
            <p><?= htmlspecialchars($successMessage) ?></p>
        </div>
    </div>
    <script>
        // Lógica para el modal de éxito de contacto
        const successModal = document.getElementById('contact-success-modal');
        if (successModal) {
            // Después de 3 segundos, añade la clase para iniciar la animación de salida.
            setTimeout(() => {
                successModal.classList.add('slide-out');
            }, 3000);
        }
    </script>
    <?php endif; ?>

    
    <!-- Incluir el modal de login en todas las páginas -->
    <?php require_once VIEWS_PATH . '/vistas/autenticacion/login_modal.php'; ?>

    <script src="<?= BASE_URL ?>/js/funciones.js"></script>
    <?php if (isset($data['extraScripts'])): ?>
        <?php foreach ($data['extraScripts'] as $script): ?>
            <script src="<?= BASE_URL ?>/js/<?= $script ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="<?= BASE_URL ?>/js/main.js"></script>

    <?php
        // Carga el sidebar del carrito.
        // Usamos un bloque try-catch para evitar que un error en el carrito rompa toda la página.
        try {
            $cartModel = new \App\Models\Cart();
            $cartData = $cartModel->getCartContents();
            // Pasamos los datos a la vista del carrito.
            $data['cartItems'] = $cartData['cartItems'] ?? [];
            $data['total'] = $cartData['total'] ?? 0;
            require_once VIEWS_PATH . '/vistas/carrito/cart.php';
        } catch (\Throwable $e) {
            error_log('Error al cargar el sidebar del carrito: ' . $e->getMessage());
        }
    ?>
</body>
</html> 
