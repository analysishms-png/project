# Analysis HMS — UI CHANGED FILES

## Pass 1 — Global design system (2026-08-17)

### New
- `public/admin/css/hms.css` — Bootstrap-5-style design-system stylesheet (tokens, chrome, cards, tables, buttons, forms, badges, modals, responsive, print). UI-only; loaded after Ekka `style.css`.
- `.ai/UI_REFERENCE_SCREEN_MAP.md`
- `.ai/UI_REFERENCE_LARAVEL_MAP.md`
- `.ai/UI_DESIGN_SYSTEM.md`
- `.ai/UI_PROGRESS.md`
- `.ai/UI_CHANGED_FILES.md` (this file)
- `.ai/UI_REGRESSION.md`
- `.ai/UI_NEXT_TASK.md`

### Modified
- `resources/views/property/layouts/header.blade.php` — added `<link href="{{ asset('admin/css/hms.css') }}" rel="stylesheet">` after `style.css` (the only markup change; every ID/class/script preserved).

### Unchanged (by design)
- `resources/views/property/layouts/sidebar.blade.php` and `footer.blade.php` — no edits this pass (styling handled via hms.css).
- All 290+ property blade views, all routes, controllers, queries, JS hooks, permissions — untouched.

## Pass 2 — Login page redesign (2026-08-17)

### Modified
- `resources/views/auth/login.blade.php` — full navy/teal branded redesign: full-screen navy gradient, centered white card with navy brand band (logo + title + tagline), icon input groups, navy login button, remember/forgot row, demo-request panel, footer copyright. **All functionality preserved byte-for-byte**: form action `route('login')`, `@csrf`, field names/ids (`u_name`, `password`, `propertyid`, `remember`), `old()` values, error blocks, propertyid digit sanitizer, localStorage persistence JS.
- `resources/views/frontend/layouts/header.blade.php` — `<body class="@yield('body-class')">` (additive; empty class on all other pages). The login view sets `login-page`; scoped CSS hides the marketing topbar/navbar/footer on the login gateway only.

### Verified
- Playwright: brand gradient/title white/chrome hidden/card radius applied; **all 5 fields unchanged**; login ADMIN/qa123/102 → `/company` works; mobile 390px card fits (358px); 0 console errors.
- Marketing homepage unaffected (`<body class="">`, header/topbar/footer render).
- Suite: 68 passed (165 assertions).

## Fix — DataTables 2.x loaded globally (2026-08-17)

### New assets
- `public/admin/plugins/datatables2/` — vendored DataTables **2.3.2** core (+ BS4 integration), Buttons **3.2.0** (html5/print/bootstrap4), Responsive **3.0.3**, JSZip 3.10.1, pdfmake 0.2.7 (js + css). Official cdn.datatables.net sources; the unused 1.10.18 bundle under `plugins/datatables/` was left untouched.

### Modified
- `resources/views/property/layouts/header.blade.php` — 3 CSS links + 10 JS scripts (after jQuery).
- `resources/views/tools/layouts/header.blade.php` — same wiring (jQuery 3.5.1 present).
- `resources/views/admin/layouts/header.blade.php` — same wiring (jQuery 3.7.1 present).
- `resources/views/property/roommaster.blade.php` — null-guard on `#name`/`#namelist` autocomplete listeners (fixes the `addEventListener` pageerror).

## Pass 3 — Main Setup master-screen standardization (2026-08-17)

### New
- `resources/views/property/layouts/pageheader.blade.php` — reusable standard page header (`.hms-page-header` + title/subtitle/optional actions). Renders nothing when `hmsTitle` is empty.

