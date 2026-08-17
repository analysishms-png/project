# UI NEXT TASK

## DONE — UI Pass 4: COMPLETE BLUE transformation
- Global design system recolored navy/teal → Bootstrap blue (#0d6efd) across hms.css, style.css, login, dashboard, 9 blades
- Verified on QA: 0 purple theme remnants, 0 errors, suite 68 passed

## NEXT — UI Pass 5 (module screens)
- Convert remaining module-specific inline styles (POS, KOT, Banquet screens with bespoke colors)
- Standardize report filter bars + summary cards (blue layout per master prompt §27)
- Full module sweep for stray legacy colors

## DONE — UI Pass 3b (edit pages)
- 11 update/edit blades now have standard "Edit X" page headers (40 screens total with headers)
- 4 update pages' autocomplete null-listener pageerrors fixed
- Suite: 68 passed (165 assertions)

## NEXT — UI Pass 3 remainder
- Finish remaining Main Setup screens (waiter/events maps, member master)
- Filter-bar standardization for master tables

## Current queue

### ✅ DONE — Pass 1: Global design system + chrome (2026-08-17)
Design tokens + `hms.css` + single header hook; navy/teal modern look applied app-wide; Playwright before/after verified; suite green.

### ✅ DONE — Pass 2a: Login page redesign (2026-08-17)
Navy/teal branded login (`auth/login.blade.php` + additive body-class hook in the frontend layout header). Fields/CSRF/redirect behavior byte-for-byte; marketing chrome hidden on login only; homepage unaffected; Playwright-verified desktop + mobile; suite 68 passed.

### ✅ DONE — Pass 3: Main Setup master-screen standardization (2026-08-17)
Reusable `property.layouts.pageheader` partial + hms.css components (`.hms-page-header`, DataTables toolbar styling) applied to **21 master screens** — standard Page Header → Form/Filter → Data Table + Actions anatomy with zero functional change. Playwright: 19/19 reachable screens render headers, 0 page errors (also fixed roomcategory/tablemaster `#name` null-guards + paymaster `Datatable` typo). Suite 68 passed.

### ✅ DONE — Pass 2b: Housekeeping Command Center (2026-08-17)
Completed with real data only: fixed the hkfloors INNER-join bug that emptied the board (P1, 6 sites), real pending inspections (fabricated rows removed), real workload efficiency from `hkroomassigns`, working read-only room modal (was a broken PATCH to a non-existent route). Playwright-verified; suite 68 passed.

### 🔶 NEXT — Pass 4: POS/KOT touch pass + report filter standardization
Large touch targets, sticky table headers, standardized report header/filter/summary/export/print blocks for Finance/FO/Inventory/Banquet — queries and totals untouched.

### Then — Pass 3: Master screens standardization
Apply the standard anatomy (Page Header → Filter → Actions → Table → Modal) to Main Setup screens using existing hooks; convert the largest tables to card + responsive treatment.

### Then — Pass 4: POS/KOT touch pass + report filter standardization (Finance/FO/Inventory/Banquet)
Large hit targets, sticky table headers, standardized report header/filter/summary/export/print blocks — queries and totals untouched.

### Blocked / deferred
- Full Bootstrap 5 framework swap: **blocked by design** — property module is BS4.1.3; hundreds of views + BS4-era plugins (`summernote-bs4`, `bootstrap-datepicker`, `data-toggle`/`data-dismiss`) would break. The design-system layer delivers the BS5 visual language without the risk.
- Members Mgmt / Maintenance reference screens: no demo screenshots captured (permission-gated in the reference run) — verify access before redesign.
- FA_Parameter reference screen: no obvious blade — confirm route existence before mapping.
