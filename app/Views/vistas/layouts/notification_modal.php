<?php
$success = \App\Utils\Session::flash('success');
$error = \App\Utils\Session::flash('error');

$message = $success ?? $error;
$type = $success ? 'success' : 'error';
?>

<?php if ($message): ?>
<div id="notification-modal" class="notification-modal notification-modal--<?= $type ?>">
    <div class="notification-modal__content">
        <p><?= htmlspecialchars($message) ?></p>
    </div>
</div>
<script>
    setTimeout(() => document.getElementById('notification-modal')?.classList.add('hide'), 3000);
</script>
<?php endif; ?>