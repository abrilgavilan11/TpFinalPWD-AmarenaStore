<?php
$loginError = \App\Utils\Session::flash('login_error');
// El modal se mostrará si hay un error de login en la URL.
$showLoginModal = isset($_GET['login_error']);
?>

<div id="login-modal" class="modal" style="<?= $showLoginModal ? 'display: flex;' : 'display: none;' ?>">
    <div class="modal-content">
        <span class="modal-close" onclick="document.getElementById('login-modal').style.display='none'">&times;</span>
        <h2>Iniciar Sesión</h2>
        <?php if ($loginError): ?>
            <div class="alert alert-error"><?= htmlspecialchars($loginError) ?></div>
        <?php endif; ?>
        <form action="<?= BASE_URL ?>/login" method="POST">
            <div class="form-group">
                <label for="login-email">Email:</label>
                <input type="email" id="login-email" name="email" required>
            </div>
            <div class="form-group">
                <label for="login-password">Contraseña:</label>
                <input type="password" id="login-password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Ingresar</button>
        </form>
    </div>
</div>