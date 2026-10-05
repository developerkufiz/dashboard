<?php
declare(strict_types=1);

// A file id is base64url("YYYYMMDD/filename"). Every request decodes and strictly validates it,
// so user input never becomes a filesystem path without passing the regex below.

function id_encode(string $rel): string
{
    return rtrim(strtr(base64_encode($rel), '+/', '-_'), '=');
}

function file_ext(string $name): string
{
    return strtolower(pathinfo($name, PATHINFO_EXTENSION));
}

function resolve_id(string $id): ?array
{
    $rel = base64_decode(strtr($id, '-_', '+/'), true);
    if ($rel === false || !preg_match('#^(\d{8})/([A-Za-z0-9][A-Za-z0-9._-]{0,99})$#', $rel, $m) || str_contains($m[2], '..')) {
        return null;
    }
    $ext = file_ext($m[2]);
    if (!in_array($ext, ALLOWED_EXT, true)) {
        return null;
    }
    return ['rel' => $rel, 'date' => $m[1], 'name' => $m[2], 'ext' => $ext, 'path' => UPLOAD_DIR . '/' . $m[1] . '/' . $m[2]];
}

function file_meta(array $f): array
{
    return [
        'id' => id_encode($f['rel']),
        'name' => $f['name'],
        'date' => $f['date'],
        'ext' => $f['ext'],
        'size' => (int) filesize($f['path']),
        'modified' => date('c', (int) filemtime($f['path'])),
        'editable' => in_array($f['ext'], EDITABLE_EXT, true),
    ];
}

function list_files(): array
{
    $out = [];
    foreach (glob(UPLOAD_DIR . '/[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]', GLOB_ONLYDIR) ?: [] as $dir) {
        foreach (scandir($dir) ?: [] as $name) {
            $f = resolve_id(id_encode(basename($dir) . '/' . $name));
            if ($f && is_file($f['path'])) {
                $out[] = file_meta($f);
            }
        }
    }
    usort($out, fn ($a, $b) => strcmp($b['modified'], $a['modified']));
    return $out;
}

function sanitize_name(string $original): string
{
    $name = basename(str_replace('\\', '/', $original));
    $name = preg_replace('/[^A-Za-z0-9._-]+/', '_', $name) ?? '';
    $name = preg_replace('/\.{2,}/', '.', $name) ?? '';
    $name = ltrim($name, '._-');
    $ext = file_ext($name);
    $base = $ext === '' ? $name : substr($name, 0, -(strlen($ext) + 1));
    $base = substr($base, 0, 90) ?: 'file';
    return $ext === '' ? $base : "$base.$ext";
}

// Returns an error string, or null when the content fits its extension.
function check_content(string $ext, string $data): ?string
{
    if (in_array($ext, EDITABLE_EXT, true)) {
        if (str_contains($data, "\0")) {
            return 'File does not look like text';
        }
        if ($ext === 'json') {
            json_decode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return 'Invalid JSON: ' . json_last_error_msg();
            }
        }
        return null;
    }
    $magic = ['png' => "\x89PNG", 'jpg' => "\xFF\xD8\xFF", 'jpeg' => "\xFF\xD8\xFF", 'pdf' => '%PDF', 'glb' => 'glTF', 'xlsx' => "PK"][$ext] ?? null;
    return $magic !== null && str_starts_with($data, $magic) ? null : "File content does not match .$ext";
}

function fmt_size(int $n): string
{
    if ($n < 1024) {
        return "$n B";
    }
    return $n < 1048576 ? round($n / 1024, $n < 10240 ? 1 : 0) . ' KB' : round($n / 1048576, 1) . ' MB';
}
