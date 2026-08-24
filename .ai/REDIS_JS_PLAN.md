================================================================================
        REDIS + JAVASCRIPT ADOPTION PLAN - ANALYSIS HMS
================================================================================
Date: 2026-08-23 | Status: PLANNED | Priority: MEDIUM-HIGH
Rule (from promt.txt): Never break existing functionality. Every phase must be
independently deployable + revertable.

--------------------------------------------------------------------------------
1. CURRENT STATE AUDIT (verified 2026-08-23)
--------------------------------------------------------------------------------
REDIS:
  - composer.json: NO predis/predis, NO phpredis ext (php -m: not installed)
  - .env: REDIS_HOST=127.0.0.1 configured BUT drivers are file/file/sync
    (CACHE_DRIVER=file, SESSION_DRIVER=file, QUEUE_CONNECTION=sync)
  - Redis server NOT running on port 6379 (TcpTestSucceeded=False)
  - Cache:: usage only 10 places | Redis facade usage: 0 places

JS:
  - 416 of 438 property views already have <script> blocks
  - 22 views without JS are mostly print/CSS partials (legitimately no-JS):
    billprinttable, dailyreportprint, dashboardcss, salebillprint2css,
    salebillprint2ctype2ss, taxmasterprint, mrprinting, pdf_expense,
    blankgrcform, advancereceipt, banquetparameter, datanotfound, etc.
  - Interactive pages still using full-page reloads / no auto-fetch:
    chainreport, channelavailability, channeldashboard, lookupdashboard,
    revenueratecomparison, planmaster, stateform, roomtest
  - DUPLICATION PROBLEM: fmt()/fmtDate()/radioVal() copy-pasted into every
    report blade (Batch A/B/C = 23+ copies). No shared JS file exists
    (public/js is empty).

HOTSPOT (biggest win):
  - revokeopen() defined at app/Helpers/Helpers.php:110 is called 631 times
    across controllers -> EVERY page load runs a fresh menuhelp DB query.
    This is the #1 Redis candidate.

--------------------------------------------------------------------------------
2. PHASE PLAN
--------------------------------------------------------------------------------
PHASE 0 - REDIS RUNTIME SETUP (foundation, ~30 min)
  [ ] composer require predis/predis (pure-PHP client; phpredis ext is
      painful on Windows/XAMPP, predis avoids DLL issues)
  [ ] Start Redis server locally: use Memurai or tporadowski/redis build
      (redis-server.exe on 127.0.0.1:6379); add to XAMPP startup notes
  [ ] Create App\Services\RedisHealth wrapper: checks PING once per request;
      if Redis down -> silently fall back to file cache (never fatal)
  [ ] Switch .env ONLY after health-check passes: CACHE_DRIVER=redis first.
      Sessions/Queue stay on file/sync until Phase 5.
  ROLLBACK: set CACHE_DRIVER=file (one line)

PHASE 1 - PERMISSION CACHE (highest impact, ~45 min)
  [ ] Wrap revokeopen(): Cache::remember("perm:{prop}:{user}:{code}", 300s)
      - Helpers.php:110 only change; all 631 call sites benefit free
  [ ] Invalidate: flush perm:* keys wherever menuhelp rows are
      inserted/updated/deleted (UserController, permission screens)
      Use Cache tag "perms" if driver supports; else key-prefix delete loop
  [ ] TEST: login page load time before/after; permission change reflects
      within <=5 min or instantly after invalidation

PHASE 2 - MASTER DATA CACHE (~45 min)
  [ ] Cache rarely-changing lookups (TTL 10-15 min):
      companyreg (property header data), rest_code outlet lists,
      revmast/subgroup dropdown lists used by reports
  [ ] Add Cache::remember in the shared helper functions / model scopes
  [ ] Invalidate on respective master CRUD saves

PHASE 3 - REPORT RESULT CACHE (~60 min)
  [ ] Batch A/B/C fetch methods: Cache::remember(
        "rpt:{method}:{prop}:{fd}:{td}:{filters-hash}", 60s)
  [ ] Purge strategy: any controller POST that mutates folio/sale/ledger/
      stock tables calls CacheService::purgeReports($propertyid)
  [ ] Keep DataTables client-side as-is; cache only JSON payloads

PHASE 4 - REALTIME / ADVANCED (optional, later)
  [ ] Redis pub/sub for KOT notifications (Kot.php) instead of AJAX polling
  [ ] Rate-limit counters for login attempts (ThrottleRequests redis driver)
  [ ] Laravel Horizon only if queue volume justifies it (currently sync)

PHASE 5 - SESSIONS + QUEUE ON REDIS (only after 1 month stable)
  [ ] SESSION_DRIVER=redis, QUEUE_CONNECTION=redis
  [ ] WARNING: logs out all users once during switch; schedule off-hours

--------------------------------------------------------------------------------
3. JAVASCRIPT PLAN
--------------------------------------------------------------------------------
PHASE J-A - SHARED HELPERS LIBRARY (~40 min)
  [ ] Create public/js/hms-report.js with ONE copy of:
        hmsFmt(v), hmsFmtDate(d), hmsRadioVal(name),
        hmsAutoFetch(bindSelector, fn), hmsTableInit(id, opts)
  [ ] Register in resources/views/property/layouts/main.blade.php via
      <script src="{{ asset('js/hms-report.js') }}"></script> (after jQuery)
  [ ] No behaviour change; library is additive

PHASE J-B - DEDUPLICATE BATCH A/B/C BLADES (~90 min)
  [ ] Replace inline fmt/fmtDate/radioVal definitions in 23 report blades
      with hms-* equivalents (mechanical find-replace, blade-by-blade verify)
  [ ] TEST each report page manually after refactor (fetch + radios + totals)

PHASE J-C - UPGRADE REMAINING INTERACTIVE PAGES (~2-3 hrs)
  [ ] chainreport            -> date range + radio (chain-wise/all) + auto-fetch
  [ ] channelavailability    -> radio (OTA/direct/all) + live availability grid
  [ ] channeldashboard       -> auto-refresh cards via AJAX + Redis cached counts
  [ ] lookupdashboard        -> tabbed AJAX panels
  [ ] revenueratecomparison  -> dual-date pickers + plan radio + chart toggle
  [ ] Follow existing reference pattern: bookingdetail.blade.php
      (btn-group-toggle radios + radioVal + auto-fetch on change)
EXCLUDED from J-C (intentionally no-JS print/CSS partials):
  billprinttable, dailyreportprint, dashboardcss, salebillprint2css,
  salebillprint2ctype2ss, taxmasterprint, mrprinting, pdf_expense,
  blankgrcform, datanotfound, updatetaxform, stateform (simple forms)

--------------------------------------------------------------------------------
4. EXECUTION ORDER & EFFORT
--------------------------------------------------------------------------------
  Week 1: Phase 0 + Phase 1 + Phase J-A          (~2 hrs total)
  Week 2: Phase J-B + Phase 2                    (~2.5 hrs)
  Week 3: Phase 3 + Phase J-C                    (~4 hrs)
  Later:  Phases 4-5 optional

SAFETY RULES (every phase):
  - php -l + route:list + blade compile sweep after changes
  - Regression suites: test_batch_a2.php, test_batch_b.php, test_batch_c.php
  - Redis failure must NEVER break a page (health-wrapper fallback)
  - Commit per phase; changelog entry per phase (.ai/CHANGELOG_AI.md)
================================================================================
