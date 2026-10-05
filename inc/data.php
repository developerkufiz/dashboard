<?php
declare(strict_types=1);

// Dashboards built automatically from an uploaded CSV / XLSX or a published Google Sheets CSV link.
// Defaults: numbers -> KPI cards, date column -> line chart, text column -> bar + donut, first rows -> table.
// A per-dashboard "config" (column names) overrides any of those choices. No database: a JSON registry
// in uploads/ lists the dashboards.

const MAX_ROWS = 5000;
const MAX_COLS = 30;
const DASH_FILE = UPLOAD_DIR . '/_dashboards.json';

// ---- dashboard registry ----------------------------------------------------------------------
function dash_load(): array
{
    $d = json_decode((string) @file_get_contents(DASH_FILE), true);
    $items = is_array($d['items'] ?? null) ? array_values(array_filter($d['items'], 'is_array')) : [];
    return ['default' => (string) ($d['default'] ?? ''), 'items' => $items];
}

function dash_save(array $d): void
{
    file_put_contents(DASH_FILE, json_encode($d, JSON_UNESCAPED_SLASHES), LOCK_EX);
}

function dash_find(array $d, string $key): ?array
{
    foreach ($d['items'] as $i) {
        if (($i['key'] ?? '') === $key) {
            return $i;
        }
    }
    return null;
}

function dash_pick(array $d, string $key): ?array
{
    return dash_find($d, $key) ?? dash_find($d, $d['default']) ?? ($d['items'][0] ?? null);
}

function dash_remove_where(callable $match): void
{
    $d = dash_load();
    $d['items'] = array_values(array_filter($d['items'], fn ($i) => !$match($i)));
    dash_save($d);
}

// ---- remote CSV (Google Sheets "Publish to web") ----------------------------------------------
function remote_allowed(string $url): bool
{
    $p = parse_url($url);
    return ($p['scheme'] ?? '') === 'https' && preg_match('/^(docs\.google\.com|[a-z0-9-]+\.googleusercontent\.com)$/i', $p['host'] ?? '') === 1;
}

// Returns a local cache path (refetched after 5 minutes). Only Google hosts are contacted, redirects included.
function fetch_remote(string $url): string
{
    $cache = UPLOAD_DIR . '/_remote_' . md5($url) . '.csv';
    if (is_file($cache) && filemtime($cache) > time() - 300) {
        return $cache;
    }
    if (!function_exists('curl_init')) {
        throw new RuntimeException('PHP curl extension is not enabled');
    }
    for ($hop = 0; $hop < 4; $hop++) {
        if (!remote_allowed($url)) {
            throw new RuntimeException('Only https Google Sheets links are allowed');
        }
        $body = '';
        $location = null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => false, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS, CURLOPT_USERAGENT => 'dashboard/1.0',
            CURLOPT_HEADERFUNCTION => function ($c, $h) use (&$location) {
                if (stripos($h, 'location:') === 0) {
                    $location = trim(substr($h, 9));
                }
                return strlen($h);
            },
            CURLOPT_WRITEFUNCTION => function ($c, $chunk) use (&$body) {
                $body .= $chunk;
                return strlen($body) > MAX_UPLOAD_BYTES ? 0 : strlen($chunk); // abort when too large
            },
        ]);
        $ok = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($code >= 300 && $code < 400 && $location) {
            $url = $location;
            continue;
        }
        if ($ok === false || $code !== 200) {
            throw new RuntimeException($err !== '' ? "Download failed: $err" : "Download failed (HTTP $code)");
        }
        if (stripos($body, '<html') !== false && stripos($body, '<html') < 200) {
            throw new RuntimeException('That link returned a web page, not CSV. Use File > Share > Publish to web > CSV.');
        }
        file_put_contents($cache, $body, LOCK_EX);
        return $cache;
    }
    throw new RuntimeException('Too many redirects');
}

// ---- readers ----------------------------------------------------------------------------------
function dash_table(array $item): array
{
    if (($item['type'] ?? '') === 'url') {
        return read_table(fetch_remote((string) $item['url']), 'csv');
    }
    $f = resolve_id((string) ($item['id'] ?? ''));
    if (!$f || !is_file($f['path'])) {
        throw new RuntimeException('The source file no longer exists');
    }
    return read_table($f['path'], $f['ext']);
}

