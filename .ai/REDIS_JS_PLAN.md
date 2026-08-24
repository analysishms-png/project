================================================================================
        REDIS + JAVASCRIPT ADOPTION PLAN - ANALYSIS HMS
================================================================================
Date: 2026-08-23 | Status: PHASES 0-3 + JS ROLLOUT COMPLETE (2026-08-24) | Priority: MEDIUM-HIGH
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
  [x] composer require predis/predis (DONE 2026-08-24; v3.6; constraint
      pinned ^3.6 in composer.json)
  [x] Start Redis server locally: tporadowski/redis v5.0.14.1 at
      C:\xampp\redis (redis-server.exe on 127.0.0.1:6379). NOTE: start
      manually after reboot:
      Start-Process C:\xampp\redis\redis-server.exe -ArgumentList '--port','6379'
  [x] Create App\Services\RedisHealth wrapper: implemented as
      CacheService::redisUp() (150ms fsockopen probe, cached per request)
      + ResilientCacheManager (extends CacheManager) registered via
      app->extend('cache') in AppServiceProvider — resolve('redis')
      silently redirects to file when the daemon is down. All ~90 direct
      Cache:: call sites protected; fallback verified by killing the server.
  [x] Switch .env ONLY after health-check passes: CACHE_DRIVER=redis,
      REDIS_CLIENT=predis. Sessions/Queue stay file/sync until Phase 5.
      TIP: Laravel's cache connection uses Redis DB 1 (REDIS_CACHE_DB) —
      inspect keys with `redis-cli -n 1`, db 0 looks empty.
  ROLLBACK: set CACHE_DRIVER=file (one line)

PHASE 1 - PERMISSION CACHE (highest impact, ~45 min)
  [x] Wrap revokeopen(): DONE via CacheService::remember with per-user +
      per-property version keys ("perm:{prop}:p{pv}:{user}:{code}:u{uv}",
      300s) — Helpers.php only change; all 631 call sites benefit free
  [x] Invalidate: permCacheBump($prop, $user|'*') bumps group versions;
      driver-agnostic (works on file + redis). Call from menuhelp mutation
      screens.
  [ ] TEST: login page load time before/after; permission change reflects
      within <=5 min or instantly after invalidation
      (instant after bump by design; before/after timing still to measure)

PHASE 2 - MASTER DATA CACHE (~45 min)
  [x] Cache rarely-changing lookups: outlets (+Room Service variant),
      header company switcher list (company table — Companyreg model maps
      there, NOT a companyreg table), plus pre-existing travelagents/
      corporates/companiesagents/rooms/fomcharges
  [x] Add Cache::remember in shared helpers: MasterDataCache::outlets() /
      headerCompanies(); header ViewComposer swapped to cached list;
      12 identical Depart list queries in InventoryController + 2 in
      Reporting replaced with MasterDataCache::outlets($pid, true).
      (groupBy('dcode') variants left as-is — different semantics.)
  [x] Invalidate on respective master CRUD saves: MasterDataCache::flush()
      extended to clear outlets/.rs/headercompanies keys; flush wired into
      Pos depart insert/delete + CompanyController outletsetupupdate.
  BUG FIXED: headerCompanies initially queried DB::table('companyreg')
  which does not exist (model $table = 'company') — caught by new test.

PHASE 3 - REPORT RESULT CACHE (~60 min)
  [x] Batch A/B/C fetch methods: committed CacheReportFetch middleware
      (POST + path-contains-fetch guard, JSON-only, version-keyed,
      60s TTL, per-user keys) already covered routes/reporting.php via the
      'reporting' middleware group; this session aliased it as
      'report.cache' and attached to the 19 finance X/fetch POST routes +
      fetchhousekeepingstatusreport in routes/company.php (verified via
      route:list -v)
  [x] Purge strategy: CacheService::purgeReports() bumps "rpt:{prop}"
      version; wired into ChargePosting accountpoststore, nightaudit,
      room settle, POS sale submit/update/nillsettle, Kot x2, MR entry,
      stock transfer (pre-existing), PLUS submitledger/updateledgerstore,
      banquet advance/billing/performa submit+update+editAdvance,
      purchasebillsubmit/purchasebillupdate (this session). 60s TTL bounds
      staleness for anything unwired.
  [x] Keep DataTables client-side as-is; cache only JSON payloads
      (middleware stores decoded JSON body; non-JSON/4xx/5xx never stored)
  BUG FIXED: Finance\FinanceController constructor read uninitialized
  $this->propertyid -> users lookup with NULL -> every finance /fetch
  endpoint fataled 500. Now seeded from Auth::user()->propertyid.

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
  [x] Create public/js/hms-report.js with ONE copy of:
        hmsFmt(v), hmsFmtDate(d), hmsRadioVal(name),
        hmsAutoFetch(bindSelector, fn), hmsTableInit(id, opts)
      (DONE 2026-08-24; also hmsDmy(d) + window aliases fmt/fmtDate/dmy/radioVal
      that only apply when the blade does not define its own)
  [x] Register in resources/views/property/layouts/main.blade.php via
      <script src="{{ asset('js/hms-report.js') }}"></script> (after jQuery)
      (registered in property/layouts/header.blade.php:471, loaded on every page)
  [x] No behaviour change; library is additive

