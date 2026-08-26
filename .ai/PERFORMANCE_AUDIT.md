# Analysis HMS — PERFORMANCE AUDIT

> Verified findings 2026-08-16. Measure before optimizing — do not optimize blindly.

---

## Findings

| # | Finding | Severity | Status |
|---|---------|----------|--------|
| PERF-01 | God controllers — CompanyController ~22.5K lines, PrintController ~6K, InventoryController ~5.9K, Reporting ~5.4K, Banquet ~4.7K, ToolsController ~4.7K | HIGH | OPEN — split gradually (never rewrite for style) |
| PERF-02 | Zero eager loading (`->with()`/`->load()`) in controllers | HIGH | PARTIALLY DONE 2026-08-26 — hot paths batched (Daily Report 224→66, reservedrooms 880→5, lookuproomtype 310→4, roominventory 110→3, getindex loops; Pos possalebillsettle mergedBills 2q→1 per bill, Banquet tax-name lookups 1q→1 per booking, focc_reportfetch depart 1q→1 per report); **2026-08-26**: Reporting.php 4 critical N+1 fixed (fetchposreportdata 300+→3 queries, fetchstddayreportdata 50+→1 query, amrmorningreportfetch 10→1 query, fetchoccupancyvsrevenuedata 200+→2 queries); remaining N+1 in Banquet.savebill and CompanyController.updatewalkinrate still OPEN |
| PERF-03 | Only 2 cache usages in whole app | MEDIUM | DONE 2026-08-17 — `App\Helpers\MasterDataCache` (travelAgents, corporates, companiesAndAgents, rooms, fomCharges) wired into walkin/reservation/openreservation/FOM/Reporting hot pages + flush() on all 22 subgroup/revmast/room_mast write paths (walkin page 15q/63.6ms → 13q/19ms). **Follow-up 2026-08-17**: per-date room availability cached too (`availableRooms` — version-keyed, `flushAvailability()` bumps the per-property version → all date combos invalidated in one write). Wired into getRoomswalkin, getRooms, openupdatewalkin/openupdatereservation room pickers; flush wired into 12 booking/blockout write paths (walkin submit/update/delete, reservation update, API/channel/frontend booking, HouseKeeping OOO blockout create/clear, Pointofsale checkout). TTL 300s safety net for any unwired write path. Reserved for the validation path in reservation submit (auto-fill/empty-room check) — always fresh. Measured: getRoomswalkin 1q cold → 0q warm, output byte-identical; regression test added. |
| PERF-04 | `QUEUE_CONNECTION=sync`, `CACHE_DRIVER=file`, `SESSION_DRIVER=file` | MEDIUM | OPEN — prod hardening |
| PERF-05 | Complex nested subqueries via `$query->toSql()` in DB::raw (Reporting/CheckRegister) | MEDIUM | OPEN — correct but hard to index |
| PERF-06 | `whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci')` repeated 8+ times in HouseKeeping | LOW-MED | OPEN — root fix = align table collations (schema change → approval) |
| PERF-07 | `orderByRaw('CAST(... AS DECIMAL)')` on string columns (datatables) | LOW | OPEN |
| PERF-08 | Large Excel exports (phpspreadsheet) may timeout | LOW | OPEN — chunked/queued exports |

## Quick wins (safe, no schema change)

1. **Eager loading on top 10 list pages** (PERF-02) — highest impact, low risk. DONE for Reporting.php top 4 hotspots.
2. **Cache master data** with `Cache::remember` (PERF-03) — travel agents, revenue codes, room lists; add cache invalidation on updates.
3. **Paginate unbounded queries** — DONE 2026-08-26: Added ->limit() safety caps on 7 unbounded queries (CompanyController roomocc 500, InventoryController purch1/gin 500, Banquet Hallsale1Est 500, HouseKeeping UpdateLog 1000). Remaining unbounded in Reporting.php batch fetch methods still OPEN.
4. **OPcache/JIT ini** for XAMPP (documented in `.ai/RESEARCH.md`).

## To measure (before/after)

- `EXPLAIN` on top 10 slowest queries (Reporting bill summaries, HouseKeeping floor joins, inventory stock views).
- Page load times on: reservation list, in-house list, housekeeping grid, reports.
- Suggest composite indexes on `(propertyid, vdate)`, `(paycode, propertyid)`, `(roomno, propertyid)` — **index addition = schema change → needs approval**.

## Guardrail

Do NOT add indexes or collation changes without approval (mission §22). Query-level fixes (eager loading, caching, pagination) are safe and can proceed.
