# Analysis HMS — UI MAP

> The UI is already modernized — **do not redesign randomly**. New modules must follow the existing design system below (verified from ticket views + layouts).

---

## Design system (existing)

- **Framework**: Bootstrap 5 + custom CSS; jQuery; SweetAlert2 for dialogs; DataTables (yajra) for tables; Font Awesome icons.
- **Signature gradient**: `linear-gradient(135deg, #667eea 0%, #764ba2 100%)` — page headers, modals, active filter tabs, primary buttons.
- **Cards**: white, `border-radius: 10px`, soft shadow (`0 2px 10px rgba(0,0,0,0.1)`), hover lift.
- **Badges**: rounded-pill status badges (`status-pending` amber, `status-working` cyan, `status-complete` green).
- **Layouts**: per-portal `layouts/main` — `admin/layouts/main`, `tools/layouts/main`, `property/layouts/header` (includes session printdata/nightinfo JSON), `frontend/` public.
- **Tables**: DataTables with server-side processing, sortable columns, `orderByRaw` CAST for numeric sort.
- **Forms**: `form-control`/`form-select` classes, inline validation via `Validator::make` + toast/Swal errors.
- **Modals**: Bootstrap `modal fade`, gradient modal headers.
- **Responsive**: container-fluid content-body, responsive grids.
- **Print**: dedicated print blades under `resources/views/property/prints/` and `*print*.blade.php` (thermal POS prints via websocket events).

## Conventions for new UI

1. Extend the correct portal layout (never invent a new shell).
2. Use the signature gradient for page headers/modals; keep card + badge styles.
3. Escape all user data with `{{ }}` (or `nl2br(e())` for multiline) — **never `{!! !!}` on user input** (BUG-022 regression rule).
4. Keep JS in the blade `<script>` block using jQuery + Swal patterns already present.
5. Preserve print layouts when touching billing views.
6. Don't change functionality while improving UI.

## Known UI anomalies (low priority)

- Junk file `resources/views/e = statename();.blade.php` (cleanup candidate — confirm unused).
- Legacy inline `<style>` blocks duplicated per view (acceptable; consolidation is P3).