function read_table(string $path, string $ext): array
{
    $rows = $ext === 'xlsx' ? read_xlsx($path) : read_csv($path);
    $rows = array_values(array_filter($rows, fn ($r) => trim(implode('', $r)) !== ''));
    if (count($rows) < 2) {
        throw new RuntimeException('Need a header row and at least one data row');
    }
    $headers = [];
    foreach (array_slice($rows[0], 0, MAX_COLS) as $i => $h) {
        $h = trim((string) $h) !== '' ? trim((string) $h) : 'Column ' . ($i + 1);
        while (in_array($h, $headers, true)) {
            $h .= '_';
        }
        $headers[] = $h;
    }
    $n = count($headers);
    $data = array_map(fn ($r) => array_map('trim', array_slice(array_pad($r, $n, ''), 0, $n)), array_slice($rows, 1, MAX_ROWS));
    return [$headers, $data];
}

function read_csv(string $path): array
{
    $h = fopen($path, 'rb');
    $first = preg_replace('/^\xEF\xBB\xBF/', '', (string) fgets($h));
    $delim = ',';
    foreach ([';', "\t"] as $d) {
        if (substr_count($first, $d) > substr_count($first, $delim)) {
            $delim = $d;
        }
    }
    rewind($h);
    $rows = [];
    while (($r = fgetcsv($h, 0, $delim, '"', '')) !== false && count($rows) <= MAX_ROWS + 1) {
        $rows[] = array_map('strval', $r);
    }
    fclose($h);
    if ($rows) {
        $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);
    }
    return $rows;
}

function read_xlsx(string $path): array
{
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        throw new RuntimeException('Not a valid .xlsx file');
    }
    $sheet = null;
    for ($i = 0; $i < $zip->numFiles; $i++) { // first worksheet
        $s = $zip->statIndex($i);
        if (preg_match('#^xl/worksheets/[^/]+\.xml$#', $s['name'])) {
            if ($s['size'] > 30 * 1048576) {
                throw new RuntimeException('Sheet too large');
            }
            $sheet = $sheet === null || strnatcmp($s['name'], $sheet) < 0 ? $s['name'] : $sheet;
        }
    }
    if ($sheet === null) {
        throw new RuntimeException('No worksheet found');
    }
    $xml = fn (string $n) => ($raw = $zip->getFromName($n)) === false ? null : simplexml_load_string($raw, 'SimpleXMLElement', LIBXML_NONET);

    $shared = [];
    if ($ss = $xml('xl/sharedStrings.xml')) {
        foreach ($ss->si as $si) {
            $t = isset($si->t) ? (string) $si->t : '';
            foreach ($si->r as $r) {
                $t .= (string) $r->t;
            }
            $shared[] = $t;
        }
    }
    $ws = $xml($sheet);
    $rows = [];
    foreach ($ws->sheetData->row ?? [] as $row) {
        $r = [];
        foreach ($row->c as $c) {
            preg_match('/^([A-Z]+)/', (string) $c['r'], $m);
            $idx = 0;
            foreach (str_split($m[1] ?? 'A') as $ch) {
                $idx = $idx * 26 + ord($ch) - 64;
            }
            $t = (string) $c['t'];
            $r[$idx - 1] = $t === 's' ? ($shared[(int) $c->v] ?? '') : ($t === 'inlineStr' ? (string) $c->is->t : (string) $c->v);
        }
        if ($r) {
            $out = [];
            for ($i = 0, $max = min(max(array_keys($r)), MAX_COLS - 1); $i <= $max; $i++) {
                $out[] = $r[$i] ?? '';
            }
            $rows[] = $out;
        }
        if (count($rows) > MAX_ROWS + 1) {
            break;
        }
    }
    return $rows;
}

// ---- column analysis --------------------------------------------------------------------------
function to_num(string $v): ?float
{
    $v = str_replace([',', ' ', '$', '€', '£', '%'], '', trim($v));
    return preg_match('/^-?\d+(\.\d+)?$/', $v) ? (float) $v : null;
}

