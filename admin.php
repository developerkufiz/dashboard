<?php
declare(strict_types=1);
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/layout.php';
require __DIR__ . '/inc/files.php';
require __DIR__ . '/inc/data.php';
require_admin_page();

$sorts = [
    'newest' => fn ($a, $b) => strcmp($b['modified'], $a['modified']),
    'name' => fn ($a, $b) => strcasecmp($a['name'], $b['name']),
    'size' => fn ($a, $b) => $b['size'] <=> $a['size'],
    'type' => fn ($a, $b) => strcmp($a['ext'], $b['ext']) ?: strcasecmp($a['name'], $b['name']),
];
$sort = isset($sorts[$_GET['sort'] ?? '']) ? $_GET['sort'] : 'newest';
$files = list_files();
usort($files, $sorts[$sort]);
$groups = [];
foreach ($files as $f) {
    $groups[$f['date']][] = $f;
}
krsort($groups);
$iconFor = ['xlsx' => 'csv', 'json' => 'json', 'csv' => 'csv', 'txt' => 'txt', 'png' => 'image', 'jpg' => 'image', 'jpeg' => 'image', 'pdf' => 'pdf'];

$reg = dash_load();
$defaultKey = dash_pick($reg, '')['key'] ?? '';
$usedFiles = [];
foreach ($reg['items'] as $it) {
    if (($it['type'] ?? '') === 'file') {
        $usedFiles[(string) $it['id']] = true;
    }
}

page_head('Dashboard - Administration');
topbar('admin');
?>
<main class="admin" id="admin">
  <div class="admin-inner">
    <header class="admin-head">
      <div>
        <h2>File Manager</h2>
        <p class="muted">Stored on the server in <span class="mono">/uploads/YYYYMMDD/</span> · JSON, CSV, XLSX, TXT, PNG, JPG, PDF, GLB · max 5 MB</p>
      </div>
      <button class="btn primary" id="upload-btn"><?= icon('upload') ?> Upload</button>
      <input type="file" id="upload-input" multiple hidden>
    </header>

    <section class="card source">
      <header><h3>Dashboards</h3><span class="muted">Built from a CSV, XLSX or Google Sheet. The dashboard is empty until you add one.</span></header>
      <?php if (!$reg['items']) : ?>
        <p class="muted">None yet. Upload a CSV/XLSX below and click its <b>dashboard icon</b> (<?= icon('dash', 14) ?>) in the file list, or paste a Google Sheets link. Each dashboard then lists exactly what it will display.</p>
      <?php endif; ?>
      <?php foreach ($reg['items'] as $it) : $sum = dash_summary($it); ?>
        <div class="dash-item" data-key="<?= e($it['key']) ?>" data-title="<?= e($it['title'] ?? 'Dashboard') ?>">
          <div class="dash-row">
            <b class="dash-name"><?= e($it['title'] ?? 'Dashboard') ?></b>
            <?php if ($it['key'] === $defaultKey) : ?><span class="chip in-use">DEFAULT</span><?php endif; ?>
            <span class="muted mono"><?= e($it['name'] ?? '') ?></span>
            <div class="spacer"></div>
            <button class="btn sm" data-dash="rename">Rename</button>
            <button class="btn sm" data-dash="config">Customize</button>
            <?php if ($it['key'] !== $defaultKey) : ?><button class="btn sm" data-dash="default">Make default</button><?php endif; ?>
            <button class="btn sm" data-dash="remove">Remove</button>
          </div>
          <dl class="dash-sum">
            <?php if ($sum) : foreach ($sum as [$what, $detail]) : ?>
              <dt><?= e($what) ?></dt><dd><?= e($detail) ?></dd>
            <?php endforeach; else : ?>
              <dt>Shows</dt><dd class="muted">Open the dashboard once to load the Google Sheet and see what it displays.</dd>
            <?php endif; ?>
          </dl>
        </div>
      <?php endforeach; ?>
      <div class="url-add">
        <input id="sheet-url" placeholder="Google Sheets link: File → Share → Publish to web → CSV" aria-label="Google Sheets CSV link">
        <button class="btn" id="sheet-add">Add link</button>
      </div>
    </section>

    <div class="toolbar">
      <label class="search"><?= icon('search') ?><input id="search" placeholder="Search files…" aria-label="Search files"></label>
      <form class="sort" method="get">Sort
        <select name="sort">
          <?php foreach (['newest' => 'Newest first', 'name' => 'Name', 'size' => 'Size', 'type' => 'Type'] as $k => $label) : ?>
            <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $label ?></option>
          <?php endforeach; ?>
        </select>
      </form>
    </div>

    <div class="form-error" id="error" role="alert" hidden></div>

    <?php foreach ($groups as $date => $list) : $date = (string) $date; ?>
      <section class="group">
        <h4><?= e(substr($date, 0, 4) . '-' . substr($date, 4, 2) . '-' . substr($date, 6)) ?> <span class="muted">· <?= count($list) ?> file<?= count($list) > 1 ? 's' : '' ?></span></h4>
        <div class="files">
          <?php foreach ($list as $f) : $ic = $iconFor[$f['ext']] ?? 'file'; $used = isset($usedFiles[$f['id']]); ?>
            <div class="file-row" data-search="<?= e(strtolower($f['name'] . ' ' . $f['ext'])) ?>">
              <span class="ficon t-<?= e($f['ext']) ?>"><?= icon($ic) ?></span>
              <button class="fname mono" data-act="view" data-id="<?= e($f['id']) ?>" data-name="<?= e($f['name']) ?>" data-ext="<?= e($f['ext']) ?>" data-editable="<?= $f['editable'] ? '1' : '0' ?>"><?= e($f['name']) ?></button>
              <span class="chip <?= $used ? 'in-use' : '' ?>"><?= $used ? 'DASHBOARD' : e(strtoupper($f['ext'])) ?></span>
              <span class="fsize mono"><?= e(fmt_size($f['size'])) ?></span>
              <span class="fmod muted"><?= e(date('d M, H:i', strtotime($f['modified']))) ?></span>
              <div class="acts" data-id="<?= e($f['id']) ?>" data-name="<?= e($f['name']) ?>" data-ext="<?= e($f['ext']) ?>" data-editable="<?= $f['editable'] ? '1' : '0' ?>">
                <button class="icon-btn sm" data-act="view" title="View" aria-label="View <?= e($f['name']) ?>"><?= icon('eye') ?></button>
                <?php if (in_array($f['ext'], ['csv', 'xlsx'], true)) : ?><button class="icon-btn sm" data-act="dash" title="Add as dashboard" aria-label="Add as dashboard"><?= icon('dash') ?></button><?php else : ?><span class="slot"></span><?php endif; ?>
                <?php if ($f['editable']) : ?><button class="icon-btn sm" data-act="edit" title="Edit" aria-label="Edit"><?= icon('edit') ?></button><?php else : ?><span class="slot"></span><?php endif; ?>
                <a class="icon-btn sm" href="api/files.php?action=download&amp;id=<?= e(rawurlencode($f['id'])) ?>" title="Download" aria-label="Download"><?= icon('download') ?></a>
                <button class="icon-btn sm danger-text" data-act="delete" title="Delete" aria-label="Delete"><?= icon('trash') ?></button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <div class="empty" id="empty" <?= $groups ? 'hidden' : '' ?>>
      <?= icon('upload', 28) ?><b id="empty-title"><?= $groups ? 'No files match your search' : 'No files yet' ?></b><span class="muted">Drop files here or use Upload.</span>
    </div>
  </div>