PHASE J-B - DEDUPLICATE BATCH A/B/C BLADES (~90 min)
  [x] Replace inline fmt/fmtDate/radioVal definitions in report blades
      with hms-* equivalents (blade-by-blade verified; DONE 2026-08-24):
      - fmt removed: guestwiseanalysis, advreconreport
      - fmtDate removed: cashiersettlement, coveranalysis, guestpayments,
        roomchangehistory, taxsummarypos, taxreportinv,
        chequenotclearedregister, chequeclearedregister
      - dmy removed: advreconreport, complimentaryreport, taxreporpos
        (hmsDmy added to library + window.dmy alias)
      - radioVal: no local definitions remain anywhere; 12 blades already
        call the global alias
      - Intentionally KEPT locals (different semantics — changing would
        alter output): arrivaldep/expecteddep/roomoccdisp (string
        passthrough v||''), occupancyforecast ('0', no grouping),
        taxreport (dash separators), chequenotclearedregister +
        chequeclearedregister fmt (western grouping regex vs en-IN)
  [x] Regression: full suite green (81 passed / 212 assertions incl. new
      JC endpoint tests) + view:cache compile sweep clean

PHASE J-C - UPGRADE REMAINING INTERACTIVE PAGES (~2-3 hrs)
  [x] chainreport            -> date range + radio (all properties / chain
                                 total) + auto-fetch via chain/report/data JSON
  [x] channelavailability    -> radio (all categories / OTA-mapped) + live grid
                                 refresh via channel/availability/data JSON;
                                 week-nav links unchanged
  [x] channeldashboard       -> KPI cards auto-refresh every 60s via
                                 channel/dashboard/counts (Redis-cached 60s in
                                 controller through CacheService::remember) +
                                 manual refresh button + "updated hh:mm" stamp
  [x] lookupdashboard        -> tabbed UI: Quick Links | Live Summary (AJAX
                                 invdashboard/summary, Redis-cached 60s);
                                 5 cards pointing at non-existent routes
                                 (pendingindent, pendingpurchaseorder,
                                 supplierwisepurchase, getPurchaseAmount,
                                 miniusstock) were FATALING at render via
                                 route() helper — now disabled with
                                 "Setup pending" badge
  [x] revenueratecomparison  -> occtype radio (singleuser/multiuser) + view
                                 toggle (table/cards) + auto-fetch via
                                 revenue/rate-comparison/data
  [x] New endpoint smoke tests: tests/Feature/JCEndpointsSmokeTest.php (5 tests)

  BUGS FIXED DURING J-C (pages were broken before the JS work):
  - ChannelPush::dashboard() referenced unqualified `ChannelPushes` class
    (resolved to App\Http\Controllers\ChannelPushes = missing) => fatal on
    page load. Added `use App\Models\ChannelPushes;`.
  - ChannelPush::dashboard() compact('ncurdate') referenced an undefined
    local variable. Removed from compact list.
  - RevenueManagementController queried channelrate.rate — column/table
    shape does not exist (channelrate is a push-log). Now uses latest
    channelderived.price as the channel rate.
  - ChannelPush availability queries selected room_cat.totalroom — column
    does not exist (real column: norooms). Selected via COALESCE(norooms,0)
    AS totalroom so blade/JSON keys stay stable.

EXCLUDED from J-C (intentionally no-JS print/CSS partials):
  billprinttable, dailyreportprint, dashboardcss, salebillprint2css,
  salebillprint2ctype2ss, taxmasterprint, mrprinting, pdf_expense,
  blankgrcform, datanotfound, updatetaxform, stateform (simple forms)

REDIS SETUP NOTES (Phase 0, done 2026-08-24):
  - Server: tporadowski/redis v5.0.14.1 extracted to C:\xampp\redis.
    Start manually after reboot:
      Start-Process C:\xampp\redis\redis-server.exe -ArgumentList '--port','6379'
  - predis/predis v3.6 installed; .env has REDIS_CLIENT=predis and
    CACHE_DRIVER=redis (SESSION_DRIVER stays file until Phase 5).
  - App\Services\ResilientCacheManager (wired in AppServiceProvider::register)
    resolves ANY Cache::store('redis') to the file store while
    CacheService::redisUp() fails -> ~90 direct Cache:: call sites are safe.
  - NOTE: Laravel's cache connection uses Redis DB 1 (config
    database.redis.cache.database default '1'); redis-cli defaults to db 0,
    use `redis-cli -n 1` to inspect cached keys.

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