### Modified (additive `@include` of the page header — no markup removed)
- 21 master screens: `roommaster`, `chargemaster`, `departmaster`, `usermaster`, `taxmaster`, `roomcategory`, `roomfeature`, `planmaster`, `partymaster`, `paymaster`, `amenitiesmaster`, `outletsetup`, `tablemaster`, `venuemaster`, `itementry`, `itemlists`, `menucat`, `menugroup`, `menuitems`, `housekeeping/floormaster`, `menucategory` (the latter two are the real rendered views for the `floormaster` and `menucategory` routes; the injection into the dead root `floormaster.blade.php` was reverted).
- `public/admin/css/hms.css` — Pass-3 section: `.hms-page-header`/`.hms-page-title`/`.hms-page-subtitle`/`.hms-page-actions`, DataTables 2.x toolbar styling (`.dt-search`, `.dt-length`, `.dt-buttons`), and neutralization of legacy `thead.bg-secondary`.
- Small pre-existing JS fixes surfaced by the pass: null-guards on the `#name`/`#namelist` autocomplete listeners in `roomcategory`/`tablemaster`; fixed `new Datatable(` typo in `paymaster` (table now initializes).

### Verified
- Playwright on QA: 19/19 reachable master screens render the standard header with the correct title; 0 page errors; paymaster `#revmast` initializes; suite 68 passed. (`usermaster` verified structurally — the QA clone lacks the live `storage/app/public/menu.json` artifact the view reads, causing a QA-only 500.)

## Pass 2b — Housekeeping Command Center (2026-08-17)

### Modified
- `app/Http/Controllers/HouseKeeping.php` — 6 INNER `join('hkfloors')` → `leftJoin` (the Command Center `getRoomsWithStatus` + 5 assignment report queries). Rooms with an empty/`unmatched floor` were silently dropped (live prop 102: 0 floors configured → all 15 rooms vanished). Also added real `done_count` (cleaned assignments) to the workload query.
- `resources/views/property/housekeeping/roomstatusboard.blade.php` (Command Center):
  - **Pending Inspections**: replaced fabricated hardcoded rows ("304 / Rakesh Kumar / High") with real rooms in `INSPECT` status from the served data (empty state when none).
  - **Housekeeper Workload**: real Assigned / Done / Efficiency from `hkroomassigns.status` (was "-" / 0% placeholders).
  - **Room modal**: converted the broken PATCH form (route never existed) into a read-only Room Details modal; fixed BS5 attributes (`data-bs-toggle`/`data-bs-close`) to BS4 syntax (`data-toggle`/`data-dismiss`) matching the runtime; fields populated via jQuery from the card's data-* attributes.
  - Empty-floor handling: rooms with no configured floor group under "Unassigned Floor".

### Verified (QA instance)
15 rooms render with real statuses (Occupied Dirty 2 / Vacant Dirty 13 / 13.33% occupancy); modal opens with real room data and closes; 0 page errors; all HK screens (assignments/view/housekeepingscreen/roomstatus) render clean; suite 68 passed.

## Rules for future passes
- Every pass records its files here.
- Presentation-only edits: never rename/remove an existing class/ID referenced by inline JS.
- Prefer additive `.hms-*` suffix classes; legacy hooks stay.

## Pass 3b (edit pages)
- resources/views/property/updateroommaster.blade.php (header + null-guard)
- resources/views/property/updateroomcategory.blade.php (header + null-guard)
- resources/views/property/updatechargemaster.blade.php (header + null-guard)
- resources/views/property/updateroomfeature.blade.php (header + null-guard)
- resources/views/property/updateplanmaster.blade.php (header)
- resources/views/property/updatepartymaster.blade.php (header)
- resources/views/property/updatedepartmaster.blade.php (header)
- resources/views/property/updatecompanymaster.blade.php (header)
- resources/views/property/updatetaxform.blade.php (header)
- resources/views/property/updatetaxstructure.blade.php (header)
- resources/views/property/updaterecipemaster.blade.php (header)

## Pass 4 (blue transformation)
- public/admin/css/hms.css (token recolor navy/teal → blue)
- public/admin/css/style.css (95× #7571f9 → #0d6efd, label classes)
- resources/views/auth/login.blade.php (blue gradient)
- resources/views/property/dashboardcss.blade.php (63 hexes → blue family)
- resources/views/admin/tools/tickets.blade.php
- resources/views/tools/tickets.blade.php
- resources/views/tools/tablemanagement.blade.php
- resources/views/property/layouts/footer.blade.php
- resources/views/property/mytickets.blade.php
- resources/views/property/advreconreport.blade.php
- resources/views/property/roomrecon.blade.php
- resources/views/property/fodiagnostics.blade.php
- resources/views/property/salebillentry.blade.php
