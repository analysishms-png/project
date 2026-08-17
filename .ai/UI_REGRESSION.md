# Analysis HMS — UI REGRESSION

## Pass 1 — Global design system (2026-08-17)

### Method
Playwright (Chromium) against a dedicated QA instance (`analysis_qa` clone, property 102, `artisan serve :8001`) — no production writes. Before = `hms.css` request aborted; After = normal.

### Assertions (computed styles, 1366px)
| Check | Before | After |
|---|---|---|
| `.nk-sidebar` background | white `rgb(255,255,255)` | navy `linear-gradient(rgb(15,43,91), rgb(10,31,66))` |
| `.btn-primary` background | purple `rgb(117,113,249)` | navy `rgb(15,43,91)` |
| `.card` border / radius | 1px / 0 | 0 / 12px |
| `body` background | — | slate `rgb(241,245,249)` |
| `.table thead th` background | — | `rgb(248,250,252)` + uppercase |
| `.nav-header` | dark | navy gradient |
| `.page-titles` background | `#F3F3F9` | transparent |
| `.page-titles .breadcrumb a` | black | navy `rgb(15,43,91)` |

### Functional smoke
- Login → dashboard (desktop + mobile 390px): ✅
- Mobile off-canvas sidebar hidden by default (`left:-390px`) + toggle: ✅
- Dynamic sidebar menu injection under new styling (click Finance → 3 submenu items, light-on-navy text): ✅
- Topbar room-status navigation: ✅
- Full PHPUnit suite: **68 passed (165 assertions)**, 1 skipped (live-DB fixture absent) — no regressions.
- `php -l` header.blade.php clean; `view:cache` compiles.

## Pass 2 — Login redesign (2026-08-17)

### Assertions (Playwright, desktop 1366 + mobile 390)
| Check | Result |
|---|---|
| `body` class hook | `login-page` |
| `.login-brand` background | navy→teal `linear-gradient(rgb(15,43,91), rgb(20,64,126), rgb(14,165,164))` |
| `.login-title` color | white |
| `#header` / `#topbar` / `#footer` on login | `display: none` (scoped to `.login-page`) |
| `.login-card` radius | 16px |
| Form fields | `_token, u_name, password, propertyid, remember` — identical to before |
| Login end-to-end (ADMIN/qa123/102) | → `/company` ✅ |
| Mobile card width (390px viewport) | 358px — fits, no overflow |
| Console/page errors | 0 |

### Marketing homepage (shared layout) regression
`<body class="">` — additive hook is a no-op elsewhere; `#header`/`#topbar`/`#footer` all render. ✅

### FIXED — DataTables-loading errors (2026-08-17, functional bug fix)
Root cause: the property/tools/admin layouts never loaded the DataTables plugin, yet **101 views** call it — 49 with the DataTables **2.x-only** `new DataTable(...)` syntax. The bundled asset was 1.10.18 and unused.

Fix: vendored **DataTables 2.3.2 + Buttons 3.2.0 + Responsive 3.0.3** (with JSZip + pdfmake) into `public/admin/plugins/datatables2/` and loaded them in all three layout headers (CSS in `<head>`, JS after jQuery). Also null-guarded roommaster's `#name`/`#namelist` autocomplete listeners (bound to a non-existent element → `addEventListener` pageerror).

Verified on QA (Playwright): `roommaster`, `voucherentry`, `advancelist`, `checkinlist`, `bookingsource` all show full DataTables 2.x features (search/info/paging); export buttons render where configured (voucherentry 3, bookingsource 4); **0 DataTables errors and 0 pageerrors on those pages**; mobile OK; DataTables globals present on the admin layout too. Suite 68 passed.

## Pass 3 — Master-screen standardization (2026-08-17)

### Assertions (Playwright, 19 reachable Main Setup screens)
| Check | Result |
|---|---|
| `.hms-page-header` rendered | ✅ on all 19 (roommaster, chargemaster, departmaster, floormaster, roomfeatures, roomcategory, planmaster, partymaster, paymaster, amenitiesmaster, setupoutlet, tablemaster, itementery, itemlists, menucategory, menugroup, + verified structurally for usermaster/venuemaster/menuitems) |
| `.hms-page-title` text | matches screen (e.g. "Room Master") |
| Page errors | 0 — fixed roomcategory/tablemaster `#name` null-guards + paymaster `Datatable`→`DataTable` typo (`#revmast` now initializes) |
| DataTables toolbars | `.dt-search`/`.dt-length` styled; export buttons keep working |
| Full suite | 68 passed (165 assertions) |

### QA-environment note (not a code defect)
- `/usermaster` 500s on the QA clone only: the view reads `storage/app/public/menu.json`, a live-server artifact absent from the QA clone. Blade structure verified correct (permission granted; page passes the guard and reaches the view).
- `/venuemaster`, `/menuitems` are permission-gated for the QA login (no menuhelp rows) — blades verified as the controllers' rendered views.

## Pass 2b — Housekeeping Command Center (2026-08-17)

### Fixes & assertions (Playwright on QA)
| Check | Result |
|---|---|
| Room grid | 15 real rooms render (was **0** — `getRoomsWithStatus` INNER-joined `hkfloors`; live prop 102 has 0 floors so every room was dropped — fixed to LEFT join, all 6 sites) |
| Status chips | Total 15 / Occupied Dirty 2 / Vacant Dirty 13 / Occupancy 13.33% — matches DB |
| Pending Inspections | real INSPECT-status rooms; fabricated "Rakesh Kumar/Pankaj Sharma" rows removed |
| Workload | real Assigned/Done/Efficiency from `hkroomassigns.status='cleaned'` (no "-"/0% placeholders) |
| Room modal | opens, shows real room + status, closes — was broken: PATCH route never existed + BS5 attributes (`data-bs-toggle`) on a BS4 runtime; now read-only + BS4 syntax |
| Page errors | 0; assignments/view/housekeepingscreen/roomstatus all render clean |
| Full suite | 68 passed (165 assertions) |

### Remaining pre-existing issues (NOT caused by the UI/DataTables passes)
- `GeolocationError` — dashboard weather widget in a non-secure/headless context (browser-level, cosmetic).
- `Datamap is not defined` — `superadmin/backups` page references a Google-Charts `Datamap` global that isn't loaded on that page (separate page bug; logged for the functional backlog).

## Pass 3b — update/edit pages
- 4 edit pages (updateroommast, updateroomcategory, updateplanmast, updatechargemaster) verified: standard header renders, 0 page errors
- Fixed `#name` autocomplete null-listener pageerrors on 4 update blades (same family as list-page fixes)
- Full suite: 68 passed (165 assertions)

## Pass 4 — blue transformation regression
- Login gradient rgb(13,110,253)→rgb(8,66,152) asserted; sidebar blue gradient; primary buttons #0d6efd
- Dashboard: 5 KPI cards render, 0 page errors, gradients blue
- roommaster / outletsetup / poskot / reservationlist / chargemaster: 0 errors, no purple theme hexes
- Mobile login 358px fits, 0 errors
- Suite: 68 passed (165 assertions)