function to_date(string $v, string $header): ?int
{
    $v = trim($v);
    if ($v === '') {
        return null;
    }
    if (is_numeric($v)) { // Excel serial date (only trusted when the header says so)
        return preg_match('/date|day|time|month/i', $header) && $v > 20000 && $v < 80000 ? (int) (($v - 25569) * 86400) : null;
    }
    if (!preg_match('/\d/', $v)) {
        return null;
    }
    $t = strtotime($v);
    return $t === false ? null : $t;
}

function analyse(array $headers, array $rows): array
{
    $cols = [];
    foreach ($headers as $i => $h) {
        $vals = array_filter(array_column($rows, $i), fn ($v) => $v !== '');
        $n = max(1, count($vals));
        $num = count(array_filter($vals, fn ($v) => to_num($v) !== null));
        $date = count(array_filter($vals, fn ($v) => to_date($v, $h) !== null));
        $id = (bool) preg_match('/(^|[\s_])(id|code|zip|phone|no|number|year)$/i', $h);
        $isDate = $date / $n >= 0.9 && ($num / $n < 0.9 || preg_match('/date|day|time|month/i', $h));
        $cols[$i] = [
            'name' => $h,
            'type' => $isDate ? 'date' : ($num / $n >= 0.9 && !$id ? 'number' : 'text'),
            'distinct' => count(array_unique($vals)),
        ];
    }
    return $cols;
}

function is_avg_name(string $h): bool
{
    return (bool) preg_match('/rate|avg|average|price|score|percent|ratio|margin|%/i', $h);
}

// ---- layout (auto choices + config overrides) ---------------------------------------------------
function resolve_layout(array $headers, array $cols, array $rows, array $config): array
{
    $idx = function ($name) use ($headers): ?int {
        $i = is_string($name) ? array_search($name, $headers, true) : false;
        return $i === false ? null : (int) $i;
    };
    $num = fn (string $v) => to_num($v) ?? 0.0;
    $autoDate = null;
    $nums = [];
    $cats = [];
    foreach ($cols as $i => $c) {
        if ($c['type'] === 'date' && $autoDate === null) {
            $autoDate = $i;
        } elseif ($c['type'] === 'number') {
            $nums[] = $i;
        } elseif ($c['type'] === 'text' && $c['distinct'] >= 2 && $c['distinct'] <= 30 && $c['distinct'] < count($rows)) {
            $cats[] = $i;
        }
    }

    $date = array_key_exists('date', $config) ? ($config['date'] === '' ? null : ($idx($config['date']) ?? $autoDate)) : $autoDate;

    $kpis = [];
    foreach ((array) ($config['kpis'] ?? []) as $k) {
        $i = $idx($k['col'] ?? null);
        if ($i !== null && count($kpis) < 4) {
            $kpis[] = ['col' => $i, 'agg' => in_array($k['agg'] ?? '', ['sum', 'avg'], true) ? $k['agg'] : 'auto'];
        }
    }
    if (!$kpis) {
        $kpis = array_map(fn ($i) => ['col' => $i, 'agg' => 'auto'], array_slice($nums, 0, 4));
    }

    $line = array_slice(array_values(array_filter(array_map($idx, (array) ($config['line'] ?? [])), fn ($i) => $i !== null)), 0, 2);
    if (!$line && $nums) {
        $line = [$nums[0]];
        if (isset($nums[1])) { // share one axis only when the two columns are on a similar scale
            $mx = fn (int $i) => max(1.0, ...array_map($num, array_column($rows, $i)));
            $ratio = $mx($nums[0]) / $mx($nums[1]);
            if ($ratio <= 10 && $ratio >= 0.1) {
                $line[] = $nums[1];
            }
        }
    }

    $cat = array_key_exists('category', $config) ? ($config['category'] === '' ? null : ($idx($config['category']) ?? ($cats[0] ?? null))) : ($cats[0] ?? null);
    $metric = ($config['metric'] ?? '') === '__count' ? null : ($idx($config['metric'] ?? null) ?? ($nums[0] ?? null));
    $donutAuto = $cats[1] ?? ($cats[0] ?? null);
    $donut = array_key_exists('donut', $config) ? ($config['donut'] === '' ? null : ($idx($config['donut']) ?? $donutAuto)) : $donutAuto;

    return [
        'date' => $date, 'kpis' => $kpis, 'line' => $line, 'cat' => $cat, 'metric' => $metric, 'donut' => $donut,
        'barType' => ($config['barType'] ?? '') === 'line' ? 'line' : 'bar', 'filterCols' => array_slice($cats, 0, 3),
    ];
}

