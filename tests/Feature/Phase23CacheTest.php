<?php

namespace Tests\Feature;

use App\Helpers\MasterDataCache;
use App\Services\CacheService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PDOException;
use Tests\TestCase;

/**
 * Phase 2 (master-data cache extension) + Phase 3 (report result cache)
 * regression tests (REDIS_JS_PLAN.md).
 *
 * Read-only against seeded data; skipped when the DB is unreachable.
 */
class Phase23CacheTest extends TestCase
{
    /** @var \PDO|null */
    private $pdo = null;

    private $userId = null;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            $this->pdo = DB::connection()->getPdo();
        } catch (PDOException $e) {
            $this->markTestSkipped('Database unavailable — skipping phase 2/3 cache tests: ' . $e->getMessage());
        }

        $this->userId = (int) $this->pdo
            ->query("SELECT id FROM users WHERE propertyid = 103 AND u_name = 'sa' LIMIT 1")
            ->fetchColumn();
        if (!$this->userId) {
            $this->markTestSkipped('Fixture user sa@103 not found.');
        }
    }

    public function test_outlets_cache_matches_direct_query_and_flush_clears()
    {
        Auth::loginUsingId($this->userId);

        foreach ([false, true] as $roomServiceToo) {
            $cached = MasterDataCache::outlets('103', $roomServiceToo);

            $q = DB::table('depart')->where('propertyid', '103');
            if ($roomServiceToo) {
                $q->whereIn('nature', ['Outlet', 'Room Service']);
            } else {
                $q->where('nature', 'Outlet');
            }
            $direct = $q->orderBy('name', 'ASC')->get();

            $this->assertEquals(
                $direct->pluck('dcode')->all(),
                $cached->pluck('dcode')->all(),
                "outlets(roomServiceToo=$roomServiceToo) diverged from the live query"
            );

            $key = 'masterdata.103.outlets' . ($roomServiceToo ? '.rs' : '');
            $this->assertTrue(Cache::store()->has($key), "$key should exist after first read");
        }

        MasterDataCache::flush('103');

        foreach (['masterdata.103.outlets', 'masterdata.103.outlets.rs'] as $key) {
            $this->assertFalse(Cache::store()->has($key), "$key should be gone after flush()");
        }
    }

    public function test_header_companies_cache_matches_direct_query()
    {
        Auth::loginUsingId($this->userId);

        $cached = MasterDataCache::headerCompanies('103');

        $direct = DB::table('company')
            ->where('propertyid', '103')
            ->orderBy('comp_code', 'ASC')
            ->get();

        $this->assertEquals(
            $direct->pluck('comp_code')->all(),
            $cached->pluck('comp_code')->all(),
            'headerCompanies diverged from the live query'
        );
    }

    public function test_report_middleware_caches_fetch_response_and_purges()
    {
        Auth::loginUsingId($this->userId);
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        $payload = ['fromdate' => '2026-01-01', 'todate' => '2026-12-31'];

        $res1 = $this->post('/detailedtrialledger/fetch', $payload);
        $this->assertEquals(200, $res1->getStatusCode());
        $this->assertJson($res1->getContent());
        $body1 = json_decode($res1->getContent(), true);
        $this->assertArrayHasKey('data', $body1);

        // Replicate the committed middleware key to prove the response was stored.
        $username = Auth::user()->name;
        $ver1 = CacheService::version('rpt:103');
        $key1 = 'rptresp:103:' . $username . ':' . $ver1 . ':'
            . Str::slug('detailedtrialledger/fetch') . ':'
            . \App\Http\Middleware\CacheReportFetch::hashInput($payload);

        $stored = CacheService::get($key1);
        $this->assertNotNull($stored, 'middleware should have cached the fetch response');
        $this->assertEquals($body1['data'], $stored['body']['data']);

        // A purge bumps the report version, invalidating every old key instantly.
        CacheService::purgeReports('103');
        $ver2 = CacheService::version('rpt:103');
        $this->assertGreaterThan($ver1, $ver2, 'purgeReports must bump the report version');

        $key2 = 'rptresp:103:' . $username . ':' . $ver2 . ':'
            . Str::slug('detailedtrialledger/fetch') . ':'
            . \App\Http\Middleware\CacheReportFetch::hashInput($payload);
        $this->assertNull(CacheService::get($key2), 'new version key must start empty');

        $res2 = $this->post('/detailedtrialledger/fetch', $payload);
        $this->assertEquals(200, $res2->getStatusCode());
    }
}
