<?php
declare(strict_types=1);
require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/files.php';
require __DIR__ . '/../inc/data.php';

// Admin-only API.
// Files:      GET ?action=list|get|download|raw&id=...   POST ?action=upload|save|delete
// Dashboards: GET ?action=dash_columns&key=...           POST ?action=dash_add|dash_config|dash_default|dash_remove
// Writes need the CSRF header X-CSRF-Token (or Bearer ADMIN_TOKEN).
require_admin_api();
session_write_close();

$action = $_GET['action'] ?? 'list';
$isPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$body = $isPost && $action !== 'upload' ? (json_decode((string) file_get_contents('php://input'), true) ?: []) : [];
$id = (string) ($body['id'] ?? $_GET['id'] ?? '');

function target(string $id): array
{
    $f = resolve_id($id);
    if (!$f || !is_file($f['path'])) {
        json_out(['error' => 'File not found'], 404);
    }
    return $f;
}

function dash_or_404(array $reg, string $key): array
{
    return dash_find($reg, $key) ?? json_out(['error' => 'Dashboard not found'], 404);
}

if (!$isPost) {
    switch ($action) {
        case 'list':
            json_out(list_files());
        case 'get':
            $f = target($id);
            json_out(file_meta($f) + ['content' => in_array($f['ext'], EDITABLE_EXT, true) ? file_get_contents($f['path']) : null]);
        case 'download':
            $f = target($id);
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $f['name'] . '"');
            header("Content-Security-Policy: sandbox");
            header('Content-Length: ' . filesize($f['path']));
            readfile($f['path']);
            exit;
        case 'raw': // inline preview, images only
            $f = target($id);
            if (!in_array($f['ext'], IMAGE_EXT, true)) {
                json_out(['error' => 'Not an image'], 415);
            }
            header('Content-Type: ' . ($f['ext'] === 'png' ? 'image/png' : 'image/jpeg'));
            header("Content-Security-Policy: default-src 'none'; sandbox");
            readfile($f['path']);
            exit;
        case 'dash_columns':
            $item = dash_or_404(dash_load(), (string) ($_GET['key'] ?? ''));
            try {
                [$headers, $rows] = dash_table($item);
                json_out([
                    'title' => $item['title'] ?? '', 'config' => $item['config'] ?? new stdClass(),
                    'columns' => column_info($headers, analyse($headers, $rows)), 'summary' => layout_summary($headers, $rows, (array) ($item['config'] ?? [])),
                ]);
            } catch (Throwable $e) {
                json_out(['error' => $e->getMessage()], 400);
            }
    }
    json_out(['error' => 'Unknown action'], 400);
}

