# Analysis HMS — UI DESIGN SYSTEM

A Bootstrap-5-**style** presentation layer implemented in `public/admin/css/hms.css`, loaded after the Ekka BS4 theme so it overrides the visual language **without touching functionality**. The runtime stack stays Bootstrap 4.1.3 + Ekka + plugins; only appearance, spacing, typography and component treatment change.

## Design tokens (CSS variables)

```css
:root {
  /* Brand — hotel-PMS navy + teal accent (replaces Ekka purple #7571f9) */
  --hms-primary:       #0f2b5b;   /* deep navy — sidebar, primary buttons */
  --hms-primary-2:     #14407e;   /* hover / gradient partner */
  --hms-accent:        #0ea5a4;   /* teal — highlights, active states */
  --hms-accent-2:      #14b8b6;
  --hms-secondary:     #64748b;   /* slate */
  --hms-success:       #16a34a;
  --hms-warning:       #f59e0b;
  --hms-danger:        #dc2626;
  --hms-info:          #0ea5e9;

  /* Surfaces */
  --hms-bg:            #f1f5f9;   /* page background (light slate) */
  --hms-card-bg:       #ffffff;
  --hms-sidebar-bg:    #0f2b5b;
  --hms-topbar-bg:     #ffffff;
  --hms-border:        #e2e8f0;

  /* Text */
  --hms-text:          #1e293b;
  --hms-text-muted:    #64748b;
  --hms-text-inverse:  #f8fafc;

  /* Shape & depth (Bootstrap-5 language) */
  --hms-radius:        .75rem;    /* cards */
  --hms-radius-sm:     .5rem;     /* inputs, buttons */
  --hms-shadow-sm:     0 1px 2px rgba(15,43,91,.06);
  --hms-shadow:        0 4px 16px rgba(15,43,91,.08);
  --hms-shadow-lg:     0 12px 32px rgba(15,43,91,.14);

  /* Type */
  --hms-font:          "Roboto", system-ui, -apple-system, "Segoe UI", sans-serif;
  --hms-font-size:     .875rem;
  --hms-h-size:        1.05rem;
}
```

## Page anatomy (applied to every screen)

```
Page header  →  h4.hms-page-title + .text-muted subtitle (breadcrumb slot optional)
Filter bar   →  .card.hms-filter-card  (label + form-control + search/print/export buttons)
Data table   →  .card > .table-responsive > table.table.hms-table (hover, striped)
Actions      →  .btn.btn-hms-primary | .btn-hms-light | .btn-hms-outline
Add/Edit     →  Bootstrap modal (existing data-toggle/data-target preserved)
```

## Component language

| Component | Treatment |
|---|---|
| Sidebar `.nk-sidebar` | deep-navy gradient, white 10% alpha separators, rounded active pill on accent, 1.05rem item text |
| Topbar `.header` | white, bottom hairline border, subtle shadow, 64px height, hamburger stays |
| KPI cards (dashboard) | white cards, colored icon tile (rounded), 1.4rem bold value, muted label, left accent bar per status |
| `.card` | white, `--hms-radius`, `--hms-shadow-sm`, `border: 0` |
| `table.hms-table` | `thead` navy-tinted background, uppercase 12px headers, hover row, striped |
| `.btn` (Ekka's BS4 buttons) | normalized radius/typography; add `.btn-hms-primary` (navy), `.btn-hms-light` (slate-50 + border), `.btn-hms-outline` |
| `.form-control`/`.form-select` | `--hms-radius-sm`, focus ring in accent |
| Badges/status chips | room status → pill badges: Clean(green)/Dirty(amber)/OOO(red)/Occupied(navy)/Inspection(purple) — **values still come from existing data** |
| Modal | radius, shadow-lg, header border-0, footer border-0 |
| Print | `@media print` — hide sidebar/topbar, white background, A4 margins, keep all report data |

## Rules of engagement

1. `hms.css` **only** adds/overrides presentation. No `display:none` on functional elements; no reordering via JS.
2. Never rename/remove an existing class or ID that inline JS references (e.g. `.nav-control`, `.toggle-icon`, `.mainmenu`, `#usernameshow`, `.propertysllist`).
3. New styles target existing selectors + new *suffix* classes (`.hms-*`) that pages may adopt progressively — adoption never removes a legacy hook.
4. No new fonts/CDNs that gate rendering; `Roboto` is already loaded by Ekka.
5. Dark sidebar + white content is the default; Ekka `settings.js` / `styleSwitcher.js` remain untouched.
6. Accessibility: keep focus rings (accent), minimum 4.5:1 contrast for text on navy (use `--hms-text-inverse`), larger hit targets on POS/KOT (≥40px).