// ---- filters ----------------------------------------------------------------------------------
function filter_info(array $headers, array $rows, array $layout): array
{
    $date = null;
    if ($layout['date'] !== null) {
        $ts = array_filter(array_map(fn ($r) => to_date($r[$layout['date']], $headers[$layout['date']]), $rows));
        if ($ts) {
            $date = ['name' => $headers[$layout['date']], 'min' => date('Y-m-d', min($ts)), 'max' => date('Y-m-d', max($ts))];
        }
    }
    $cols = array_map(function (int $i) use ($headers, $rows) {
        $v = array_values(array_unique(array_filter(array_column($rows, $i), fn ($x) => $x !== '')));
        sort($v);
        return ['name' => $headers[$i], 'values' => $v];
    }, $layout['filterCols']);
    return ['date' => $date, 'cols' => $cols];
}

function apply_filters(array $headers, array $rows, array $layout, array $q): array
{
    $from = isset($q['from']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $q['from']) ? strtotime($q['from']) : null;
    $to = isset($q['to']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $q['to']) ? strtotime($q['to']) + 86399 : null;
    if (($from || $to) && $layout['date'] !== null) {
        $d = $layout['date'];
        $rows = array_values(array_filter($rows, function ($r) use ($headers, $d, $from, $to) {
            $t = to_date($r[$d], $headers[$d]);
            return $t !== null && (!$from || $t >= $from) && (!$to || $t <= $to);
        }));
    }
    foreach ((array) ($q['f'] ?? []) as $name => $value) {
        $i = array_search((string) $name, $headers, true);
        if ($i !== false && in_array($i, $layout['filterCols'], true) && is_string($value) && $value !== '') {
            $rows = array_values(array_filter($rows, fn ($r) => $r[$i] === $value));
        }
    }
    if ($layout['date'] !== null) {
        $d = $layout['date'];
        usort($rows, fn ($a, $b) => (to_date($a[$d], $headers[$d]) ?? 0) <=> (to_date($b[$d], $headers[$d]) ?? 0));
    }
    return $rows;
}

