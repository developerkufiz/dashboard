# Dashboard

Plain PHP + HTML/CSS/JS. No database, no build step, no Node.
Guest/Admin login, an admin file manager, and **dashboards built from your own CSV, Excel (.xlsx) or Google Sheet**.
Until an admin adds a dashboard, the dashboard page is empty.

## Run
1. Copy the folder to `htdocs` (XAMPP) so it is served at `http://localhost/ammar/`.
2. Create `config/credentials.json` (it is git-ignored) containing:
   ```json
   { "username": "admin", "password": "CHANGE_ME", "token": "CHANGE_ME" }
   ```
3. Open `http://localhost/ammar/`. Needs PHP 8.1+ with `zip` (for .xlsx) and `curl` (for Google Sheets), and Apache (`.htaccess` is used).

## Make a dashboard
1. Sign in as admin -> **Administration** -> **Upload** a `.csv` or `.xlsx`.
2. Click the dashboard icon on the file row (**Add as dashboard**), or paste a Google Sheets link
   (File -> Share -> Publish to web -> CSV). Several dashboards are allowed; viewers switch between them.
3. **Customize** to override the automatic choices; **Make default** picks the one shown first; **Remove** deletes it.

The first row must be column names. By default:

| Your column | Becomes |
|---|---|
| Numbers (up to 4) | KPI cards: total (average for names like rate/avg/price/%), trend, change vs previous |
| Date column | Line chart (daily, monthly for long ranges) and a date-range filter |
| Text column with 2-30 values | Bar chart (top 10), donut (top 5 + Other) and a filter drop-down |
| First 10 rows | Data preview table |

Customize lets you choose the date column, which numbers become KPIs (total or average), the line-chart columns,
the bar/donut grouping, the measure, and bar vs line style. Everyone viewing a dashboard can filter by date range and
category, **Export CSV** (the filtered rows) and download any chart as **PNG**.

Limits: first sheet of an .xlsx, 5000 rows, 30 columns, 12 dashboards. Google Sheets data is cached for 5 minutes and
only `docs.google.com` / `*.googleusercontent.com` over https are contacted.

## Files
| Path | Purpose |
|---|---|
| `config/credentials.json` | Admin username / password / token (git-ignored, blocked from the web) |
| `inc/config.php` | Reads the credentials file and sets limits |
| `inc/data.php` | CSV/XLSX/Google Sheets reader, filters and automatic dashboard builder |
| `api/dashboard.php` | Dashboard data (guests + admins), filters and CSV export |
| `api/files.php` | Admin-only API: files and dashboard management |
| `inc/ui.php` | All CSS and JS in one file, printed inline on each page |
| `uploads/YYYYMMDD/` | Uploaded files; `uploads/_dashboards.json` lists dashboards (git-ignored, never served directly) |

## Security notes (not enterprise-grade)
- Guest = a session without admin rights. Every admin endpoint is checked on the server (HttpOnly, SameSite=Strict session cookie).
- CSRF token on all writes; `Authorization: Bearer <token>` also works for scripts. Login limited to 5 failures / 15 min / IP.
- Uploads: extension allowlist + content check, sanitized names, never overwritten, 5 MB cap, server-side date folder, ids validated (no path traversal), `uploads/` blocked from direct access and execution.
- CSV export neutralises spreadsheet formulas. `password` may be plain text or a `password_hash()` value. Use HTTPS in production.
- Without Apache (`php -S`) the `.htaccess` rules do not apply, so `config/` would be reachable. Use Apache/XAMPP.
