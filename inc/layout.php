<?php
declare(strict_types=1);
require_once __DIR__ . '/ui.php';

const ICONS = [
    'expand' => ['M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5'],
    'compress' => ['M9 4v5H4M15 4v5h5M9 20v-5H4M15 20v-5h5'],
    'dash' => ['M4 4h7v9H4zM13 4h7v5h-7zM13 11h7v9h-7zM4 15h7v5H4z'],
    'shield' => ['M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z'],
    'eye' => ['M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12z', 'M12 15a3 3 0 100-6 3 3 0 000 6z'],
    'logout' => ['M10 4H5v16h5', 'M15 8l4 4-4 4M19 12H9'],
    'search' => ['M11 18a7 7 0 100-14 7 7 0 000 14z', 'M20 20l-4-4'],
    'upload' => ['M12 16V4M7 9l5-5 5 5', 'M4 20h16'],
    'more' => ['M12 5h.01M12 12h.01M12 19h.01'],
    'x' => ['M6 6l12 12M18 6L6 18'],
    'json' => ['M8 4C6 4 6 6 6 8s0 3-2 4c2 1 2 2 2 4s0 4 2 4', 'M16 4c2 0 2 2 2 4s0 3 2 4c-2 1-2 2-2 4s0 4-2 4'],
    'csv' => ['M4 5h16v14H4z', 'M4 10h16M4 15h16M10 5v14'],
    'txt' => ['M6 3h9l4 4v14H6z', 'M9 11h7M9 15h7'],
    'image' => ['M4 5h16v14H4z', 'M4 16l5-5 4 4 3-3 4 4', 'M9 9h.01'],
    'pdf' => ['M6 3h9l4 4v14H6z', 'M9 14h6M9 17h4'],
    'file' => ['M6 3h9l4 4v14H6z'],
    'edit' => ['M4 20l1-5L16 4l4 4L9 19z'],
    'download' => ['M12 4v12M7 11l5 5 5-5', 'M4 20h16'],
    'trash' => ['M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13'],
];

function icon(string $name, int $size = 16): string
{
    $paths = '';
    foreach (ICONS[$name] ?? ICONS['file'] as $d) {
        $paths .= '<path d="' . $d . '"/>';
    }
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $paths . '</svg>';
}

function page_head(string $title, string $bodyClass = ''): void
{
    ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf" content="<?= e(csrf()) ?>">
  <link rel="icon" href="data:,">
  <?php ui_css(); ?>
  <title><?= e($title) ?></title>
</head>
<body class="<?= e($bodyClass) ?>">
<?php
}

function topbar(string $active): void
{
    ?>
<div class="app">
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-mark"></span><span class="brand-name">Dashboard</span></a>
  <nav class="nav" aria-label="Main">
    <?php if (is_admin()) : ?>
      <a href="admin.php" class="<?= $active === 'admin' ? 'on' : '' ?>"><?= icon('shield') ?><span>Administration</span></a>
    <?php endif; ?>
  </nav>
  <div class="spacer"></div>
  <div class="clock mono" id="clock"></div>
  <button type="button" class="icon-btn" id="fs-btn" title="Fullscreen" aria-label="Toggle fullscreen"><span class="fs-in"><?= icon('expand', 18) ?></span><span class="fs-out"><?= icon('compress', 18) ?></span></button>
  <form method="post" action="logout.php">
    <input type="hidden" name="csrf" value="<?= e(csrf()) ?>">
    <button class="user-btn" title="Sign out"><?= icon('logout') ?><span class="role">Logout</span></button>
  </form>
</header>
<?php
}

function page_foot(): void
{
    echo "</div>\n";
    ui_js();
    echo "</body>\n</html>\n";
}