// ---- widgets ----------------------------------------------------------------------------------
function build_widgets(array $headers, array $rows, array $layout): array
{
    if (!$rows) {
        return ['kpis' => [], 'charts' => [], 'table' => ['title' => 'Data preview', 'columns' => array_slice($headers, 0, 6), 'rows' => []], 'noRows' => true];
    }
    $num = fn (string $v) => to_num($v) ?? 0.0;
    $kpis = [];
    foreach ($layout['kpis'] as $k) {
        $h = $headers[$k['col']];
        $vals = array_map(fn ($r) => $num($r[$k['col']]), $rows);
        $avg = $k['agg'] === 'avg' || ($k['agg'] === 'auto' && is_avg_name($h));
        $trend = buckets($vals, $avg);
        $n = count($trend);
        $delta = $n >= 2 && $trend[$n - 2] != 0 ? ($trend[$n - 1] / $trend[$n - 2] - 1) * 100 : 0;
        $total = $avg ? array_sum($vals) / count($vals) : array_sum($vals);
        $kpis[] = ['label' => $h . ($avg ? ' (avg)' : ''), 'value' => fmt_num($total), 'delta' => round($delta, 1), 'trend' => array_map(fn ($x) => round($x, 2), $n > 1 ? $trend : [0, $trend[0] ?? 0])];
    }

    $charts = [];
    $date = $layout['date'];
    if ($layout['line']) {
        $series = $layout['line'];
        $groups = [];
        $monthly = false;
        if ($date !== null) {
            $ts = array_filter(array_map(fn ($r) => to_date($r[$date], $headers[$date]), $rows));
            $monthly = count(array_unique(array_map(fn ($t) => date('Y-m-d', $t), $ts))) > 120;
            foreach ($rows as $r) {
                $t = to_date($r[$date], $headers[$date]);
                if ($t === null) {
                    continue;
                }
                foreach ($series as $s => $i) {
                    $groups[date($monthly ? 'Y-m' : 'Y-m-d', $t)][$s][] = $num($r[$i]);
                }
            }
            ksort($groups);
            $labels = array_map(fn ($k) => date($monthly ? 'M y' : 'd M', strtotime($monthly ? "$k-01" : $k)), array_keys($groups));
        } else {
            foreach (array_slice($rows, 0, 40) as $n => $r) {
                foreach ($series as $s => $i) {
                    $groups[$n][$s][] = $num($r[$i]);
                }
            }
            $labelCol = $layout['cat'];
            $labels = array_map(fn ($n) => $labelCol !== null ? $rows[$n][$labelCol] : (string) ($n + 1), array_keys($groups));
        }
        if (count($groups) >= 2) {
            $charts[] = [
                'type' => 'line', 'title' => implode(' & ', array_map(fn ($i) => $headers[$i], $series)),
                'sub' => $date !== null ? 'by ' . ($monthly ? 'month' : 'day') : 'by row', 'labels' => array_values($labels),
                'series' => array_map(fn ($s, $i) => ['name' => $headers[$i], 'data' => array_map(fn ($g) => round(is_avg_name($headers[$i]) ? array_sum($g[$s]) / count($g[$s]) : array_sum($g[$s]), 2), array_values($groups))], array_keys($series), $series),
            ];
        }
    }

    $metric = $layout['metric'];
    $unit = $metric === null ? 'Rows' : $headers[$metric];
    $aggregate = function (int $cat) use ($rows, $metric, $num): array {
        $sum = [];
        foreach ($rows as $r) {
            $k = $r[$cat] === '' ? '(blank)' : $r[$cat];
            $sum[$k] = ($sum[$k] ?? 0) + ($metric === null ? 1 : $num($r[$metric]));
        }
        arsort($sum);
        return $sum;
    };
    $sum = $layout['cat'] !== null ? array_slice($aggregate($layout['cat']), 0, 10, true) : [];
    if (count($sum) >= 2) { // a single category makes a pointless chart
        $title = "$unit by {$headers[$layout['cat']]}";
        $labels = array_map('strval', array_keys($sum));
        $data = array_map(fn ($v) => round($v, 2), array_values($sum));
        $charts[] = $layout['barType'] === 'line' && count($sum) >= 2
            ? ['type' => 'line', 'title' => $title, 'sub' => 'top ' . count($sum), 'labels' => $labels, 'series' => [['name' => $unit, 'data' => $data]]]
            : ['type' => 'bar', 'title' => $title, 'sub' => 'top ' . count($sum), 'labels' => $labels, 'data' => $data];
    }
    $sum = $layout['donut'] !== null ? $aggregate($layout['donut']) : [];
    if (count($sum) >= 2) {
        if (count($sum) > 6) {
            $sum = array_slice($sum, 0, 5, true) + ['Other' => array_sum(array_slice($sum, 5))];
        }
        $charts[] = ['type' => 'donut', 'title' => "{$headers[$layout['donut']]} share", 'unit' => $unit, 'items' => array_map(fn ($k, $v) => ['label' => (string) $k, 'value' => round($v, 2)], array_keys($sum), array_values($sum))];
    }

    return [
        'kpis' => $kpis,
        'charts' => $charts,
        'table' => ['title' => 'Data preview', 'columns' => array_slice($headers, 0, 6), 'rows' => array_map(fn ($r) => array_slice($r, 0, 6), array_slice($rows, 0, 10))],
    ];
}

