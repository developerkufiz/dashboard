<?php
declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/files.php';
require __DIR__ . '/../inc/data.php';

// Dashboard data for guests and admins.
//   GET ?d=<key>            which dashboard (default: the default / first one)
//   &from=YYYY-MM-DD&to=..  date range, &f[Column]=Value  category filters
//   &export=csv             download the filtered rows
// With no dashboards configured the result is empty (the page shows an empty state).
if (role() === '') {
    json_out(['error' => 'Authentication required'], 401);
}
session_write_close();

$reg = dash_load();
$list = array_map(fn ($i) => ['key' => $i['key'], 'title' => $i['title'] ?? 'Dashboard'], $reg['items']);
$item = dash_pick($reg, (string) ($_GET['d'] ?? ''));
if (!$item) {
    json_out(['dashboards' => [], 'current' => null]);
}

try {
    [$headers, $all] = dash_table($item);
    $cols = analyse($headers, $all);
    $layout = resolve_layout($headers, $cols, $all, (array) ($item['config'] ?? []));
    $rows = apply_filters($headers, $all, $layout, $_GET);

    if (($_GET['export'] ?? '') === 'csv') {
        $safe = fn (string $v) => preg_match('/^[=+@\t\r]/', $v) ? "'" . $v : $v; // neutralise spreadsheet formulas
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . preg_replace('/[^A-Za-z0-9._-]+/', '_', $item['title'] ?? 'data') . '.csv"');
        $out = fopen('php://output', 'wb');
        fputcsv($out, array_map($safe, $headers), ',', '"', '');
        foreach ($rows as $r) {
            fputcsv($out, array_map($safe, $r), ',', '"', '');
        }
        exit;
    }

    json_out([
        'dashboards' => $list,
        'current' => $item['key'],
        'title' => $item['title'] ?? 'Dashboard',
        'source' => ['name' => ($item['type'] ?? '') === 'url' ? 'Google Sheets' : ($item['name'] ?? 'File'), 'rows' => count($rows), 'total' => count($all), 'columns' => count($headers)],
        'filters' => filter_info($headers, $all, $layout),
    ] + build_widgets($headers, $rows, $layout));
} catch (Throwable $e) {
    json_out(['dashboards' => $list, 'current' => $item['key'], 'title' => $item['title'] ?? 'Dashboard', 'error' => $e->getMessage()]);
}
