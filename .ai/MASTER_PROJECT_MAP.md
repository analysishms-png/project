# Analysis HMS — MASTER PROJECT MAP

> Canonical project inventory. Rebuilt and verified **2026-08-16** from direct source inspection.
> Previous .ai docs (dated 2026-08-07) described work that is **not present in the repository** — this map reflects verified reality.

---

## 1. System Snapshot (VERIFIED)

| Component | Value |
|-----------|-------|
| PHP | 8.2.33 (CLI, ZTS VC19 x64) |
| Laravel | 10.50.2 (EOL — security fixes ended early 2025) |
| Composer | 2.10.2 |
| Database | MySQL — `db_analysishms` |
| Git | ✅ Repo exists, **1 commit** `67e9744` "Initial upload of Analysis HMS" |
| Controllers | 55 top-level + 64 subdirectory = **119 total** |
| Models | **162** |
| Migrations | **412** |
| Blade views | **549** |
| Middleware | **19** (custom auth stack: company, staff, superadmin, user, frontlogin, ApiAuth, ProtectRoute, SetToolsSessionLifetime, LogActivity, LogThirdPartyActivity…) |
| Route files | **14** (web, api, admin, company, property, reporting, tools, userparam, pointofsale, pointofsale/, channel, channels, console) |
| API routes | 13 (sanctum + ApiAuth middleware) |
| Tests | **27 passing (33 assertions)** — verified 2026-08-16 |
| Queue/Cache/Session | `sync` / `file` / `file` |
| Broadcasting | Laravel Reverb ^1.0 (config present; REVERB_APP_* keys empty in dev) |
| Services | `app/Services/AccountPosting.php` (accounting isolation) |
| Helpers | 8 files autoloaded: DateHelper, UpdateRepeat, ResHelper, Helpers, Gstr1, LogHelper, PrintHelper (+ WhatsappSend not in autoload files list) |

---

## 2. Controllers (55 top-level)

Key controllers and line counts (from prior analysis — re-verify before refactoring):
- `CompanyController` ~22.5K lines (god controller — largest)
- `PrintController` ~6K
- `InventoryController` ~5.9K
- `Reporting` ~5.4K
- `Banquet` ~4.7K
- `Tools/ToolsController` ~4.7K
- `Pointofsale` ~3.5K+

Subdirectories: `app/Http/Controllers/Auth`, `Frontend`, `SuperAdmin`, `Tools`, `Pointofsale` (and others).

## 3. Models (162)

Cover all core domains: Booking, GrpBookingDetails, RoomOcc, Guestfolio, Paycharge, PaychargeLog, PaychargeH, BookingSource, PlanMast, RoomCat, RoomMast, Revmast, Suntran, Ledger, Sale1, Sale2, Kot, HallSale1/2, HallBook, Stock, Purch1/2, Indent, Porder, Gin, Expsheet, VenueMast/Occ, SupportTicket, UserUpdate, Enviro* (General, Pos, Fom, Finance, Banquet, Inventory, Payroll, Whatsapp, Einvoice, Channel), Companyreg, User, MenuHelp, UserPermission, VoucherPrefix/Type, TaxStru, MemberCategory, Employee, States/Cities/Countries, etc.

All models define `$fillable` (mass-assignment safe — verified previously).

## 4. Views (549) — top-level layout groups

| Area | Purpose |
|------|---------|
| `resources/views/admin/` | Super-admin portal |
| `resources/views/property/` | Main hotel portal (FOM, reservations, billing, housekeeping, prints/) |
| `resources/views/tools/` | Support/tools portal (tickets, table management, data tools) |
| `resources/views/frontend/` | Public site, login pages, CMS pages |
| `resources/views/hr/` | HR/payroll |
| `resources/views/nightauditlog/` | Night audit |
| `resources/views/maintenance/`, `queries/`, `test/`, `emails/`, `components/` | Misc |

## 5. Route Files

| File | Middleware | Purpose |
|------|-----------|---------|
| `routes/web.php` | web | Main app |
| `routes/company.php` | web | Company portal |
| `routes/property.php` | web | Property portal |
| `routes/admin.php` | web | Admin/superadmin |
| `routes/pointofsale.php` + `pointofsale/` | pointofsale group | POS |
| `routes/reporting.php` | reporting group | Reports |
| `routes/userparam.php` | userparam group | User parameters |
| `routes/tools.php` | **tools group (NO auth middleware — controllers self-guard)** | Tools/support |
| `routes/channel.php` | channel group | Channel manager |
| `routes/api.php` | api + sanctum/ApiAuth | 13 API endpoints |
| `routes/channels.php` / `console.php` | — | Broadcast channels / schedule |

## 6. Middleware (19)

TrustHosts, TrustProxies, PreventRequestsDuringMaintenance, EncryptCookies, AddQueuedCookiesToResponse (core), StartSession (core), ShareErrorsFromSession (core), VerifyCsrfToken (core), SubstituteBindings (core), Authenticate, company, staff, superadmin, user, frontlogin, ApiAuth, ProtectRoute, SetToolsSessionLifetime, LogActivity, LogThirdPartyActivity, TrimStrings, RedirectIfAuthenticated, ValidateSignature.

> ⚠️ The `tools` middleware group contains **session/CSRF only — no auth**. Access control for all ToolsController routes is enforced by a constructor middleware (auth + superadmin-or-property-20 check). Verified 2026-08-16.

## 7. Key Services / Logic

- `app/Services/AccountPosting.php` — accounting posting isolation
- `app/Helpers/*` — 8 autoloaded helper files
- Events: `SaleBillPrintEvent`, `PrintEvent` (Reverb broadcasting)
- No Jobs / Observers / Policies / Form Requests / Notifications (controller-inline logic)

## 8. Legacy References (.ai)

| File | Content |
|------|---------|
| `.ai/HMS.bas` / `.ai/HMS.text` | ~995K lines VB6 legacy HMS source (**151 forms**) |
| `.ai/visahl.sql` | UTF-16 SQL Server dump `VishalDataNew2627` (legacy schema — `Suntran` matches Laravel) |
| `.ai/modules/*.md` | 11 module docs (FRONT_OFFICE, POS, INVENTORY, FINANCE, HOUSEKEEPING, BANQUET, HR, REPORTS, TOOLS, ADMIN, CHANNEL, EINVOICE) |

## 9. Verification State (2026-08-16)

- ✅ `php artisan test` → **27 passed (33 assertions)**
- ✅ `php artisan view:cache` → all 549 blade templates compile
- ✅ BUG-022 (stored XSS in ticket views) — FIXED
- ✅ BUG-023 (dynamic SQL in ToolsController) — VERIFIED SAFE (auth-gated, names validated)
- ⚠️ `.ai` docs dated 2026-08-07 overstate repo state — CHANGELOG_AI.md tracks actual verified work

## 10. Next Actions (see .ai/NEXT_TASK.md)

Highest-priority safe work queue is maintained in `.ai/NEXT_TASK.md`.
