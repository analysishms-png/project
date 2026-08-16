# Analysis HMS — PERFORMANCE AUDIT

> Verified findings 2026-08-16. Measure before optimizing — do not optimize blindly.

---

## Findings

| # | Finding | Severity | Status |
|---|---------|----------|--------|
| PERF-01 | God controllers — CompanyController ~22.5K lines, PrintController ~6K, InventoryController ~5.9K, Reporting ~5.4K, Banquet ~4.7K, ToolsController ~4.7K | HIGH | OPEN — split gradually (never rewrite for style) |
| PERF-02 | Zero eager loading (`->with()`/`->load()`) in controllers | HIGH | OPEN — N+1 on every list/report page; add on hot paths first |
| PERF-03 | Only 2 cache usages in whole app | MEDIUM | OPEN — cache master data (travel agents, revmast, room lists) |
| PERF-04 | `QUEUE_CONNECTION=sync`, `CACHE_DRIVER=file`, `SESSION_DRIVER=file` | MEDIUM | OPEN — prod hardening |
| PERF-05 | Complex nested subqueries via `$query->toSql()` in DB::raw (Reporting/CheckRegister) | MEDIUM | OPEN — correct but hard to index |
| PERF-06 | `whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci')` repeated 8+ times in HouseKeeping | LOW-MED | OPEN — root fix = align table collations (schema change → approval) |
| PERF-07 | `orderByRaw('CAST(... AS DECIMAL)')` on string columns (datatables) | LOW | OPEN |
| PERF-08 | Large Excel exports (phpspreadsheet) may timeout | LOW | OPEN — chunked/queued exports |

## Quick wins (safe, no schema change)

1. **Eager loading on top 10 list pages** (PERF-02) — highest impact, low risk.
2. **Cache master data** with `Cache::remember` (PERF-03) — travel agents, revenue codes, room lists; add cache invalidation on updates.
3. **Paginate unbounded queries** — audit `->get()` on large tables.
4. **OPcache/JIT ini** for XAMPP (documented in `.ai/RESEARCH.md`).

## To measure (before/after)

- `EXPLAIN` on top 10 slowest queries (Reporting bill summaries, HouseKeeping floor joins, inventory stock views).
- Page load times on: reservation list, in-house list, housekeeping grid, reports.
- Suggest composite indexes on `(propertyid, vdate)`, `(paycode, propertyid)`, `(roomno, propertyid)` — **index addition = schema change → needs approval**.

## Guardrail

Do NOT add indexes or collation changes without approval (mission §22). Query-level fixes (eager loading, caching, pagination) are safe and can proceed.
