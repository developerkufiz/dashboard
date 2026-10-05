<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require_role();

page_head('Dashboard');
topbar('dashboard');
?>
<main class="dash" id="dash" data-admin="<?= is_admin() ? "1" : "0" ?>"></main>
<?php page_foot(); ?>