</main>

<div class="toast" id="toast" role="status" hidden></div>

<div class="modal-back" id="file-modal" hidden>
  <div class="modal wide" role="dialog" aria-modal="true" aria-labelledby="fm-title">
    <header class="modal-head">
      <b class="mono" id="fm-title"></b><span class="muted" id="fm-mode"></span>
      <div class="spacer"></div>
      <button class="icon-btn sm" data-close aria-label="Close"><?= icon('x', 14) ?></button>
    </header>
    <div class="modal-main">
      <img class="preview" id="fm-img" alt="" hidden>
      <textarea class="editor mono" id="fm-text" spellcheck="false" wrap="off" hidden></textarea>
      <p class="muted pad" id="fm-none" hidden>No inline preview for this file type. Use Download.</p>
    </div>
    <footer class="modal-foot">
      <span class="validity" id="fm-status"></span>
      <div class="spacer"></div>
      <button class="btn" id="fm-format" hidden>Format</button>
      <button class="btn" data-close id="fm-cancel">Cancel</button>
      <button class="btn primary" id="fm-save" hidden>Save</button>
    </footer>
  </div>
</div>

<div class="modal-back" id="del-modal" hidden>
  <div class="modal" role="dialog" aria-modal="true">
    <div class="modal-pad">
      <h3 id="del-title">Delete file?</h3>
      <p class="mono file-name" id="del-name"></p>
      <p class="muted">This action cannot be undone.</p>
      <div class="modal-actions">
        <button class="btn" data-close>Cancel</button>
        <button class="btn danger" id="del-confirm">Delete</button>
      </div>
    </div>
  </div>
</div>

<div class="modal-back" id="cz-modal" hidden>
  <div class="modal wide" role="dialog" aria-modal="true" aria-labelledby="cz-heading">
    <header class="modal-head">
      <b id="cz-heading">Customize dashboard</b>
      <span class="muted">Anything left on “Auto” is chosen from your data</span>
      <div class="spacer"></div>
      <button class="icon-btn sm" data-close aria-label="Close"><?= icon('x', 14) ?></button>
    </header>
    <div class="modal-main"><form class="cz-form" id="cz-form"><p class="muted">Loading…</p></form></div>
    <footer class="modal-foot">
      <span class="validity" id="cz-status"></span>
      <div class="spacer"></div>
      <button class="btn" data-close>Cancel</button>
      <button class="btn primary" id="cz-save">Save</button>
    </footer>
  </div>
</div>
<?php page_foot(); ?>
