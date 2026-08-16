# Analysis HMS — SECURITY AUDIT

> Audit log. Verified state 2026-08-16. Continuous — re-run on every significant change.

---

## Status summary (2026-08-16)

| # | Finding | Severity | Status |
|---|---------|----------|--------|
| SEC-01 | No version control | HIGH | ✅ RESOLVED (git init, 1 baseline commit) |
| SEC-02 | APP_DEBUG=true / APP_ENV=local | MEDIUM | ⚠️ OPEN — deploy-time; do not flip on dev machine |
| SEC-03 | Stored XSS — `{!! $ticket->problem !!}` (3 views) | HIGH | ✅ **FIXED 2026-08-16** (`nl2br(e(...))`) |
| SEC-04 | Raw CMS output `{!! $page->description/content !!}` | LOW | ⚠️ ACCEPTABLE if page editor is superadmin-only — verify editor role |
| SEC-05 | Raw JSON in inline scripts (header.blade.php:670,768 — session printdata/bills) | LOW-MED | ⚠️ REVIEW — server-generated; verify session payload sources |
| SEC-06 | Dynamic SQL in Reporting/CheckRegister (`{$code}`,`{$alias}`,`{$cgst}`…) | MEDIUM | ⚠️ VERIFY input sources ($seqrevcode from revmast/propertyid — server-side); aliases preg_replace-sanitized |
| SEC-07 | Raw `SHOW TABLES`/`SHOW COLUMNS` in ToolsController | MEDIUM | ✅ **VERIFIED SAFE 2026-08-16** — whitelist + DB-introspection validation |
| SEC-08 | `whereRaw($sqlWhere)` user-supplied in ToolsController | MEDIUM | ✅ **VERIFIED SAFE 2026-08-16** — auth-gated (superadmin/property-20 only); by-design DB tool |
| SEC-09 | `shell_exec('chmod -R 777')` (MainController, SetFolderPermissions) | LOW | ⚠️ OPEN — arg escaped; consider 755 |
| SEC-10 | Sensitive-ish INFO logging (PythonRoomKeyController) | LOW | ⚠️ OPEN — review log levels |
| SEC-11 | EOL laravel/framework 10.50.2 (unfixable CVEs on L10) | HIGH* | ⚠️ OPEN — requires L12 upgrade (UPGRADE_PLAN.md) |
| SEC-12 | yajra datatables ^10.11 in AIKIDO-2025-10705 RCE range | MEDIUM | ⚠️ OPEN — upgrade to ^12.6/^13 (deferred to upgrade plan) |
| SEC-13 | phpMyAdmin/XAMPP default exposure (dev) | LOW | ⚠️ OPEN — deployment checklist |

*Laravel 10 EOL = no security patches; the single highest-priority strategic item (requires approval — major version upgrade).

## Verified good

- All models use `$fillable` — mass-assignment safe
- Config files use `env()` only — no committed secrets
- `.env` in `.gitignore`
- CSRF active on web/pointofsale/tools/kot/reporting/userparam/channel groups
- Custom auth middleware stack (company/staff/superadmin/user/frontlogin/ApiAuth)
- No `dd()`/`dump()`/`var_dump()` leftovers
- ToolsController constructor auth gate verified (SEC-07/08)

## OWASP mapping (from prior analysis)

| OWASP | Finding |
|-------|---------|
| A03 Injection | SEC-06/07/08 — verified/needs verification as above |
| A03 XSS | SEC-03 FIXED; SEC-04/05 review |
| A01 Broken access | tools routes lack group auth — mitigated by controller guard (verify on every new ToolsController method) |
| A05 Misconfig | SEC-02 debug; SEC-09 777 |
| A06 Vulnerable components | SEC-11/12/13 |

## Deployment checklist (pre-production)

1. ✅ git repo exists (add CI)
2. ⬜ `APP_ENV=production`, `APP_DEBUG=false`
3. ⬜ Strong DB credentials
4. ⬜ `composer audit` clean (framework EOL remains → plan L12)
5. ⬜ REVERB_APP_* real keys + TLS (wss) if realtime used
6. ⬜ HTTPS, disable directory listing, remove phpMyAdmin exposure
7. ⬜ Redis for cache/session/queue (or documented tradeoff)
8. ⬜ Error monitoring (Sentry/Flare)
9. ⬜ Backup automation + restore drill
