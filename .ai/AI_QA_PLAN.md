# AI QA — TEST PLAN (2026-08-17)

## Ordering (risk-first)

1. **Static safety sweep (all modules)** — permission guards on write endpoints, missing transactions, unguarded `->first()` derefs. Fix P0/P1 confirmed bugs.
2. **Auth / permission agent** — menuhelp-vs-guard parity scan; users without permission must get 403.
3. **Financial integrity agent** — re-run write-path audit for stragglers; verify audit trails (PaychargeLog/LedgerLogService/Suntranlog) on all delete paths; Dr=Cr invariants.
4. **Housekeeping agent** — covered by 2026-08-17 pass (BUG-045/046, 17 guards, 6 tests). Re-run regression.
5. **Reservation → Check-in → Folio → Settlement** — trace advance ₹1000 end-to-end (read-only: existing live rows reconcile).
6. **POS / Kot / Banquet** — atomicity + permission (POS partial pass done in ML-04; scan remaining).
7. **Inventory / Purchase** — atomicity (ML-06 done), PO/indent linkage (BUG-040-042 done) — scan stragglers.
8. **Accounts / Finance** — report joins property-scoped (BUG-044 fixed) — re-scan new reports.
9. **Performance agent** — scan remaining N+1 loops (PERF-02 tail).
10. **DB integrity agent** — orphan/duplicate checks on live DB (read-only).

## Agent assignments (logical)

| # | Agent | Scope | Status |
|---|---|---|---|
| 01 | MASTER ORCHESTRATOR | plan, combine, report | ACTIVE |
| 02 | AUTHENTICATION | login, QR login, session | PENDING |
| 03 | USER/PERMISSION | menuhelp parity, 403 on bypass | PENDING |
| 04 | MASTER DATA | subgroup/revmast/room_mast caches, CRUD guards | PENDING |
| 05 | GUEST MANAGEMENT | guestprof, C-Form (GM gaps) | PENDING |
| 06 | ROOM MASTER | room_mast CRUD guards | PENDING |
| 07 | RESERVATION | advance→folio trace, cancel | PENDING |
| 08 | ADVANCE/PAYMENT | paycharge audit, void/reverse | PENDING |
| 09 | CHECK-IN | atomicity (verified ML-02) | DONE* |
| 10 | FOLIO/LEDGER | merge/reverse (verified ML-05), ledger posting | DONE* |
| 11 | CHECK-OUT | atomicity (verified ML-03) | DONE* |
| 12 | FRONT OFFICE | walkin/reservation pages, availability cache | DONE* |
| 13 | HOUSEKEEPING | full pass 2026-08-17 (BUG-045/046) | DONE* |
| 14 | POS | atomicity (ML-04), permission | PARTIAL |
| 15 | KOT | token sequence | DONE* |
| 16 | BANQUET | tax posting batch, atomicity | PARTIAL |
| 17 | INVENTORY | atomicity (ML-06) | DONE* |
| 18 | PURCHASE | PO/indent linkage (BUG-040-042) | DONE* |
| 19 | ACCOUNTS | report join scope (BUG-044), GL | DONE* |
| 20 | GST/TAX | taxstru slabs | PENDING |
| 21 | NIGHT AUDIT | daily report batch, depdate updates | PARTIAL |
| 22 | REPORTING | N+1 scan (PERF-02) | PARTIAL |
| 23 | HR/PAYROLL | module exists (HrpayrollsController) — scan | PENDING |
| 24 | LAUNDRY | guarded 2026-08-17 | DONE* |
| 25 | SECURITY | XSS (BUG-022), CSRF, auth bypass | PENDING |
| 26 | DATABASE INTEGRITY | orphan/duplicate/negative checks | PENDING |
| 27 | UI/UX | Playwright smoke | PENDING |
| 28 | PERFORMANCE | N+1, availability cache (PERF-03) | PARTIAL |
| 29 | CONCURRENCY | vno/docid max+1 races | PENDING |
| 30 | REGRESSION | full suite after each fix | RUNNING |

*DONE = covered by prior documented passes (see .ai/CHANGELOG_AI.md, COMPLETED_TASKS.md). Re-run regression as part of this QA cycle.
