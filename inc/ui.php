<?php
declare(strict_types=1);

// All CSS and JS for the UI live in this one file. layout.php prints them inline on every page
// (allowed by the Content-Security-Policy nonce set in bootstrap.php), so there is no assets folder.

function ui_css(): void
{
    ?>
<style nonce="<?= e(CSP_NONCE) ?>">
:root {
  --bg: #0a1017;
  --bg-2: #0e1620;
  --panel: rgba(13, 20, 29, 0.86);
  --line: #213043;
  --line-2: #2c4058;
  --text: #dbe5ef;
  --muted: #7d91a6;
  --accent: #3ad0e0;
  --accent-dim: rgba(58, 208, 224, 0.14);
  --ok: #3ddc97;
  --warn: #f5b942;
  --bad: #ff5d5d;
  --radius: 10px;
  --mono: ui-monospace, 'Cascadia Mono', 'SF Mono', Consolas, monospace;
  --sans: 'Segoe UI Variable', 'Segoe UI', system-ui, -apple-system, Roboto, sans-serif;
  color-scheme: dark;
}

* { box-sizing: border-box; }
html, body, #root { height: 100%; margin: 0; }
body { background: var(--bg); color: var(--text); font: 13px/1.45 var(--sans); overflow: hidden; -webkit-font-smoothing: antialiased; }
button, input, select, textarea { font: inherit; color: inherit; }
button { cursor: pointer; }
h2, h3, h4, h5, p { margin: 0; }
.mono { font-family: var(--mono); font-variant-numeric: tabular-nums; }
.muted { color: var(--muted); }
.pad { padding: 14px; }
.spacer { flex: 1; }
:focus-visible { outline: 2px solid var(--accent); outline-offset: 1px; }

.app { height: 100%; display: flex; flex-direction: column; }
.splash { position: absolute; inset: 0; display: grid; place-items: center; color: var(--muted); letter-spacing: 0.04em; }

/* ---------- buttons & inputs ---------- */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 32px; padding: 0 14px; background: var(--bg-2); border: 1px solid var(--line-2); border-radius: 7px; transition: background 0.12s, border-color 0.12s; }
.btn:hover:not(:disabled) { border-color: var(--accent); }
.btn.on { background: var(--accent-dim); border-color: var(--accent); color: var(--accent); }
.btn.primary { background: var(--accent); border-color: var(--accent); color: #04222a; font-weight: 600; }
.btn.primary:hover:not(:disabled) { background: #62dde9; }
.btn.danger { background: var(--bad); border-color: var(--bad); color: #2a0707; font-weight: 600; }
.btn.wide { width: 100%; height: 38px; }
.btn:disabled { opacity: 0.45; cursor: not-allowed; }
.icon-btn { position: relative; display: inline-grid; place-items: center; width: 34px; height: 34px; background: none; border: 1px solid transparent; border-radius: 7px; color: var(--muted); }
.icon-btn.sm { width: 28px; height: 28px; }
.icon-btn:hover { color: var(--text); background: var(--accent-dim); }
input:not([type='checkbox']), select, textarea { background: var(--bg); border: 1px solid var(--line-2); border-radius: 7px; padding: 7px 10px; width: 100%; }
input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); }
.chip { font: 600 10px/1 var(--mono); letter-spacing: 0.06em; padding: 4px 6px; border-radius: 4px; background: #16222f; color: var(--muted); border: 1px solid var(--line); }
.seg { display: inline-flex; padding: 3px; gap: 2px; background: var(--bg); border: 1px solid var(--line); border-radius: 8px; }
.seg button { display: inline-flex; align-items: center; justify-content: center; gap: 6px; flex: 1; height: 30px; padding: 0 12px; border: 0; border-radius: 6px; background: none; color: var(--muted); }
.seg button.on { background: var(--accent-dim); color: var(--accent); }
.seg-full { display: flex; width: 100%; }
.form-error { padding: 8px 10px; border: 1px solid rgba(255, 93, 93, 0.5); background: rgba(255, 93, 93, 0.1); color: #ff9c9c; border-radius: 7px; margin: 8px 0; }

.panel { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); backdrop-filter: blur(10px); box-shadow: 0 8px 28px rgba(0, 0, 0, 0.35); }

/* ---------- login ---------- */
.login { height: 100%; display: grid; place-items: center; padding: 16px; background: radial-gradient(900px 500px at 50% 0%, #10304a 0%, var(--bg) 70%); }
.login-card { width: min(400px, 100%); padding: 28px; display: grid; gap: 14px; background: var(--panel); border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5); }
.login-body { display: grid; gap: 12px; margin-top: 4px; }
.login-body label { display: grid; gap: 5px; color: var(--muted); font-size: 12px; }
.login-body p { color: var(--muted); }

.brand { color: inherit; text-decoration: none; display: flex; align-items: center; gap: 9px; font-weight: 650; letter-spacing: 0.02em; white-space: nowrap; }
.brand-lg { font-size: 19px; }
.brand-mark { width: 20px; height: 20px; border-radius: 5px; background: linear-gradient(135deg, var(--accent), #2a7bd0); position: relative; flex: none; }
.brand-mark::after { content: ''; position: absolute; inset: 5px 4px 4px; border: 2px solid #06202a; border-top-width: 3px; border-radius: 1px; }

/* ---------- top bar ---------- */
.topbar { display: flex; align-items: center; gap: 12px; height: 48px; padding: 0 12px; background: #0b121a; border-bottom: 1px solid var(--line); position: relative; z-index: 30; flex: none; }
.nav { display: flex; gap: 2px; margin-left: 10px; }
.nav a { text-decoration: none; display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 12px; background: none; border: 0; border-radius: 7px; color: var(--muted); }
.nav a:hover { color: var(--text); }
.nav a.on { color: var(--accent); background: var(--accent-dim); }
.clock { color: var(--text); font-size: 13px; white-space: nowrap; }
.clock span { color: var(--muted); margin-right: 4px; }
.user-btn { display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 10px; background: none; border: 1px solid var(--line); border-radius: 7px; color: var(--muted); }
.user-btn:hover { color: var(--text); border-color: var(--line-2); }
.role { font: 600 11px/1 var(--mono); letter-spacing: 0.06em; text-transform: uppercase; color: var(--text); }
.role.admin { color: var(--accent); }
.danger-text { color: #ff8f8f !important; }

/* ---------- dashboard ---------- */
.dash { flex: 1; overflow-y: auto; position: relative; }
.dash-inner { width: min(1280px, 100%); margin: 0 auto; padding: 16px; display: grid; gap: 16px; }
.card { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); padding: 14px 16px; min-width: 0; }
.card header { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 10px; }
.card h3 { font-size: 14px; font-weight: 600; }
.kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; }
.kpi-card { display: grid; gap: 2px; }
.kpi-label { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; color: var(--muted); }
.kpi-value { font-size: 28px; font-weight: 600; line-height: 1.2; }
.delta { font-size: 12px; font-weight: 600; }
.delta.up { color: var(--ok); } .delta.down { color: var(--bad); }
.spark { width: 100%; height: 28px; margin-top: 6px; }
.row { display: grid; gap: 16px; }
.r-2-1 { grid-template-columns: 2fr 1fr; }
.r-1-2 { grid-template-columns: 1fr 2fr; }
.chart { width: 100%; height: auto; display: block; }
.chart text { fill: var(--muted); font: 10px var(--mono); }
.chart .grid { stroke: var(--line); stroke-width: 1; }
.chart .line { fill: none; stroke: var(--c); stroke-width: 2.2; stroke-linejoin: round; }
.chart .area { fill: var(--c); opacity: 0.12; }
.chart .pt { fill: var(--bg); stroke: var(--c); stroke-width: 2; }
.chart .bar { fill: #2a6f86; } .chart .bar.hi { fill: var(--accent); }
.chart .val { fill: var(--text); }
.legend { display: flex; gap: 16px; margin-top: 8px; color: var(--muted); }
.legend i { display: inline-block; width: 10px; height: 3px; margin-right: 6px; vertical-align: middle; border-radius: 2px; }
.legend .l0 { background: #3ad0e0; } .legend .l1 { background: #7a8cff; }
.donut { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; justify-content: center; }
.donut svg { width: 150px; flex: none; }
.donut-n { fill: var(--text); font: 600 20px var(--mono); }
.donut-l { fill: var(--muted); font: 10px var(--sans); }
.donut ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; flex: 1; min-width: 120px; }
.donut li { display: flex; align-items: center; gap: 8px; }
.donut li i { width: 9px; height: 9px; border-radius: 2px; }
.donut li b { margin-left: auto; font-weight: 500; }
.tbl { width: 100%; border-collapse: collapse; }
.tbl th { text-align: left; font-size: 11px; letter-spacing: 0.06em; text-transform: uppercase; color: var(--muted); font-weight: 600; padding: 6px 8px; border-bottom: 1px solid var(--line); }
.tbl td { padding: 9px 8px; border-bottom: 1px solid var(--line); }
.tbl tr:last-child td { border: 0; }
.tbl .r { text-align: right; }
.tag { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 99px; border: 1px solid currentColor; }
.s-paid { color: var(--ok); } .s-pending { color: var(--warn); } .s-overdue { color: var(--bad); }

/* ---------- admin ---------- */
.admin { flex: 1; overflow-y: auto; background: radial-gradient(800px 360px at 50% -80px, #11293d 0%, var(--bg) 70%); position: relative; }
.admin.drag::after { content: 'Drop files to upload'; position: fixed; inset: 60px 12px 12px; display: grid; place-items: center; border: 2px dashed var(--accent); border-radius: 14px; background: rgba(10, 16, 23, 0.8); color: var(--accent); font-size: 18px; z-index: 20; pointer-events: none; }
.admin-inner { width: min(960px, 100%); margin: 0 auto; padding: 24px 16px 60px; }
.admin-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
.admin-head h2 { font-size: 20px; margin-bottom: 3px; }
.toolbar { display: flex; gap: 12px; align-items: center; margin-bottom: 8px; }
.search { position: relative; flex: 1; display: block; }
.search svg { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: var(--muted); }
.search input { padding-left: 32px; }
.sort { display: flex; align-items: center; gap: 8px; color: var(--muted); white-space: nowrap; }
.sort select { width: auto; }
.group { margin-top: 18px; }
.group h4 { font: 600 12px var(--mono); letter-spacing: 0.06em; margin-bottom: 7px; }
.files { background: var(--panel); border: 1px solid var(--line); border-radius: var(--radius); }
.file-row { display: grid; grid-template-columns: 32px minmax(0, 1fr) 78px 76px 120px 156px; align-items: center; gap: 12px; padding: 8px 12px; border-bottom: 1px solid var(--line); }
.file-row:last-child { border: 0; }
.file-row:hover { background: rgba(58, 208, 224, 0.045); }
.ficon { display: grid; place-items: center; width: 30px; height: 30px; border-radius: 7px; background: #16222f; color: var(--muted); }
.t-json { color: #f5b942; } .t-csv { color: #3ddc97; } .t-txt { color: #9db2c7; } .t-png, .t-jpg, .t-jpeg { color: #b88cf0; } .t-pdf { color: #ff7f7f; } .t-glb { color: var(--accent); }
.fname { background: none; border: 0; padding: 0; text-align: left; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.fname:hover { color: var(--accent); text-decoration: underline; }
.fsize { text-align: right; color: var(--muted); }
.fmod { font-size: 12px; white-space: nowrap; }
.empty { display: grid; justify-items: center; gap: 6px; padding: 60px 0; color: var(--muted); }
.toast { position: fixed; bottom: 18px; left: 50%; transform: translateX(-50%); padding: 9px 16px; background: #10202c; border: 1px solid var(--accent); border-radius: 8px; z-index: 60; }

.modal-back { position: fixed; inset: 0; z-index: 50; display: grid; place-items: center; padding: 16px; background: rgba(4, 8, 12, 0.7); backdrop-filter: blur(3px); }
.modal { width: min(420px, 100%); background: #0f1823; border: 1px solid var(--line-2); border-radius: 12px; box-shadow: 0 24px 70px rgba(0, 0, 0, 0.6); display: flex; flex-direction: column; max-height: calc(100vh - 32px); }
.modal.wide { width: min(820px, 100%); height: min(640px, calc(100vh - 32px)); }
.modal-pad { padding: 20px; display: grid; gap: 8px; }
.modal-pad h3 { font-size: 17px; }
.file-name { padding: 8px 10px; background: var(--bg); border: 1px solid var(--line); border-radius: 7px; word-break: break-all; }
.modal-actions { display: flex; justify-content: flex-end; gap: 8px; margin-top: 10px; }
.modal-head, .modal-foot { display: flex; align-items: center; gap: 10px; padding: 10px 14px; flex: none; }
.modal-head { border-bottom: 1px solid var(--line); }
.modal-foot { border-top: 1px solid var(--line); }
.modal-main { flex: 1; min-height: 0; display: flex; overflow: auto; }
.editor { flex: 1; resize: none; border: 0; border-radius: 0; background: var(--bg); line-height: 1.55; font-size: 12.5px; tab-size: 2; white-space: pre; padding: 14px; }
.preview { max-width: 100%; max-height: 100%; margin: auto; object-fit: contain; }
.validity { font-size: 12px; color: var(--muted); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.validity.good { color: var(--ok); }
.validity.bad { color: #ff9c9c; }

/* ---------- small screens ---------- */
@media (max-width: 760px) {
  .brand-name, .clock span, .role { display: none; }
  .nav { margin-left: 0; }
  .file-row { grid-template-columns: 32px minmax(0, 1fr) 156px; gap: 8px; }
  .file-row .fsize { display: none; }
  .file-row .chip, .file-row .fmod { display: none; }
  .admin-head p { display: none; }
  .toolbar { flex-wrap: wrap; }
}
@media (max-width: 560px) {
  .clock { display: none; }
}
@media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
@media (max-width: 960px) { .r-2-1, .r-1-2 { grid-template-columns: 1fr; } }

[hidden] { display: none !important; }
.topbar form { margin: 0; }
body.login-page { overflow: auto; }
#fs-btn span { display: inline-grid; place-items: center; }
#fs-btn .fs-out, html:fullscreen #fs-btn .fs-in { display: none; }
html:fullscreen #fs-btn .fs-out { display: inline-grid; }
.src { font-size: 12px; }
.src b { color: var(--text); font-weight: 500; }
.tbl-wrap { overflow-x: auto; }
.tbl td { white-space: nowrap; max-width: 220px; overflow: hidden; text-overflow: ellipsis; }
.chip.in-use { color: var(--ok); border-color: var(--ok); }
.t-xlsx { color: #3ddc97; }
.tbl th.r { text-align: right; }

/* ---------- dashboards: filters, customize, empty state ---------- */
.dash-bar { display: flex; flex-wrap: wrap; align-items: end; gap: 10px 14px; }
.dash-bar label { display: grid; gap: 3px; font-size: 11px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; }
.dash-bar input, .dash-bar select { width: auto; min-width: 130px; padding: 5px 8px; text-transform: none; letter-spacing: 0; font-size: 13px; }
.dash-title { font-size: 18px; margin-right: auto; align-self: center; }
.dash-bar #f-dash { font-size: 15px; font-weight: 600; margin-right: auto; }
.dash-bar .btn { text-decoration: none; }
.btn.sm { height: 28px; padding: 0 10px; font-size: 12px; }
.head-r { display: inline-flex; align-items: center; gap: 6px; }
.empty-state { display: grid; justify-items: center; gap: 6px; padding: 48px 16px; text-align: center; }
.empty-state a { color: var(--accent); }
.dash-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; flex-wrap: wrap; }
.source > header { margin-bottom: 6px; }
.source { display: block; }
.url-add { display: flex; gap: 8px; margin-top: 10px; }
.cz-form { padding: 14px 16px; display: grid; gap: 14px; align-content: start; width: 100%; overflow-y: auto; }
.cz-field { display: grid; gap: 4px; color: var(--muted); font-size: 12px; }
.cz-field input, .cz-field select { color: var(--text); }
.cz-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.cz-form fieldset { border: 1px solid var(--line); border-radius: 8px; padding: 8px 12px 10px; margin: 0; display: flex; flex-wrap: wrap; gap: 6px 18px; }
.cz-form legend { padding: 0 6px; font-size: 12px; color: var(--muted); }
.cz-form legend small { opacity: 0.8; }
.cz-row { display: flex; align-items: center; gap: 10px; width: 100%; }
.cz-row label { flex: 1; display: flex; align-items: center; gap: 8px; }
.cz-row select { width: 120px; padding: 4px 8px; }
.cz-chk { display: inline-flex; align-items: center; gap: 6px; }
@media (max-width: 760px) { .cz-grid { grid-template-columns: 1fr; } }
.acts { display: flex; justify-content: flex-end; gap: 2px; }
.acts .slot { width: 28px; flex: none; }
.acts .icon-btn { text-decoration: none; }
.source { margin-bottom: 16px; }
.url-add .btn { flex: none; white-space: nowrap; }
.dash-item { border-top: 1px solid var(--line); padding: 10px 0; }
.dash-item .dash-row { border-top: 0; padding: 0 0 6px; }
.dash-sum { display: grid; grid-template-columns: max-content 1fr; gap: 3px 14px; margin: 0; font-size: 12.5px; }
.dash-sum dt { color: var(--muted); }
.dash-sum dd { margin: 0; }
.rename-input { width: 220px; padding: 4px 8px; font-weight: 600; }
.cz-info { background: var(--bg); border: 1px solid var(--line); border-radius: 8px; padding: 10px 12px; display: grid; gap: 6px; }
.cz-cols { display: flex; flex-wrap: wrap; gap: 6px; }
.cz-field small { opacity: 0.8; }
</style>
<?php
}

function ui_js(): void
{
    ?>
<script nonce="<?= e(CSP_NONCE) ?>">
// Shared UI script. Sections run only on the page that has their elements.
const $ = (id) => document.getElementById(id);
const esc = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c]);

// ---- shared: clock, fullscreen, login tabs -------------------------------------------------------
(() => {
  const clock = $('clock');
  if (clock) {
    const tick = () => {
      const d = new Date();
      clock.innerHTML = '<span>' + d.toLocaleDateString([], { day: '2-digit', month: 'short', year: 'numeric' }) + '</span> ' + d.toLocaleTimeString([], { hour12: false });
    };
    tick();
    setInterval(tick, 1000);
  }

  const fs = $('fs-btn');
  if (fs && document.documentElement.requestFullscreen) {
    fs.addEventListener('click', () => (document.fullscreenElement ? document.exitFullscreen() : document.documentElement.requestFullscreen()));
  } else if (fs) {
    fs.hidden = true; // browser without Fullscreen API
  }

  document.querySelectorAll('[data-tab]').forEach((btn) =>
    btn.addEventListener('click', () => {
      document.querySelectorAll('[data-tab]').forEach((b) => b.classList.toggle('on', b === btn));
      document.querySelectorAll('[data-panel]').forEach((p) => (p.hidden = p.dataset.panel !== btn.dataset.tab));
    }),
  );
})();

// ---- charts (dependency-free SVG; each returns an SVG/HTML string) -------------------------------
const COLORS = ['#3ad0e0', '#7a8cff', '#3ddc97', '#f5b942', '#ff7f7f'];
const compact = (n) => {
  const a = Math.abs(n);
  return a >= 1e9 ? +(n / 1e9).toFixed(1) + 'B' : a >= 1e6 ? +(n / 1e6).toFixed(1) + 'M' : a >= 1e4 ? +(n / 1e3).toFixed(1) + 'k' : String(Math.round(n * 100) / 100);
};
const niceMax = (v) => {
  if (v <= 0) return 1;
  const p = 10 ** Math.floor(Math.log10(v)), n = v / p;
  return ([1, 1.2, 1.5, 2, 2.5, 3, 4, 5, 6, 8, 10].find((s) => n <= s)) * p;
};
const clip = (s, n) => (String(s).length > n ? String(s).slice(0, n - 1) + '…' : String(s));

function sparkline(data, color = COLORS[0]) {
  const max = Math.max(...data), min = Math.min(...data);
  const pts = data.map((v, i) => `${(i / (data.length - 1)) * 100},${28 - ((v - min) / (max - min || 1)) * 26 - 1}`).join(' ');
  return `<svg viewBox="0 0 100 28" preserveAspectRatio="none" class="spark" aria-hidden="true"><polyline points="${pts}" fill="none" stroke="${color}" stroke-width="1.6" vector-effect="non-scaling-stroke"/></svg>`;
}

function lineChart({ labels, series, title }) {
  const W = 640, H = 240, L = 46, B = 24, T = 10, R = 8;
  const all = series.flatMap((s) => s.data);
  const min = Math.min(0, ...all), max = niceMax(Math.max(...all));
  const x = (i) => L + (i / (labels.length - 1)) * (W - L - R);
  const y = (v) => T + (1 - (v - min) / (max - min || 1)) * (H - T - B);
  let g = '';
  for (const f of [0, 0.25, 0.5, 0.75, 1]) {
    const v = min + (max - min) * f;
    g += `<line x1="${L}" x2="${W - R}" y1="${y(v)}" y2="${y(v)}" class="grid"/><text x="${L - 6}" y="${y(v) + 3}" text-anchor="end">${compact(v)}</text>`;
  }
  const step = Math.ceil(labels.length / 12);
  labels.forEach((l, i) => { if (i % step === 0) g += `<text x="${x(i)}" y="${H - 6}" text-anchor="middle">${esc(clip(l, 8))}</text>`; });
  const dots = labels.length <= 31;
  series.forEach((s, k) => {
    const pts = s.data.map((v, i) => `${x(i)},${y(v)}`);
    g += `<g style="--c:${COLORS[k]}">`;
    if (k === 0) g += `<polygon points="${x(0)},${y(0)} ${pts.join(' ')} ${x(s.data.length - 1)},${y(0)}" class="area"/>`;
    g += `<polyline points="${pts.join(' ')}" class="line"/>`;
    if (dots) s.data.forEach((v, i) => (g += `<circle cx="${x(i)}" cy="${y(v)}" r="3" class="pt"><title>${esc(s.name)} · ${esc(labels[i])}: ${v}</title></circle>`));
    g += '</g>';
  });
  return `<svg viewBox="0 0 ${W} ${H}" class="chart" role="img" aria-label="${esc(title)}">${g}</svg>`
    + `<div class="legend">${series.map((s, i) => `<span><i style="background:${COLORS[i]}"></i>${esc(s.name)}</span>`).join('')}</div>`;
}

function barChart({ labels, data, title }) {
  const W = 320, H = 200, B = 22, T = 14;
  const max = Math.max(...data, 1), bw = W / data.length;
  let g = '';
  data.forEach((v, i) => {
    const h = (v / max) * (H - B - T);
    g += `<rect x="${i * bw + 4}" y="${H - B - h}" width="${bw - 8}" height="${h}" rx="3" class="bar${v === max ? ' hi' : ''}"><title>${esc(labels[i])}: ${v}</title></rect>`
      + `<text x="${i * bw + bw / 2}" y="${H - 6}" text-anchor="middle">${esc(clip(labels[i], data.length > 7 ? 4 : 7))}</text>`
      + `<text x="${i * bw + bw / 2}" y="${H - B - h - 4}" text-anchor="middle" class="val">${compact(v)}</text>`;
  });
  return `<svg viewBox="0 0 ${W} ${H}" class="chart" role="img" aria-label="${esc(title)}">${g}</svg>`;
}

function donut({ items, unit, title }) {
  const total = items.reduce((s, i) => s + i.value, 0) || 1;
  const R = 52, C = 2 * Math.PI * R;
  let offset = 0, arcs = '';
  items.forEach((it, i) => {
    const len = (it.value / total) * C;
    arcs += `<circle cx="70" cy="70" r="${R}" fill="none" stroke="${COLORS[i % COLORS.length]}" stroke-width="18" stroke-dasharray="${Math.max(0, len - 1.5)} ${C - len + 1.5}" stroke-dashoffset="${-offset}"><title>${esc(it.label)}: ${it.value}</title></circle>`;
    offset += len;
  });
  const legend = items.map((it, i) => `<li><i style="background:${COLORS[i % COLORS.length]}"></i>${esc(clip(it.label, 16))}<b class="mono">${Math.round((it.value / total) * 100)}%</b></li>`).join('');
  return `<div class="donut"><svg viewBox="0 0 140 140" role="img" aria-label="${esc(title)}"><g transform="rotate(-90 70 70)">${arcs}</g>`
    + `<text x="70" y="68" text-anchor="middle" class="donut-n">${compact(total)}</text><text x="70" y="84" text-anchor="middle" class="donut-l">${esc(clip(unit || 'Total', 14))}</text></svg><ul>${legend}</ul></div>`;
}

// Download a chart's SVG as a PNG (styles are inlined because the file has no stylesheet)
function svgToPng(svg, name) {
  const clone = svg.cloneNode(true);
  const props = ['fill', 'stroke', 'stroke-width', 'stroke-dasharray', 'stroke-dashoffset', 'stroke-linejoin', 'opacity', 'font-family', 'font-size', 'font-weight', 'text-anchor'];
  const src = [svg, ...svg.querySelectorAll('*')], dst = [clone, ...clone.querySelectorAll('*')];
  src.forEach((el, i) => {
    const cs = getComputedStyle(el);
    dst[i].setAttribute('style', props.map((p) => `${p}:${cs.getPropertyValue(p)}`).join(';'));
  });
  const vb = svg.viewBox.baseVal;
  const scale = 2, w = vb.width * scale, h = vb.height * scale;
  clone.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
  clone.setAttribute('width', w);
  clone.setAttribute('height', h);
  clone.insertAdjacentHTML('afterbegin', `<rect x="0" y="0" width="${vb.width}" height="${vb.height}" fill="#0d141d"/>`);
  const img = new Image();
  img.onload = () => {
    const canvas = document.createElement('canvas');
    canvas.width = w;
    canvas.height = h;
    canvas.getContext('2d').drawImage(img, 0, 0, w, h);
    canvas.toBlob((blob) => {
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = name.replace(/[^\w.-]+/g, '_') + '.png';
      a.click();
      setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    });
  };
  img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(new XMLSerializer().serializeToString(clone));
}

// ---- dashboard page ----------------------------------------------------------------------------
(() => {
  const root = $('dash');
  if (!root) return;
  const isAdmin = root.dataset.admin === '1';
  root.innerHTML = '<div class="dash-inner"><div id="bar"></div><div id="body"></div></div>';
  const bar = $('bar'), body = $('body');
  const state = { d: '', from: '', to: '', f: {} };
  let barSig = '';
  const dlIcon = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v12M7 11l5 5 5-5"/><path d="M4 20h16"/></svg>';

  function query(extra = {}) {
    const p = new URLSearchParams();
    if (state.d) p.set('d', state.d);
    if (state.from) p.set('from', state.from);
    if (state.to) p.set('to', state.to);
    for (const [k, v] of Object.entries(state.f)) if (v) p.set(`f[${k}]`, v);
    for (const [k, v] of Object.entries(extra)) p.set(k, v);
    return p.toString();
  }

  const card = (c, inner, exportable) => `<section class="card"><header><h3>${esc(c.title)}</h3><span class="head-r">${c.sub ? `<span class="muted">${esc(c.sub)}</span>` : ''}${exportable ? `<button class="icon-btn sm" data-png="${esc(c.title)}" title="Download as PNG" aria-label="Download ${esc(c.title)} as PNG">${dlIcon}</button>` : ''}</span></header>${inner}</section>`;
  const draw = { line: lineChart, bar: barChart, donut };
  const isNum = (v) => /^-?[\d.,$%€£]+$/.test(v);

  function table(t) {
    const head = t.columns.map((c, i) => `<th class="${t.rows[0] && isNum(t.rows[0][i]) ? 'r' : ''}">${esc(c)}</th>`).join('');
    const rows = t.rows.map((r) => `<tr>${r.map((v, i) => `<td class="${isNum(v) ? 'mono r' : ''}">${/^status$/i.test(t.columns[i]) ? `<span class="tag s-${esc(String(v).toLowerCase())}">${esc(v)}</span>` : esc(v)}</td>`).join('')}</tr>`).join('');
    return `<div class="tbl-wrap"><table class="tbl"><thead><tr>${head}</tr></thead><tbody>${rows}</tbody></table></div>`;
  }

  function renderBar(d) {
    const sig = JSON.stringify([d.dashboards, d.current, d.filters]);
    if (sig !== barSig) {
      barSig = sig;
      const f = d.filters || { date: null, cols: [] };
      const picker = d.dashboards.length > 1
        ? `<select id="f-dash" aria-label="Dashboard">${d.dashboards.map((x) => `<option value="${esc(x.key)}" ${x.key === d.current ? 'selected' : ''}>${esc(x.title)}</option>`).join('')}</select>`
        : `<h2 class="dash-title">${esc(d.title || '')}</h2>`;
      const dates = f.date
        ? `<label>From <input type="date" id="f-from" min="${f.date.min}" max="${f.date.max}" value="${esc(state.from)}"></label><label>To <input type="date" id="f-to" min="${f.date.min}" max="${f.date.max}" value="${esc(state.to)}"></label>` : '';
      const cols = f.cols.map((c) => `<label>${esc(c.name)} <select data-fcol="${esc(c.name)}"><option value="">All</option>${c.values.map((v) => `<option ${state.f[c.name] === v ? 'selected' : ''}>${esc(v)}</option>`).join('')}</select></label>`).join('');
      bar.innerHTML = `<div class="dash-bar">${picker}${dates}${cols}<button class="btn sm" id="f-reset">Reset</button><a class="btn sm" id="f-csv" download>Export CSV</a></div>`;
    }
    const csv = $('f-csv');
    if (csv) csv.href = 'api/dashboard.php?' + query({ export: 'csv' });
  }

  function render(d) {
    if (!d.dashboards.length) {
      bar.innerHTML = '';
      barSig = '';
      body.innerHTML = `<div class="empty-state card"><b>No dashboard yet</b><span class="muted">${isAdmin
        ? 'Upload a CSV or Excel file (or paste a Google Sheets link) in <a href="admin.php">Administration</a>, then add it as a dashboard.'
        : 'An administrator has not set up a dashboard yet.'}</span></div>`;
      return;
    }
    state.d = d.current;
    renderBar(d);
    if (d.error) { body.innerHTML = `<div class="form-error">${esc(d.error)}</div>`; return; }

    const by = (type) => d.charts.find((c) => c.type === type);
    const lines = d.charts.filter((c) => c.type === 'line'), bars = d.charts.filter((c) => c.type === 'bar');
    const cell = (c) => c && card(c, draw[c.type](c), true);
    const pair = (a, b, cls) => {
      const parts = [a, b].filter(Boolean);
      return parts.length ? `<div class="row ${parts.length > 1 ? cls : ''}">${parts.join('')}</div>` : '';
    };
    const kpis = d.kpis.map((k) => `
      <div class="card kpi-card">
        <span class="kpi-label">${esc(k.label)}</span>
        <span class="kpi-value mono">${esc(k.value)}</span>
        <span class="delta ${k.delta >= 0 ? 'up' : 'down'}">${k.delta >= 0 ? '▲' : '▼'} ${Math.abs(k.delta)}% <span class="muted">vs previous</span></span>
        ${sparkline(k.trend)}
      </div>`).join('');
    const filtered = d.source.rows !== d.source.total ? ` (filtered from ${d.source.total})` : '';
    const empty = d.noRows ? '<div class="empty-state card"><b>No rows match the filters</b><span class="muted">Change or reset the filters above.</span></div>' : '';
    const second = bars[0] || lines[1];

    body.innerHTML = `<div class="src muted">Data: <b class="mono">${esc(d.source.name)}</b> · ${d.source.rows} rows${filtered} · ${d.source.columns} columns</div>
      ${kpis ? `<div class="kpi-grid">${kpis}</div>` : ''}${empty}
      ${pair(cell(lines[0]), cell(by('donut')), 'r-2-1')}
      ${d.noRows ? '' : pair(cell(second), card(d.table, table(d.table), false), 'r-1-2')}`;
  }

  let seq = 0;
  function load() {
    const mine = ++seq;
    return fetch('api/dashboard.php?' + query(), { credentials: 'same-origin' })
      .then((r) => (r.status === 401 ? (location.href = 'login.php') : r.json()))
      .then((d) => { if (mine === seq) render(d); })
      .catch(() => { if (!body.children.length) body.innerHTML = '<div class="empty-state card"><b>Could not load data</b></div>'; });
  }

  bar.addEventListener('change', (e) => {
    const t = e.target;
    if (t.id === 'f-dash') Object.assign(state, { d: t.value, from: '', to: '', f: {} });
    else if (t.id === 'f-from') state.from = t.value;
    else if (t.id === 'f-to') state.to = t.value;
    else if (t.dataset.fcol) state.f[t.dataset.fcol] = t.value;
    else return;
    if (t.id === 'f-dash') barSig = '';
    load();
  });
  bar.addEventListener('click', (e) => {
    if (e.target.id === 'f-reset') { Object.assign(state, { from: '', to: '', f: {} }); barSig = ''; load(); }
  });
  body.addEventListener('click', (e) => {
    const b = e.target.closest('[data-png]');
    if (!b) return;
    const svg = b.closest('.card').querySelector('svg.chart, .donut svg');
    if (svg) svgToPng(svg, b.dataset.png);
  });

  load();
  setInterval(load, 30000);
})();

// ---- admin page: files + dashboards ------------------------------------------------------------
(() => {
  const main = $('admin');
  if (!main) return;
  const csrf = document.querySelector('meta[name=csrf]').content;
  const API = 'api/files.php';

  async function call(query, { method = 'GET', body, form } = {}) {
    const headers = {};
    if (method !== 'GET') headers['X-CSRF-Token'] = csrf;
    if (body !== undefined) headers['Content-Type'] = 'application/json';
    const res = await fetch(`${API}?${query}`, { method, headers, body: form ?? (body !== undefined ? JSON.stringify(body) : undefined), credentials: 'same-origin' });
    if (res.status === 401) location.href = 'login.php';
    const data = res.status === 204 ? null : await res.json().catch(() => null);
    if (!res.ok) throw new Error(data?.error || `Request failed (${res.status})`);
    return data;
  }
  const post = (action, body) => call('action=' + action, { method: 'POST', body });

  // ---- notices ----
  let toastTimer;
  function toast(msg) {
    const t = $('toast');
    t.textContent = msg;
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => (t.hidden = true), 4000);
  }
  function showError(msg) {
    const el = $('error');
    el.textContent = msg;
    el.hidden = !msg;
    if (msg) el.scrollIntoView({ block: 'nearest' });
  }
  try {
    const n = sessionStorage.getItem('notice');
    if (n) { sessionStorage.removeItem('notice'); toast(n); }
  } catch { /* storage unavailable */ }
  const reloadWith = (msg) => {
    try { sessionStorage.setItem('notice', msg); } catch { /* ignore */ }
    location.reload();
  };

  // ---- upload ----
  async function upload(list) {
    showError('');
    const errors = [];
    let ok = 0;
    for (const f of list) {
      const form = new FormData();
      form.append('file', f);
      try { await call('action=upload', { method: 'POST', form }); ok++; } catch (e) { errors.push(`${f.name}: ${e.message}`); }
    }
    if (errors.length) showError(errors.join(' · '));
    if (ok && !errors.length) reloadWith(`Uploaded ${ok} file${ok > 1 ? 's' : ''}`);
    else if (ok) setTimeout(() => reloadWith(`Uploaded ${ok}, ${errors.length} failed`), 2500);
  }
  $('upload-btn').addEventListener('click', () => $('upload-input').click());
  $('upload-input').addEventListener('change', (e) => { upload([...e.target.files]); e.target.value = ''; });
  main.addEventListener('dragover', (e) => { e.preventDefault(); main.classList.add('drag'); });
  main.addEventListener('dragleave', (e) => { if (e.target === main) main.classList.remove('drag'); });
  main.addEventListener('drop', (e) => { e.preventDefault(); main.classList.remove('drag'); upload([...e.dataTransfer.files]); });

  // ---- search + sort ----
  $('search').addEventListener('input', (e) => {
    const q = e.target.value.trim().toLowerCase();
    let shown = 0;
    document.querySelectorAll('.group').forEach((g) => {
      let any = false;
      g.querySelectorAll('.file-row').forEach((r) => {
        const hit = !q || r.dataset.search.includes(q);
        r.hidden = !hit;
        any ||= hit;
      });
      g.hidden = !any;
      shown += any ? 1 : 0;
    });
    $('empty').hidden = shown > 0 || !document.querySelector('.group');
    $('empty-title').textContent = 'No files match your search';
  });
  document.querySelector('.sort select').addEventListener('change', (e) => e.target.form.submit());

  // ---- modals ----
  const closeModals = () => document.querySelectorAll('.modal-back:not([hidden])').forEach((m) => (m.hidden = true));
  document.querySelectorAll('.modal-back').forEach((m) => {
    m.addEventListener('mousedown', (e) => e.target === m && closeModals());
    m.querySelectorAll('[data-close]').forEach((b) => b.addEventListener('click', closeModals));
  });
  document.addEventListener('keydown', (e) => e.key === 'Escape' && closeModals());

  // view / edit
  const text = $('fm-text'), status = $('fm-status'), save = $('fm-save'), format = $('fm-format');
  let current = null, original = '';

  const jsonError = () => {
    if (current?.ext !== 'json') return '';
    try { JSON.parse(text.value); return ''; } catch (e) { return e.message; }
  };
  function refresh() {
    const bad = jsonError();
    const editing = !text.readOnly;
    status.className = 'validity ' + (bad ? 'bad' : current.ext === 'json' ? 'good' : '');
    status.textContent = bad || (current.ext === 'json' ? 'Valid JSON' : '');
    save.disabled = !editing || !!bad || text.value === original;
    format.hidden = !(editing && current.ext === 'json' && !bad);
  }
  text.addEventListener('input', refresh);
  format.addEventListener('click', () => { text.value = JSON.stringify(JSON.parse(text.value), null, 2); refresh(); });

  async function openFile(el, mode) {
    current = { id: el.dataset.id, name: el.dataset.name, ext: el.dataset.ext, editable: el.dataset.editable === '1' };
    const isImage = ['png', 'jpg', 'jpeg'].includes(current.ext);
    const editing = mode === 'edit';
    $('fm-title').textContent = current.name;
    $('fm-mode').textContent = editing ? 'Editing' : 'Read only';
    $('fm-img').hidden = !isImage;
    if (isImage) { $('fm-img').src = `${API}?action=raw&id=${encodeURIComponent(current.id)}`; $('fm-img').alt = current.name; }
    text.hidden = !current.editable;
    $('fm-none').hidden = isImage || current.editable;
    text.readOnly = !editing;
    save.hidden = !editing;
    format.hidden = true;
    $('fm-cancel').textContent = editing ? 'Cancel' : 'Close';
    status.textContent = '';
    text.value = '';
    original = '';
    $('file-modal').hidden = false;
    if (current.editable) {
      try {
        const f = await call(`action=get&id=${encodeURIComponent(current.id)}`);
        text.value = original = f.content;
        refresh();
      } catch (e) { status.className = 'validity bad'; status.textContent = e.message; }
    }
  }

  save.addEventListener('click', async () => {
    save.disabled = true;
    try {
      await post('save', { id: current.id, content: text.value });
      reloadWith(`Saved ${current.name}`);
    } catch (e) { status.className = 'validity bad'; status.textContent = e.message; save.disabled = false; }
  });

  // delete (files) / remove (dashboards) share one confirm dialog
  let pending = null;
  function confirmDialog(p, title, label) {
    pending = p;
    $('del-title').textContent = title;
    $('del-name').textContent = p.name;
    $('del-confirm').textContent = label;
    $('del-modal').hidden = false;
  }
  $('del-confirm').addEventListener('click', async () => {
    const btn = $('del-confirm');
    btn.disabled = true;
    try {
      if (pending.kind === 'dash') { await post('dash_remove', { key: pending.key }); reloadWith(`Removed ${pending.name}`); }
      else { await post('delete', { id: pending.id }); reloadWith(`Deleted ${pending.name}`); }
    } catch (e) { closeModals(); showError(e.message); }
    btn.disabled = false;
  });

  // ---- dashboards ----
  async function addDashboard(body, okMsg) {
    showError('');
    try {
      const r = await post('dash_add', body);
      const what = (r.summary || []).filter(([k]) => k !== 'Table' && k !== 'Filters').map(([k]) => k).join(', ');
      reloadWith(`${okMsg}. It will show: ${what || 'a data table'}. Rename or customize it below.`);
    } catch (e) { showError(e.message); }
  }
  $('sheet-add').addEventListener('click', () => {
    const url = $('sheet-url').value.trim();
    if (url) addDashboard({ type: 'url', url }, 'Google Sheet added as a dashboard');
  });

  // Customize form
  let czKey = null;
  const opt = (value, label, sel) => `<option value="${esc(value)}" ${sel ? 'selected' : ''}>${esc(label)}</option>`;
  async function openCustomize(key) {
    czKey = key;
    const form = $('cz-form');
    $('cz-status').textContent = '';
    form.innerHTML = '<p class="muted">Loading…</p>';
    $('cz-modal').hidden = false;
    let info;
    try { info = await call('action=dash_columns&key=' + encodeURIComponent(key)); } catch (e) { form.innerHTML = `<div class="form-error">${esc(e.message)}</div>`; return; }
    const cfg = Array.isArray(info.config) ? {} : info.config;
    const nums = info.columns.filter((c) => c.type === 'number'), texts = info.columns.filter((c) => c.type !== 'number');
    const kpi = Object.fromEntries((cfg.kpis || []).map((k) => [k.col, k.agg]));
    const line = new Set(cfg.line || []);
    const pick = (cur, extra, list) => extra.map(([v, l]) => opt(v, l, cur === v)).join('') + list.map((c) => opt(c.name, c.name, cur === c.name)).join('');
    const has = (k) => Object.prototype.hasOwnProperty.call(cfg, k);
    const typeLabel = { number: 'number', date: 'date', text: 'text' };
    form.innerHTML = `
      <div class="cz-info">
        <div><b>Your columns</b> <span class="muted">(detected from the file)</span></div>
        <div class="cz-cols">${info.columns.map((c) => `<span class="chip">${esc(c.name)} · ${typeLabel[c.type]}</span>`).join('')}</div>
        <div><b>Currently shows</b></div>
        <dl class="dash-sum">${info.summary.map(([k, v]) => `<dt>${esc(k)}</dt><dd>${esc(v)}</dd>`).join('')}</dl>
      </div>
      <label class="cz-field">Name <small>(shown in the dashboard drop-down)</small> <input name="title" maxlength="60" value="${esc(info.title)}"></label>
      <label class="cz-field">Date column
        <select name="date">${pick(has('date') ? cfg.date : '__auto', [['__auto', 'Auto'], ['', 'None']], info.columns)}</select></label>
      <fieldset><legend>KPI cards <small>(up to 4 · none ticked = auto)</small></legend>
        ${nums.length ? nums.map((c) => `<div class="cz-row"><label><input type="checkbox" data-kpi="${esc(c.name)}" ${c.name in kpi ? 'checked' : ''}> ${esc(c.name)}</label>
          <select data-agg="${esc(c.name)}">${opt('auto', 'Auto', (kpi[c.name] || 'auto') === 'auto')}${opt('sum', 'Total', kpi[c.name] === 'sum')}${opt('avg', 'Average', kpi[c.name] === 'avg')}</select></div>`).join('') : '<p class="muted">No numeric columns found.</p>'}
      </fieldset>
      <fieldset><legend>Line chart columns <small>(up to 2 · none ticked = auto)</small></legend>
        ${nums.map((c) => `<label class="cz-chk"><input type="checkbox" data-line="${esc(c.name)}" ${line.has(c.name) ? 'checked' : ''}> ${esc(c.name)}</label>`).join('') || '<p class="muted">No numeric columns found.</p>'}
      </fieldset>
      <div class="cz-grid">
        <label class="cz-field">Group by (bar chart)
          <select name="category">${pick(has('category') ? cfg.category : '__auto', [['__auto', 'Auto'], ['', 'None']], texts)}</select></label>
        <label class="cz-field">Measure
          <select name="metric">${pick(cfg.metric || '__auto', [['__auto', 'Auto'], ['__count', 'Count rows']], nums)}</select></label>
        <label class="cz-field">Group chart style
          <select name="barType">${opt('bar', 'Bars', cfg.barType !== 'line')}${opt('line', 'Line', cfg.barType === 'line')}</select></label>
        <label class="cz-field">Donut by
          <select name="donut">${pick(has('donut') ? cfg.donut : '__auto', [['__auto', 'Auto'], ['', 'None']], texts)}</select></label>
      </div>`;
  }
  $('cz-save').addEventListener('click', async () => {
    const f = $('cz-form');
    const val = (n) => f.elements[n].value;
    const config = {
      kpis: [...f.querySelectorAll('[data-kpi]:checked')].slice(0, 4).map((c) => ({ col: c.dataset.kpi, agg: f.querySelector(`[data-agg="${CSS.escape(c.dataset.kpi)}"]`).value })),
      line: [...f.querySelectorAll('[data-line]:checked')].slice(0, 2).map((c) => c.dataset.line),
      date: val('date'), category: val('category'), metric: val('metric'), barType: val('barType'), donut: val('donut'),
    };
    try { await post('dash_config', { key: czKey, title: val('title'), config }); reloadWith('Dashboard saved'); } catch (e) { $('cz-status').className = 'validity bad'; $('cz-status').textContent = e.message; }
  });

  // inline rename: the name becomes an input; Enter saves, Escape cancels
  function startRename(row) {
    const nameEl = row.querySelector('.dash-name');
    if (nameEl.tagName === 'INPUT') return;
    const input = document.createElement('input');
    input.className = 'rename-input';
    input.maxLength = 60;
    input.value = row.dataset.title;
    input.setAttribute('aria-label', 'Dashboard name');
    nameEl.replaceWith(input);
    input.focus();
    input.select();
    let done = false;
    const finish = async (commit) => {
      if (done) return;
      done = true;
      const title = input.value.trim();
      if (!commit || !title || title === row.dataset.title) { location.reload(); return; }
      try { await post('dash_rename', { key: row.dataset.key, title }); reloadWith(`Renamed to “${title}”`); } catch (e) { showError(e.message); done = false; }
    };
    input.addEventListener('keydown', (e) => { if (e.key === 'Enter') finish(true); else if (e.key === 'Escape') finish(false); });
    input.addEventListener('blur', () => finish(true));
  }

  document.querySelectorAll('[data-dash]').forEach((b) =>
    b.addEventListener('click', () => {
      const row = b.closest('.dash-item'), key = row.dataset.key;
      if (b.dataset.dash === 'rename') startRename(row);
      else if (b.dataset.dash === 'config') openCustomize(key);
      else if (b.dataset.dash === 'default') post('dash_default', { key }).then(() => reloadWith(`${row.dataset.title} is now the default`), (e) => showError(e.message));
      else confirmDialog({ kind: 'dash', key, name: row.dataset.title }, 'Remove dashboard?', 'Remove');
    }),
  );

  // row actions (file name button + action icons); data-id/name/ext live on the element itself or its .acts parent
  document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-act]');
    if (!b) return;
    const src = b.closest('[data-id]');
    const act = b.dataset.act;
    if (act === 'delete') confirmDialog({ kind: 'file', id: src.dataset.id, name: src.dataset.name }, 'Delete file?', 'Delete');
    else if (act === 'dash') addDashboard({ type: 'file', id: src.dataset.id }, `${src.dataset.name} added as a dashboard`);
    else openFile(src, act);
  });
})();
</script>
<?php
}