switch ($action) {
    case 'upload':
        $up = $_FILES['file'] ?? null;
        if (!$up || $up['error'] === UPLOAD_ERR_NO_FILE) {
            json_out(['error' => 'No file provided'], 400);
        }
        if ($up['error'] === UPLOAD_ERR_INI_SIZE || $up['error'] === UPLOAD_ERR_FORM_SIZE || $up['size'] > MAX_UPLOAD_BYTES) {
            json_out(['error' => 'File exceeds ' . (MAX_UPLOAD_BYTES / 1048576) . ' MB limit'], 413);
        }
        if ($up['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($up['tmp_name'])) {
            json_out(['error' => 'Upload failed'], 400);
        }
        $name = sanitize_name((string) $up['name']);
        $ext = file_ext($name);
        if (!in_array($ext, ALLOWED_EXT, true)) {
            json_out(['error' => 'File type .' . ($ext ?: '?') . ' is not allowed'], 415);
        }
        $data = (string) file_get_contents($up['tmp_name']);
        if ($bad = check_content($ext, $data)) {
            json_out(['error' => $bad], 415);
        }

        $date = date('Ymd'); // server clock, never the browser's
        $dir = UPLOAD_DIR . '/' . $date;
        if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
            json_out(['error' => 'Could not create upload folder'], 500);
        }
        $stem = substr($name, 0, -(strlen($ext) + 1));
        for ($i = 0; $i < 1000; $i++) {
            $candidate = $i ? "$stem-$i.$ext" : $name;
            $f = resolve_id(id_encode("$date/$candidate"));
            if (!$f) {
                json_out(['error' => 'Invalid file name'], 400);
            }
            $h = @fopen($f['path'], 'xb'); // x: fails if it exists - never overwrite
            if ($h) {
                fwrite($h, $data);
                fclose($h);
                json_out(file_meta($f), 201);
            }
        }
        json_out(['error' => 'Could not allocate a unique file name'], 409);

    case 'save':
        $f = target($id);
        if (!in_array($f['ext'], EDITABLE_EXT, true)) {
            json_out(['error' => 'This file type cannot be edited'], 415);
        }
        $content = $body['content'] ?? null;
        if (!is_string($content) || strlen($content) > MAX_UPLOAD_BYTES) {
            json_out(['error' => 'content must be a string under the size limit'], 400);
        }
        if ($bad = check_content($f['ext'], $content)) {
            json_out(['error' => $bad], 400);
        }
        file_put_contents($f['path'], $content, LOCK_EX);
        clearstatcache();
        json_out(file_meta($f));

    case 'delete':
        $f = target($id);
        $rel = $f['rel'];
        dash_remove_where(fn ($i) => ($i['type'] ?? '') === 'file' && ($r = resolve_id((string) ($i['id'] ?? ''))) && $r['rel'] === $rel);
        unlink($f['path']);
        @rmdir(dirname($f['path'])); // drop the date folder once empty
        http_response_code(204);
        exit;

    case 'dash_add': // {type:'file', id} or {type:'url', url, title?}
        $reg = dash_load();
        if (count($reg['items']) >= 12) {
            json_out(['error' => 'Limit of 12 dashboards reached'], 400);
        }
        if (($body['type'] ?? '') === 'url') {
            $url = trim((string) ($body['url'] ?? ''));
            if (!remote_allowed($url)) {
                json_out(['error' => 'Use an https Google Sheets link (File > Share > Publish to web > CSV)'], 400);
            }
            $item = ['type' => 'url', 'url' => $url, 'name' => 'Google Sheets', 'title' => trim((string) ($body['title'] ?? '')) ?: 'Google Sheet'];
        } else {
            $f = target($id);
            if (!in_array($f['ext'], ['csv', 'xlsx'], true)) {
                json_out(['error' => 'Only CSV or XLSX files can drive a dashboard'], 415);
            }
            $item = ['type' => 'file', 'id' => id_encode($f['rel']), 'name' => $f['name'], 'title' => pathinfo($f['name'], PATHINFO_FILENAME)];
        }
        try {
            [$h, $r] = dash_table($item);
        } catch (Throwable $e) {
            json_out(['error' => $e->getMessage()], 400);
        }
        $item['key'] = bin2hex(random_bytes(4));
        $item['config'] = new stdClass();
        $reg['items'][] = $item;
        if ($reg['default'] === '') {
            $reg['default'] = $item['key'];
        }
        dash_save($reg);
        json_out(['ok' => true, 'key' => $item['key'], 'rows' => count($r), 'columns' => count($h), 'summary' => layout_summary($h, $r, [])]);

    case 'dash_rename': // {key, title}
        $reg = dash_load();
        dash_or_404($reg, (string) ($body['key'] ?? ''));
        $title = trim((string) ($body['title'] ?? ''));
        if ($title === '') {
            json_out(['error' => 'Name cannot be empty'], 400);
        }
        foreach ($reg['items'] as &$it) {
            if ($it['key'] === $body['key']) {
                $it['title'] = mb_substr($title, 0, 60);
            }
        }
        unset($it);
        dash_save($reg);
        json_out(['ok' => true]);

    case 'dash_config': // {key, title, config}
        $reg = dash_load();
        dash_or_404($reg, (string) ($body['key'] ?? ''));
        try {
            [$headers] = dash_table(dash_find($reg, $body['key']));
        } catch (Throwable $e) {
            json_out(['error' => $e->getMessage()], 400);
        }
        foreach ($reg['items'] as &$it) {
            if ($it['key'] === $body['key']) {
                $title = trim((string) ($body['title'] ?? ''));
                $it['title'] = $title !== '' ? mb_substr($title, 0, 60) : $it['title'];
                $it['config'] = clean_config((array) ($body['config'] ?? []), $headers);
            }
        }
        unset($it);
        dash_save($reg);
        json_out(['ok' => true]);

    case 'dash_default':
        $reg = dash_load();
        dash_or_404($reg, (string) ($body['key'] ?? ''));
        $reg['default'] = $body['key'];
        dash_save($reg);
        json_out(['ok' => true]);

    case 'dash_remove':
        $reg = dash_load();
        dash_or_404($reg, (string) ($body['key'] ?? ''));
        $reg['items'] = array_values(array_filter($reg['items'], fn ($i) => $i['key'] !== $body['key']));
        if ($reg['default'] === $body['key']) {
            $reg['default'] = '';
        }
        dash_save($reg);
        json_out(['ok' => true]);
}
json_out(['error' => 'Unknown action'], 400);