// Splits values into <=16 near-equal buckets (sum or mean) for sparklines and the change figure
function buckets(array $vals, bool $avg): array
{
    $n = count($vals);
    $k = min(16, $n);
    $out = [];
    for ($i = 0; $i < $k; $i++) {
        $c = array_slice($vals, intdiv($i * $n, $k), intdiv(($i + 1) * $n, $k) - intdiv($i * $n, $k));
        $out[] = $avg ? array_sum($c) / count($c) : array_sum($c);
    }
    return $out;
}

function fmt_num(float $n): string
{
    return number_format($n, abs($n) >= 100 ? 0 : 2);
}

// Column info for the Customize form
function column_info(array $headers, array $cols): array
{
    return array_map(fn ($i, $c) => ['name' => $headers[$i], 'type' => $c['type'], 'distinct' => $c['distinct']], array_keys($cols), $cols);
}

// Accepts only known column names and values; anything else is dropped
function clean_config(array $in, array $headers): array
{
    $has = fn ($n) => is_string($n) && in_array($n, $headers, true);
    $out = [];
    if (array_key_exists('date', $in)) {
        $out['date'] = $in['date'] === '' ? '' : ($has($in['date']) ? $in['date'] : null);
        if ($out['date'] === null) {
            unset($out['date']);
        }
    }
    $out['kpis'] = [];
    foreach (array_slice((array) ($in['kpis'] ?? []), 0, 4) as $k) {
        if (is_array($k) && $has($k['col'] ?? null)) {
            $out['kpis'][] = ['col' => $k['col'], 'agg' => in_array($k['agg'] ?? '', ['sum', 'avg'], true) ? $k['agg'] : 'auto'];
        }
    }
    $out['line'] = array_slice(array_values(array_filter((array) ($in['line'] ?? []), $has)), 0, 2);
    foreach (['category', 'donut'] as $k) {
        if (array_key_exists($k, $in) && ($in[$k] === '' || $has($in[$k]))) {
            $out[$k] = $in[$k];
        }
    }
    if (($in['metric'] ?? '') === '__count' || $has($in['metric'] ?? null)) {
        $out['metric'] = $in['metric'];
    }
    $out['barType'] = ($in['barType'] ?? '') === 'line' ? 'line' : 'bar';
    return $out;
}

// Plain-language description of what a dashboard will display (shown in Administration)
function layout_summary(array $headers, array $rows, array $config): array
{
    $L = resolve_layout($headers, analyse($headers, $rows), $rows, $config);
    $h = fn (int $i) => $headers[$i];
    $out = [];
    if ($L['kpis']) {
        $out[] = ['KPI cards', implode(', ', array_map(fn ($k) => $h($k['col']) . ($k['agg'] === 'avg' ? ' (avg)' : ''), $L['kpis']))];
    }
    if ($L['line']) {
        $out[] = ['Line chart', implode(' & ', array_map($h, $L['line'])) . ($L['date'] !== null ? ' by ' . $h($L['date']) : ' by row')];
    }
    $measure = $L['metric'] === null ? 'row count' : $h($L['metric']);
    if ($L['cat'] !== null) {
        $out[] = [$L['barType'] === 'line' ? 'Line chart (groups)' : 'Bar chart', "$measure by " . $h($L['cat'])];
    }
    if ($L['donut'] !== null) {
        $out[] = ['Donut chart', "$measure by " . $h($L['donut'])];
    }
    $filters = array_map($h, $L['filterCols']);
    if ($L['date'] !== null) {
        array_unshift($filters, $h($L['date']) . ' (date range)');
    }
    if ($filters) {
        $out[] = ['Filters', implode(', ', $filters)];
    }
    $out[] = ['Table', 'first 10 rows of ' . count($headers) . ' columns'];
    return $out;
}

// Summary for a registry item; Google Sheets items use the cached copy only (no network on page load)
function dash_summary(array $item): ?array
{
    try {
        if (($item['type'] ?? '') === 'url') {
            $cache = UPLOAD_DIR . '/_remote_' . md5((string) $item['url']) . '.csv';
            if (!is_file($cache)) {
                return null;
            }
            [$h, $r] = read_table($cache, 'csv');
        } else {
            [$h, $r] = dash_table($item);
        }
        return layout_summary($h, $r, (array) ($item['config'] ?? []));
    } catch (Throwable $e) {
        return null;
    }
}
