<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';

if (is_admin()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['mode'] ?? '') === 'guest') {
        start_session_as('guest');
        header('Location: index.php');
        exit;
    }
    $user = (string) ($_POST['username'] ?? '');
    $pass = (string) ($_POST['password'] ?? '');
    if (ADMIN_PASSWORD === '') {
        $error = 'Admin login is not configured (config/credentials.json).';
    } elseif (throttled()) {
        $error = 'Too many attempts. Try again in 15 minutes.';
    } elseif (hash_equals(ADMIN_USERNAME, $user) & password_ok($pass)) {
        throttle_clear();
        start_session_as('admin');
        header('Location: index.php');
        exit;
    } else {
        throttle_fail();
        usleep(400000);
        $error = 'Invalid username or password';
    }
}
$tab = ($_POST['mode'] ?? '') === 'admin' ? 'admin' : 'guest';

page_head('Dashboard - Sign in', 'login-page');
?>
<div class="login">
  <div class="login-card">
    <div class="brand brand-lg"><span class="brand-mark"></span>Dashboard</div>
    <p class="muted">Sign in to continue</p>

    <div class="seg seg-full" role="tablist" aria-label="Access level">
      <button type="button" role="tab" data-tab="guest" class="<?= $tab === 'guest' ? 'on' : '' ?>"><?= icon('eye') ?> Guest</button>
      <button type="button" role="tab" data-tab="admin" class="<?= $tab === 'admin' ? 'on' : '' ?>"><?= icon('shield') ?> Admin</button>
    </div>

    <form method="post" class="login-body" data-panel="guest" <?= $tab === 'guest' ? '' : 'hidden' ?>>
      <p>View-only access to KPIs, charts and tables. No password required.</p>
      <input type="hidden" name="mode" value="guest">
      <button class="btn primary wide">Continue as Guest</button>
    </form>

    <form method="post" class="login-body" data-panel="admin" <?= $tab === 'admin' ? '' : 'hidden' ?>>
      <input type="hidden" name="mode" value="admin">
      <label>Username <input name="username" autocomplete="username" required></label>
      <label>Password <input type="password" name="password" autocomplete="current-password" required></label>
      <?php if ($error) : ?><div class="form-error" role="alert"><?= e($error) ?></div><?php endif; ?>
      <button class="btn primary wide">Sign in</button>
    </form>
  </div>
</div>
<?php ui_js(); ?>
</body>
</html>
