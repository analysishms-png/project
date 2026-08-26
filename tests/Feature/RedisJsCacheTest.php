<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CacheService;
use App\Http\Middleware\CacheReportFetch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RedisJsCacheTest extends TestCase
{
    /**
     * CacheService: store() returns 'file' when Redis is down.
     */
    public function test_store_falls_back_to_file_when_redis_down()
    {
        if (CacheService::redisUp()) {
            $this->markTestSkipped('Redis is running — cannot test fallback');
        }

        $store = CacheService::store();
        $this->assertContains($store, ['file', 'array'], 'Store should fall back when Redis is down');
    }

    /**
     * CacheService: put/get/forget cycle works on file driver.
     */
    public function test_cache_put_get_forget_cycle()
    {
        CacheService::put('test:cycle', 'hello', 60);
        $this->assertEquals('hello', CacheService::get('test:cycle'));

        CacheService::forget('test:cycle');
        $this->assertNull(CacheService::get('test:cycle'));
    }

    /**
     * CacheService: remember() executes callback and caches result.
     */
    public function test_remember_executes_callback()
    {
        $executed = false;
        $result = CacheService::remember('test:remember', 60, function () use (&$executed) {
            $executed = true;
            return 'computed';
        });

        $this->assertTrue($executed);
        $this->assertEquals('computed', $result);

        // Second call should hit cache, not re-execute callback
        $executed = false;
        $result2 = CacheService::remember('test:remember', 60, function () use (&$executed) {
            $executed = true;
            return 'new_value';
        });

        $this->assertFalse($executed);
        $this->assertEquals('computed', $result2);

        CacheService::forget('test:remember');
    }

    /**
     * CacheService: version() initializes to 1 and increments on bump.
     */
    public function test_version_and_bump()
    {
        // Reset by forgetting
        CacheService::forget('ver:test:version_group');

        $v1 = CacheService::version('test:version_group');
        $this->assertEquals(1, $v1);

        $v2 = CacheService::bump('test:version_group');
        $this->assertEquals(2, $v2);

        $v3 = CacheService::version('test:version_group');
        $this->assertEquals(2, $v3);

        CacheService::forget('ver:test:version_group');
    }

    /**
     * CacheService: purgeReports() bumps the rpt version.
     */
    public function test_purge_reports_increments_version()
    {
        CacheService::forget('ver:rpt:999');

        $v1 = CacheService::version('rpt:999');
        CacheService::purgeReports(999);
        $v2 = CacheService::version('rpt:999');

        $this->assertGreaterThan($v1, $v2);

        CacheService::forget('ver:rpt:999');
    }

    /**
     * CacheReportFetch: hashInput() is order-independent.
     */
    public function test_hash_input_order_independent()
    {
        $h1 = CacheReportFetch::hashInput(['b' => 2, 'a' => 1, 'c' => 3]);
        $h2 = CacheReportFetch::hashInput(['c' => 3, 'a' => 1, 'b' => 2]);
        $h3 = CacheReportFetch::hashInput(['a' => 1, 'b' => 2, 'c' => 3]);

        $this->assertEquals($h1, $h2, 'hashInput should be order-independent');
        $this->assertEquals($h2, $h3, 'hashInput should be order-independent');
    }

    /**
     * CacheReportFetch: hashInput() handles nested arrays.
     */
    public function test_hash_input_nested_arrays()
    {
        $h1 = CacheReportFetch::hashInput(['outer' => ['b' => 2, 'a' => 1]]);
        $h2 = CacheReportFetch::hashInput(['outer' => ['a' => 1, 'b' => 2]]);

        $this->assertEquals($h1, $h2, 'hashInput should sort nested keys');
    }

    /**
     * CacheReportFetch: hashInput() returns valid MD5.
     */
    public function test_hash_input_returns_md5()
    {
        $hash = CacheReportFetch::hashInput(['test' => 'value']);

        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $hash);
    }

    /**
     * CacheReportFetch: hashInput() produces different hashes for different inputs.
     */
    public function test_hash_input_different_for_different_data()
    {
        $h1 = CacheReportFetch::hashInput(['a' => 1]);
        $h2 = CacheReportFetch::hashInput(['a' => 2]);

        $this->assertNotEquals($h1, $h2);
    }

    /**
     * CacheService: reportRemember() caches and returns data.
     */
    public function test_report_remember_caches_data()
    {
        $executed = false;
        $result = CacheService::reportRemember('testMethod', 103, ['param1', 'param2'], 60, function () use (&$executed) {
            $executed = true;
            return ['rows' => [1, 2, 3]];
        });

        $this->assertTrue($executed);
        $this->assertEquals([1, 2, 3], $result['rows']);

        // Second call should be cached
        $executed = false;
        $result2 = CacheService::reportRemember('testMethod', 103, ['param1', 'param2'], 60, function () use (&$executed) {
            $executed = true;
            return ['rows' => [4, 5, 6]];
        });

        $this->assertFalse($executed);
        $this->assertEquals([1, 2, 3], $result2['rows']);
    }

    /**
     * CacheService: reportRemember() with different params returns different cache.
     */
    public function test_report_remember_different_params_different_cache()
    {
        $r1 = CacheService::reportRemember('testM', 103, ['a'], 60, fn() => 'first');
        $r2 = CacheService::reportRemember('testM', 103, ['b'], 60, fn() => 'second');

        $this->assertEquals('first', $r1);
        $this->assertEquals('second', $r2);
    }

    /**
     * CacheService: redisUp() returns boolean.
     */
    public function test_redis_up_returns_boolean()
    {
        $result = CacheService::redisUp();
        $this->assertIsBool($result);
    }

    /**
     * MasterDataCache: flush() clears the cache.
     */
    public function test_master_data_cache_flush()
    {
        if (class_exists(\App\Helpers\MasterDataCache::class)) {
            // Should not throw
            \App\Helpers\MasterDataCache::flush(103);
            $this->assertTrue(true);
        } else {
            $this->markTestSkipped('MasterDataCache class not found');
        }
    }
}
