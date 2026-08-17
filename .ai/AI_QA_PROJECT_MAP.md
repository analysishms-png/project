# AI QA — PROJECT MAP (discovered 2026-08-17)

> Source: live Laravel inspection (`app/`, `routes/`, `resources/views/`, live `analysis` DB).
> Laravel implementation is the source of truth over generated docs.

## Stack

| Component | Version / Value |
|---|---|
| Laravel | 10.50.2 (`laravel/framework ^10.10`) |
| PHP | 8.2.33 (ZTS, Windows) |
| DB | MySQL — live DB **`analysis`** (215 tables, ~598K paycharge rows) |
| Queue/broadcast | laravel/reverb 1.11, laravel/sanctum 3.2, livewire 4.4 |
| PDF | barryvdh/laravel-dompdf 3.x |
| Excel | phpoffice/phpspreadsheet 5.x |
| QR | endroid/qr-code 5, simplesoftwareio/simple-qrcode 4 |
| DataTables | yajra/laravel-datatables-oracle 10.11 |
| Dev | phpunit 10.1, laravel/pint, laravel/sail, migrations-generator 7 |

## Scale

| Item | Count |
|---|---|
| Controllers | 119 (top-level + subdirs: FrontOffice/, Frontend/, Reservation/, Api/, NightAudit/, Pos/, Finance/, Tools/, MainSetup/) |
| Models | 162 |
| Services | 6 (`AccountPosting`, `DailyReportSnapshotService`, …) |
| Blade views | 574 |
| Migrations | 419 |
| Route files | 12 (`web`, `api`, `company`, `pointofsale`, `reporting`, `tools`, `userparam`, `channel`, `console`, `kot`, `channel`, `pointofsale/kot`) |
| Registered routes | 1,521 |
| DB tables | 215 |
| Test files | 9 (Feature: CheckInOutRegression, PerformanceEagerLoad, RouteTest, HouseKeepingModule; Unit: Helpers) |

## Module → Controller → Routes map (top-level controllers)

| Module | Controllers | Notes |
|---|---|---|
| Auth | AutoLoginController, HkQrLoginController, PythonAuth | QR login, autologin dev helpers |
| Front Office | CompanyController (~20K lines, biggest), RoomController, RoomStatus, ChargePosting, CheckRegister, ChargePosting | walkin, reservation, folio, advance, night-audit |
| Housekeeping | HouseKeeping (4.5K lines), HkQrLoginController | room status board, cleaning, lost&found, laundry, damage, inspection |
| POS | Pos, Pointofsale, SaleBill, Kot | billing, settle, KOT |
| Banquet | Banquet | booking, halls, settlement |
| Inventory | InventoryController, MainSetup/Inventory/* | MR, PO, stock transfer, opening stock |
| Purchase | PurchaseController | PO/indent (some in InventoryController) |
| Accounts/Finance | FinanceController, ChargePosting, EInvoiceParameter, FinancialPush | GL, trial, P&L, balance sheet, exports |
| Reporting | Reporting (large), ReportController, PrintController | report + print views |
| HR | HrpayrollsController | |
| Property/Config | PropertyController, ConfigController, CompanyController (setup) | company reg, menus, masters |
| Misc | GatePassController, FeedbackMasterController, HolidayController, HappyhourController, MaintenanceController, BookingInquiryController, BookingFollowUp, DemoRequestController, DeveloperTools, MailController, WPParameter, Location | |

## Permission model

- `menuhelp` table: per (propertyid, username) rows with `code` (e.g. 151111), `opt1/2/3`, `route`, and flags `view/ins/edit/del/print`.
- Helper `revokeopen($code)` = `MenuHelp::where(propertyid, username, code)->first()` → null = no row = denied.
- Menu sidebar is built from `menuhelp` via `UserParam::getmainmenu`/`fetchsubmenu` — the same codes the controllers guard with.
- Many controllers check `revokeopen` at method top; audit passes have been filling gaps (BUG-040, HK pass 2026-08-17).

## Known risks / testing priority

| Risk | Priority |
|---|---|
| Financial write paths without transactions (mostly fixed 2026-08-17; re-scan for stragglers) | P0 |
| Missing permission guards on write endpoints (HK pass fixed 17; scan remaining modules) | P0 |
| N+1 query loops on list/report pages (PERF-02 partially done) | P1 |
| Report joins not scoped by property (BUG-044 fixed; re-check new reports) | P1 |
| Delete-path audit coverage (ML-08 done for paycharge/ledger/suntran; check other tables) | P1 |
| Concurrency: docid/vno generation is max+1 (no lock) → double-posting risk under concurrency | P2 |
| Laravel 10.50.2 is EOL (needs L12; see UPGRADE_PLAN) | P3 |
| Composer audit: verify current state (`composer audit`) | P3 |

## Testing approach (safe)

- Feature tests: **read-only against live `analysis` DB**, dynamic fixtures, skip when DB down. Never RefreshDatabase.
- Static scans: brace-aware parsers for transactions / permission guards / N+1 loops.
- Browser: Playwright available; use for UI smoke of key flows (login → navigate → form → save → DB verify).
